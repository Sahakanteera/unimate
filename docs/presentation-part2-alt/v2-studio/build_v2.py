"""UniMate Part 2 — Version 2 "Studio" (free design).

Based on the frontend-slides "studio" template: near-black / acid-yellow binary,
massive headlines, mono metadata, flat hairlines, no rounded corners. Told as one
student's journey (demo account Narin) through real phone screens.

python -I -X utf8 build_v2.py -> UniMate-Part2-V2-Slides.pptx, UniMate-Part2-V2-Appendix.pptx
Fonts: Prompt (headlines) + IBM Plex Sans Thai (body) + IBM Plex Mono (Latin metadata).
"""
import sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
sys.path.insert(0, str(HERE.parent / 'kit'))

from annot import Shot, block_h  # noqa: E402
from content import ASSERTIONS, GROUP, MEMBERS, MOBILE, SCREENS, SECTION, SECTIONS, TESTS  # noqa: E402
from deckkit import Deck  # noqa: E402

ASSETS = HERE / 'assets'

# === THEME (binary palette; muted tones are the same colour blended, not new colours) ===
DARK, YEL = '1C1C1C', 'F5D200'
Y2, Y3, BORDER_D = '9A860C', '615613', '2E2E2C'          # yellow 58% / 32% on dark, hairline on dark
D2, BORDER_L = '6E6111', 'CEB105'                          # near-black 62% on yellow, hairline on yellow
HEAD, BODY, MONO = 'Prompt', 'IBM Plex Sans Thai', 'IBM Plex Mono'
TOTAL = 20


def fg(dark):
    return (YEL, Y2, Y3, BORDER_D) if dark else (DARK, D2, D2, BORDER_L)


def std(d, n, dark, label, section):
    """Standard slide: mono chrome bar + foot bar with hairlines."""
    sl = d.slide(DARK if dark else YEL)
    c1, c2, c3, b = fg(dark)
    sl.text(96, 40, 900, 36, 'UNIMATE — CP410805 PART 2', font=MONO, size=22, color=c2, lh=1.0, name='แถบบน')
    sl.text(1424, 40, 400, 36, f'{n:02d} / {TOTAL}', font=MONO, size=22, color=c2, align='r', lh=1.0, name='เลขหน้า')
    sl.rect(96, 88, 1728, 2, fill=b, name='เส้นบน')
    sl.rect(96, 1000, 1728, 2, fill=b, name='เส้นล่าง')
    sl.text(96, 1016, 1200, 40, label, font=BODY, size=22, color=c2, lh=1.0, name='แถบล่าง')
    sl.text(1424, 1016, 400, 40, f'GROUP {GROUP}', font=MONO, size=22, color=c2, align='r', lh=1.0, name='กลุ่ม')
    return sl


def h(sl, x, y, w, text, size, color, lh=0.88, name=None, align='l'):
    hh = block_h(text, 'Prompt-Bold.ttf', size, w, lh)
    sl.text(x, y, w, hh + size * 0.2, text, font=HEAD, size=size, color=color, bold=True, lh=lh, align=align, name=name)
    return y + hh + size * 0.2


def body(sl, x, y, w, text, size, color, bold=False, lh=0.85, name=None, align='l'):
    hh = block_h(text, 'IBMPlexSansThai-Bold.ttf' if bold else 'IBMPlexSansThai-Regular.ttf', size, w, lh)
    sl.text(x, y, w, hh + 8, text, font=BODY, size=size, color=color, bold=bold, lh=lh, align=align, name=name)
    return y + hh + 8


def dashes(sl, x, y, w, items, size, color, gap=18):
    """Em-dash list: one text box per item so each line can be edited/moved alone."""
    for t in items:
        hh = block_h('— ' + t, 'IBMPlexSansThai-Regular.ttf', size, w - 40, 0.85)
        sl.text(x, y, 44, size * 1.5, '—', font=BODY, size=size, color=color, bold=True, lh=0.85, name='ขีด')
        sl.text(x + 44, y, w - 44, hh + 8, t, font=BODY, size=size, color=color, lh=0.85, name='ข้อความ')
        y += hh + gap
    return y


