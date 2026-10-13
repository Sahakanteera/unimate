"""Screenshot crops + callouts (highlight box, number badge, leader arrow, label)."""
import json
import math
from pathlib import Path

from PIL import Image

from deckkit import text_width

KIT = Path(__file__).resolve().parent
CAPS = KIT / 'captures'
BOXES = json.loads((CAPS / 'boxes.json').read_text(encoding='utf-8'))
FONTS = KIT / 'fonts'


def box(cap, key):
    b = BOXES[cap]['boxes'].get(key)
    if not b:
        raise KeyError(f'{cap}:{key}')
    return b


def crop_image(cap, crop, outdir: Path, scale=None) -> Path:
    """Cut [x,y,w,h] (CSS px) out of a 2x/3x capture and save it as its own JPEG."""
    src = Image.open(CAPS / f'{cap}.jpg')
    css_w = BOXES[cap]['css'][0]
    s = src.width / css_w
    x, y, w, h = crop
    im = src.crop((round(x * s), round(y * s), round((x + w) * s), round((y + h) * s)))
    outdir.mkdir(parents=True, exist_ok=True)
    out = outdir / f'{cap}_{x}-{y}-{w}x{h}.jpg'
    im.save(out, quality=92, subsampling=0)
    return out


class Shot:
    """A cropped screenshot placed on a slide; maps CSS boxes to slide px."""

    def __init__(self, sl, cap, crop, x, y, w, assets: Path, line='D6DAE1', lw=2, name=None):
        self.cap, self.crop, self.x, self.y, self.w = cap, crop, x, y, w
        self.k = w / crop[2]
        self.h = crop[3] * self.k
        path = crop_image(cap, crop, assets)
        sl.image(path, x, y, w, self.h, line=line, lw=lw, name=name or f'ภาพหน้าจอ {cap}')

    def map(self, b, pad=0):
        cx, cy, cw, ch = self.crop
        x0 = max(b[0], cx); y0 = max(b[1], cy)
        x1 = min(b[0] + b[2], cx + cw); y1 = min(b[1] + b[3], cy + ch)
        r = [self.x + (x0 - cx) * self.k - pad, self.y + (y0 - cy) * self.k - pad,
             (x1 - x0) * self.k + 2 * pad, (y1 - y0) * self.k + 2 * pad]
        # keep highlight inside the image frame
        r[0] = max(r[0], self.x + 3); r[1] = max(r[1], self.y + 3)
        r[2] = min(r[2], self.x + self.w - 3 - r[0]); r[3] = min(r[3], self.y + self.h - 3 - r[1])
        return r

    def b(self, key, pad=8):
        return self.map(box(self.cap, key), pad)


def em(font_file):
    """Natural line height of a font in em (what PowerPoint/LibreOffice call 'single')."""
    from PIL import ImageFont
    a, d = ImageFont.truetype(str(FONTS / font_file), 100).getmetrics()
    return (a + d) / 100


def block_h(text, font_file, size, width, lh=1.0):
    return lines_needed(text, font_file, size, width) * size * em(font_file) * lh


def lines_needed(text, font_file, size, width):
    total = 0
    for para in text.split('\n'):
        wpx = text_width(para, str(FONTS / font_file), size) * 1.08
        total += max(1, math.ceil(wpx / width))
    return total


class Callouts:
    """Label column with numbered entries, each tied to a highlight by a leader arrow."""

    def __init__(self, sl, theme, x=1328, w=512, top=200, bottom=1000, gap=22, renumber=True, start=1):
        self.sl, self.t, self.x, self.w, self.top, self.bottom, self.gap = sl, theme, x, w, top, bottom, gap
        self.renumber, self.start = renumber, start
        self.items = []

    def add(self, n, rect, title, desc, anchor_y=None, side='r', badge=True):
        self.items.append(dict(n=n, rect=rect, title=title, desc=desc, anchor_y=anchor_y, side=side, badge=badge))
        return self

    def _height(self, it):
        t = self.t
        tw = self.w - 64
        h = block_h(it['title'], t['bold_file'], t['label_title'], tw, 1.0)
        if it['desc']:
            h += 6 + block_h(it['desc'], t['body_file'], t['label_desc'], tw, 1.0)
        return max(h, 52)

    def draw(self):
        t, sl = self.t, self.sl
        # desired label y = vertical centre of its target, then push down to avoid overlaps
        for it in self.items:
            r = it['rect']
            it['h'] = self._height(it)
            want = it['anchor_y'] if it['anchor_y'] is not None else r[1] + r[3] / 2
            it['y'] = want - 26
        self.items.sort(key=lambda i: i['y'])
        y = self.top
        for it in self.items:
            it['y'] = max(it['y'], y)
            y = it['y'] + it['h'] + self.gap
        over = y - self.gap - self.bottom
        if over > 0:   # shift the whole stack up, never above top
            for it in reversed(self.items):
                it['y'] -= over
            y = self.top
            for it in self.items:
                it['y'] = max(it['y'], y)
                y = it['y'] + it['h'] + self.gap
        if self.renumber:
            for i, it in enumerate(self.items):
                it['n'] = self.start + i
        # Progressive build: this slide stays clean; Deck.save() adds one copy per point
        sl.deferred = self

    def render(self, sl, step):
        """Draw points 1..step on a copy of the slide. Only point `step` gets the orange
        arrow; earlier points stay as small grey labels so the listener keeps context."""
        t = self.t
        grey_pin, grey_text = 'B8BEC8', '9CA3AF'
        for it in self.items:
            if it['n'] > step:
                continue
            active = it['n'] == step
            r = it['rect']
            ly = it['y']
            if active:
                # arrow from the label to the target's nearest vertical edge, tip just inside it
                ax = r[0] + r[2] - 2 if self.x > r[0] + r[2] else r[0] + 2
                ay = min(max(ly + 26, r[1] + r[3] * 0.3), r[1] + r[3] * 0.7)
                sx = self.x - 10 if self.x > ax else self.x + self.w + 10
                sl.line(sx, ly + 26, ax, ay, t['anno'], lw=4, tail='triangle', name=f'เส้นชี้ {it["n"]}')
            sl.pin(it['n'], self.x + 26, ly + 26, d=50, fill=t['anno'] if active else grey_pin, size=28, name=f'เลข {it["n"]}')
            th = block_h(it['title'], t['bold_file'], t['label_title'], self.w - 64, 1.0)
            sl.text(self.x + 64, ly + 4, self.w - 64, th + 4, it['title'], font=t['body'], size=t['label_title'],
                    color=t['ink'] if active else grey_text, bold=True, lh=1.0, name=f'หัวข้อ {it["n"]}')
            if it['desc'] and active:
                dh = it['h'] - th - 6
                sl.text(self.x + 64, ly + 4 + th + 6, self.w - 64, dh + 6, it['desc'], font=t['body'], size=t['label_desc'],
                        color=t['ink2'], lh=1.0, name=f'คำอธิบาย {it["n"]}')
