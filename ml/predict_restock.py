# Script to perform restock predictions using the trained model

import pandas as pd
import joblib
import os
from ml.database import fetch_data, QUERY_COMPOSITIONS, QUERY_PRODUCT_RECIPES, QUERY_SALES, get_db_connection # Import get_db_connection
from ml.config import MODEL_DIR, PREDICTIONS_FILE # Import config values
from sqlalchemy import text
from datetime import datetime
from dateutil.relativedelta import relativedelta

def generate_restock_predictions():
    """
    Loads the trained model, fetches necessary data, makes predictions,
    and saves the results to the database and a CSV file.
    """
    print("Starting restock prediction generation...")

    # --- Load Model ---
    model_path = os.path.join(MODEL_DIR, 'random_forest_restock.pkl')
    if not os.path.exists(model_path):
        print(f"Error: Trained model not found at {model_path}. Please run train_model.py first.")
        return

    try:
        model = joblib.load(model_path)
        print("Trained model loaded successfully.")
    except Exception as e:
        print(f"Error loading model from {model_path}: {e}")
        return

    # --- Fetch Data for Prediction ---
    print("Fetching current data for prediction...")
    try:
        compositions_df = fetch_data("SELECT id, composition_code, name, unit, current_stock, minimum_stock FROM compositions")
        
        # Fetch sales data for the current month to calculate recent usage
        current_month_str = datetime.now().strftime('%Y-%m')
        
        # Query for sales in the current month
        # NOTE: Adjust SQL dialect if necessary (e.g., DATE_FORMAT for MySQL)
        sales_current_month_query = f"""
            SELECT s.sale_date, sd.product_id, sd.qty, sd.price, sd.subtotal
            FROM sales s
            JOIN sale_details sd ON s.id = sd.sale_id
            WHERE strftime('%Y-%m', s.sale_date) = '{current_month_str}'
        """
        sales_current_month_df = fetch_data(sales_current_month_query)

        recipes_df = fetch_data("SELECT product_id, composition_id, quantity_used FROM product_recipes")
        
        if compositions_df.empty:
            print("Error: Compositions data is empty. Cannot generate predictions.")
            return
        if recipes_df.empty or sales_current_month_df.empty:
            print("Warning: Sales or recipe data is missing. Usage calculation might be zero.")
            # Proceeding with available data, but results might be less accurate.
            
    except Exception as e:
        print(f"Error fetching data for prediction: {e}")
        return

    # --- Feature Engineering for Prediction ---
    # This logic MUST mirror the feature engineering done in train_model.py
    print("Engineering features for prediction...")
    
    # Calculate total usage for the current month
    composition_usage_month_df = pd.DataFrame()
    if not recipes_df.empty and not sales_current_month_df.empty:
        merged_sales_recipes = pd.merge(sales_current_month_df, recipes_df, left_on='product_id', right_on='product_id', how='inner')
        merged_sales_recipes['total_usage'] = merged_sales_recipes['qty'] * merged_sales_recipes['quantity_used']
        
        # Ensure sale_date is datetime for period extraction
        merged_sales_recipes['sale_date_dt'] = pd.to_datetime(merged_sales_recipes['sale_date'])
        merged_sales_recipes['sale_year_month'] = merged_sales_recipes['sale_date_dt'].dt.to_period('M')

        composition_usage_month_df = merged_sales_recipes.groupby(['composition_id', 'sale_year_month'])['total_usage'].sum().reset_index()
        composition_usage_month_df.rename(columns={'total_usage': 'total_usage_month'}, inplace=True)
        print(f"Calculated monthly usage for {len(composition_usage_month_df)} composition-month entries.")
    else:
        print("Not enough data for usage calculation.")

    # Prepare a DataFrame for prediction based on current composition data
    prediction_df = compositions_df.copy()
    prediction_df['current_month_period'] = datetime.now().strftime('%Y-%m') # Period for current month

    # Merge with current stock data and aggregated current month usage
    prediction_df = pd.merge(prediction_df, composition_usage_month, left_on=['id', 'current_month_period'], right_on=['composition_id', 'sale_year_month'], how='left')
    
    # Fill NaNs from merge
    prediction_df['total_usage_month'] = prediction_df['total_usage_month'].fillna(0)
    
    # Calculate features used in training
    prediction_df['stock_gap'] = prediction_df['current_stock'] - prediction_df['minimum_stock']
    
    # Placeholders for features that require more historical data aggregation
    # For prediction, we'll use current data or simple proxies.
    prediction_df['incoming_stock_month'] = 0 # Placeholder: Needs historical aggregation
    prediction_df['outgoing_stock_month'] = prediction_df['total_usage_month'] # Proxy: Current month usage as outgoing
    prediction_df['usage_last_month'] = 0 # Placeholder: Needs historical data
    prediction_df['avg_usage_3_month'] = 0 # Placeholder: Needs historical data
    prediction_df['related_best_seller_sales'] = 0 # Placeholder: Needs analysis of product sales related to composition

    # Define features to be used for prediction
    # THESE MUST EXACTLY MATCH THE FEATURES USED DURING TRAINING (from train_model.py)
    # Feature columns include those from compositions_df and engineered ones.
    feature_columns = [
        'id', # composition_id, needed to link results
        'current_stock',
        'minimum_stock',
        'stock_gap',
        'total_usage_month',
        'incoming_stock_month', # Placeholder value
        'outgoing_stock_month', # Proxy value
        'usage_last_month',     # Placeholder value
        'avg_usage_3_month',    # Placeholder value
        'related_best_seller_sales' # Placeholder value
    ]
    
    # Filter for columns that actually exist in prediction_df
    # Ensure 'id' (composition_id) is kept for linking back
    existing_features_for_pred = [f for f in feature_columns if f in prediction_df.columns]
    
    if 'id' not in existing_features_for_pred:
        print("Error: 'id' (composition_id) is missing in prediction data. Cannot link results.")
        return
    
    model_features = [f for f in existing_features_for_pred if f != 'id'] # Features for the model

    if not model_features:
        print("Error: No valid features found for prediction after filtering.")
        return

    X_predict = prediction_df[model_features]
    
    # Fill any remaining NaNs in features before prediction
    X_predict = X_predict.fillna(X_predict.mean(numeric_only=True))

    # --- Make Predictions ---
    try:
        print(f"Making predictions using model: {model_path}")
        predictions = model.predict(X_predict)
        probabilities = model.predict_proba(X_predict)[:, 1] if hasattr(model, "predict_proba") else [None] * len(X_predict)

        # Map predictions to labels (should match labels used in training)
        # Assuming binary classification: 0 -> Low Priority, 1 -> High Priority
        label_map = {0: 'Low Priority', 1: 'High Priority'} 
        predicted_labels = [label_map.get(p, 'Unknown') for p in predictions]
        
        print(f"Generated {len(predictions)} predictions.")

        # --- Save Predictions ---
        # Save results to the database and CSV file.
        
        conn = get_db_connection()
        current_period = datetime.now().strftime('%Y-%m') # e.g., '2026-04'

        # Clear previous predictions for the current period to avoid duplicates
        delete_query = text(f"DELETE FROM restock_predictions WHERE period = '{current_period}'")
        with conn.begin(): # Use a transaction for safety
            conn.execute(delete_query)
        
        print(f"Cleared previous predictions for period {current_period}.")

        # Prepare data for insertion into the database
        prediction_results_for_db = []
        for index, row in prediction_df.iterrows():
            composition_id = row['id'] # This is the composition ID from compositions_df
            label = predicted_labels[index] if index < len(predicted_labels) else 'Unknown'
            probability = probabilities[index] if probabilities is not None and index < len(probabilities) else None
            
            # Assign recommendation score based on label (example mapping)
            score_map = {'High Priority': 90, 'Medium Priority': 60, 'Low Priority': 30, 'Unknown': 0}
            recommendation_score = score_map.get(label, 0)

            notes = f"ML Prediction for {current_period}."

            prediction_results_for_db.append({
                'composition_id': composition_id,
                'period': current_period,
                'predicted_label': label,
                'probability': probability,
                'recommendation_score': recommendation_score,
                'notes': notes,
                'created_at': datetime.now(),
                'updated_at': datetime.now()
            })

        if prediction_results_for_db:
            pred_db_df = pd.DataFrame(prediction_results_for_db)
            # Use to_sql to insert into the database
            pred_db_df.to_sql('restock_predictions', con=engine, if_exists='append', index=False)
            print(f"Successfully saved {len(prediction_results_for_db)} predictions to the database.")
        else:
            print("No predictions generated to save to database.")

        # Save predictions to CSV as well (as per structure)
        if 'pred_db_df' in locals() and not pred_db_df.empty:
            pred_db_df.to_csv(PREDICTIONS_FILE, index=False)
            print(f"Predictions also saved to {PREDICTIONS_FILE}")

    except Exception as e:
        print(f"Error during prediction process or saving results: {e}")
        # Log the exception for debugging
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    # Ensure necessary directories exist before running
    os.makedirs(os.path.dirname(MODEL_DIR), exist_ok=True)
    os.makedirs(os.path.dirname(PREDICTIONS_FILE), exist_ok=True)
    os.makedirs(os.path.dirname(EVALUATION_FILE), exist_ok=True) # Ensure evaluation dir exists too
    
    generate_restock_predictions()
    print("Prediction script finished.")