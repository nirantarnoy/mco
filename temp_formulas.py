import glob
import openpyxl

f = glob.glob(r'C:\Users\niran.w\Downloads\*HD*.xlsx')[0]
print('File:', f)
wb = openpyxl.load_workbook(f, data_only=False)
ws = wb.active

formulas = []
for row in ws.iter_rows(min_row=1, max_row=20, min_col=1, max_col=20):
    for cell in row:
        if isinstance(cell.value, str) and cell.value.startswith('='):
            formulas.append(f'Sheet: {ws.title}, Cell {cell.coordinate}: {cell.value}')

if formulas:
    for form in formulas[:20]:
        print(form)
else:
    print('No formulas found in the first 20 rows of active sheet.')
