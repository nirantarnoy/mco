import os

file_path = r'e:\xampp\htdocs\mco\backend\views\quotation\_form.php'

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Based on previous analysis:
# Lines 0-171 (1-172 in editor) are the first block.
# Lines 172-343 (173-344 in editor) are the duplicate block.
# Lines 344-end (345-end in editor) are the rest.

# We want to keep 0-171 and 344-end.
# Let's verify the content at the boundary.

print(f"Line 172 (index 171): {lines[171].strip()}")
print(f"Line 173 (index 172): {lines[172].strip()}")
print(f"Line 344 (index 343): {lines[343].strip()}")
print(f"Line 345 (index 344): {lines[344].strip()}")

# If line 173 starts with <?php, it's the duplicate.
if lines[172].strip() == '<?php':
    print("Detected duplicate block starting at line 173.")
    # Keep lines before index 172, and lines from index 344 onwards.
    # Wait, if line 344 (index 343) is 'CSS;', then the next line (index 344) is the start of the rest.
    new_lines = lines[:172] + lines[344:]
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.writelines(new_lines)
    print("File fixed.")
else:
    print("Duplicate block not found at expected location.")
