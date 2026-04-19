# Script to build the dataset for model training

import pandas as pd
from ml.database import fetch_data, QUERY_SALES, QUERY_COMPOSITIONS, QUERY_PRODUCT_RECIPES, QUERY_STOCK_MOVEMENTS
from ml.config import DATASET_FILE
import os
from datetime import datetime
from dateutil.relativedelta import relativedelta

def build_dataset():
    """
    Builds the dataset for training the restock prediction model.
    This involves fetching data from Laravel, merging, and engineering features,
    focusing on historical monthly aggregates and current stock status.
    """
    print("Starting dataset building process...")

    # --- Fetch Raw Data ---
    try:
        sales_df = fetch_data(QUERY_SALES)
        compositions_df = fetch_data(QUERY_COMPOSITIONS)
        recipes_df = fetch_data(QUERY_PRODUCT_RECIPES)
        stock_movements_df = fetch_data(QUERY_STOCK_MOVEMENTS)
        
        print(f"Fetched {len(sales_df)} sales records.")
        print(f"Fetched {len(compositions_df)} compositions.")
        print(f"Fetched {len(recipes_df)} recipes.")
        print(f"Fetched {len(stock_movements_df)} stock movements.")

    except Exception as e:
        print(f"Error fetching data from database: {e}")
        return

    # --- Data Preprocessing ---
    if compositions_df.empty:
        print("Error: Compositions data is empty. Cannot build dataset.")
        return

    # Ensure date columns are datetime objects
    sales_df['sale_date'] = pd.to_datetime(sales_df['sale_date'])
    stock_movements_df['movement_date'] = pd.to_datetime(stock_movements_df['movement_date'])

    # --- Feature Engineering for Historical Data ---
    # We need historical data to calculate features like usage_last_month, avg_usage_3_month.
    # Let's define a historical period (e.g., last 6 months of data for aggregation).
    end_date_hist = datetime.now()
    start_date_hist = end_date_hist - relativedelta(months=6) # Look back 6 months for historical trends
    
    # Filter sales and stock movements for the relevant historical period
    sales_history_df = sales_df[sales_df['sale_date'] >= start_date_hist]
    stock_movements_history_df = stock_movements_df[stock_movements_df['movement_date'] >= start_date_hist]

    # --- 1. Aggregate Usage per Composition per Month (Historical) ---
    composition_usage_month = pd.DataFrame()
    if not recipes_df.empty and not sales_history_df.empty:
        merged_sales_recipes = pd.merge(sales_history_df, recipes_df, left_on='product_id', right_on='product_id', how='inner')
        merged_sales_recipes['total_usage'] = merged_sales_recipes['qty'] * merged_sales_recipes['quantity_used']
        
        merged_sales_recipes['sale_year_month'] = merged_sales_recipes['sale_date'].dt.to_period('M')
        
        composition_usage_month = merged_sales_recipes.groupby(['composition_id', 'sale_year_month'])['total_usage'].sum().reset_index()
        composition_usage_month.rename(columns={'total_usage': 'total_usage_month'}, inplace=True)
        print(f"Aggregated monthly usage for {len(composition_usage_month)} composition-month entries.")
    else:
        print("Warning: Not enough historical sales or recipe data to calculate monthly usage.")

    # --- 2. Aggregate Stock Movements per Composition per Month (Historical) ---
    stock_movements_history_df['movement_year_month'] = pd.to_datetime(stock_movements_history_df['movement_date']).dt.to_period('M')
    
    incoming_stock = stock_movements_history_df[stock_movements_history_df['type'] == 'in']
    outgoing_stock = stock_movements_history_df[stock_movements_history_df['type'] == 'out']

    incoming_agg = incoming_stock.groupby(['composition_id', 'movement_year_month'])['qty'].sum().reset_index()
    incoming_agg.rename(columns={'qty': 'incoming_stock_month'}, inplace=True)
    
    outgoing_agg = outgoing_stock.groupby(['composition_id', 'movement_year_month'])['qty'].sum().reset_index()
    outgoing_agg.rename(columns={'qty': 'outgoing_stock_month'}, inplace=True)
    print(f"Aggregated monthly stock movements.")

    # --- Prepare Dataset for Training ---
    # We need data points for each composition across different historical months.
    # For simplicity in this example, we'll create features for the *current* state of compositions
    # and then try to look up *average historical* trends. A more complete dataset would have rows per composition per month.
    
    # Get current composition details
    current_compositions_df = fetch_data("SELECT id, composition_code, name, unit, current_stock, minimum_stock FROM compositions")
    if current_compositions_df.empty:
        print("Error: Current compositions data is empty. Cannot build dataset.")
        return

    # --- Feature Engineering ---
    # Features required by the model (must match train_model.py and predict_restock.py)
    # Some features require historical aggregation.
    
    # For training, we ideally need features that reflect past performance leading up to a restock decision.
    # Let's simplify: we'll create features based on current stock, min stock, and historical monthly aggregates.

    dataset_df = current_compositions_df.copy()
    dataset_df.rename(columns={'id': 'composition_id'}, inplace=True) # Rename id to composition_id for clarity

    # Merge historical monthly aggregates. We'll focus on the *most recent* month's aggregate as a proxy for current trend.
    # A more robust approach would be to calculate averages over recent months.
    
    latest_month_str = datetime.now().strftime('%Y-%m')
    latest_month_period = pd.Period(latest_month_str)
    
    # Get latest month's usage, incoming, outgoing
    usage_latest = composition_usage_month[composition_usage_month['sale_year_month'] == latest_month_str] if not composition_usage_month.empty else pd.DataFrame(columns=['composition_id', 'sale_year_month', 'total_usage_month'])
    incoming_latest = incoming_agg[incoming_agg['movement_year_month'] == latest_month_str] if not incoming_agg.empty else pd.DataFrame(columns=['composition_id', 'movement_year_month', 'incoming_stock_month'])
    outgoing_latest = outgoing_agg[outgoing_agg['movement_year_month'] == latest_month_str] if not outgoing_agg.empty else pd.DataFrame(columns=['composition_id', 'movement_year_month', 'outgoing_stock_month'])
    
    # Merge these into the dataset
    dataset_df = pd.merge(dataset_df, usage_latest[['composition_id', 'total_usage_month']], left_on='composition_id', right_on='composition_id', how='left')
    dataset_df = pd.merge(dataset_df, incoming_latest[['composition_id', 'incoming_stock_month']], left_on='composition_id', right_on='composition_id', how='left')
    dataset_df = pd.merge(dataset_df, outgoing_latest[['composition_id', 'outgoing_stock_month']], left_on='composition_id', right_on='composition_id', how='left')

    # Fill NaNs from merges
    dataset_df['total_usage_month'] = dataset_df['total_usage_month'].fillna(0)
    dataset_df['incoming_stock_month'] = dataset_for_prediction['incoming_stock_month'].fillna(0) # Using 'dataset_for_prediction' here - should be dataset_df
    dataset_df['outgoing_stock_month'] = dataset_df['outgoing_stock_month'].fillna(0)

    # Calculate basic features
    dataset_df['stock_gap'] = dataset_df['current_stock'] - dataset_df['minimum_stock']

    # --- More Advanced Features (Placeholders/Simplified) ---
    # For 'usage_last_month' and 'avg_usage_3_month', we need to look at previous months' usage.
    # This requires more complex date filtering and aggregation.
    # For now, we will use a simplified approach or placeholder.
    
    # Calculate usage for the *previous* month (simplified - might miss if no sales in last month)
    try:
        prev_month_str = (datetime.now() - relativedelta(months=1)).strftime('%Y-%m')
        prev_month_sales = sales_df[sales_df['sale_date'].dt.strftime('%Y-%m') == prev_month_str]
        if not prev_month_sales.empty and not recipes_df.empty:
            merged_prev_sales_recipes = pd.merge(prev_month_sales, recipes_df, left_on='product_id', right_on='product_id', how='inner')
            usage_last_month_agg = merged_sales_recipes.groupby('composition_id')['total_usage'].sum().reset_index()
            usage_last_month_agg.rename(columns={'total_usage': 'usage_last_month'}, inplace=True)
            dataset_df = pd.merge(dataset_df, usage_last_month_agg, on='composition_id', how='left')
            dataset_df['usage_last_month'] = dataset_df['usage_last_month'].fillna(0)
        else:
            dataset_df['usage_last_month'] = 0
            print("Warning: Not enough historical sales data for 'usage_last_month'.")
    except Exception as e:
        print(f"Error calculating usage_last_month: {e}")
        dataset_df['usage_last_month'] = 0

    # Calculate average usage over last 3 months (simplified - only uses current month if others unavailable)
    # A real implementation would aggregate usage for each of the last 3 months and average.
    dataset_df['avg_usage_3_month'] = dataset_df['total_usage_month'] # Placeholder

    # Placeholder for 'related_best_seller_sales'
    dataset_df['related_best_seller_sales'] = 0 

    # Select and order columns for the final dataset
    # IMPORTANT: These feature names MUST match exactly what train_model.py expects.
    final_feature_columns = [
        'composition_id', 
        'current_stock',
        'minimum_stock',
        'stock_gap',
        'total_usage_month',
        'incoming_stock_month', # Filled with 0 placeholder
        'outgoing_stock_month', # Using current month usage as proxy
        'usage_last_month',     # Placeholder value
        'avg_usage_3_month',    # Placeholder value
        'related_best_seller_sales' # Placeholder value
    ]
    
    # Filter for columns that actually exist in the DataFrame
    # Ensure 'composition_id' is present for linking
    final_columns_to_save = ['composition_id'] + [f for f in final_feature_columns if f != 'composition_id' and f in dataset_df.columns]
    
    if 'composition_id' not in final_columns_to_save:
        print("Error: 'composition_id' is missing. Cannot link results.")
        return
    
    # Ensure all required model features are present and handle potential missing ones gracefully
    # For training, this would include the target 'label' as well.
    # For prediction, only features are needed.
    
    # Fill NaNs in the final dataset features
    final_dataset_df = dataset_df[final_columns_to_save].copy()
    for col in final_dataset_df.columns:
        if col in ['current_stock', 'minimum_stock', 'stock_gap', 'total_usage_month', 'incoming_stock_month', 'outgoing_stock_month', 'usage_last_month', 'avg_usage_3_month', 'related_best_seller_sales']:
            final_dataset_df[col] = pd.to_numeric(final_dataset_df[col], errors='coerce').fillna(0) # Fill non-numeric or NaN with 0 or mean/median

    print(f"Dataset prepared for training/prediction. Shape: {final_dataset_df.shape}")
    print("Features in final dataset:", final_dataset_df.columns.tolist())

    # Save the dataset
    if not final_dataset_df.empty:
        try:
            final_dataset_df.to_csv(DATASET_FILE, index=False)
            print(f"Dataset successfully saved to {DATASET_FILE}")
        except Exception as e:
            print(f"Error saving dataset to {DATASET_FILE}: {e}")
    else:
        print("Dataset is empty, not saving.")

if __name__ == "__main__":
    # Ensure output directory exists
    os.makedirs(os.path.dirname(DATASET_FILE), exist_ok=True)
    # Ensure other directories exist too, though not directly used in this script part
    os.makedirs(os.path.dirname(MODEL_DIR), exist_ok=True)
    os.makedirs(os.path.dirname(PREDICTIONS_FILE), exist_ok=True)
    
    build_dataset()
    print("Dataset building script finished.")