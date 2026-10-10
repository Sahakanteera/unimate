<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use App\Support\AdminInsights;
use Carbon\CarbonImmutable;

// ──────────────── Helpers (ขึ้นต้น ins เพื่อไม่ชนกับ createUser / p5* / ui* ที่เป็นฟังก์ชัน global) ────────────────

function insUser(string $email, string $role = 'student'): User
{
    return User::create([
        'name' => 'User '.$email,
        'email' => $email,
        'password' => 'password',
        'role' => $role,
        'status' => 'active',
    ]);
}

function insActivity(User $host, string $startsAt, int $capacity = 10, string $status = 'published', string $category = 'กีฬา'): Activity
{
    $start = CarbonImmutable::parse($startsAt);
    $activity = new Activity([
        'category_id' => Category::firstOrCreate(['name' => $category])->id,
        'title' => 'กิจกรรมทดสอบ',
        'description' => 'ทดสอบหน้าภาพรวมข้อมูล',
        'location' => 'สนาม',
        'starts_at' => $start,
        'ends_at' => $start->addHours(2),
        'capacity' => $capacity,
    ]);
    $activity->user()->associate($host);
    $activity->status = $status;
    $activity->save();

    return $activity;
}

function insJoin(Activity $activity, User $user, string $status = 'approved', ?string $attendance = null): ActivityParticipant
{
    $p = new ActivityParticipant;
    $p->activity()->associate($activity);
    $p->user()->associate($user);
    $p->status = $status;
    $p->attendance = $attendance;
    $p->save();

    return $p;
}

function insReview(Activity $activity, User $user, int $rating): Review
{
    $r = new Review(['rating' => $rating]);
    $r->activity()->associate($activity);
    $r->user()->associate($user);
    $r->save();

    return $r;
}

/** แปลงเนื้อหา CSV (ตัด BOM) เป็น list ของแถว */
function insCsv(string $content): array
{
    $lines = preg_split('/\r?\n/', rtrim(substr($content, 3), "\n"));

    return array_map(fn ($line) => str_getcsv($line, ',', '"', ''), $lines);
}

beforeEach(function () {
    // วันอาทิตย์ 11 ต.ค. 2026 12:00 เวลาไทย
    $this->travelTo(CarbonImmutable::parse('2026-10-11 12:00:00'));
});

// ──────────────── สิทธิ์และเมนู ────────────────

test('เฉพาะแอดมินเปิดหน้าภาพรวมข้อมูลและดาวน์โหลด CSV ได้', function () {
    $this->get(route('admin.insights.index'))->assertRedirect(route('login'));
    $this->get(route('admin.insights.export'))->assertRedirect(route('login'));

    $student = insUser('s@test.com');
    $this->actingAs($student)->get(route('admin.insights.index'))->assertForbidden();
    $this->actingAs($student)->get(route('admin.insights.export'))->assertForbidden();

    $this->actingAs(insUser('admin@test.com', 'admin'))->get(route('admin.insights.index'))
        ->assertOk()
        ->assertSee('ภาพรวมข้อมูล')
        ->assertSee(route('admin.insights.export', ['range' => '90d']), false);
});

test('เมนูแอดมินมีลิงก์ไปหน้าภาพรวมข้อมูล', function () {
    $this->actingAs(insUser('admin@test.com', 'admin'))->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee(route('admin.insights.index'), false);
});

test('DB ว่างแสดง empty state ได้โดยไม่ error และไม่หารด้วย 0', function () {
    $admin = insUser('admin@test.com', 'admin');

    foreach (array_keys(AdminInsights::RANGES) as $range) {
        $this->actingAs($admin)->get(route('admin.insights.index', ['range' => $range]))
            ->assertOk()
            ->assertSee('ยังไม่มีกิจกรรมในช่วงนี้');
        $this->actingAs($admin)->get(route('admin.insights.export', ['range' => $range]))->assertOk();
    }

    $data = (new AdminInsights('90d'))->toArray();
    expect($data['kpis']['stats']['fillRate'])->toBeNull()
        ->and($data['kpis']['stats']['attendanceRate'])->toBeNull()
        ->and($data['kpis']['cards'][2]['value'])->toBe('–');
});

