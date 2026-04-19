# Script to train the Random Forest model

import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score, precision_score, recall_score, f1_score
import joblib
import os
import json
from ml.config import DATASET_FILE, MODEL_DIR, EVALUATION_FILE, PREDICTIONS_FILE # Ensure all config paths are imported
# Assuming ml.database is available if needed, but training typically uses the saved dataset.

def train_model():
    """
    Trains the Random Forest model using the dataset.
    """
    print("Starting model training process...")

    # --- Load Dataset ---
    if not os.path.exists(DATASET_FILE):
        print(f"Error: Dataset file not found at {DATASET_FILE}. Please ensure ml/outputs/dataset.csv exists. Run build_dataset.py first.")
        return

    try:
        df = pd.read_csv(DATASET_FILE)
        print(f"Dataset loaded successfully. Shape: {df.shape}")
    except Exception as e:
        print(f"Error loading dataset from {DATASET_FILE}: {e}")
        return

    # --- Feature Engineering and Label Creation ---
    # Ensure required columns for features and label are present.
    # The 'label' column needs to be created based on business rules.
    
    # Defining features and target based on build_dataset.py and the brief:
    # Features: 'current_stock', 'minimum_stock', 'stock_gap', 'total_usage_month',
    # 'incoming_stock_month', 'outgoing_stock_month', 'usage_last_month', 
    # 'avg_usage_3_month', 'related_best_seller_sales'
    # Target: 'label' (1 for restock priority, 0 otherwise)

    # --- Target Variable Creation (Label Engineering) ---
    # Rule: label = 1 if stock is near or below minimum AND usage is high.
    # Refined logic for creating the 'label' column:
    # - Label = 1 if (stock_gap < 0 AND total_usage_month > 0) OR (current_stock is critically low AND total_usage_month > 0)
    # - Label = 0 otherwise.

    # Ensure required columns are numeric and handle NaNs
    numeric_cols = ['current_stock', 'minimum_stock', 'stock_gap', 'total_usage_month', 
                    'incoming_stock_month', 'outgoing_stock_month', 'usage_last_month', 
                    'avg_usage_3_month', 'related_best_seller_sales']
    for col in numeric_cols:
        if col in df.columns:
            df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0) # Fill NaNs with 0 or mean/median as appropriate
        else:
            print(f"Warning: Feature '{col}' not found in dataset. It might be a placeholder or missing from build_dataset.py.")
            df[col] = 0 # Assign default value if missing

    # Ensure minimum_stock has a reasonable default if all are NaN (though it should be handled by build_dataset)
    if df['minimum_stock'].isnull().all():
        df['minimum_stock'] = 0 # Or a default value
    
    # Re-calculate stock_gap after ensuring numeric and handling NaNs
    df['stock_gap'] = df['current_stock'] - df['minimum_stock']
    
    # Define conditions for restock priority (label = 1)
    # Condition 1: Stock is below minimum AND there was recent usage
    condition_below_min_and_used = (df['stock_gap'] < 0) & (df['total_usage_month'] > 0)
    
    # Condition 2: Current stock is critically low (e.g., less than 10% of minimum_stock) AND there was recent usage
    # Added a check to prevent division by zero if minimum_stock is 0
    condition_critically_low = (df['minimum_stock'] > 0) & (df['current_stock'] < df['minimum_stock'] * 0.1) & (df['total_usage_month'] > 0)
    # If minimum_stock is 0, critically low might be just current_stock <= 0
    condition_critically_low_no_min = (df['minimum_stock'] == 0) & (df['current_stock'] <= 0) & (df['total_usage_month'] > 0)

    # Combine conditions for restock priority (label = 1)
    df['label'] = ((condition_below_min_and_used) | (condition_critically_low) | (condition_critically_low_no_min)).astype(int)
    
    print("Target variable 'label' created based on stock gap, current stock, and usage.")

    print(f"Dataset shape after label engineering: {df.shape}")
    print("Columns:", df.columns.tolist())
    print("Label distribution:
", df['label'].value_counts())

    # Define features (X) and target (y)
    # IMPORTANT: Features MUST exactly match those used during training and prediction.
    features = [
        'current_stock', 'minimum_stock', 'stock_gap', 'total_usage_month',
        'incoming_stock_month', 'outgoing_stock_month', 'usage_last_month', 
        'avg_usage_3_month', 'related_best_seller_sales'
    ]
    
    # Filter features to only include those that actually exist in the DataFrame
    existing_features = [f for f in features if f in df.columns]

    if not existing_features or 'label' not in df.columns:
        print("Error: Essential features or target variable ('label') are missing.")
        print(f"Available columns: {df.columns.tolist()}")
        return

    X = df[existing_features]
    y = df['label']

    # Handle potential missing values in features (e.g., fill with mean or median)
    X = X.fillna(X.mean(numeric_only=True))

    # Split data into training and testing sets
    # Stratify is important for classification tasks, especially with imbalanced datasets.
    try:
        # Check if there are enough samples and classes for splitting
        if len(y.unique()) > 1 and len(y_train.index) > 1 and len(y_test.index) > 1:
            X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y)
            print(f"Data split complete. Training data shape: {X_train.shape}, Test data shape: {X_test.shape}")
        else:
            print("Warning: Dataset is too small or has only one class. Using all data for training. Evaluation might be unreliable.")
            X_train, y_train = X, y
            X_test, y_test = X, y # Using same data for test if split fails
    except ValueError as e:
        print(f"Error during train_test_split: {e}. Using all data for training.")
        X_train, y_train = X, y
        X_test, y_test = X, y # Using same data for test if split fails

    # --- Model Training ---
    class_weight_param = None
    try:
        if len(y_train.unique()) > 1: # Only check imbalance if there are multiple classes
            counts = y_train.value_counts()
            if len(counts) > 1 and counts.min() / counts.max() < 0.5:
                print("Dataset appears imbalanced. Using class_weight='balanced'.")
                class_weight_param = 'balanced'
            else:
                print("Dataset class distribution appears balanced.")
        else:
            print("Dataset has only one class, cannot check for imbalance.")
    except Exception as e:
        print(f"Could not check for class imbalance: {e}. Using default class_weight.")

    model = RandomForestClassifier(random_state=42, class_weight=class_weight_param)
    
    print("Training RandomForestClassifier...")
    model.fit(X_train, y_train)
    print("Model training complete.")

    # --- Model Evaluation ---
    print("Evaluating model performance...")
    # Only evaluate if test set exists and is different from training set
    if not X_test.empty and len(y_test.unique()) > 1: # Check if test set has data and multiple classes
        y_pred = model.predict(X_test)

        print("
Model Evaluation:")
        try:
            accuracy = accuracy_score(y_test, y_pred)
            precision = precision_score(y_test, y_pred, average='weighted', zero_division=0)
            recall = recall_score(y_test, y_pred, average='weighted', zero_division=0)
            f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
            cm = confusion_matrix(y_test, y_pred)
            cr = classification_report(y_test, y_pred, zero_division=0)

            print(f"Accuracy: {accuracy:.4f}")
            print(f"Precision (weighted): {precision:.4f}")
            print(f"Recall (weighted): {recall:.4f}")
            print(f"F1-Score (weighted): {f1:.4f}")

            print("
Confusion Matrix:")
            print(cm)
            print("
Classification Report:")
            print(cr)

            # Save evaluation results
            evaluation_metrics = {
                'accuracy': accuracy,
                'precision': precision,
                'recall': recall,
                'f1_score': f1,
                'confusion_matrix': cm.tolist(),
                'classification_report': cr
            }

            os.makedirs(os.path.dirname(EVALUATION_FILE), exist_ok=True)
            with open(EVALUATION_FILE, 'w') as f:
                json.dump(evaluation_metrics, f, indent=4)
            print(f"Evaluation results saved to {EVALUATION_FILE}")
            
        except ValueError as ve:
            print(f"Skipping detailed evaluation due to error: {ve}")
            print("This might happen if the test set is empty or has only one class.")
            evaluation_metrics = {'error': str(ve)}
            os.makedirs(os.path.dirname(EVALUATION_FILE), exist_ok=True)
            with open(EVALUATION_FILE, 'w') as f:
                json.dump(evaluation_metrics, f, indent=4)
            print(f"Evaluation results (error) saved to {EVALUATION_FILE}")
    else:
        print("Skipping evaluation as test set is not valid or has only one class.")
        evaluation_metrics = {'message': 'Skipped evaluation due to insufficient test data or single class.'}
        os.makedirs(os.path.dirname(EVALUATION_FILE), exist_ok=True)
        with open(EVALUATION_FILE, 'w') as f:
            json.dump(evaluation_metrics, f, indent=4)
        print(f"Evaluation summary (skipped) saved to {EVALUATION_FILE}")


    # --- Save Model ---
    os.makedirs(os.path.dirname(MODEL_DIR), exist_ok=True)
    model_path = os.path.join(MODEL_DIR, 'random_forest_restock.pkl')
    try:
        joblib.dump(model, model_path)
        print(f"Model successfully saved to {model_path}")
    except Exception as e:
        print(f"Error saving model to {model_path}: {e}")

if __name__ == "__main__":
    # Ensure output directories exist before running
    os.makedirs(os.path.dirname(DATASET_FILE), exist_ok=True)
    os.makedirs(os.path.dirname(PREDICTIONS_FILE), exist_ok=True) # Predictions file is for later stages
    os.makedirs(os.path.dirname(EVALUATION_FILE), exist_ok=True)
    
    train_model()
    print("Model training script finished.")