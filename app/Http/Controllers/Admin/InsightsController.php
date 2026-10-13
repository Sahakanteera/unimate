<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminInsights;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InsightsController extends Controller
{
    /** หน้าภาพรวมข้อมูลสำหรับผู้ดูแลระบบ ค่า range ที่ไม่รู้จักใช้ค่าเริ่มต้น (90d) แทนการ error */
    public function index(Request $request): View
    {
        $insights = new AdminInsights(AdminInsights::normalizeRange($request->query('range')));

        return view('admin.insights.index', ['data' => $insights->toArray()]);
    }

    /** ดาวน์โหลดสถิติของช่วงที่เลือกเป็น CSV (UTF-8 มี BOM ให้ Excel แสดงภาษาไทยถูก) */
    public function export(Request $request): StreamedResponse
    {
        $insights = new AdminInsights(AdminInsights::normalizeRange($request->query('range')));
        $filename = 'unimate-stats-'.$insights->range.'-'.$insights->now->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($insights) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($insights->exportRows() as $row) {
                fputcsv($out, array_map([AdminInsights::class, 'csvCell'], $row), ',', '"', '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