def phone(sl, cap, x, y, hgt, dark=True, crop=None):
    css = crop or [0, 0, 390, 844]
    w = hgt * css[2] / css[3]
    s = Shot(sl, cap, css, x, y, w, ASSETS, line=Y3 if dark else DARK, lw=2, name=f'มือถือ {cap}')
    return s


def step(d, n, k, verb, title, lead, items, phones, crop=None):
    sl = std(d, n, True, f'STEP {k:02d} / 06 · {verb}', 'journey')
    sl.text(96, 128, 600, 36, f'STEP {k:02d} / 06', font=MONO, size=24, color=Y2, lh=1.0, name='ลำดับขั้น')
    y = h(sl, 96, 176, 880, title, 92, YEL)
    y = body(sl, 96, y + 16, 860, lead, 34, YEL, lh=0.85)
    dashes(sl, 96, y + 26, 860, items, 28, Y2)
    ph = 780
    xs = [1460] if len(phones) == 1 else [1040, 1460]
    for cap, x in zip(phones, xs):
        phone(sl, cap, x, 170, ph)
    return sl


def build_slides():
    d = Deck(HEAD, BODY, title='UniMate ส่วนที่ 2 — Studio', author=f'กลุ่ม {GROUP}')

    # ===== 01 COVER =====
    sl = d.slide(DARK)
    sl.text(80, 70, 1300, 300, 'UNIMATE', font=HEAD, size=300, color=YEL, bold=True, lh=0.8, name='ชื่อโครงงาน')
    h(sl, 96, 420, 900, 'เว็บแอปจับกลุ่มหาเพื่อน\nร่วมทำกิจกรรมในมหาวิทยาลัย', 64, YEL)
    body(sl, 96, 640, 860, 'ส่วนที่ 2 · เว็บแอปพลิเคชันที่พัฒนาเสร็จ\nค้นหา ขอเข้าร่วม อนุมัติ เช็กชื่อ และรีวิว ในระบบเดียว', 32, Y2)
    for i, cap in enumerate(['m-activities', 'm-join', 'm-reviews']):
        phone(sl, cap, 1040 + i * 270, 400 - i * 30, 500)
    sl.rect(96, 930, 1728, 2, fill=Y3, name='เส้นล่างปก')
    sl.text(96, 950, 560, 80, f'{GROUP} × CP410805\n15.10.2026', font=MONO, size=22, color=Y2, lh=1.0, name='ข้อมูลปก ซ้าย')
    sl.text(680, 950, 560, 80, 'PART 2\nWEB APPLICATION', font=MONO, size=22, color=Y2, align='c', lh=1.0, name='ข้อมูลปก กลาง')
    sl.text(1264, 950, 560, 80, f'GROUP {GROUP}\n{SECTION.upper()}', font=MONO, size=22, color=Y2, align='r', lh=1.0, name='ข้อมูลปก ขวา')
    sl.notes('ชื่อโครงงาน UniMate ส่วนที่ 2 วันนี้จะเล่าผ่านผู้ใช้คนหนึ่ง แล้วเดโมเว็บจริง')

    # ===== 02 TEAM =====
    sl = std(d, 2, False, 'ชื่อโครงงานและสมาชิก', 'team')
    sl.text(96, 128, 900, 36, f'GROUP {GROUP} · {SECTION.upper()}', font=MONO, size=24, color=D2, lh=1.0)
    h(sl, 96, 170, 700, 'ทีม\nผู้พัฒนา', 140, DARK)
    for i, m in enumerate(MEMBERS):
        y = 200 + i * 150
        sl.rect(900, y, 924, 2, fill=DARK, name='เส้นสมาชิก')
        sl.text(900, y + 22, 120, 60, f'{i + 1:02d}', font=MONO, size=30, color=D2, lh=1.0)
        sl.text(1010, y + 16, 560, 70, m['name'], font=BODY, size=40, color=DARK, bold=True, lh=0.85)
        sl.text(1560, y + 24, 264, 60, m['id'], font=MONO, size=28, color=DARK, align='r', lh=1.0)

    # ===== 03 STATEMENT =====
    sl = d.slide(DARK)
    sl.text(96, 96, 900, 40, '01 — หลักการและเหตุผล', font=BODY, size=26, color=Y2, bold=True, lh=1.0)
    y = h(sl, 96, 360, 1728, '“เล่นบาส 18:00 สนามกลาง\nขาดอีก 4 คน”', 132, YEL, lh=0.88)
    h(sl, 96, y + 30, 1728, 'แล้วใครจะมาแน่?', 72, Y2)
    body(sl, 96, 930, 1500, 'ตัวอย่างโพสต์ชวนกันในกลุ่มแชต ซึ่งเป็นที่มาของโครงงาน', 26, Y2)

    # ===== 04 PROBLEM =====
    sl = std(d, 4, True, '01 · หลักการและเหตุผล', 'why')
    h(sl, 96, 130, 1600, 'กิจกรรมมีทุกวัน\nแต่การหาเพื่อนยังไม่เป็นระบบ', 84, YEL)
    probs = [('01', 'ข้อมูลกระจัดกระจาย', 'โพสต์ชวนอยู่หลายกลุ่มแชต ถูกข้อความใหม่ดันหาย ค้นย้อนหลังยาก'),
             ('02', 'จัดคนไม่ลงตัว', 'ไม่รู้ว่ายังขาดกี่คน ใครยืนยันแล้ว และใครยังรอคำตอบ'),
             ('03', 'ขาดความน่าเชื่อถือ', 'ไม่มีประวัติว่าใครมาจริง และไม่มีช่องทางแจ้งปัญหา')]
    for i, (no, t, b) in enumerate(probs):
        x = 96 + i * 588
        sl.rect(x, 440, 548, 3, fill=YEL, name='เส้นหัวการ์ด')
        sl.text(x, 464, 200, 50, no, font=MONO, size=28, color=Y2, lh=1.0)
        h(sl, x, 520, 548, t, 52, YEL)
        body(sl, x, 620, 520, b, 28, Y2)

    # ===== 05 OBJECTIVES =====
    sl = std(d, 5, False, '02 · วัตถุประสงค์', 'objectives')
    h(sl, 96, 130, 900, 'วัตถุประสงค์', 120, DARK)
    objs = [('รวมกิจกรรมไว้ในที่เดียว', 'ค้นหาและเปรียบเทียบกิจกรรมในมหาวิทยาลัยได้จากหน้าเดียว'),
            ('รับสมาชิกอย่างโปร่งใส', 'เห็นจำนวนที่รับ ที่ว่าง สถานะคำขอ และรายชื่อผู้เข้าร่วม'),
            ('ลดปัญหาการนัดล่ม', 'ยืนยันการเข้าร่วม เช็กชื่อ และให้คะแนนหลังจบกิจกรรม'),
            ('ใช้งานได้จริงตามข้อกำหนด', 'Laravel + SQLite/MySQL และผู้ใช้มากกว่า 1 ระดับ')]
    for i, (t, b) in enumerate(objs):
        col, row = i % 2, i // 2
        x, y = 96 + col * 880, 400 + row * 280
        sl.rect(x, y, 840, 3, fill=DARK, name='เส้นหัวข้อ')
        sl.text(x, y + 22, 120, 50, f'0{i + 1}', font=MONO, size=28, color=D2, lh=1.0)
        h(sl, x + 110, y + 14, 730, t, 48, DARK)
        body(sl, x + 110, y + 100, 720, b, 28, D2)

    # ===== 06 USERS =====
    sl = std(d, 6, True, '03 · ฟังก์ชันระบบแบ่งตามผู้ใช้งาน', 'users')
    h(sl, 96, 130, 1600, 'ผู้ใช้ 3 กลุ่ม สิทธิ์ต่างกัน', 84, YEL)
    cols = [('GUEST', 'ผู้ยังไม่เข้าสู่ระบบ', ['สมัครสมาชิกนักศึกษา', 'เข้าสู่ระบบ', 'บัญชีที่ถูกระงับเข้าไม่ได้']),
            ('STUDENT', 'นักศึกษา (ผู้จัดและผู้เข้าร่วม)', ['ค้นหา ขอเข้าร่วม ติดตามนัด', 'สร้างและจัดการโพสต์ของตัวเอง', 'อนุมัติคำขอ เช็กชื่อ', 'รีวิว รายงาน แจ้งเตือน']),
            ('ADMIN', 'ผู้ดูแลระบบ', ['สถิติ ค้นหา ระงับ/เปิดบัญชี', 'เพิ่ม แก้ไข ลบหมวดหมู่', 'ตรวจรายงาน ซ่อนกิจกรรม', 'ดูประวัติการดำเนินการ'])]
    for i, (en, th, items) in enumerate(cols):
        x = 96 + i * 588
        if i:
            sl.rect(x - 22, 330, 2, 440, fill=YEL, name='เส้นแบ่ง')
        sl.text(x, 330, 540, 90, en, font=HEAD, size=64, color=YEL, bold=True, lh=0.85)
        body(sl, x, 430, 540, th, 28, Y2, bold=True)
        dashes(sl, x, 510, 540, items, 28, YEL)

    # ===== 07 CHAPTER =====
    sl = d.slide(YEL)
    sl.text(96, 96, 900, 40, '03 — ฟังก์ชันระบบ', font=BODY, size=26, color=D2, bold=True, lh=1.0)
    y = h(sl, 96, 320, 1728, 'หนึ่งกิจกรรม\nของนรินทร์', 160, DARK)
    body(sl, 96, y + 40, 1500, 'ตามบัญชีทดสอบ student@unimate.ac.th ตั้งแต่ค้นหาจนรีวิว แล้วดูมุมของผู้ดูแลระบบ\nภาพทั้งหมดจากเว็บจริงบนมือถือ (ข้อมูลทดสอบ)', 32, D2)

    # ===== 08-13 STEPS =====
    step(d, 8, 1, 'หา', 'หากิจกรรม\nที่ใช่', 'นรินทร์เปิดหน้ากิจกรรม แล้วกรองหมวดที่สนใจ',
         ['คำค้น หมวดหมู่ สถานที่ วันที่ สถานะ ใช้ร่วมกันได้', 'กิจกรรมที่ยังไม่จบขึ้นก่อน หน้าละ 12 รายการ', 'การ์ดบอกวัน เวลา สถานที่ และที่ว่าง'], ['m-activities'])
    step(d, 9, 2, 'ขอเข้าร่วม', 'ดูให้ครบ\nแล้วขอเข้าร่วม', 'หน้ารายละเอียดบอกทุกอย่างที่ต้องรู้ก่อนตัดสินใจ',
         ['วันเวลา สถานที่ จำนวนที่รับ และผู้จัด', 'แนบข้อความถึงผู้จัดได้ไม่เกิน 500 ตัวอักษร', 'หลังส่ง สถานะเป็น “รออนุมัติ” ถอนคำขอได้'], ['m-detail', 'm-join'])
    step(d, 10, 3, 'จัดเอง', 'เป็นผู้จัด\nก็ได้', 'นรินทร์สร้างโพสต์ของตัวเอง แล้วจัดการคำขอ',
         ['ระบบตรวจข้อมูลทั้งในฟอร์มและที่เซิร์ฟเวอร์', 'อนุมัติ/ปฏิเสธได้ไม่เกินจำนวนที่รับ', 'เช็กชื่อ มา / ขาด เฉพาะคนที่อนุมัติแล้ว'], ['m-create', 'm-requests'])
    step(d, 11, 4, 'ติดตาม', 'ไม่พลาด\nทุกนัด', 'นัดของฉัน กับการแจ้งเตือนในเว็บ',
         ['รวมกิจกรรมที่สร้างและที่ขอเข้าร่วม', 'แจ้งเตือนเมื่อคำขอได้รับอนุมัติ/ปฏิเสธ', 'แจ้งผลเมื่อผู้ดูแลตรวจรายงาน'], ['m-my', 'm-notifications'])
    step(d, 12, 5, 'เชื่อใจ', 'รีวิว\nจากคนที่มาจริง', 'หลังกิจกรรมจบ ความเชื่อใจมาจากข้อมูล ไม่ใช่คำพูด',
         ['รีวิวได้เมื่อจบแล้ว และถูกเช็กชื่อว่า “มา”', 'คนละ 1 รีวิวต่อกิจกรรม ผู้จัดรีวิวตัวเองไม่ได้', 'รายงานกิจกรรมหรือผู้ใช้ที่เกี่ยวข้องได้'], ['m-reviews'])
    step(d, 13, 6, 'ผู้ดูแล', 'ผู้ดูแล\nตัดสินพร้อมเหตุผล', 'บัญชี admin@unimate.ac.th',
         ['ค้นหาบัญชี ระงับ / เปิดใช้งาน', 'ตรวจรายงาน ซ่อนกิจกรรม หรือยกรายงาน', 'ทุกการตัดสินบันทึกเหตุผลและประวัติ'], ['m-admin-users', 'm-admin-reports'])

    # ===== 14 JOURNEY SUMMARY =====
    sl = std(d, 14, False, '03 · ลำดับการทำงาน', 'flow')
    h(sl, 96, 130, 1700, 'จากโพสต์ ถึงรีวิว', 120, DARK)
    flow = [('สร้างโพสต์', 'ผู้จัดกำหนดวัน เวลา สถานที่ จำนวนคน'), ('ค้นหา', 'กรองหมวด สถานที่ วันที่'), ('ขอเข้าร่วม', 'สถานะรออนุมัติ แนบข้อความได้'),
            ('อนุมัติ', 'ไม่เกินจำนวนที่รับ ผู้ขอได้แจ้งเตือน'), ('เช็กชื่อ', 'มา / ขาด'), ('รีวิว', 'หลังจบ เฉพาะคนที่มาจริง')]
    for i, (t, b) in enumerate(flow):
        x = 96 + i * 292
        sl.rect(x, 420, 268, 3, fill=DARK, name='เส้นขั้น')
        sl.text(x, 440, 200, 50, f'0{i + 1}', font=MONO, size=30, color=D2, lh=1.0)
        h(sl, x, 500, 268, t, 44, DARK)
        body(sl, x, 580, 260, b, 26, D2)
    sl.rect(96, 820, 1728, 2, fill=BORDER_L, name='เส้นคั่น')
    body(sl, 96, 846, 1728, 'ทุกขั้นตอน: พบปัญหา — รายงานกิจกรรมหรือผู้ใช้ — ผู้ดูแลตรวจ ซ่อน/ระงับ/ยกรายงาน — แจ้งผลผู้รายงาน', 30, DARK, bold=True)

    # ===== 15 SCOPE =====
    sl = std(d, 15, True, '04 · ขอบเขตระบบ', 'scope')
    h(sl, 96, 130, 1600, 'ขอบเขตระบบ', 100, YEL)
    sl.text(96, 330, 800, 70, 'ทำแล้ว', font=HEAD, size=52, color=YEL, bold=True, lh=0.85)
    dashes(sl, 96, 420, 900, ['บัญชี 2 บทบาท (นักศึกษา / ผู้ดูแล) และการระงับบัญชี', 'โพสต์: สร้าง แก้ไข ยกเลิก ค้นหา กรอง', 'คำขอเข้าร่วม อนุมัติ/ปฏิเสธ ตามจำนวนที่รับ',
                               'เช็กชื่อ นัดของฉัน แจ้งเตือนในเว็บ', 'รีวิว รายงาน และการตรวจของผู้ดูแล', 'เว็บเบราว์เซอร์ ทั้งคอมพิวเตอร์และมือถือ'], 28, YEL, gap=14)
    sl.rect(1060, 330, 2, 620, fill=YEL, name='เส้นแบ่ง')
    sl.text(1100, 330, 700, 70, 'ไม่ได้ทำ', font=HEAD, size=52, color=Y2, bold=True, lh=0.85)
    dashes(sl, 1100, 420, 724, ['ซื้อขายสินค้าและชำระเงิน (ข้อห้ามของวิชา)', 'แชตระหว่างผู้ใช้', 'แจ้งเตือนทางอีเมลหรือพุช', 'อัปโหลดรูปกิจกรรม และแผนที่',
                                'เชื่อมบัญชีมหาวิทยาลัย / แอปมือถือ'], 28, Y2, gap=14)

    # ===== 16 RULES =====
    sl = std(d, 16, True, '04 · ขอบเขตระบบ', 'rules')
    h(sl, 96, 130, 1700, 'ระบบปฏิเสธที่เซิร์ฟเวอร์', 84, YEL)
    rules = [('แก้ไข/ยกเลิกโพสต์ของคนอื่น (รวม Admin)', '403 · ActivityPolicy'), ('นักศึกษาเปิดหน้า /admin', '403 · Middleware admin'),
             ('แก้ไขกิจกรรมที่ยกเลิกแล้ว', '409'), ('ขอเข้าร่วมกิจกรรมที่เริ่มแล้ว ของตัวเอง ถูกซ่อน หรือเต็ม', 'Gate join + isFull()'),
             ('อนุมัติเกินจำนวนที่รับ แม้กดพร้อมกัน', 'transaction + lockForUpdate'), ('รีวิวซ้ำ หรือรีวิวโดยไม่ได้มาจริง', 'reviewBlockReason · unique'),
             ('บัญชีที่ถูกระงับใช้งานต่อ', 'Middleware active')]
    for i, (a, b) in enumerate(rules):
        y = 320 + i * 94
        sl.rect(96, y, 1728, 1, fill=BORDER_D if i else YEL, name='เส้นตาราง')
        sl.text(96, y + 18, 1100, 60, a, font=BODY, size=29, color=YEL, lh=0.85)
        sl.text(1220, y + 22, 604, 60, b, font=MONO, size=23, color=Y2, align='r', lh=1.0)

    # ===== 17 UNDER THE HOOD =====
    sl = std(d, 17, True, '05 · โครงสร้างระบบ', 'stack')
    h(sl, 96, 130, 1700, 'เบื้องหลังที่ทำให้กฎเหล่านี้เป็นจริง', 72, YEL)
    stack = [('VIEW', 'Blade + Tailwind CSS + JavaScript'), ('ROUTE', 'Middleware auth · active · admin + CSRF'), ('CONTROLLER', 'Form Request · Policy / Gate · Notifications'),
             ('MODEL', 'Eloquent: belongsTo / hasMany'), ('DATABASE', 'SQLite (เปลี่ยนเป็น MySQL ได้จาก .env)')]
    for i, (k, v) in enumerate(stack):
        y = 300 + i * 120
        sl.rect(96, y, 900, 2, fill=YEL if i == 0 else BORDER_D, name='เส้นชั้น')
        sl.text(96, y + 24, 260, 50, k, font=MONO, size=26, color=Y2, lh=1.0)
        sl.text(360, y + 18, 640, 90, v, font=BODY, size=29, color=YEL, lh=0.85)
    sl.rect(1060, 300, 2, 600, fill=YEL, name='เส้นแบ่ง')
    sl.text(1100, 300, 724, 60, '8 ตารางหลัก', font=HEAD, size=48, color=YEL, bold=True, lh=0.85)
    tables = ['users', 'categories', 'activities', 'activity_participants', 'reviews', 'reports', 'moderation_logs', 'notifications']
    for i, t in enumerate(tables):
        sl.text(1100, 390 + i * 62, 724, 50, t, font=MONO, size=28, color=YEL if i in (0, 2, 3) else Y2, lh=1.0)
    body(sl, 96, 930, 1728, 'นำสิ่งที่เรียนมาใช้: HTML/CSS · JavaScript · PHP · Laravel · Eloquent ORM · Authentication', 24, Y2)

    # ===== 18 TESTS =====
    sl = std(d, 18, False, '05 · การทดสอบ', 'tests')
    sl.text(80, 110, 900, 480, str(TESTS), font=HEAD, size=420, color=DARK, bold=True, lh=0.8, name='ตัวเลขทดสอบ')
    h(sl, 96, 600, 820, 'กรณีทดสอบอัตโนมัติ\nผ่านทั้งหมด', 60, DARK)
    sl.text(96, 820, 820, 50, f'{ASSERTIONS} ASSERTIONS · PEST + SQLITE IN-MEMORY', font=MONO, size=24, color=D2, lh=1.0)
    body(sl, 96, 870, 820, 'รันด้วย php artisan test เมื่อ 10 ต.ค. 2569', 26, D2)
    suites = [('ActivitiesTest', 'สร้าง ค้นหา แก้ไข ยกเลิก ปลอมเจ้าของไม่ได้ หมวดหมู่'), ('ParticipationTest', 'ขอเข้าร่วม อนุมัติไม่เกินจำนวน เช็กชื่อ แจ้งเตือน'),
              ('ReviewReportTest', 'เงื่อนไขรีวิว รายงานซ้ำ ซ่อน/เลิกซ่อน ประวัติ'), ('UiStatesTest', 'สถานะบนหน้าจอ เต็ม จบแล้ว ยกเลิก')]
    for i, (a, b) in enumerate(suites):
        y = 200 + i * 190
        sl.rect(1000, y, 824, 3, fill=DARK, name='เส้นชุดทดสอบ')
        sl.text(1000, y + 22, 824, 50, a, font=MONO, size=28, color=DARK, bold=True, lh=1.0)
        body(sl, 1000, y + 80, 824, b, 28, D2)

    # ===== 19 DEMO =====
    sl = std(d, 19, True, '06 · สาธิตระบบจริง', 'demo')
    h(sl, 96, 130, 1700, 'เดโมเว็บจริง 7 ขั้น', 84, YEL)
    demo = [('เข้าสู่ระบบด้วยบัญชีที่ถูกระงับ — เข้าไม่ได้', 'suspended@'), ('ค้นหาหมวดติวหนังสือ แล้วส่งคำขอเข้าร่วม', 'student@'),
            ('สร้างโพสต์ ลองส่งฟอร์มว่างให้เห็นการตรวจ', 'student@'), ('อนุมัติคำขอ แล้วเช็กชื่อ “มา”', 'student@'),
            ('รีวิวกิจกรรมที่จบแล้ว และรายงานปัญหา', 'student@'), ('พิมพ์ /admin/users ด้วยบัญชีนักศึกษา — 403', 'student@'),
            ('ผู้ดูแลตรวจรายงาน ซ่อนกิจกรรม ดูประวัติ', 'admin@')]
    for i, (t, acc) in enumerate(demo):
        y = 290 + i * 88
        sl.rect(96, y, 1728, 1, fill=BORDER_D if i else YEL, name='เส้นเดโม')
        sl.text(96, y + 20, 100, 50, f'0{i + 1}', font=MONO, size=28, color=Y2, lh=1.0)
        sl.text(210, y + 14, 1200, 60, t, font=BODY, size=31, color=YEL, lh=0.85)
        sl.text(1400, y + 22, 424, 50, acc + 'unimate.ac.th', font=MONO, size=22, color=Y2, align='r', lh=1.0)
    body(sl, 96, 930, 1728, 'สำรองฐานข้อมูลก่อนเดโม · รหัสผ่านอยู่ใน UserSeeder · ถ้าเดโมสดขัดข้อง เปิดภาคผนวกหน้าที่ตรงกัน', 24, Y2)

    # ===== 20 END =====
    sl = d.slide(YEL)
    sl.text(96, 96, 900, 40, 'Q & A', font=MONO, size=26, color=D2, lh=1.0)
    h(sl, 96, 300, 1728, 'ขอบคุณ\nถามได้เลย', 200, DARK, lh=0.85)
    sl.rect(96, 930, 1728, 2, fill=DARK, name='เส้นล่างปิด')
    sl.text(96, 950, 800, 70, 'ภาพหน้าจอทั้งหมดอยู่ในภาคผนวกที่ส่งใน Classroom', font=BODY, size=26, color=D2, lh=0.85)
    sl.text(1224, 950, 600, 70, f'UNIMATE · GROUP {GROUP}', font=MONO, size=24, color=D2, align='r', lh=1.0)
    return d


