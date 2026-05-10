import os
import re

dir_path = r'c:\xampp\htdocs\HackMe\server\helpers\whitebox'
for filename in os.listdir(dir_path):
    if filename.endswith('_verify.php'):
        filepath = os.path.join(dir_path, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # We want to find arrays returning ok => false and replace their message with 'Wrong answer'
        # Example: ['ok' => false, 'message' => 'Line number is out of range...']
        # We can match `['ok' => false, 'message' => ...` up to `,` or `]`
        
        def repl(m):
            # m.group(1) is the `['ok' => false, 'message' => `
            # m.group(2) is the value
            return m.group(1) + "'Wrong answer'"
            
        new_content = re.sub(r"(\['ok'\s*=>\s*false\s*,\s*'message'\s*=>\s*)(.+?)(?=\s*,\s*'patched'|\s*\])", repl, content, flags=re.DOTALL)
        
        if content != new_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Updated {filename}")
