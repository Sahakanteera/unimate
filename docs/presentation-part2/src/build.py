"""Build the UniMate Part 2 slide deck and screenshot appendix.

Usage:  python -I build.py
Writes single-file HTML (fonts and images inlined, works offline) to the
parent folder:
  UniMate-Part2-Slides.html    speaking deck (16 slides)
  UniMate-Part2-Appendix.html  screenshot appendix for Classroom
Member names come from members.json (same folder) when it exists.
"""
import base64
import html
import json
import re
from pathlib import Path

SRC = Path(__file__).resolve().parent
OUT = SRC.parent
MIME = {'.woff2': 'font/woff2', '.svg': 'image/svg+xml', '.webp': 'image/webp', '.jpg': 'image/jpeg', '.png': 'image/png'}


def data_uri(path: Path) -> str:
    return f"data:{MIME[path.suffix]};base64,{base64.b64encode(path.read_bytes()).decode()}"


def inline(page: str) -> str:
    css = (SRC / 'deck.css').read_text(encoding='utf-8')
    css = re.sub(r'url\((fonts/[^)]+)\)', lambda m: f'url({data_uri(SRC / m.group(1))})', css)
    page = page.replace('<link rel="stylesheet" href="deck.css">', f'<style>\n{css}\n</style>')
    page = page.replace('<script src="deck.js"></script>', f"<script>\n{(SRC / 'deck.js').read_text(encoding='utf-8')}\n</script>")
    page = re.sub(r'src="((?:\.\./screens|img)/[^"]+)"', lambda m: f'src="{data_uri((SRC / m.group(1)).resolve())}"', page)
    return page


def members_html() -> str:
    f = SRC / 'members.json'
    rows = json.loads(f.read_text(encoding='utf-8')) if f.exists() else [{'name': 'ชื่อ-นามสกุล', 'id': 'รหัสนักศึกษา'}] * 5
    out = []
    for i, m in enumerate(rows, 1):
        out.append(f'<li><b>{i:02d}</b><span>{html.escape(m["name"])}</span><span class="sid">{html.escape(m["id"])}</span></li>')
    return '\n            '.join(out)