# ===================== APPENDIX =====================
def build_appendix():
    d = Deck(HEAD, BODY, title='UniMate ส่วนที่ 2 — ภาคผนวกหน้าจอ (V2)', author=f'กลุ่ม {GROUP}')
    total = 1 + sum(len(s[3]) for s in SECTIONS) + 1
    role_acc = {'Guest': 'GUEST', 'Student': 'student@unimate.ac.th', 'Organizer': 'student@unimate.ac.th', 'Trust': 'student@unimate.ac.th',
                'Admin': 'admin@unimate.ac.th', 'Errors': 'student@unimate.ac.th'}

    def chrome(sl, n, label):
        sl.text(96, 34, 1100, 36, label, font=BODY, size=22, color=Y2, lh=1.0, name='แถบบน')
        sl.text(1424, 34, 400, 36, f'APPENDIX {n:02d} / {total}', font=MONO, size=22, color=Y2, align='r', lh=1.0, name='เลขหน้า')
        sl.rect(96, 80, 1728, 2, fill=BORDER_D, name='เส้นบน')

    sl = d.slide(DARK)
    chrome(sl, 1, 'ภาคผนวก')
    h(sl, 96, 140, 1100, 'หน้าจอ\nการทำงาน\nทั้งหมด', 140, YEL)
    body(sl, 96, 720, 820, f'ภาพจากเว็บ UniMate ที่รันจริงในเครื่อง เรียงตามผู้ใช้และลำดับการใช้งาน รวม {total - 1} หน้า ข้อมูลในภาพเป็นข้อมูลทดสอบ บันทึกเมื่อ 10 ต.ค. 2569', 28, Y2)
    n = 1
    for i, (code, name, role, items) in enumerate(SECTIONS + [('G', 'มุมมองบนมือถือ', 'Mobile', [None])]):
        y = 160 + i * 112
        a, b = n + 1, n + len(items)
        n = b
        sl.rect(1060, y, 764, 1, fill=YEL if i == 0 else BORDER_D, name='เส้นสารบัญ')
        sl.text(1060, y + 26, 80, 60, code, font=HEAD, size=44, color=YEL, bold=True, lh=0.85)
        sl.text(1150, y + 28, 480, 60, name, font=BODY, size=29, color=YEL, lh=0.85)
        sl.text(1630, y + 32, 194, 50, f'{a:02d}' if a == b else f'{a:02d}–{b:02d}', font=MONO, size=24, color=Y2, align='r', lh=1.0)

    n = 1
    for code, name, role, items in SECTIONS:
        for k, (img, title, url, desc) in enumerate(items, 1):
            n += 1
            sl = d.slide(DARK)
            chrome(sl, n, f'ภาคผนวก {code} — {name}')
            sl.image(SCREENS / f'{img}.jpg', 96, 112, 1376, 774, line=Y3, lw=2, name=f'ภาพหน้าจอ {code}{k:02d}')
            sl.text(1512, 112, 312, 110, f'{code}{k:02d}', font=HEAD, size=96, color=YEL, bold=True, lh=0.85)
            y = h(sl, 1512, 250, 312, title, 40, YEL)
            body(sl, 1512, y + 10, 312, desc, 25, Y2)
            sl.rect(1512, 780, 312, 1, fill=BORDER_D, name='เส้นข้อมูล')
            # URLs can contain Thai query text, which IBM Plex Mono lacks: use the Thai body font here
            sl.text(1512, 796, 312, 110, f'{role_acc[role]}\n{url}', font=BODY, size=20, color=Y2, lh=0.85, name='บัญชีและ URL')
            sl.rect(96, 920, 1728, 1, fill=BORDER_D, name='เส้นล่าง')
            sl.text(96, 940, 1728, 50, f'UNIMATE — CP410805 PART 2 · GROUP {GROUP}', font=MONO, size=20, color=Y2, lh=1.0)
    n += 1
    sl = d.slide(DARK)
    chrome(sl, n, 'ภาคผนวก G — มุมมองบนมือถือ')
    for i, (img, t) in enumerate(MOBILE):
        x = 96 + i * 420
        sl.image(SCREENS / f'{img}.jpg', x, 112, 370, 801, line=Y3, lw=2, name=f'มือถือ {t}')
        sl.text(x, 930, 370, 60, f'G{i + 1:02d} {t}', font=BODY, size=27, color=YEL, bold=True, lh=0.85)
    h(sl, 1380, 112, 444, 'ใช้บนมือถือได้', 52, YEL)
    body(sl, 1380, 220, 444, 'เมนูย้ายลงแถบล่าง มีปุ่มสร้างโพสต์ตรงกลาง การ์ดและตัวกรองเรียงเป็นคอลัมน์เดียว', 28, Y2)
    sl.text(1380, 800, 444, 100, 'student@unimate.ac.th\n390 PX', font=MONO, size=20, color=Y2, lh=1.0)
    return d


if __name__ == '__main__':
    s = build_slides()
    s.save(HERE / 'UniMate-Part2-V2-Slides.pptx')
    a = build_appendix()
    a.save(HERE / 'UniMate-Part2-V2-Appendix.pptx')
    print('slides', len(s.prs.slides), 'appendix', len(a.prs.slides))
