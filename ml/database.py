# Database interaction module for ML scripts

import os
import pandas as pd
from sqlalchemy import create_engine, text

# --- Configuration Loading ---
# In a real application, sensitive details like passwords should not be hardcoded.
# This example assumes configuration is managed externally (e.g., environment variables or a config file).
# For now, we'll use placeholder values that would need to be configured.

# Using environment variables is recommended for real-world applications.
# For demonstration, we'll use values that would typically come from Laravel's .env
# This part needs to be configured to match your Laravel's database settings.

# Example placeholder values. These should ideally be loaded securely.
DB_HOST = os.environ.get('DB_HOST', '127.0.0.1')
DB_PORT = os.environ.get('DB_PORT', '3306')
DB_DATABASE = os.environ.get('DB_DATABASE', 'pos_parfum_restock')
DB_USERNAME = os.environ.get('DB_USERNAME', 'root')
DB_PASSWORD = os.environ.get('DB_PASSWORD', '') # Leave empty if no password

# Constructing the database URL
# For MySQL, the format is: mysql://user:password@host:port/database
# If password is empty, it's handled correctly by create_engine
db_url = f"mysql://{DB_USERNAME}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_DATABASE}"

try:
    engine = create_engine(db_url)
except Exception as e:
    print(f"Error creating database engine: {e}")
    engine = None

def get_db_connection():
    """
    Establishes and returns a database connection engine.
    """
    if engine is None:
        raise Exception("Database engine not initialized.")
    return engine

def fetch_data(query: str) -> pd.DataFrame:
    """
    Fetches data from the database using a given SQL query and returns a pandas DataFrame.
    """
    if engine is None:
        raise Exception("Database engine not initialized. Cannot fetch data.")
    
    try:
        with engine.connect() as connection:
            df = pd.read_sql(text(query), connection)
        return df
    except Exception as e:
        print(f"Error executing query: {e}")
        return pd.DataFrame() # Return empty DataFrame on error

# Example queries (these would be used by build_dataset.py)
# Example: Fetch sales data
QUERY_SALES = """
SELECT s.id, s.invoice_number, s.sale_date, s.total_amount, sd.product_id, sd.qty, sd.price, sd.subtotal
FROM sales s
JOIN sale_details sd ON s.id = sd.sale_id
"""

# Example: Fetch composition stock data
QUERY_COMPOSITIONS = """
SELECT id, composition_code, name, unit, current_stock, minimum_stock
FROM compositions
"""

# Example: Fetch product recipes
QUERY_PRODUCT_RECIPES = """
SELECT product_id, composition_id, quantity_used
FROM product_recipes
"""

# Example: Fetch stock movements
QUERY_STOCK_MOVEMENTS = """
SELECT composition_id, type, qty, stock_before, stock_after, movement_date
FROM stock_movements
"""
