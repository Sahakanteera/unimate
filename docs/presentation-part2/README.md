# UniMate ส่วนที่ 2 — สไลด์และภาคผนวก (CP410805 กลุ่ม AI04)

| ไฟล์ | ใช้ทำอะไร |
|---|---|
| `UniMate-Part2-Slides.html` | สไลด์พูดหน้าชั้นเรียน 16 หน้า ไฟล์เดียว ฝังฟอนต์และรูปไว้แล้ว เปิดได้โดยไม่ใช้อินเทอร์เน็ต |
| `UniMate-Part2-Appendix.html` | ภาคผนวกหน้าจอการทำงาน 43 หน้า (สำรองตอนเดโมสด) |
| `UniMate-Part2-Classroom.pdf` | ไฟล์ส่ง Classroom: สไลด์ 16 หน้า + ภาคผนวก 43 หน้า (59 หน้า) |
| `demo-script.md` | ลำดับเดโม 7 ขั้น บัญชีที่ใช้ และสิ่งที่ต้องเตรียม |
| `screens/` | ภาพหน้าจอต้นฉบับ (1440×810 ที่ 1.5x และมือถือ 390px ที่ 2x) |
| `src/` | ต้นฉบับ HTML/CSS/JS ฟอนต์ และสคริปต์ build |

## ใช้ตอนนำเสนอ

- ดับเบิลคลิก `UniMate-Part2-Slides.html` เพื่อเปิดใน Chrome หรือ Edge แล้วกด `F` เพื่อเต็มจอ
- เลื่อนหน้าด้วย ← → Space PageUp/PageDown หรือคลิกเกอร์ · Home/End ไปหน้าแรก/หน้าสุดท้าย · เปิด `#5` ต่อท้ายลิงก์เพื่อเข้าหน้า 5 ตรง ๆ
- กด `E` เพื่อแก้ข้อความบนสไลด์ แล้วกด Ctrl+S เพื่อดาวน์โหลดสำเนาที่แก้แล้ว (ไฟล์ต้นฉบับไม่ถูกเปลี่ยน)

## สร้างไฟล์ใหม่หลังแก้เนื้อหา

แก้ `src/slides.src.html`, `src/deck.css` หรือรายชื่อใน `src/members.json` แล้วรันใน `src/`:

```powershell
python -I build.py
```

```powershell
node export-pdf.mjs "C:/Users/Admin/.agents/tools/designlang-13.3.2/package.json" "C:/Program Files/Google/Chrome/Application/chrome.exe"
```

```powershell
python -I merge_pdf.py
```

ต้องใช้ Node 22 (`C:\Users\Admin\AppData\Local\nvm\v22.17.0`) และ Python ที่มี `pymupdf` สคริปต์ `export-pdf.mjs` จะสร้าง PDF ย่อยไว้ใน `src/` และ `merge_pdf.py` รวมเป็นไฟล์ Classroom

## หมายเหตุ

- ภาพหน้าจอถ่ายจากสำเนาฐานข้อมูลตัวอย่าง (เลื่อนวันกิจกรรมไป 7 วันให้ยังไม่หมดเวลา และตัดบัญชี/หมวดทดลองที่ไม่เกี่ยวข้อง) ฐานข้อมูลจริงของโปรเจกต์ไม่ได้ถูกแก้
- ดีไซน์ใช้ Creative Mode จากสกิล frontend-slides ปรับฟอนต์ไทยเป็น Kanit (หัวเรื่อง) และ IBM Plex Sans Thai (เนื้อหา)
- โฟลเดอร์นี้ยังไม่ได้ commit ภาพและ HTML รวมกันประมาณ 20 MB
