import os, shutil

# Get opd-screenshots too
src = 'E:/sithemr/opd-screenshots'
dst = '/e/sithemr/SSSITHEMR'

if os.path.isdir(src):
    count = 0
    for entry in os.scandir(src):
        if entry.is_file() and entry.name.lower().endswith('.png'):
            s = entry.path
            d = os.path.join(dst, entry.name)
            if os.path.isfile(s):
                try:
                    shutil.copy2(s, d)
                    count += 1
                except:
                    pass
    print('opd-screenshots: ' + str(count) + ' PNGs copied')
else:
    print('opd-screenshots not found at ' + src)

# Final count
cwd = '/e/sithemr'
sss_count = len([f for f in os.listdir(dst) if f.lower().endswith('.png')])
cwd_count = len([f for f in os.listdir(cwd) if f.lower().endswith('.png')])
print('SSSITHEMR total: ' + str(sss_count))
print('CWD total: ' + str(cwd_count))