# ---------- Appendix content (every screen captured from the running app) ----------
SECTIONS = [
    ('A', 'ผู้ใช้ทั่วไป', 'Guest', [
        ('g01-login', 'เข้าสู่ระบบ', '/login', 'ฟอร์มอีเมลและรหัสผ่าน มีปุ่มเปิด-ปิดดูรหัสผ่าน ด้านซ้ายแนะนำระบบ'),
        ('g02-register', 'สมัครสมาชิก', '/register', 'กรอกชื่อ รหัสนักศึกษา อีเมล Bio และรหัสผ่านอย่างน้อย 6 ตัวอักษร'),
        ('g03-register-errors', 'สมัครไม่ผ่านการตรวจข้อมูล', '/register', 'ส่งฟอร์มไม่ครบ ระบบแจ้งข้อผิดพลาดใต้ช่อง เช่น อีเมลซ้ำ รหัสผ่านสั้นเกินไป'),
        ('g04-login-suspended', 'บัญชีถูกระงับ', '/login', 'บัญชีที่ผู้ดูแลระงับไว้เข้าสู่ระบบไม่ได้ และมีข้อความแจ้งเหตุผล'),
    ]),
    ('B', 'นักศึกษา: ค้นหาและเข้าร่วม', 'Student', [
        ('s01-activities', 'รายการกิจกรรม', '/activities', 'หน้าแรกหลังเข้าสู่ระบบ มีช่องค้นหา ชิปหมวดหมู่ และตัวกรองเพิ่มเติม'),
        ('s01b-activities-cards', 'การ์ดกิจกรรม', '/activities', 'การ์ดแสดงวันที่ หมวด สถานะ เวลา สถานที่ จำนวนผู้เข้าร่วม และผู้จัด กิจกรรมที่ยังไม่จบอยู่ก่อน'),
        ('s02-activities-filter', 'ค้นหาและกรอง', '/activities?category_id=2&q=สอบ', 'ค้นคำว่า “สอบ” ในหมวดติวหนังสือ และเปิดตัวกรองสถานที่ วันที่ สถานะ'),
        ('s07-detail-join', 'รายละเอียดกิจกรรม', '/activities/9', 'ข้อมูลนัดหมาย ที่ว่าง ผู้จัด และฟอร์มขอเข้าร่วมพร้อมข้อความถึงผู้จัด'),
        ('s08-join-pending', 'ส่งคำขอเข้าร่วมแล้ว', '/activities/9', 'สถานะเปลี่ยนเป็น “รออนุมัติ” และกดถอนคำขอได้'),
        ('s14-my-activities', 'นัดของฉัน', '/my-activities', 'สรุปจำนวน และกิจกรรมที่ฉันสร้างพร้อมจำนวนผู้เข้าร่วมและคำขอ'),
        ('s14b-my-activities-joined', 'กิจกรรมที่ขอเข้าร่วม', '/my-activities', 'สถานะของแต่ละคำขอ เช่น รออนุมัติ เข้าร่วมแล้ว มาแล้ว รอเช็กชื่อ'),
        ('s15-notifications', 'การแจ้งเตือน', '/notifications', 'แจ้งผลการอนุมัติคำขอ และกด “อ่านทั้งหมดแล้ว” ได้'),
        ('s16-profile', 'โปรไฟล์', '/profile', 'ข้อมูลบัญชี บทบาท สถานะ และจำนวนกิจกรรมที่สร้างและได้เข้าร่วม'),
        ('s16b-profile-edit', 'แก้ไขโปรไฟล์', '/profile', 'แก้ชื่อ รหัสนักศึกษา อีเมล Bio รูปโปรไฟล์ และเปลี่ยนรหัสผ่าน'),
    ]),
    ('C', 'นักศึกษา: ผู้จัดกิจกรรม', 'Organizer', [
        ('s03-create', 'สร้างโพสต์กิจกรรม', '/activities/create', 'ฟอร์มชื่อ หมวด รายละเอียด สถานที่ เวลาเริ่ม–สิ้นสุด และจำนวนคนที่รับ'),
        ('s04-create-errors', 'ตรวจข้อมูลไม่ผ่าน', '/activities/create', 'ส่งฟอร์มว่าง ระบบสรุปรายการที่ต้องแก้ด้านบนและแจ้งใต้แต่ละช่อง'),
        ('s05-created-owner', 'สร้างสำเร็จ (มุมมองเจ้าของ)', '/activities/12', 'เจ้าของเห็นปุ่มจัดการคำขอ แก้ไขกิจกรรม และยกเลิกกิจกรรม'),
        ('s06-edit', 'แก้ไขกิจกรรม', '/activities/12/edit', 'ฟอร์มเดิมพร้อมข้อมูลปัจจุบัน เปิดได้เฉพาะเจ้าของโพสต์'),
        ('s09-requests-pending', 'คำขอรออนุมัติ', '/activities/3/requests', 'สรุปรออนุมัติ ผู้เข้าร่วม ที่ว่าง แท็บตามสถานะ และปุ่มอนุมัติ / ปฏิเสธ'),
        ('s10-request-approved', 'อนุมัติคำขอแล้ว', '/activities/3/requests', 'ระบบแจ้งผล และอัปเดตจำนวนผู้เข้าร่วมเป็น 3/10'),
        ('s11-requests-attendance', 'สมาชิกที่อนุมัติแล้ว', '/activities/3/requests?status=approved', 'แท็บอนุมัติแล้ว มีปุ่มเช็กชื่อ “มา” / “ขาด” ของแต่ละคน'),
        ('s12-attendance-saved', 'บันทึกการเช็กชื่อ', '/activities/3/requests?status=approved', 'บันทึกว่า “มา” แล้ว ปุ่มแสดงสถานะที่เลือก'),
        ('s13-cancelled', 'กิจกรรมที่ถูกยกเลิก', '/activities/10', 'แสดงแถบยกเลิกแล้ว และไม่รับคำขอเข้าร่วมใหม่'),
    ]),
    ('D', 'หลังจบกิจกรรม: รีวิวและรายงาน', 'Trust', [
        ('s17-ended-detail', 'กิจกรรมที่จบแล้ว', '/activities/11', 'แสดงคะแนนเฉลี่ย และยืนยันว่าผู้ใช้อยู่ในรายชื่อผู้เข้าร่วม'),
        ('s18-reviews-form', 'รีวิวและฟอร์มให้คะแนน', '/activities/11#reviews', 'ผู้ที่ถูกเช็กชื่อว่ามาให้คะแนน 1–5 ดาวพร้อมความคิดเห็นได้'),
        ('s19-review-sent', 'ส่งรีวิวแล้ว', '/activities/11#reviews', 'รีวิวขึ้นในรายการ คะแนนเฉลี่ยอัปเดต และรีวิวซ้ำไม่ได้'),
        ('s20-report-form', 'ฟอร์มรายงานปัญหา', '/activities/5', 'เลือกรายงานกิจกรรมหรือผู้ใช้ที่เกี่ยวข้อง พร้อมระบุเหตุผล'),
        ('s21-report-sent', 'ส่งรายงานแล้ว', '/activities/5', 'ระบบยืนยันการส่ง และรอผู้ดูแลระบบตรวจสอบ'),
    ]),
    ('E', 'ผู้ดูแลระบบ', 'Admin', [
        ('a01-users', 'จัดการผู้ใช้', '/admin/users', 'สถิติสมาชิก 4 ช่อง ช่องค้นหา และตัวกรองสถานะบัญชี'),
        ('a01b-users-table', 'ตารางผู้ใช้', '/admin/users', 'รหัสนักศึกษา บทบาท สถานะ และปุ่มระงับบัญชีของแต่ละคน'),
        ('a02-users-search', 'ค้นหาผู้ใช้', '/admin/users?search=แพรว', 'ค้นด้วยชื่อ อีเมล หรือรหัสนักศึกษา'),
        ('a03-user-suspended', 'ระงับบัญชี', '/admin/users?search=แพรว', 'สถานะเปลี่ยนเป็นถูกระงับ ปุ่มเปลี่ยนเป็นเปิดใช้งาน และสถิติอัปเดต'),
        ('a04-categories', 'จัดการหมวดหมู่', '/admin/categories', 'เพิ่ม เปลี่ยนชื่อ และลบหมวดหมู่ ปุ่มลบปิดเมื่อหมวดยังมีกิจกรรม'),
        ('a05-category-added', 'เพิ่มหมวดหมู่แล้ว', '/admin/categories', 'เพิ่มหมวด “บอร์ดเกม” ระบบตรวจชื่อไม่ให้ซ้ำ'),
        ('a06-reports', 'รายงานรอตรวจ', '/admin/reports', 'แท็บตามสถานะ รายละเอียดผู้รายงาน เหตุผล และปุ่มซ่อน / ยกรายงาน'),
        ('a07-report-resolved', 'ดำเนินการกับรายงาน', '/admin/reports', 'ซ่อนกิจกรรมพร้อมบันทึกเหตุผล จำนวนรายงานรอตรวจลดลง'),
        ('a08-reports-all', 'รายงานทั้งหมด', '/admin/reports?status=all', 'รายงานที่ดำเนินการแล้วแสดงผู้ดำเนินการ เวลา และเหตุผล'),
        ('a09-moderation-log', 'กิจกรรมที่ซ่อนและประวัติ', '/admin/reports?status=all', 'เลิกซ่อนกิจกรรมได้พร้อมเหตุผล และดูประวัติการดำเนินการทั้งหมด'),
        ('a10-hidden-activity', 'กิจกรรมที่ถูกซ่อน', '/activities/5', 'ผู้ดูแลเห็นแถบเหตุผลการซ่อน ผู้ใช้ทั่วไปจะไม่เห็นกิจกรรมนี้'),
    ]),
    ('F', 'สิทธิ์และหน้าข้อผิดพลาด', 'Errors', [
        ('s22-403', 'ไม่มีสิทธิ์เข้าถึง (403)', '/admin/users', 'นักศึกษาเปิดหน้าผู้ดูแลระบบโดยพิมพ์ URL เอง ระบบปฏิเสธ'),
        ('s23-404', 'ไม่พบหน้า (404)', '/activities/99999', 'เปิดกิจกรรมที่ไม่มีอยู่ ระบบแสดงหน้า 404 พร้อมปุ่มกลับ'),
    ]),
]
MOBILE = [('m01-mobile-activities', 'รายการกิจกรรม'), ('m02-mobile-detail', 'รายละเอียดกิจกรรม'), ('m03-mobile-my', 'นัดของฉัน')]
ROLE = {'Guest': 'ยังไม่เข้าสู่ระบบ', 'Student': 'student@unimate.ac.th', 'Organizer': 'student@ (เจ้าของโพสต์)', 'Trust': 'student@unimate.ac.th', 'Admin': 'admin@unimate.ac.th', 'Errors': 'student@unimate.ac.th'}
COLORS = {'A': 'bg-yellow', 'B': 'bg-pink', 'C': 'bg-yellow', 'D': 'bg-green', 'E': 'bg-pink', 'F': 'bg-yellow', 'G': 'bg-green'}

