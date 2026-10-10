"""Merge slides + appendix into the Classroom PDF (run after export-pdf.mjs)."""
from pathlib import Path
import pymupdf

src = Path(__file__).resolve().parent
out = pymupdf.open()
for name in ['UniMate-Part2-Slides', 'UniMate-Part2-Appendix']:
    out.insert_pdf(pymupdf.open(src / f'{name}.pdf'))
out.set_metadata({'title': 'UniMate ส่วนที่ 2 — CP410805 กลุ่ม AI04', 'author': 'กลุ่ม AI04', 'subject': 'สไลด์นำเสนอและภาคผนวกหน้าจอการทำงาน'})
out.set_toc([[1, 'สไลด์นำเสนอ', 1], [1, 'ภาคผนวก: หน้าจอการทำงาน', 17]])
out.save(src.parent / 'UniMate-Part2-Classroom.pdf', garbage=3, deflate=True)
print('pages', out.page_count)
