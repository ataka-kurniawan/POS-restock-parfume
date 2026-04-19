# Configuration file for ML module

# Database connection details (will be read from Laravel's .env or provided securely)
# Example:
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=pos_parfum_restock
# DB_USERNAME=root
# DB_PASSWORD=

# Path to the Laravel project root (if needed for file access)
LARAVEL_PROJECT_ROOT = '..' 

# Path to the Python scripts directory
ML_DIR = '.'

# Paths for model and outputs
MODEL_DIR = 'ml/models'
OUTPUT_DIR = 'ml/outputs'

# Model file name
MODEL_FILE = 'random_forest_restock.pkl'

# Dataset file name for training
DATASET_FILE = 'outputs/dataset.csv'

# Prediction results file name
PREDICTIONS_FILE = 'outputs/predictions.csv'

# Model evaluation results file name
EVALUATION_FILE = 'outputs/evaluation.json'