// ──────────────── ความถูกต้องของตัวเลข ────────────────

test('คำนวณกิจกรรม การยกเลิก ผู้มีส่วนร่วม เติมที่นั่ง มาตามนัด และหมวดหมู่ถูกต้อง', function () {
    $host = insUser('host@test.com');
    $users = collect(range(1, 6))->map(fn ($i) => insUser("u{$i}@test.com"));

    // ในช่วง 90 วัน: 2 กิจกรรมที่จัด + 1 ยกเลิก
    $a = insActivity($host, '2026-09-01 18:00', capacity: 4);
    $b = insActivity($host, '2026-09-20 18:00', capacity: 6);
    insActivity($host, '2026-09-25 18:00', capacity: 5, status: 'cancelled');
    // กิจกรรมที่แอดมินจัดแล้วยกเลิก: นับในยอดยกเลิก แต่แอดมินไม่นับเป็นผู้มีส่วนร่วม (ตัวหารคือนักศึกษา)
    insActivity(insUser('admin@test.com', 'admin'), '2026-09-26 18:00', status: 'cancelled');

    insJoin($a, $users[0], 'approved', 'present');
    insJoin($a, $users[1], 'approved', 'present');
    insJoin($a, $users[2], 'approved', 'absent');
    insJoin($a, $users[3], 'rejected');
    insJoin($b, $users[4], 'approved', 'present');
    insJoin($b, $users[5], 'approved', null); // จบแล้วแต่ยังไม่เช็กชื่อ
    insJoin($b, $users[3], 'cancelled');

    insReview($a, $users[0], 5);
    insReview($a, $users[1], 4);
    insReview($b, $users[4], 3);

    // นอกช่วง 90 วัน
    $old = insActivity($host, '2026-05-01 18:00', capacity: 4);
    insJoin($old, $users[0], 'approved', 'present');

    $data = (new AdminInsights('90d'))->toArray();
    $s = $data['kpis']['stats'];

    expect($s['held'])->toBe(2)
        ->and($s['cancelled'])->toBe(2)
        ->and($s['approved'])->toBe(5)
        ->and($s['capacity'])->toBe(10)
        ->and($s['fillRate'])->toEqual(50.0)
        ->and($s['present'])->toBe(3)
        ->and($s['absent'])->toBe(1)
        ->and($s['unchecked'])->toBe(1)
        ->and($s['attendanceRate'])->toEqual(75.0)
        ->and($s['engaged'])->toBe(7) // host + 6 คน ไม่นับแอดมิน
        ->and($s['students'])->toBe(7);

    $sport = collect($data['categories']['rows'])->firstWhere('name', 'กีฬา');
    expect($sport['activities'])->toBe(2)
        ->and($sport['fillRate'])->toEqual(50.0)
        ->and($sport['attendanceRate'])->toEqual(75.0)
        ->and($sport['rating'])->toEqual(4.0)
        ->and($sport['reviews'])->toBe(3);

    // ช่วง "ทั้งหมด" รวมกิจกรรมเก่าด้วย
    expect((new AdminInsights('all'))->toArray()['kpis']['stats']['held'])->toBe(3);
});

// ──────────────── ตัวกรองช่วงเวลา ────────────────

test('กิจกรรมเมื่อ 60 วันก่อนนับใน 90d แต่ไม่นับใน 30d และค่า range ผิดใช้ 90d', function () {
    insActivity(insUser('host@test.com'), now()->subDays(60)->toDateTimeString());

    expect((new AdminInsights('90d'))->toArray()['kpis']['stats']['held'])->toBe(1)
        ->and((new AdminInsights('30d'))->toArray()['kpis']['stats']['held'])->toBe(0)
        ->and(AdminInsights::normalizeRange('abc'))->toBe('90d')
        ->and(AdminInsights::normalizeRange('6m'))->toBe('90d')
        ->and(AdminInsights::normalizeRange(['x']))->toBe('90d');

    $this->actingAs(insUser('admin@test.com', 'admin'))
        ->get(route('admin.insights.index', ['range' => 'abc']))
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSee('13 ก.ค. 2026 – 11 ต.ค. 2026');
});

