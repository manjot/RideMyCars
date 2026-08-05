import os
import re

views_dir = '/Users/manjotsingh/RideMyCars/laravel_web/resources/views'

replacements = {
    'bg-white': 'bg-white dark:bg-[#111]',
    'text-gray-900': 'text-gray-900 dark:text-white',
    'text-gray-800': 'text-gray-800 dark:text-white',
    'text-gray-700': 'text-gray-700 dark:text-gray-300',
    'text-gray-600': 'text-gray-600 dark:text-gray-400',
    'text-gray-500': 'text-gray-500 dark:text-gray-400',
    'text-gray-400': 'text-gray-400 dark:text-gray-500',
    'bg-gray-50': 'bg-gray-50 dark:bg-[#1a1a1a]',
    'bg-gray-100': 'bg-gray-100 dark:bg-[#222]',
    'border-gray-100': 'border-gray-100 dark:border-white/10',
    'border-gray-200': 'border-gray-200 dark:border-white/10',
    'border-gray-300': 'border-gray-300 dark:border-white/20',
    'hover:bg-gray-50': 'hover:bg-gray-50 dark:hover:bg-white/5',
    'hover:text-gray-900': 'hover:text-gray-900 dark:hover:text-white',
}

def replace_classes(match):
    classes = match.group(1).split()
    new_classes = []
    
    for c in classes:
        new_classes.append(c)
        if c in replacements:
            dark_part = replacements[c].split(' ')[1]
            if dark_part not in classes and dark_part not in new_classes:
                new_classes.append(dark_part)
                
    seen = set()
    final_classes = []
    for c in new_classes:
        if c not in seen:
            seen.add(c)
            final_classes.append(c)
            
    return 'class="' + ' '.join(final_classes) + '"'


def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    original = content
    content = re.sub(r'class="([^"]+)"', replace_classes, content)
        
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, _, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.blade.php'):
            process_file(os.path.join(root, file))

print("Done.")
