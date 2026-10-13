"""UniMate Part 2 — Version 1 "วงและชี้อธิบาย" (annotated screenshots).

python -I -X utf8 build_v1.py   ->  UniMate-Part2-V1-Slides.pptx, UniMate-Part2-V1-Appendix.pptx
Fonts: Mitr (headings) + Sarabun (body). Every text, highlight, number, arrow and
shape is a separate editable object; screenshots are separate pictures.
"""
import sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
sys.path.insert(0, str(HERE.parent / 'kit'))

from annot import Callouts, Shot, block_h, box  # noqa: E402
from content import ACCOUNTS, ASSERTIONS, GROUP, MEMBERS, MOBILE, SCREENS, SECTION, SECTIONS, TESTS  # noqa: E402
from deckkit import Deck  # noqa: E402

KIT = HERE.parent / 'kit'
ASSETS = HERE / 'assets'

# === THEME ===
INK, INK2, MUTED = '131922', '4B5563', '6B7280'
LINE, PANEL, WHITE = 'D6DAE1', 'F3F4F6', 'FFFFFF'
ANNO, ANNO_SOFT = 'E8470C', 'FDE6DC'
YEL, YEL_SOFT = 'F5C518', 'FFF3C4'
HEAD, BODY = 'Mitr', 'Sarabun'
T = dict(ink=INK, ink2=INK2, anno=ANNO, anno_w=5, body=BODY, head=HEAD, body_file='Sarabun-Regular.ttf',
         bold_file='Sarabun-Bold.ttf', label_title=30, label_desc=26)
TOTAL = 22


def chrome(d, n, kicker, title):
    sl = d.slide(WHITE)
    sl.text(80, 46, 1500, 40, kicker, font=BODY, size=24, color=ANNO, bold=True, lh=1.0, name='หมวด')
    sl.text(80, 84, 1700, 100, title, font=HEAD, size=56, color=INK, bold=True, lh=0.9, name='หัวเรื่อง')
    sl.image(KIT / 'img' / 'unimate-mark.png', 1796, 52, 44, 44, name='โลโก้')
    footer(sl, n)
    return sl


def footer(sl, n, color=MUTED):
    sl.text(80, 1026, 900, 32, f'UniMate · CP410805 ส่วนที่ 2 · กลุ่ม {GROUP}', font=BODY, size=20, color=color, lh=1.0, name='ส่วนท้าย')
    sl.text(1640, 1026, 200, 32, f'{n:02d} / {TOTAL}', font=BODY, size=20, color=color, align='r', lh=1.0, name='เลขหน้า')


def tag(sl, x, y, text, fill=YEL, color=INK, size=24, w=None, name=None):
    w = w or (len(text) * size * 0.62 + 36)
    return sl.text(x, y, w, size * 1.31 + 16, text, font=BODY, size=size, color=color, bold=True, align='c', anchor='m',
                   lh=1.0, fill=fill, radius=12, pad=(14, 4), name=name or f'ป้าย {text}')


def card(sl, x, y, w, h, title, body, n=None, fill=WHITE, line=LINE, title_size=32, body_size=26):
    sl.rect(x, y, w, h, fill=fill, line=line, lw=2, radius=18, name=f'กรอบ {title}')
    tx = x + 28
    if n is not None:
        sl.pin(n, x + 46, y + 48, d=52, fill=ANNO, size=28)
        tx = x + 88
    sl.text(tx, y + 22, w - (tx - x) - 24, title_size * 1.6, title, font=HEAD, size=title_size, color=INK, bold=True, lh=0.9)
    if body:
        sl.text(tx, y + 22 + title_size * 1.5, w - (tx - x) - 24, h - 40 - title_size * 1.5, body, font=BODY, size=body_size, color=INK2, lh=1.0)


def arrow(sl, x1, y1, x2, y2, color=INK, lw=4, dash=False):
    return sl.line(x1, y1, x2, y2, color, lw=lw, tail='triangle', dash=dash)