// ──────────────── Heatmap ────────────────

test('กิจกรรมวันศุกร์ 17:30 อยู่ในช่องศุกร์ × 16-18 และข้อสังเกตชี้ช่องนี้', function () {
    insActivity(insUser('host@test.com'), '2026-10-09 17:30'); // วันศุกร์

    $heat = (new AdminInsights('30d'))->toArray()['heatmap'];
    $friday = collect($heat['rows'])->firstWhere('day', 5);

    expect($friday['cells'][8]['value'])->toBe(1)
        ->and($friday['cells'][8]['level'])->toBe(5)
        ->and($heat['total'])->toBe(1)
        ->and($heat['insight'])->toContain('วันศุกร์ 16:00–18:00 · 1 กิจกรรม');

    $this->actingAs(insUser('admin@test.com', 'admin'))
        ->get(route('admin.insights.index', ['range' => '30d']))
        ->assertOk()
        ->assertSee('คึกคักที่สุด: วันศุกร์ 16:00–18:00');
});

// ──────────────── Export CSV ────────────────

test('ดาวน์โหลด CSV ได้ข้อมูลชุดเดียวกับหน้าเว็บ พร้อม BOM และชื่อไฟล์ตามช่วง', function () {
    $host = insUser('host@test.com');
    $u = insUser('u@test.com');
    $a = insActivity($host, '2026-10-09 17:30', capacity: 4); // วันศุกร์
    insJoin($a, $u, 'approved', 'present');
    insReview($a, $u, 5);

    $response = $this->actingAs(insUser('admin@test.com', 'admin'))
        ->get(route('admin.insights.export', ['range' => '30d']));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertDownload('unimate-stats-30d-2026-10-11.csv');

    $content = $response->streamedContent();
    expect(substr($content, 0, 3))->toBe("\xEF\xBB\xBF");

    $rows = insCsv($content);
    expect($rows)->toContain(['กิจกรรมที่จัด', '1', 'กิจกรรม'])
        ->toContain(['อัตราเติมที่นั่ง', '25', '%'])
        ->toContain(['อัตรามาตามนัด', '100', '%'])
        ->toContain(['กีฬา', '1', '25', '100', '5', '1']);

    $friday = collect($rows)->first(fn ($r) => $r[0] === 'ศุกร์');
    expect($friday[9])->toBe('1') // คอลัมน์ 16:00–18:00
        ->and(end($friday))->toBe('1');
});

test('CSV ใส่ เครื่องหมาย \' นำหน้าข้อความที่ Excel จะตีความเป็นสูตร', function () {
    insActivity(insUser('host@test.com'), '2026-10-01 18:00', category: '=HYPERLINK("http://x")');

    expect(AdminInsights::csvCell('=1+1'))->toBe("'=1+1")
        ->and(AdminInsights::csvCell('-5'))->toBe("'-5")
        ->and(AdminInsights::csvCell('กีฬา'))->toBe('กีฬา')
        ->and(AdminInsights::csvCell(-5))->toBe(-5)
        ->and(AdminInsights::csvCell(null))->toBe('');

    $content = $this->actingAs(insUser('admin@test.com', 'admin'))
        ->get(route('admin.insights.export'))
        ->streamedContent();

    expect(collect(insCsv($content))->pluck(0)->all())->toContain('\'=HYPERLINK("http://x")')
        ->not->toContain('=HYPERLINK("http://x")');
});

// ──────────────── ความปลอดภัยของข้อความ ────────────────

test('ชื่อหมวดที่มี script ถูก escape', function () {
    insActivity(insUser('host@test.com'), '2026-10-01 18:00', category: '<script>alert(1)</script>');

    $this->actingAs(insUser('admin@test.com', 'admin'))
        ->get(route('admin.insights.index'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});
