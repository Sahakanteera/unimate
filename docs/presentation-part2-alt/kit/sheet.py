"""Combine rendered page PNGs into 2x2 contact sheets: python -I sheet.py <png-dir>"""
import glob
import os
import sys

from PIL import Image

d = sys.argv[1]
fs = sorted(glob.glob(os.path.join(d, 'p*.png')))
for k in range(0, len(fs), 4):
    c = Image.new('RGB', (1920, 1080), 'white')
    for j, f in enumerate(fs[k:k + 4]):
        c.paste(Image.open(f).convert('RGB').resize((960, 540)), ((j % 2) * 960, (j // 2) * 540))
    c.save(os.path.join(d, f'sheet{k // 4:02d}.png'))
print(len(fs))
