<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** ส่วนที่ 5: หน้า Admin ตรวจรายงาน + ประวัติการดำเนินการ */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        if (! in_array($status, ['pending', 'actioned', 'dismissed', 'all'])) {
            $status = 'pending';
        }

        $query = Report::with(['reporter', 'handler'])->latest();
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $counts = Report::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $logs = ModerationLog::with('admin')->latest()->limit(50)->get();
        $hiddenActivities = Activity::whereNotNull('hidden_at')->latest('hidden_at')->get();

        return view('admin.reports.index', [
            'reports' => $query->get(),
            'status' => $status,
            'counts' => $counts,
            'logs' => $logs,
            'hiddenActivities' => $hiddenActivities,
        ]);
    }

    /** action = hide (ซ่อนกิจกรรม / ระงับผู้ใช้) หรือ dismiss (ไม่ดำเนินการ) ต้องมีเหตุผลเสมอ */
    public function resolve(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:hide,dismiss'],
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'note.required' => 'กรุณาบันทึกเหตุผลการดำเนินการ',
            'note.min' => 'เหตุผลต้องมีอย่างน้อย 3 ตัวอักษร',
        ]);

        if (! $report->isPending()) {
            return back()->with('error', 'รายงานนี้ถูกดำเนินการไปแล้ว');
        }

        $target = $report->target();
        $admin = $request->user();

        if ($validated['action'] === 'hide') {
            if ($target === null) {
                return back()->with('error', 'ไม่พบเนื้อหาที่ถูกรายงาน อาจถูกลบไปแล้ว');
            }
            if ($target instanceof User && $target->isAdmin()) {
                return back()->with('error', 'ไม่สามารถระงับบัญชีผู้ดูแลระบบได้');
            }
        }

        DB::transaction(function () use ($validated, $report, $target, $admin) {
            if ($validated['action'] === 'hide') {
                if ($target instanceof Activity) {
                    $target->hidden_at = now();
                    $target->hidden_reason = $validated['note'];
                    $target->save();
                    $logAction = 'hide_activity';
                } else {
                    $target->status = 'suspended';
                    $target->save();
                    $logAction = 'suspend_user';
                }
                $newStatus = 'actioned';
            } else {
                $logAction = 'dismiss';
                $newStatus = 'dismissed';
            }

            $report->status = $newStatus;
            $report->admin_note = $validated['note'];
            $report->handler()->associate($admin);
            $report->handled_at = now();
            $report->save();

            ModerationLog::create([
                'admin_id' => $admin->id,
                'report_id' => $report->id,
                'action' => $logAction,
                'target_type' => $report->target_type,
                'target_id' => $report->target_id,
                'reason' => $validated['note'],
            ]);

            // แจ้งผู้รายงานผ่านระบบแจ้งเตือนของส่วนที่ 4
            $report->reporter->notify(new ActivityNotification(
                $newStatus === 'actioned'
                    ? 'ผู้ดูแลระบบดำเนินการกับรายงานของคุณแล้ว: '.$validated['note']
                    : 'ผู้ดูแลระบบตรวจสอบรายงานของคุณแล้ว และไม่พบการกระทำผิด: '.$validated['note']
            ));
        });

        return back()->with('success', 'บันทึกการดำเนินการเรียบร้อยแล้ว');
    }

    /** เลิกซ่อนกิจกรรม พร้อมบันทึกเหตุผล */
    public function unhide(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'note.required' => 'กรุณาบันทึกเหตุผลการเลิกซ่อน',
            'note.min' => 'เหตุผลต้องมีอย่างน้อย 3 ตัวอักษร',
        ]);

        if (! $activity->isHidden()) {
            return back()->with('error', 'กิจกรรมนี้ไม่ได้ถูกซ่อน');
        }

        $activity->hidden_at = null;
        $activity->hidden_reason = null;
        $activity->save();

        ModerationLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'unhide_activity',
            'target_type' => 'activity',
            'target_id' => $activity->id,
            'reason' => $validated['note'],
        ]);

        return back()->with('success', 'เลิกซ่อนกิจกรรมเรียบร้อยแล้ว');
    }
}
