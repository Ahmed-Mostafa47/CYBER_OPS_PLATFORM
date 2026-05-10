import os
import re

dir_path = r'c:\xampp\htdocs\HackMe\server\helpers\whitebox'
for filename in os.listdir(dir_path):
    if filename.endswith('_verify.php'):
        filepath = os.path.join(dir_path, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Fix the syntax error created by previous regex
        new_content = re.sub(r"'Wrong answer'\s*\],\s*'patched'", r"'Wrong answer', 'patched'", content)
        
        # Also, check if there are any other specific messages left that didn't get replaced
        # We can just run a better regex to replace any remaining ones:
        # Match `['ok' => false, 'message' => ` followed by anything up to `, 'patched'` or `]`
        # Actually, let's just use a simpler regex:
        # replace `['ok' => false, 'message' => $some_var, 'patched' => ...]`
        
        def repl(m):
            return m.group(1) + "'Wrong answer'" + m.group(3)
            
        new_content = re.sub(r"(\['ok'\s*=>\s*false\s*,\s*'message'\s*=>\s*)(.+?)(,\s*'patched'\s*=>\s*[^\]]+\]|\])", repl, new_content, flags=re.DOTALL)
        
        if content != new_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Fixed {filename}")
