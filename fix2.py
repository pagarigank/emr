import os, shutil

src = '/e/sithemr/module-screenshots'
dst = '/e/sithemr/SSSITHEMR'
cwd = '/e/sithemr'

# Use os.scandir for reliable listing
count = 0
for entry in os.scandir(src):
    if entry.is_file() and entry.name.endswith('.png'):
        s = entry.path
        d = os.path.join(dst, entry.name)
        if os.path.isfile(s):
            shutil.copy2(s, d)
            count += 1

print('Copied ' + str(count) + ' PNGs from module-screenshots into SSSITHEMR')

# Also copy into CWD
cwcount = 0
for entry in os.scandir(dst):
    if entry.is_file() and entry.name.endswith('.png'):
        s = entry.path
        d = os.path.join(cwd, entry.name)
        if os.path.isfile(s):
            shutil.copy2(s, d)
            cwcount += 1

print('Copied ' + str(cwcount) + ' PNGs into CWD')
print('CWD PNG count: ' + str(len([f for f in os.listdir(cwd) if f.endswith('.png')])))
print('SSSITHEMR PNG count: ' + str(len([f for f in os.listdir(dst) if f.endswith('.png')])))
