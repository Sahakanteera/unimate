"""Merge a version's slide PDF and appendix PDF into the Classroom PDF.
usage: python -I -X utf8 merge_pdf.py <version-dir> <prefix> <title>"""
import sys
from pathlib import Path

import pymupdf

d, prefix, title = Path(sys.argv[1]), sys.argv[2], sys.argv[3]
out = pymupdf.open()
n_slides = 0
for part in ('Slides', 'Appendix'):
    src = pymupdf.open(d / f'{prefix}-{part}.pdf')
    if part == 'Slides':
        n_slides = len(src)
    out.insert_pdf(src)
out.set_metadata({'title': title, 'author': 'กลุ่ม AI04', 'subject': 'สไลด์นำเสนอและภาคผนวกหน้าจอการทำงาน'})
out.set_toc([[1, 'สไลด์นำเสนอ', 1], [1, 'ภาคผนวก: หน้าจอการทำงาน', n_slides + 1]])
dst = d / f'{prefix}-Classroom.pdf'
out.save(dst, garbage=3, deflate=True)
fonts = sorted({f[3].split('+')[-1] for p in out for f in p.get_fonts()})
print(dst.name, len(out), 'pages', f'{dst.stat().st_size / 1e6:.1f} MB', fonts)