APPENDIX_CSS = """
.ap .shot { position: absolute; left: 96px; top: 116px; width: 1448px; }
.ap .shot img { width: 1440px; height: 810px; }
.ap .side { position: absolute; left: 1600px; right: 72px; top: 116px; height: 818px; display: flex; flex-direction: column; }
.ap .side .code { font-family: var(--f-display); font-size: 76px; line-height: 1; padding: 14px 0 8px; text-align: center; border: 4px solid var(--ink); }
.ap .side h2 { font-family: var(--f-display); font-weight: 400; font-size: 40px; line-height: 1.25; margin-top: 26px; }
.ap .side p { font-size: 24px; line-height: 1.55; margin-top: 16px; color: var(--ink-2); }
.ap .side .url { margin-top: auto; font-family: var(--f-mono); font-size: 19px; line-height: 1.45; overflow-wrap: anywhere; border-top: 3px solid var(--ink); padding-top: 12px; }
.ap .side .url b { display: block; font-weight: 600; letter-spacing: 0.08em; font-size: 17px; }
.ap-cover .title { position: absolute; left: 96px; top: 170px; }
.ap-cover .lede { position: absolute; left: 100px; top: 540px; width: 860px; }
.ap-cover .toc { position: absolute; left: 1080px; top: 170px; width: 744px; }
.ap-cover .toc .tbl td { font-size: 25px; padding: 12px 20px; }
.ap-cover .toc .tbl td:first-child { font-family: var(--f-display); width: 80px; text-align: center; }
.ap-cover .toc .tbl td:last-child { font-family: var(--f-mono); width: 150px; font-size: 22px; }
.ap-cover .note { position: absolute; left: 96px; top: 740px; width: 900px; padding: 20px 28px; font-size: 24px; line-height: 1.55; }
.ap-mobile .phones { position: absolute; left: 96px; top: 124px; display: flex; gap: 60px; }
.ap-mobile .phone { width: 348px; }
.ap-mobile .phone .shot { position: static; width: 348px; box-shadow: 18px 18px 0 var(--ink); }
.ap-mobile .phone img { width: 340px; height: 736px; }
.ap-mobile .phone h3 { font-family: var(--f-display); font-weight: 400; font-size: 30px; margin-top: 30px; line-height: 1.25; }
"""


