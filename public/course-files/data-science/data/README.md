# Nkwen Traders datasets (I-NNOVA Data Science track)

Fictional sales data for Nkwen Traders Ltd., a retail chain around Bamenda. Prices are in FCFA.

| File | Used in | What it is |
|---|---|---|
| nkwen_traders_sales.csv | Milestones 1 and 3 | Chapter 1: one store, Jan-Jun 2026, 500 sales, a little messy |
| nkwen_week_sales.py / .json | Milestone 2 | One week of sales as a Python list of dictionaries |
| nkwen_daily_sales.py | Milestone 2 | The 10 daily sales totals used by the Python workbook |
| nkwen_traders_ch2_sales.csv | Milestone 4 | Chapter 2: four branches, Jan-Jun 2026 |
| nkwen_traders_branches.csv | Milestone 4 | Branch reference table (region, city, manager) |
| nkwen_traders_ch3_sales_raw.csv | Milestone 5 | Chapter 3: Jul-Sep 2026, raw and messy, to clean |

Load any CSV in Colab or Kaggle with:

```python
import pandas as pd
df = pd.read_csv("https://innovakickstarter.com/course-files/data-science/data/nkwen_traders_ch2_sales.csv")
```
