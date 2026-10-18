import os
dst = '/e/sithemr/SSSITHEMR'
pngs = sorted([f for f in os.listdir(dst) if f.lower().endswith('.png')])
total = sum(os.path.getsize(os.path.join(dst, f)) for f in pngs)
print('SSSITHEMR: ' + str(len(pngs)) + ' PNGs, ' + str(total/1024) + ' KB')
for f in pngs:
    sz = os.path.getsize(os.path.join(dst, f))
    print('  ' + f + '  ' + str(sz) + ' B')
