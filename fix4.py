import os, shutil

src = 'E:/sithemr/module-screenshots'
dst = '/e/sithemr/SSSITHEMR'
cwd = '/e/sithemr'

count = 0
for entry in os.scandir(src):
    if entry.is_file() and entry.name.lower().endswith('.png'):
        s = entry.path
        d = os.path.join(dst, entry.name)
        if os.path.isfile(s):
            try:
                shutil.copy2(s, d)
                count += 1
            except Exception as e:
                pass

print('Copied ' + str(count) + ' PNGs from module-screenshots into SSSITHEMR')

cwcount = 0
for entry in os.scandir(dst):
    if entry.is_file() and entry.name.lower().endswith('.png'):
        s = entry.path
        d = os.path.join(cwd, entry.name)
        if os.path.isfile(s):
            try:
                shutil.copy2(s, d)
                cwcount += 1
            except:
                pass

print('Copied ' + str(cwcount) + ' into CWD')
print('CWD PNGs: ' + str(len([f for f in os.listdir(cwd) if f.lower().endswith('.png')])))
print('SSSITHEMR PNGs: ' + str(len([f for f in os.listdir(dst) if f.lower().endswith('.png')])))
