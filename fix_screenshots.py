import os, shutil

src_dirs = ['/e/sithemr/module-screenshots', '/e/sithemr/opd-screenshots']
dst = '/e/sithemr/SSSITHEMR'
cwd = '/e/sithemr'

# Clean SSSITHEMR of old files
for f in os.listdir(dst):
    fp = os.path.join(dst, f)
    if os.path.isfile(fp) and f.endswith('.png'):
        os.remove(fp)

# Copy all PNGs from source dirs into SSSITHEMR
total = 0
for src_dir in src_dirs:
    if os.path.isdir(src_dir):
        for f in os.listdir(src_dir):
            if f.endswith('.png'):
                s = os.path.join(src_dir, f)
                d = os.path.join(dst, f)
                if os.path.isfile(s):
                    shutil.copy2(s, d)
                    total += 1

print(f'Copied {total} PNGs into SSSITHEMR')

# Also copy into CWD for the build script
copied = 0
for f in os.listdir(dst):
    if f.endswith('.png'):
        s = os.path.join(dst, f)
        d = os.path.join(cwd, f)
        if os.path.isfile(s):
            shutil.copy2(s, d)
            copied += 1

print(f'Copied {copied} PNGs into CWD')
print(f'CWD PNGs: {len([f for f in os.listdir(cwd) if f.endswith(".png")])}')
print(f'SSSITHEMR PNGs: {len([f for f in os.listdir(dst) if f.endswith(".png")])}')