def build_slides():
    d = Deck(HEAD, BODY, title='UniMate ส่วนที่ 2 — วงและชี้อธิบาย', author=f'กลุ่ม {GROUP}')

    # ===== 01 TITLE =====
    sl = d.slide(WHITE)
    sl.image(KIT / 'img' / 'unimate-mark.png', 80, 70, 92, 92, name='โลโก้')
    sl.text(188, 84, 500, 70, 'UniMate', font=HEAD, size=52, color=INK, bold=True, lh=0.9)
    sl.text(80, 210, 900, 44, 'CP410805 การพัฒนาโปรแกรมประยุกต์บนเว็บ · โครงงานส่วนที่ 2', font=BODY, size=26, color=ANNO, bold=True, lh=1.0)
    sl.text(80, 262, 880, 200, 'เว็บแอปจับกลุ่มหาเพื่อน\nร่วมทำกิจกรรมในมหาวิทยาลัย', font=HEAD, size=64, color=INK, bold=True, lh=0.9)
    sl.text(80, 470, 880, 44, 'ค้นหา · ขอเข้าร่วม · อนุมัติ · เช็กชื่อ · รีวิว', font=BODY, size=30, color=INK2, lh=1.0)
    sl.rect(80, 560, 860, 420, fill=PANEL, radius=18, name='กรอบสมาชิก')
    sl.text(112, 582, 600, 44, f'สมาชิกกลุ่ม {GROUP} · {SECTION}', font=HEAD, size=30, color=INK, bold=True, lh=0.9)
    for i, m in enumerate(MEMBERS):
        y = 650 + i * 62
        sl.pin(i + 1, 132, y + 22, d=40, fill=INK, size=22)
        sl.text(170, y, 470, 44, m['name'], font=BODY, size=28, color=INK, lh=1.0)
        sl.text(650, y, 260, 44, m['id'], font=BODY, size=28, color=INK2, align='r', lh=1.0)
    Shot(sl, 'd-activities', [96, 0, 1248, 702], 1010, 150, 830, ASSETS)
    Shot(sl, 'm-activities', [0, 0, 390, 844], 1640, 520, 200, ASSETS, line=INK, lw=3, name='ภาพมือถือ')
    sl.text(1010, 980, 620, 40, 'หน้าจริงของระบบ (ข้อมูลทดสอบ) ใช้ได้ทั้งคอมพิวเตอร์และมือถือ', font=BODY, size=22, color=MUTED, lh=1.0)
    sl.notes('แนะนำชื่อโครงงานและสมาชิก แล้วบอกว่ารอบนี้จะพาดูระบบที่ทำเสร็จจริง พร้อมเดโมสด')

    # ===== 02 PROBLEM =====
    sl = chrome(d, 2, '01 · หลักการและเหตุผล', 'กิจกรรมมีทุกวัน แต่การหาเพื่อนยังไม่เป็นระบบ')
    sl.rect(80, 210, 820, 760, fill=PANEL, radius=24, name='ภาพจำลองกลุ่มแชต')
    sl.text(112, 232, 700, 40, 'ตัวอย่างโพสต์ชวนในกลุ่มแชต (ภาพจำลอง)', font=BODY, size=24, color=MUTED, bold=True, lh=1.0)
    bubbles = [('ใครว่างติวแคลคูลัสพรุ่งนี้บ้าง', 290, 120), ('ไปด้วย ๆ', 560, 520), ('+1', 650, 560), ('เปลี่ยนเป็น 18:30 ได้ไหม', 740, 300), ('ใครถือลูกบาสไปบ้าง?', 830, 200)]
    for txt, y, x in bubbles:
        w = len(txt) * 15 + 70
        sl.text(x, y, w, 60, txt, font=BODY, size=26, color=INK2, anchor='m', lh=1.0, fill=WHITE, radius=26, pad=(24, 6), name=f'ข้อความแชต {txt}')
    sl.rect(160, 380, 660, 150, fill=WHITE, line=INK, lw=2, radius=26, name='โพสต์หลัก')
    t1 = (196, 398, 560, 50)
    t2 = (196, 456, 300, 50)
    sl.text(*t1, 'เล่นบาส 18:00 สนามกลาง', font=HEAD, size=34, color=INK, bold=True, lh=0.9)
    sl.text(*t2, 'ขาดอีก 4 คน', font=HEAD, size=34, color=INK, bold=True, lh=0.9)
    co = Callouts(sl, T, x=1000, w=840, top=220, bottom=900)
    co.add(1, [188, 396, 470, 54], 'ข้อมูลกระจัดกระจาย', 'รายละเอียดนัดอยู่ในข้อความเดียว ถูกข้อความใหม่ดันหาย ค้นย้อนหลังยาก', anchor_y=300)
    co.add(2, [188, 454, 220, 54], 'จัดคนไม่ลงตัว', 'ตอบ “+1” กันในแชต ไม่รู้ว่าใครยืนยันจริง และยังขาดกี่คน', anchor_y=500)
    co.add(3, [292, 732, 420, 76], 'ขาดความน่าเชื่อถือ', 'เปลี่ยนเวลาได้กลางทาง ไม่มีประวัติว่าใครมาจริง และไม่มีช่องทางแจ้งปัญหา', anchor_y=720)
    co.draw()
    sl.text(1000, 900, 840, 80, 'UniMate จึงเก็บโพสต์ คำขอ การยืนยัน และรีวิวเป็นข้อมูลในระบบ ตรวจสอบย้อนหลังได้', font=BODY, size=26, color=INK,
            bold=True, anchor='m', lh=1.0, fill=YEL_SOFT, radius=14, pad=(24, 8))
    sl.notes('ยกตัวอย่างโพสต์ในกลุ่มแชต แล้วชี้ทีละจุดว่าทำไมนัดล่ม')

    # ===== 03 OBJECTIVES -> FEATURES =====
    sl = chrome(d, 3, '02 · วัตถุประสงค์', 'วัตถุประสงค์ 4 ข้อ กับสิ่งที่ระบบทำได้จริง')
    sl.text(80, 196, 800, 36, 'วัตถุประสงค์', font=BODY, size=24, color=MUTED, bold=True, lh=1.0)
    sl.text(1010, 196, 800, 36, 'ในระบบที่พัฒนาเสร็จ', font=BODY, size=24, color=MUTED, bold=True, lh=1.0)
    objs = [('รวมกิจกรรมไว้ในที่เดียว', 'ค้นหาและเปรียบเทียบกิจกรรมได้จากหน้าเดียว', 'หน้ากิจกรรม: คำค้น หมวด สถานที่ วันที่ สถานะ แบ่งหน้าละ 12 รายการ'),
            ('รับสมาชิกอย่างโปร่งใส', 'เห็นจำนวนที่รับ สถานะคำขอ และรายชื่อ', 'จำนวนที่รับ / ที่ว่าง คำขอ 4 สถานะ และรายชื่อผู้เข้าร่วม'),
            ('ลดปัญหาการนัดล่ม', 'ยืนยันการเข้าร่วมและประเมินหลังจบ', 'อนุมัติไม่เกินจำนวนที่รับ เช็กชื่อ มา/ขาด รีวิวเฉพาะคนที่มาจริง'),
            ('ใช้งานได้จริงตามข้อกำหนด', 'Laravel + ฐานข้อมูล + ผู้ใช้มากกว่า 1 ระดับ', f'Laravel 13 + SQLite (เปลี่ยนเป็น MySQL ได้) นักศึกษา/ผู้ดูแล ทดสอบอัตโนมัติ {TESTS} กรณี')]
    for i, (t, sub, feat) in enumerate(objs):
        y = 244 + i * 190
        card(sl, 80, y, 820, 166, t, sub, n=i + 1)
        arrow(sl, 920, y + 83, 1000, y + 83, ANNO, lw=5)
        sl.text(1010, y, 830, 166, feat, font=BODY, size=28, color=INK, anchor='m', lh=1.0, fill=YEL_SOFT, radius=18, pad=(28, 10), name=f'ฟีเจอร์ {i + 1}')
    sl.notes('อ่านวัตถุประสงค์แต่ละข้อ แล้วชี้ไปที่สิ่งที่ระบบทำได้จริงทางขวา')

    # ===== 04 ROLES =====
    sl = chrome(d, 4, '03 · ฟังก์ชันระบบแบ่งตามผู้ใช้งาน', 'ผู้ใช้ 3 กลุ่ม เห็นหน้าจอต่างกัน')
    roles = [('ผู้เยี่ยมชม (Guest)', 'g01-login', ['สมัครสมาชิกนักศึกษา', 'เข้าสู่ระบบ', 'บัญชีถูกระงับเข้าไม่ได้']),
             ('นักศึกษา (Student)', 's01-activities', ['ค้นหา ขอเข้าร่วม ติดตามนัด', 'สร้างและจัดการโพสต์ของตัวเอง', 'อนุมัติคำขอ เช็กชื่อ', 'รีวิว รายงาน แจ้งเตือน โปรไฟล์']),
             ('ผู้ดูแลระบบ (Admin)', 'a01-users', ['สถิติ ค้นหา ระงับ/เปิดบัญชี', 'เพิ่ม แก้ไข ลบหมวดหมู่', 'ตรวจรายงาน ซ่อนกิจกรรม', 'ดูประวัติการดำเนินการ'])]
    for i, (name, img, items) in enumerate(roles):
        x = 80 + i * 596
        sl.rect(x, 200, 568, 690, fill=WHITE, line=LINE, lw=2, radius=18, name=f'กรอบ {name}')
        sl.image(SCREENS / f'{img}.jpg', x + 20, 220, 528, 297, line=LINE, lw=2, name=f'ภาพ {name}')
        sl.text(x + 28, 536, 512, 50, name, font=HEAD, size=34, color=INK, bold=True, lh=0.9)
        for j, it in enumerate(items):
            yy = 604 + j * 66
            sl.ellipse(x + 34, yy + 14, 14, 14, fill=ANNO, name='จุด')
            sl.text(x + 62, yy, 480, 60, it, font=BODY, size=27, color=INK2, lh=1.0)
    sl.text(80, 910, 1760, 70, 'นักศึกษาหนึ่งบัญชีเป็นได้ทั้ง “ผู้จัด” และ “ผู้เข้าร่วม”  ·  แก้/ยกเลิกโพสต์ได้เฉพาะเจ้าของ (Admin ก็แก้ไม่ได้)  ·  หน้า /admin เฉพาะผู้ดูแล',
            font=BODY, size=25, color=INK, bold=True, anchor='m', lh=1.0, fill=YEL_SOFT, radius=14, pad=(24, 6))

    # ===== 05 JOURNEY (swimlane) =====
    sl = chrome(d, 5, '03 · ภาพรวมการใช้งาน', 'หนึ่งกิจกรรม ผู้จัดกับผู้เข้าร่วมทำงานต่อกันอย่างไร')
    lanes = [('ผู้จัด', 200, 300), ('ผู้เข้าร่วม', 520, 300), ('ผู้ดูแล', 840, 140)]
    for name, y, h in lanes:
        sl.rect(80, y, 1760, h, fill=PANEL, radius=18, name=f'ช่อง {name}')
        sl.text(104, y + 18, 170, 90, name, font=HEAD, size=30, color=INK, bold=True, lh=0.9)
    # 16:9 crops so every thumbnail has the same size
    steps = [(1, 'สร้างโพสต์', 'd-created-owner', [112, 126, 1216, 684], 0, 290, 'สไลด์ 9'),
             (2, 'ค้นหา', 'd-activities', [96, 0, 1248, 702], 1, 570, 'สไลด์ 6'),
             (3, 'ขอเข้าร่วม', 'd-detail', [96, 96, 1248, 702], 1, 850, 'สไลด์ 8'),
             (4, 'อนุมัติ', 'd-requests', [240, 96, 960, 540], 0, 1130, 'สไลด์ 10'),
             (5, 'เช็กชื่อ', 'd-attendance', [240, 96, 960, 540], 0, 1410, 'สไลด์ 10'),
             (6, 'รีวิว', 'd-review', [96, 96, 1248, 702], 1, 1410 + 280 - 100, 'สไลด์ 12')]
    pos = {}
    for n, label, cap, crop, lane, x, ref in steps:
        ly = lanes[lane][1]
        w = 250 if n != 6 else 230
        h = w * 9 / 16
        y = ly + 34
        Shot(sl, cap, crop, x, y, w, ASSETS)
        sl.pin(n, x, y, d=46, fill=ANNO, size=26)
        sl.text(x, y + h + 12, w, 44, label, font=HEAD, size=28, color=INK, bold=True, lh=0.9)
        sl.text(x, y + h + 56, w, 30, ref, font=BODY, size=20, color=MUTED, lh=1.0)
        pos[n] = (x, y, w, h)
    for a_, b_ in [(1, 2), (2, 3), (3, 4), (4, 5), (5, 6)]:
        x1, y1, w1, h1 = pos[a_]
        x2, y2, w2, h2 = pos[b_]
        arrow(sl, x1 + w1 + 6, y1 + h1 / 2, x2 - 8, y2 + h2 / 2, ANNO, lw=4)
    sl.text(300, 860, 1500, 100, 'ทุกขั้นตอน: พบปัญหา → รายงานกิจกรรมหรือผู้ใช้ → ผู้ดูแลตรวจ ซ่อน/ระงับ/ยกรายงาน → แจ้งผลผู้รายงาน (สไลด์ 13–15)',
            font=BODY, size=26, color=INK, anchor='m', lh=1.0)
    sl.notes('ใช้สไลด์นี้เป็นแผนที่ แล้วค่อยเจาะทีละหน้าจอในสไลด์ถัดไป')

    # ===== 06 SEARCH =====
    sl = chrome(d, 6, 'นักศึกษา · ขั้น 1 ค้นหากิจกรรม', 'หน้ากิจกรรม: ค้นหาและกรองได้ในหน้าเดียว')
    s = Shot(sl, 'd-activities', [96, 0, 1248, 702], 80, 200, 1190, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('create', 6), 'ปุ่มสร้างโพสต์', 'นักศึกษาทุกคนเป็นผู้จัดกิจกรรมได้')
    co.add(2, s.b('search', 6), 'ช่องค้นหา', 'ค้นจากชื่อหรือรายละเอียดกิจกรรม')
    co.add(3, s.b('filters', 6), 'ตัวกรองเพิ่มเติม', 'สถานที่ วันที่เริ่ม และสถานะ ใช้ร่วมกับคำค้นได้')
    co.add(4, s.b('chips', 6), 'ชิปหมวดหมู่', 'กดเพื่อกรองตามหมวด เช่น กีฬา ติวหนังสือ')
    co.draw()
    sl.notes('ชี้ช่องค้นหา ตัวกรอง และการ์ด บอกว่าค้นด้วยหลายเงื่อนไขพร้อมกันได้')

    # ===== 06b ACTIVITY CARD (zoomed) =====
    sl = chrome(d, 7, 'นักศึกษา · ขั้น 1 ค้นหากิจกรรม', 'การ์ดกิจกรรม: ดูข้อมูลสำคัญได้ก่อนเปิด')
    sl.text(80, 150, 900, 40, 'เรียงกิจกรรมที่ยังไม่จบขึ้นก่อน · หน้าละ 12 การ์ด', font=BODY, size=24, color=MUTED, lh=1.0)
    s = Shot(sl, 'd-card', [0, 0, 392, 422], 160, 205, 715, ASSETS, name='ภาพการ์ดกิจกรรม')
    co = Callouts(sl, T, x=1000, w=840, top=200, bottom=990, gap=14)
    co.add(1, s.b('art', 0), 'แถบสีและไอคอนหมวด', 'สีกับไอคอนบอกหมวดกิจกรรมได้ในแวบเดียว')
    co.add(2, s.b('date', 0), 'วันที่เริ่มกิจกรรม', 'วัน วันที่ และเดือน')
    co.add(3, s.map([93, 113, 112, 28], 0), 'หมวดหมู่ + สถานะ', 'เปิดรับ · เต็มแล้ว · เริ่มแล้ว · จบแล้ว · ยกเลิก')
    co.add(4, s.map([21, 151, 350, 81], 0), 'ชื่อและรายละเอียดย่อ', 'แสดงไม่เกิน 2 บรรทัด')
    co.add(5, s.map([21, 248, 350, 48], 0), 'วันเวลาและสถานที่', 'เวลาเริ่ม–สิ้นสุด และจุดนัดพบ')
    co.add(6, s.b('seats', 0), 'ผู้เข้าร่วมและที่ว่าง', 'นับเฉพาะคนที่อนุมัติแล้ว แถบบอกสัดส่วน')
    co.add(7, s.b('host', 0), 'ผู้จัดกิจกรรม', 'กดตรงไหนก็ได้บนการ์ดเพื่อเปิดรายละเอียด')
    co.draw()
    sl.notes('ซูมการ์ดหนึ่งใบ อธิบายว่าแต่ละส่วนบอกอะไร ก่อนจะเปิดหน้ารายละเอียด')

    # ===== 07 DETAIL + JOIN =====
    sl = chrome(d, 7, 'นักศึกษา · ขั้น 2 ดูรายละเอียดและขอเข้าร่วม', 'เห็นข้อมูลครบก่อนตัดสินใจ แล้วส่งคำขอ')
    s = Shot(sl, 'd-detail', [96, 96, 1248, 540], 80, 200, 1150, ASSETS)
    s2 = Shot(sl, 'd-detail-pending', [968, 250, 360, 210], 80, 724, 440, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('stats', 6), 'ข้อมูลนัดหมาย', 'วันที่ เวลา จำนวนผู้เข้าร่วม และคะแนนรีวิวของกิจกรรม')
    co.add(2, s.b('capacity', 6), 'จำนวนที่รับ', 'นับเฉพาะคนที่ได้รับอนุมัติ เทียบกับจำนวนที่ผู้จัดรับ')
    co.add(3, s.map([993, 368, 310, 163], 6), 'ข้อความถึงผู้จัด + ส่งคำขอ', 'แนบข้อความได้ไม่เกิน 500 ตัวอักษร')
    co.add(4, s2.map([985, 290, 326, 124], 4), 'หลังส่งคำขอ', 'สถานะเป็น “รออนุมัติ” และกดถอนคำขอได้', anchor_y=830)
    co.draw()
    sl.text(540, 724, 700, 50, 'หลังกดส่ง', font=BODY, size=24, color=MUTED, bold=True, lh=1.0)

    # ===== 08 CREATE =====
    sl = chrome(d, 8, 'นักศึกษาในฐานะผู้จัด · ขั้น 3 สร้างโพสต์', 'ฟอร์มสร้างโพสต์ตรวจข้อมูลก่อนบันทึก')
    s = Shot(sl, 'd-create-errors', [176, 236, 1088, 574], 80, 200, 1000, ASSETS)
    s2 = Shot(sl, 'd-created-owner', [968, 280, 360, 190], 80, 762, 400, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('summary', 6), 'สรุปสิ่งที่ต้องแก้', 'ส่งฟอร์มว่าง ระบบรวมทุกช่องที่ผิดไว้ด้านบน')
    co.add(2, s.map([241, 645, 634, 70], 6), 'แจ้งใต้ช่องที่ผิด', 'กรอบสีแดงและข้อความใต้ช่อง')
    co.add(3, s.b('category', 6), 'ตรวจซ้ำที่เซิร์ฟเวอร์', 'หมวดต้องมีอยู่จริง เวลาเริ่มต้องเป็นอนาคต เวลาจบหลังเวลาเริ่ม')
    co.add(4, s2.map([985, 296, 326, 166], 4), 'บันทึกสำเร็จ', 'เจ้าของเห็นปุ่มจัดการคำขอ แก้ไข และยกเลิก', anchor_y=860)
    co.draw()
    sl.text(500, 762, 700, 50, 'หลังกรอกครบและบันทึก', font=BODY, size=24, color=MUTED, bold=True, lh=1.0)

    # ===== 09 REQUESTS + ATTENDANCE =====
    sl = chrome(d, 9, 'นักศึกษาในฐานะผู้จัด · ขั้น 4 อนุมัติและเช็กชื่อ', 'จัดการคำขอ: อนุมัติไม่เกินจำนวนที่รับ')
    s = Shot(sl, 'd-requests', [240, 140, 960, 440], 80, 200, 1050, ASSETS)
    s2 = Shot(sl, 'd-attendance', [272, 420, 880, 150], 80, 760, 1050, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('stats', 6), 'สรุปตัวเลข', 'รออนุมัติ ผู้เข้าร่วมแล้ว และที่ว่างคงเหลือ')
    co.add(2, s.b('tabs', 4), 'แท็บตามสถานะ', 'รออนุมัติ อนุมัติแล้ว ปฏิเสธแล้ว ทั้งหมด')
    co.add(3, s.map([969, 454, 174, 36], 6), 'อนุมัติ / ปฏิเสธ', 'ผู้ขอได้รับแจ้งเตือนทันที ถ้าเต็มแล้วระบบไม่ให้อนุมัติเพิ่ม')
    co.add(4, s2.b('rowAtt', 6), 'เช็กชื่อ มา / ขาด', 'แท็บอนุมัติแล้ว เช็กได้เฉพาะคนที่อนุมัติแล้ว')
    co.draw()
    sl.text(80, 712, 700, 44, 'แท็บ “อนุมัติแล้ว”', font=BODY, size=24, color=MUTED, bold=True, lh=1.0)

    # ===== 10 MY ACTIVITIES + NOTIFICATIONS =====
    sl = chrome(d, 10, 'นักศึกษา · ขั้น 5 ติดตามนัด', 'นัดของฉัน และการแจ้งเตือน')
    s = Shot(sl, 'd-my', [128, 196, 1184, 580], 80, 200, 1150, ASSETS)
    s2 = Shot(sl, 'd-notifications', [320, 96, 800, 300], 80, 786, 580, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.map([144, 214, 1152, 78], 6), 'สรุปสิ่งที่ต้องทำ', 'กิจกรรมที่สร้าง คำขอใหม่ คำขอที่รอ และกิจกรรมที่ได้เข้าร่วม')
    co.add(2, s.map([1175, 716, 100, 28], 4), 'ลิงก์ไปจัดการคำขอ', 'การ์ดของผู้จัดบอกจำนวนคำขอที่รอ')
    co.add(3, s2.b('item', 4), 'แจ้งเตือนในเว็บ', 'เมื่อคำขอได้รับอนุมัติ/ปฏิเสธ และผลการตรวจรายงาน')
    co.add(4, s2.b('readall', 4), 'อ่านทั้งหมดแล้ว', 'เคลียร์ตัวเลขบนกระดิ่งในแถบเมนู')
    co.draw()

    # ===== 11 REVIEW =====
    sl = chrome(d, 11, 'ผู้เข้าร่วม · ขั้น 6 รีวิวหลังจบกิจกรรม', 'รีวิวได้เฉพาะคนที่มาจริง')
    s = Shot(sl, 'd-ended', [96, 150, 1248, 470], 80, 200, 1150, ASSETS)
    s2 = Shot(sl, 'd-review', [140, 136, 780, 350], 80, 660, 720, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('ended', 4), 'กิจกรรมจบแล้ว', 'รีวิวเปิดหลังเวลาสิ้นสุดเท่านั้น')
    co.add(2, s.b('stats', 4), 'คะแนนเฉลี่ย', 'คำนวณจากรีวิวทั้งหมดของกิจกรรม')
    co.add(3, s.map([993, 340, 310, 100], 6), 'อยู่ในรายชื่อ → ให้คะแนน', 'ต้องได้รับอนุมัติและถูกเช็กชื่อว่า “มา”')
    co.add(4, s2.map([165, 229, 718, 186], 4), 'ดาว 1–5 + ความคิดเห็น', 'รีวิวได้ครั้งเดียวต่อกิจกรรม ผู้จัดรีวิวตัวเองไม่ได้')
    co.draw()

    # ===== 12 REPORT =====
    sl = chrome(d, 12, 'ผู้เข้าร่วม · ขั้น 7 รายงานปัญหา', 'รายงานกิจกรรมหรือผู้ใช้ที่เกี่ยวข้อง')
    s = Shot(sl, 'd-report', [96, 170, 900, 420], 80, 200, 1150, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('summary', 6), 'เปิดฟอร์มรายงาน', 'อยู่ท้ายหน้ารายละเอียดกิจกรรม')
    co.add(2, s.b('target', 6), 'เลือกสิ่งที่รายงาน', 'ตัวกิจกรรม หรือผู้จัด/สมาชิกที่ได้รับอนุมัติ')
    co.add(3, s.b('reason', 6), 'ระบุเหตุผล', 'อย่างน้อย 5 ตัวอักษร')
    co.add(4, s.b('submit', 6), 'ส่งรายงาน', 'รายงานตัวเองไม่ได้ และส่งซ้ำระหว่างรอตรวจไม่ได้')
    co.draw()
    sl.text(80, 790, 1150, 160, 'รายงานทุกรายการไปรอที่หน้า “ตรวจรายงาน” ของผู้ดูแลระบบ (สไลด์ 15) และผู้รายงานได้รับแจ้งผลผ่านการแจ้งเตือน',
            font=BODY, size=28, color=INK, bold=True, anchor='m', lh=1.0, fill=YEL_SOFT, radius=16, pad=(28, 10))

    # ===== 13 ADMIN USERS + CATEGORIES =====
    sl = chrome(d, 13, 'ผู้ดูแลระบบ · ขั้น 8 บัญชีและหมวดหมู่', 'จัดการบัญชีผู้ใช้และหมวดหมู่')
    s = Shot(sl, 'd-admin-users', [96, 200, 1248, 470], 80, 200, 1150, ASSETS)
    s2 = Shot(sl, 'd-admin-categories', [320, 276, 800, 260], 80, 670, 860, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('tabs', 4), 'เมนูผู้ดูแล 3 ส่วน', 'ผู้ใช้งาน หมวดหมู่ และตรวจรายงาน')
    co.add(2, s.map([112, 287, 1216, 98], 4), 'สถิติสมาชิก', 'ทั้งหมด นักศึกษา เปิดใช้งาน และถูกระงับ')
    co.add(3, s.map([157, 410, 1085, 44], 4), 'ค้นหา + กรองสถานะ', 'ชื่อ อีเมล หรือรหัสนักศึกษา')
    co.add(4, s.b('suspend', 4), 'ระงับ / เปิดใช้งาน', 'บัญชีที่ถูกระงับถูกบังคับออกจากระบบ')
    co.add(5, s2.b('del', 4), 'ลบหมวดหมู่', 'ลบได้เฉพาะหมวดที่ยังไม่มีกิจกรรม ชื่อหมวดห้ามซ้ำ')
    co.draw()

    # ===== 14 ADMIN REPORTS =====
    sl = chrome(d, 14, 'ผู้ดูแลระบบ · ขั้น 9 ตรวจรายงาน', 'ทุกการตัดสินใจต้องมีเหตุผล และย้อนดูได้')
    s = Shot(sl, 'd-admin-reports', [96, 276, 1248, 360], 80, 200, 1150, ASSETS)
    s2 = Shot(sl, 'd-admin-log', [96, 286, 1248, 300], 80, 600, 1150, ASSETS)
    co = Callouts(sl, T)
    co.add(1, s.b('filters', 4), 'แยกตามสถานะ', 'รอตรวจ ดำเนินการแล้ว ยกรายงาน ทั้งหมด')
    co.add(2, s.map([137, 440, 1166, 40], 4), 'เหตุผลจากผู้รายงาน', None)
    co.add(3, s.map([137, 503, 1166, 96], 4), 'บันทึกเหตุผล แล้วตัดสิน', 'ซ่อนกิจกรรม/ระงับผู้ใช้ หรือยกรายงาน')
    co.add(4, s2.map([112, 297, 1216, 126], 4), 'กิจกรรมที่ถูกซ่อน', 'เลิกซ่อนได้ โดยต้องใส่เหตุผล')
    co.add(5, s2.b('log', 4), 'ประวัติการดำเนินการ', 'ใคร ทำอะไร กับอะไร เมื่อไร และเพราะอะไร')
    co.draw()

    # ===== 15 STATE DIAGRAM =====
    sl = chrome(d, 15, '03 · ลำดับสถานะของคำขอ', 'คำขอหนึ่งรายการ เปลี่ยนสถานะอย่างไร')
    def node(x, y, w, h, title, sub, fill=WHITE, line=INK):
        sl.rect(x, y, w, h, fill=fill, line=line, lw=3, radius=20, name=f'สถานะ {title}')
        sl.text(x + 16, y + 14, w - 32, 50, title, font=HEAD, size=32, color=INK, bold=True, align='c', lh=0.9)
        sl.text(x + 16, y + 66, w - 32, h - 76, sub, font=BODY, size=24, color=INK2, align='c', lh=1.0)
    node(80, 360, 300, 150, 'ส่งคำขอ', 'ผู้เข้าร่วม แนบข้อความได้')
    node(470, 360, 300, 150, 'รออนุมัติ', 'pending', fill=YEL_SOFT)
    node(900, 200, 300, 150, 'อนุมัติแล้ว', 'approved')
    node(900, 400, 300, 150, 'ปฏิเสธ', 'rejected')
    node(900, 600, 300, 150, 'ถอนคำขอ', 'cancelled')
    node(1330, 200, 510, 150, 'เช็กชื่อ: มา / ขาด', 'present / absent')
    node(1330, 470, 510, 150, 'รีวิวได้ 1 ครั้ง', 'กิจกรรมจบแล้ว และถูกเช็กว่า “มา”', fill=YEL_SOFT)
    arrow(sl, 384, 435, 464, 435)
    arrow(sl, 774, 410, 894, 290)
    arrow(sl, 774, 435, 894, 475)
    arrow(sl, 774, 460, 894, 660)
    arrow(sl, 1204, 275, 1324, 275)
    arrow(sl, 1585, 354, 1585, 464)
    for n, x, y in [(1, 835, 330), (2, 835, 560), (3, 1264, 240), (4, 1625, 410)]:
        sl.pin(n, x, y, d=44, fill=ANNO, size=24)
    legend = [(1, 'ผู้จัดกดอนุมัติ หรือปฏิเสธ', 'ถ้าเต็มแล้วระบบไม่ให้อนุมัติ (transaction + lock) ผู้ขอได้รับแจ้งเตือน'),
              (2, 'ผู้ขอถอนคำขอเอง', 'ถอนได้ทั้งตอนรออนุมัติ และหลังอนุมัติ (ถอนตัว) ส่งคำขอใหม่ได้'),
              (3, 'ผู้จัดเช็กชื่อ', 'เช็กได้เฉพาะคนที่อนุมัติแล้ว'),
              (4, 'เปิดให้รีวิว', 'หลังเวลาสิ้นสุด เฉพาะคนที่ถูกเช็กว่า “มา” ผู้จัดรีวิวตัวเองไม่ได้')]
    for i, (n, t1, t2) in enumerate(legend):
        x, y = 80 + (i % 2) * 890, 790 + (i // 2) * 110
        sl.pin(n, x + 22, y + 24, d=44, fill=ANNO, size=24)
        sl.text(x + 60, y, 800, 44, t1, font=BODY, size=27, color=INK, bold=True, lh=1.0)
        sl.text(x + 60, y + 44, 800, 44, t2, font=BODY, size=24, color=INK2, lh=1.0)

    # ===== 16 SCOPE =====
    sl = chrome(d, 16, '04 · ขอบเขตระบบ', 'ทำอะไรได้ และไม่ได้ทำอะไร')
    ins = ['บัญชี 2 บทบาท (นักศึกษา / ผู้ดูแลระบบ) และการระงับบัญชี', 'โพสต์กิจกรรม: สร้าง แก้ไข ยกเลิก ค้นหา กรอง', 'คำขอเข้าร่วม อนุมัติ / ปฏิเสธ จำกัดตามจำนวนที่รับ',
           'เช็กชื่อ นัดของฉัน และการแจ้งเตือนภายในเว็บ', 'รีวิวหลังจบกิจกรรม รายงานปัญหา และการตรวจของผู้ดูแล', 'ใช้งานผ่านเว็บเบราว์เซอร์ ทั้งคอมพิวเตอร์และมือถือ']
    outs = ['ซื้อขายสินค้าและชำระเงิน (ข้อห้ามของวิชา)', 'แชตระหว่างผู้ใช้', 'แจ้งเตือนทางอีเมลหรือพุช', 'อัปโหลดรูปกิจกรรม และแผนที่', 'เชื่อมบัญชีมหาวิทยาลัย / แอปมือถือ']
    sl.rect(80, 200, 1020, 790, fill=WHITE, line=INK, lw=3, radius=20, name='กรอบในขอบเขต')
    sl.text(112, 224, 900, 56, 'อยู่ในขอบเขต (ทำแล้ว)', font=HEAD, size=38, color=INK, bold=True, lh=0.9)
    for i, t in enumerate(ins):
        y = 312 + i * 110
        sl.pin('✓' if False else i + 1, 140, y + 26, d=44, fill=INK, size=24)
        sl.text(184, y, 880, 100, t, font=BODY, size=29, color=INK, lh=1.0)
    sl.rect(1140, 200, 700, 790, fill=ANNO_SOFT, radius=20, name='กรอบนอกขอบเขต')
    sl.text(1172, 224, 640, 56, 'ไม่อยู่ในขอบเขต', font=HEAD, size=38, color=ANNO, bold=True, lh=0.9)
    for i, t in enumerate(outs):
        y = 312 + i * 110
        sl.line(1180, y + 14, 1208, y + 42, ANNO, lw=5)
        sl.line(1208, y + 14, 1180, y + 42, ANNO, lw=5)
        sl.text(1232, y, 580, 100, t, font=BODY, size=29, color=INK, lh=1.0)

    # ===== 17 RULES (403) =====
    sl = chrome(d, 17, '04 · ขอบเขตระบบ', 'สิทธิ์ตรวจที่เซิร์ฟเวอร์ ไม่ใช่แค่ซ่อนปุ่ม')
    s = Shot(sl, 'd-403', [440, 120, 560, 600], 80, 200, 560, ASSETS)
    rules = [('แก้ไข/ยกเลิกโพสต์ได้เฉพาะเจ้าของ', 'ผู้อื่นรวม Admin ได้ 403 (ActivityPolicy)'),
             ('หน้า /admin เฉพาะผู้ดูแลระบบ', 'นักศึกษาเปิด URL เองได้ 403 (Middleware admin)'),
             ('กิจกรรมที่ยกเลิกแล้วแก้ไขไม่ได้', 'ได้ 409'),
             ('ขอเข้าร่วมได้เมื่อยังไม่เริ่ม ไม่ใช่ของตัวเอง ไม่ถูกซ่อน ไม่เต็ม', 'เซิร์ฟเวอร์ปฏิเสธ แม้ส่งคำขอเอง'),
             ('อนุมัติเกินจำนวนที่รับไม่ได้ แม้กดพร้อมกัน', 'DB transaction + lockForUpdate'),
             ('บัญชีที่ถูกระงับใช้งานไม่ได้', 'ถูกบังคับออกจากระบบ (Middleware active)')]
    for i, (a, b) in enumerate(rules):
        y = 200 + i * 132
        sl.rect(760, y, 1080, 118, fill=WHITE if i != 1 else ANNO_SOFT, line=LINE if i != 1 else ANNO, lw=2 if i != 1 else 4, radius=14, name=f'กฎ {i + 1}')
        sl.text(790, y + 12, 1030, 48, a, font=BODY, size=29, color=INK, bold=True, lh=1.0)
        sl.text(790, y + 62, 1030, 44, b, font=BODY, size=25, color=INK2, lh=1.0)
    r = s.b('badge', 0)
    arrow(sl, 754, 200 + 132 + 59, r[0] + r[2] - 2, r[1] + r[3] / 2, ANNO, lw=4)
    sl.text(80, 820, 560, 140, 'ทดลองจริง: ล็อกอินเป็นนักศึกษา แล้วพิมพ์ /admin/users เอง', font=BODY, size=26, color=INK, bold=True, lh=1.0)

    # ===== 18 ARCHITECTURE =====
    sl = chrome(d, 18, '05 · เบื้องหลังระบบ', 'กด “ส่งคำขอเข้าร่วม” แล้ว Laravel ทำอะไรบ้าง')
    layers = [('เบราว์เซอร์', 'Blade + Tailwind CSS\nJavaScript'), ('Routes', 'POST /activities/{id}/join\nMiddleware auth · active'),
              ('Controller', 'Gate::authorize(\'join\')\nvalidate ข้อความ ≤ 500'), ('Model', 'Eloquent\nActivityParticipant'), ('ฐานข้อมูล', 'SQLite\n(เปลี่ยนเป็น MySQL ได้)')]
    for i, (t, sub) in enumerate(layers):
        x = 80 + i * 360
        sl.rect(x, 250, 320, 230, fill=YEL_SOFT if i in (0, 4) else WHITE, line=INK, lw=3, radius=18, name=f'ชั้น {t}')
        sl.text(x + 20, 270, 280, 56, t, font=HEAD, size=34, color=INK, bold=True, align='c', lh=0.9)
        sl.text(x + 20, 336, 280, 130, sub, font=BODY, size=24, color=INK2, align='c', lh=1.0)
        if i < 4:
            arrow(sl, x + 324, 365, x + 356, 365, INK, lw=4)
    steps = [(1, 80, 'ฟอร์มส่ง @csrf token ไปพร้อมคำขอ'), (2, 440, 'ต้องล็อกอิน และบัญชีไม่ถูกระงับ'), (3, 800, 'ไม่ใช่เจ้าของ ยังไม่เริ่ม ไม่ถูกซ่อน ไม่เต็ม'),
             (4, 1160, 'สร้างแถวสถานะ pending'), (5, 1520, 'บันทึกลงตาราง activity_participants')]
    for n, x, t in steps:
        sl.line(x + 160, 486, x + 160, 540, ANNO, lw=3)
        sl.pin(n, x + 160, 570, d=50, fill=ANNO, size=28)
        sl.text(x, 606, 320, 130, t, font=BODY, size=25, color=INK, align='c', lh=1.0)
    arrow(sl, 1680, 780, 240, 780, ANNO, lw=4, dash=True)
    sl.text(330, 800, 1260, 50, 'ส่งกลับ: redirect พร้อมข้อความ “ส่งคำขอเรียบร้อย รอผู้จัดอนุมัติ”', font=BODY, size=27, color=ANNO, bold=True, align='c', lh=1.0)
    sl.text(80, 890, 1760, 90, 'สิ่งที่เรียนนำมาใช้: HTML/CSS · JavaScript · PHP · Laravel routing, controller, Blade, validation · Eloquent ORM · Authentication และการแบ่งสิทธิ์',
            font=BODY, size=25, color=INK, anchor='m', lh=1.0, fill=PANEL, radius=14, pad=(24, 6))

    # ===== 19 DATABASE =====
    sl = chrome(d, 19, '05 · ฐานข้อมูล', '8 ตารางหลัก เชื่อมกันด้วย Eloquent')
    tables = {'users': (80, 210, ['name, student_id, email', 'role: student | admin', 'status: active | suspended'], True),
              'activities': (740, 210, ['user_id, category_id', 'starts_at, ends_at, capacity', 'status, hidden_at'], True),
              'categories': (1440, 210, ['name (unique)', 'ลบได้เมื่อไม่มีกิจกรรม'], False),
              'notifications': (80, 500, ['notifiable → users', 'data, read_at'], False),
              'activity_participants': (740, 500, ['status: pending · approved ·', 'rejected · cancelled', 'attendance: present | absent'], True),
              'reviews': (1440, 500, ['activity_id, user_id (unique)', 'rating 1–5, comment'], False),
              'reports': (420, 790, ['reporter_id → users', 'target: activity | user', 'status, admin_note'], False),
              'moderation_logs': (1100, 790, ['admin_id → users', 'report_id, action, reason'], False)}
    for name, (x, y, cols, key) in tables.items():
        w = 400
        sl.rect(x, y, w, 196, fill=WHITE, line=INK, lw=3, radius=14, name=f'ตาราง {name}')
        sl.rect(x, y, w, 52, fill=YEL if key else PANEL, radius=14, name=f'หัวตาราง {name}')
        sl.text(x + 18, y + 6, w - 36, 42, name, font=BODY, size=26, color=INK, bold=True, anchor='m', lh=1.0)
        sl.text(x + 18, y + 62, w - 36, 130, '\n'.join(cols), font=BODY, size=22, color=INK2, lh=1.0)
    rel = [((480, 290), (740, 290), '1 : N'), ((1440, 290), (1140, 290), '1 : N'), ((940, 406), (940, 500), '1 : N'),
           ((480, 380), (740, 560), '1 : N'), ((180, 406), (180, 500), '1 : N'), ((1140, 380), (1440, 560), '1 : N'),
           ((440, 406), (440, 790), '1 : N'), ((820, 890), (1100, 890), '1 : N')]
    for (x1, y1), (x2, y2), lab in rel:
        sl.line(x1, y1, x2, y2, ANNO, lw=4, tail='triangle')
        mx, my = (x1 + x2) / 2, (y1 + y2) / 2
        sl.text(mx - 46, my - 22, 92, 40, lab, font=BODY, size=22, color=ANNO, bold=True, align='c', anchor='m', lh=1.0, fill=WHITE, pad=0)
    sl.text(1060, 120, 780, 70, 'ลูกศรชี้จากฝั่ง 1 ไปฝั่ง N · reports ชี้ได้ทั้งกิจกรรมหรือผู้ใช้ (target_type / target_id)', font=BODY, size=22, color=MUTED, align='r', lh=1.0)

    # ===== 20 TESTS =====
    sl = chrome(d, 20, '05 · การทดสอบ', 'ทุกกฎข้างต้นมีชุดทดสอบอัตโนมัติรองรับ')
    for i, (v, k) in enumerate([(str(TESTS), 'กรณีทดสอบ ผ่านทั้งหมด'), (str(ASSERTIONS), 'assertions')]):
        x = 80 + i * 420
        sl.rect(x, 210, 390, 300, fill=YEL if i == 0 else WHITE, line=INK, lw=3, radius=20, name=f'ตัวเลข {k}')
        sl.text(x + 30, 230, 330, 180, v, font=HEAD, size=140, color=INK, bold=True, lh=0.85)
        sl.text(x + 30, 430, 330, 60, k, font=BODY, size=28, color=INK, bold=True, lh=1.0)
    sl.text(80, 540, 810, 110, 'รันด้วย php artisan test (Pest + SQLite ในหน่วยความจำ) เมื่อ 10 ต.ค. 2569', font=BODY, size=26, color=INK2, lh=1.0)
    suites = [('ActivitiesTest', 'สร้าง ค้นหา แก้ไข ยกเลิก ปลอมเจ้าของไม่ได้ จัดการหมวดหมู่'), ('ParticipationTest', 'ขอเข้าร่วม อนุมัติไม่เกินจำนวน เช็กชื่อ แจ้งเตือน'),
              ('ReviewReportTest', 'เงื่อนไขรีวิว รายงานซ้ำ ซ่อน/เลิกซ่อน ประวัติ'), ('UiStatesTest', 'สถานะบนหน้าจอ เช่น เต็ม จบแล้ว ยกเลิก')]
    for i, (a, b) in enumerate(suites):
        y = 210 + i * 180
        sl.rect(940, y, 900, 160, fill=WHITE, line=LINE, lw=2, radius=16, name=f'ชุด {a}')
        sl.text(970, y + 18, 840, 44, a, font=BODY, size=28, color=ANNO, bold=True, lh=1.0)
        sl.text(970, y + 70, 840, 80, b, font=BODY, size=26, color=INK, lh=1.0)

    # ===== 21 DEMO ORDER =====
    sl = chrome(d, 21, '06 · สาธิตระบบจริง', 'ลำดับเดโม 7 ขั้น (ประมาณ 6–7 นาที)')
    demo = [('เข้าสู่ระบบด้วยบัญชีที่ถูกระงับ → เข้าไม่ได้', 'suspended@', 'd-login-suspended', [520, 90, 720, 540]),
            ('ค้นหาหมวดติวหนังสือ แล้วส่งคำขอเข้าร่วม', 'student@', 'd-detail', [940, 150, 420, 315]),
            ('สร้างโพสต์ ลองส่งฟอร์มว่างให้เห็นการตรวจ', 'student@', 'd-create-errors', [200, 250, 560, 420]),
            ('อนุมัติคำขอ แล้วเช็กชื่อ “มา”', 'student@', 'd-requests', [272, 140, 560, 420]),
            ('รีวิวกิจกรรมที่จบแล้ว และรายงานปัญหา', 'student@', 'd-review', [140, 140, 560, 420]),
            ('พิมพ์ /admin/users ด้วยบัญชีนักศึกษา → 403', 'student@', 'd-403', [440, 120, 560, 420]),
            ('ผู้ดูแลตรวจรายงาน ซ่อนกิจกรรม ดูประวัติ', 'admin@', 'd-admin-reports', [96, 276, 560, 420])]
    for i, (t, acc, cap, crop) in enumerate(demo):
        col, row = divmod(i, 4)
        x, y = 80 + col * 900, 196 + row * 204
        sl.rect(x, y, 860, 186, fill=WHITE, line=LINE, lw=2, radius=16, name=f'ขั้นเดโม {i + 1}')
        Shot(sl, cap, crop, x + 16, y + 16, 205, ASSETS)
        sl.pin(i + 1, x + 16, y + 16, d=44, fill=ANNO, size=24)
        sl.text(x + 246, y + 24, 590, 100, t, font=BODY, size=27, color=INK, bold=True, lh=1.0)
        sl.text(x + 246, y + 130, 590, 40, f'บัญชี {acc}unimate.ac.th', font=BODY, size=22, color=INK2, lh=1.0)
    sl.text(980, 808, 860, 186, 'สำรองฐานข้อมูลก่อนเดโม (รีวิวได้ครั้งเดียว) ถ้าเดโมสดขัดข้อง เปิดภาคผนวกหน้าที่ตรงกัน รหัสผ่านอยู่ใน UserSeeder',
            font=BODY, size=25, color=INK, anchor='m', lh=1.0, fill=YEL_SOFT, radius=16, pad=(26, 10))

    # ===== 22 THANKS =====
    sl = d.slide(WHITE)
    sl.image(KIT / 'img' / 'unimate-mark.png', 80, 80, 110, 110, name='โลโก้')
    sl.text(80, 230, 1100, 250, 'ขอบคุณครับ/ค่ะ\nถามได้เลย', font=HEAD, size=100, color=INK, bold=True, lh=0.9)
    sl.text(80, 500, 1000, 60, 'ภาพหน้าจอการทำงานทั้งหมดอยู่ในภาคผนวกที่ส่งใน Classroom', font=BODY, size=30, color=INK2, lh=1.0)
    trio = [('หา', 'd-activities', [96, 0, 1248, 702]), ('นัด', 'd-requests', [240, 96, 960, 540]), ('เชื่อใจ', 'd-review-sent', [96, 96, 1248, 702])]
    for i, (word, cap, crop) in enumerate(trio):
        x = 80 + i * 596
        Shot(sl, cap, crop, x, 640, 568, ASSETS)
        tag(sl, x + 20, 600, word, size=30, w=150, name=f'ป้าย {word}')
    footer(sl, 22)
    return d


# ===================== APPENDIX =====================
def build_appendix():
    d = Deck(HEAD, BODY, title='UniMate ส่วนที่ 2 — ภาคผนวกหน้าจอ (V1)', author=f'กลุ่ม {GROUP}')
    total = 1 + sum(len(s[3]) for s in SECTIONS) + 1
    role_acc = {'Guest': 'ยังไม่เข้าสู่ระบบ', 'Student': 'student@unimate.ac.th', 'Organizer': 'student@ (เจ้าของโพสต์)', 'Trust': 'student@unimate.ac.th',
                'Admin': 'admin@unimate.ac.th', 'Errors': 'student@unimate.ac.th'}

    def foot(sl, n):
        sl.text(80, 1026, 900, 32, f'UniMate · ภาคผนวกหน้าจอการทำงาน · กลุ่ม {GROUP}', font=BODY, size=20, color=MUTED, lh=1.0)
        sl.text(1640, 1026, 200, 32, f'{n:02d} / {total}', font=BODY, size=20, color=MUTED, align='r', lh=1.0)

    sl = d.slide(WHITE)
    sl.text(80, 80, 900, 44, 'ภาคผนวก', font=BODY, size=28, color=ANNO, bold=True, lh=1.0)
    sl.text(80, 130, 900, 220, 'หน้าจอการทำงาน\nทั้งหมดของระบบ', font=HEAD, size=80, color=INK, bold=True, lh=0.9)
    sl.text(80, 390, 860, 130, f'ภาพจากเว็บ UniMate ที่รันจริงในเครื่อง เรียงตามผู้ใช้และลำดับการใช้งาน รวม {total - 1} หน้า', font=BODY, size=30, color=INK2, lh=1.0)
    sl.text(80, 560, 860, 150, 'ข้อมูลในภาพเป็นข้อมูลทดสอบ บันทึกภาพเมื่อ 10 ต.ค. 2569 ที่ความกว้างจอ 1440px และ 390px สำหรับมือถือ', font=BODY, size=26,
            color=INK, lh=1.0, fill=YEL_SOFT, radius=14, pad=(24, 12))
    n = 1
    for i, (code, name, role, items) in enumerate(SECTIONS + [('G', 'มุมมองบนมือถือ', 'Mobile', [None])]):
        y = 150 + i * 110
        a, b = n + 1, n + len(items)
        n = b
        sl.rect(1060, y, 780, 92, fill=WHITE, line=LINE, lw=2, radius=14, name=f'สารบัญ {code}')
        sl.pin(code, 1110, y + 46, d=56, fill=ANNO, size=28)
        sl.text(1160, y + 20, 480, 52, name, font=BODY, size=28, color=INK, bold=True, anchor='m', lh=1.0)
        sl.text(1660, y + 20, 160, 52, f'{a:02d}' if a == b else f'{a:02d}–{b:02d}', font=BODY, size=26, color=INK2, align='r', anchor='m', lh=1.0)
    foot(sl, 1)

    n = 1
    for code, name, role, items in SECTIONS:
        for k, (img, title, url, desc) in enumerate(items, 1):
            n += 1
            sl = d.slide(WHITE)
            sl.text(80, 46, 1300, 40, f'ภาคผนวก {code} · {name}', font=BODY, size=24, color=ANNO, bold=True, lh=1.0)
            sl.image(SCREENS / f'{img}.jpg', 80, 110, 1360, 765, line=LINE, lw=2, name=f'ภาพหน้าจอ {code}{k:02d}')
            sl.text(1480, 110, 360, 64, f'{code}{k:02d}', font=HEAD, size=30, color=WHITE, bold=True, align='c', anchor='m', lh=0.9, fill=ANNO, radius=14)
            th = block_h(title, 'Mitr-Bold.ttf', 40, 360, 0.9)
            sl.text(1480, 200, 360, th + 10, title, font=HEAD, size=40, color=INK, bold=True, lh=0.9)
            sl.text(1480, 200 + th + 26, 360, 330, desc, font=BODY, size=27, color=INK2, lh=1.0)
            sl.text(1480, 700, 360, 176, [[('บัญชี', {'bold': True})], role_acc[role], [('URL', {'bold': True})], url], font=BODY, size=22,
                    color=INK, lh=1.0, name='บัญชีและ URL')
            foot(sl, n)
    n += 1
    sl = d.slide(WHITE)
    sl.text(80, 46, 1300, 40, 'ภาคผนวก G · มุมมองบนมือถือ', font=BODY, size=24, color=ANNO, bold=True, lh=1.0)
    for i, (img, t) in enumerate(MOBILE):
        x = 80 + i * 420
        sl.image(SCREENS / f'{img}.jpg', x, 110, 380, 822, line=INK, lw=3, name=f'มือถือ {t}')
        sl.text(x, 944, 380, 50, f'G{i + 1:02d} {t}', font=HEAD, size=30, color=INK, bold=True, lh=0.9)
    sl.text(1360, 110, 480, 400, 'หน้าเดียวกันใช้บนมือถือได้: เมนูย้ายลงแถบล่าง มีปุ่มสร้างโพสต์ตรงกลาง การ์ดและตัวกรองเรียงเป็นคอลัมน์เดียว\n\nบัญชี student@unimate.ac.th · ความกว้างจอ 390px',
            font=BODY, size=27, color=INK, lh=1.0)
    foot(sl, n)
    return d


if __name__ == '__main__':
    out = HERE
    s = build_slides()
    s.save(out / 'UniMate-Part2-V1-Slides.pptx')
    a = build_appendix()
    a.save(out / 'UniMate-Part2-V1-Appendix.pptx')
    print('slides', len(s.prs.slides), 'appendix', len(a.prs.slides))
