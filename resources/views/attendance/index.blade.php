<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบเช็กชื่อ - Unimate</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 600px; margin: auto; }
        .alert-success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .alert-error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .text-danger { color: red; font-size: 14px; margin-top: 5px; display: block; }
        .form-group { margin-bottom: 15px; }
        input[type="text"] { padding: 10px; width: 100%; box-sizing: border-box; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;}
        button { padding: 10px 15px; background-color: #0d6efd; color: white; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 16px;}
        button:hover { background-color: #0b5ed7; }
        
        /* เพิ่มสไตล์สำหรับตารางประวัติ */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <h2>ระบบเช็กชื่อและแจ้งเตือน (หน้าที่ 4)</h2>

    @if(session('success'))
        <div class="alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('attendance.store') }}" method="POST">
        @csrf 
        
        <div class="form-group">
            <label for="student_id">รหัสนักศึกษา:</label>
            <input type="text" id="student_id" name="student_id" value="{{ old('student_id') }}" required placeholder="กรอกรหัสนักศึกษา เช่น 683380258-9">
            
            @error('student_id')
                <span class="text-danger">❌ {{ $message }}</span>
            @enderror
        </div>
        
        <button type="submit">บันทึกการเช็กชื่อ</button>
    </form>

    <hr style="margin: 30px 0;">

    <!-- ส่วนแสดงประวัติการเช็กชื่อย้อนหลัง -->
    <h3>📋 ประวัติการเช็กชื่อย้อนหลัง</h3>
    <table>
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>รหัสนักศึกษา</th>
                <th>เวลาที่เช็กชื่อ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->student_id }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="color: #777;">ยังไม่มีประวัติการเช็กชื่อ</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ส่วนแสดงประวัติการแจ้งเตือน -->
    <h3 style="margin-top: 30px;">🔔 ประวัติการแจ้งเตือนของคุณ</h3>
    <ul style="background: #f8f9fa; padding: 15px 25px; border-radius: 5px; list-style-type: disc;">
        @forelse($notifications as $notification)
            <li style="margin-bottom: 8px;">
                {{ $notification->data['message'] ?? 'แจ้งเตือนกิจกรรม' }} 
                <small style="color: gray;">({{ $notification->created_at->diffForHumans() }})</small>
            </li>
        @empty
            <li style="color: #777;">ยังไม่มีการแจ้งเตือน</li>
        @endforelse
    </ul>

</body>
</html>