def appendix_html() -> str:
    total = 1 + sum(len(s[3]) for s in SECTIONS) + 1
    slides, toc, n = [], [], 1
    for code, name, role, items in SECTIONS:
        toc.append((code, name, n + 1, n + len(items)))
        for k, (img, title, url, desc) in enumerate(items, 1):
            n += 1
            slides.append(f"""
<section class="slide ap">
    <div class="topbar"><span><span class="th">ภาคผนวก</span> {code} — <span class="th">{name}</span></span><span class="pill">{role}</span></div>
    <div class="shot"><div class="bar"><i></i><i></i><i></i><span>{html.escape(url)}</span></div><img src="../screens/{img}.jpg" alt="{html.escape(title)}"></div>
    <div class="side">
        <div class="code {COLORS[code]}">{code}{k:02d}</div>
        <h2>{html.escape(title)}</h2>
        <p>{html.escape(desc)}</p>
        <div class="url"><b>ACCOUNT</b>{html.escape(ROLE[role])}<br><b style="margin-top:8px">URL</b>{html.escape(url).replace('?', '<wbr>?').replace('&amp;', '<wbr>&amp;')}</div>
    </div>
    <div class="meta"><span>UniMate · Appendix</span><span class="num">{n:02d} <i class="dot"></i> {total:02d}</span></div>
</section>""")
    n += 1
    toc.append(('G', 'มุมมองบนมือถือ', n, n))
    phones = ''.join(f'<div class="phone"><div class="shot"><img src="../screens/{img}.jpg" alt="{t}"></div><h3>{t}</h3></div>' for img, t in MOBILE)
    slides.append(f"""
<section class="slide ap-mobile">
    <div class="topbar"><span><span class="th">ภาคผนวก</span> G — <span class="th">มุมมองบนมือถือ</span></span><span class="pill">Mobile 390px</span></div>
    <div class="phones">{phones}</div>
    <div style="position:absolute;left:1340px;right:96px;top:124px">
        <div class="cell bg-green"><span class="idx">G01</span><h3>หน้าเดียวกัน ใช้บนมือถือได้</h3><p>เมนูย้ายลงแถบล่าง มีปุ่มสร้างโพสต์ตรงกลาง การ์ดและตัวกรองเรียงเป็นคอลัมน์เดียว</p></div>
        <p class="body-md muted" style="margin-top:28px">บัญชี student@unimate.ac.th · ความกว้างจอ 390px</p>
    </div>
    <div class="meta"><span>UniMate · Appendix</span><span class="num">{n:02d} <i class="dot"></i> {total:02d}</span></div>
</section>""")
    rows = ''.join(f'<tr><td>{c}</td><td>{nm}</td><td>{a:02d}–{b:02d}</td></tr>' if a != b else f'<tr><td>{c}</td><td>{nm}</td><td>{a:02d}</td></tr>' for c, nm, a, b in toc)
    cover = f"""
<section class="slide ap-cover">
    <div class="topbar"><span>UniMate · CP410805 Part 2</span><span class="pill">Appendix</span></div>
    <div class="title"><span class="kicker th">ภาคผนวก</span><h1 class="d-lg" style="margin-top:22px">หน้าจอการทำงาน<br>ทั้งหมดของระบบ</h1></div>
    <p class="lede">ภาพหน้าจอจากเว็บแอป UniMate ที่รันจริงในเครื่อง เรียงตามผู้ใช้และลำดับการใช้งาน รวม {total - 1} หน้า</p>
    <div class="note box bg-cream2">ข้อมูลในภาพเป็นข้อมูลทดสอบ (บัญชี demo และกิจกรรมตัวอย่าง) บันทึกภาพเมื่อ 10 ต.ค. 2569 ที่ความกว้างจอ 1440px และ 390px สำหรับมือถือ</div>
    <div class="toc"><table class="tbl"><thead><tr><th>หมวด</th><th>หัวข้อ</th><th>หน้า</th></tr></thead><tbody>{rows}</tbody></table></div>
    <div class="meta"><span>UniMate · Appendix</span><span class="num">01 <i class="dot"></i> {total:02d}</span></div>
</section>"""
    body = cover + ''.join(slides)
    return f"""<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UniMate ส่วนที่ 2 — ภาคผนวกหน้าจอ</title>
<link rel="stylesheet" href="deck.css">
<style>{APPENDIX_CSS}</style>
</head>
<body>
<div class="deck-viewport">
<main class="deck-stage" id="deckStage">{body}
</main>
</div>
<div class="progress"></div>
<div class="counter"></div>
<div class="edit-hotzone"></div>
<button class="edit-toggle" id="editToggle" title="แก้ไขข้อความ (E)">แก้ไขข้อความ (E)</button>
<script src="deck.js"></script>
</body>
</html>
"""


def main() -> None:
    slides = (SRC / 'slides.src.html').read_text(encoding='utf-8').replace('<!--MEMBERS-->', members_html())
    (SRC / 'appendix.src.html').write_text(appendix_html(), encoding='utf-8', newline='\n')
    for name, page in [('UniMate-Part2-Slides.html', slides), ('UniMate-Part2-Appendix.html', (SRC / 'appendix.src.html').read_text(encoding='utf-8'))]:
        built = inline(page)
        (OUT / name).write_text(built, encoding='utf-8', newline='\n')
        print(f'{name}: {len(built) / 1e6:.1f} MB, slides={built.count(chr(60) + "section class=" + chr(34) + "slide")}')


if __name__ == '__main__':
    main()
