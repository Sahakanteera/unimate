"""Render a .pptx with LibreOffice and check text placement.

usage: python -I check.py deck.pptx [outdir]
 - writes deck.pdf next to the deck (the "reference look" PDF)
 - writes PNG previews to outdir (default: <deck>-png/)
 - prints text runs that render outside their own text box (overflow), using
   deck.manifest.json from deckkit.py
"""
import json
import os
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

import pymupdf

SOFFICE = r'C:\Program Files\LibreOffice\program\soffice.com'


def to_pdf(pptx: Path) -> Path:
    prof = Path(tempfile.gettempdir()) / 'um-lo-profile'
    cmd = [SOFFICE, f'-env:UserInstallation={prof.as_uri()}', '--headless', '--convert-to', 'pdf', '--outdir', str(pptx.parent), str(pptx)]
    subprocess.run(cmd, check=True, capture_output=True, timeout=600)
    return pptx.with_suffix('.pdf')


def check(pptx: Path, outdir: Path | None = None, dpi: int = 72) -> int:
    pdf = to_pdf(pptx)
    doc = pymupdf.open(pdf)
    manifest = json.loads(pptx.with_suffix('.manifest.json').read_text(encoding='utf-8'))
    outdir = outdir or pptx.with_name(pptx.stem + '-png')
    if outdir.exists():
        shutil.rmtree(outdir)
    outdir.mkdir(parents=True)
    k = 1920 / doc[0].rect.width        # pdf pt -> slide px
    problems = 0
    fonts = set()
    for i, page in enumerate(doc, 1):
        page.get_pixmap(dpi=dpi).save(outdir / f'p{i:02d}.png')
        fonts |= {f[3].split('+')[-1] for f in page.get_fonts()}
        boxes = [m for m in manifest if m['slide'] == i]
        for b in page.get_text('dict')['blocks']:
            for ln in b.get('lines', []):
                for sp in ln['spans']:
                    t = sp['text'].strip()
                    if not t:
                        continue
                    x0, y0, x1, y1 = [v * k for v in sp['bbox']]
                    # glyph boxes include ascender/descender room; allow a small tolerance
                    tol = 8 + 0.25 * (y1 - y0)
                    owners = [m for m in boxes if t[:6] in m['text'].replace('\n', '')]
                    ok = any(x0 >= m['rect'][0] - tol and x1 <= m['rect'][0] + m['rect'][2] + tol and
                             y0 >= m['rect'][1] - tol and y1 <= m['rect'][1] + m['rect'][3] + tol for m in owners)
                    if owners and not ok:
                        problems += 1
                        m = owners[0]
                        print(f'  p{i}: "{t[:40]}" at [{x0:.0f},{y0:.0f},{x1:.0f},{y1:.0f}] outside {m["name"]} {m["rect"]}')
    bad = sorted(f for f in fonts if any(s in f for s in ('Tahoma', 'Arial', 'DejaVu', 'Liberation', 'Times', 'Segoe', 'Cordia', 'Angsana')))
    print(f'{pptx.name}: {len(doc)} pages, {problems} overflow spans, fonts={sorted(fonts)}' + (f'  FALLBACK={bad}' if bad else ''))
    return problems


if __name__ == '__main__':
    p = Path(sys.argv[1]).resolve()
    sys.exit(1 if check(p, Path(sys.argv[2]) if len(sys.argv) > 2 else None) else 0)
