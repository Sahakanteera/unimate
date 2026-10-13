<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * โปรไฟล์สาธารณะ: ข้อมูลพื้นฐาน ตัวเลขสรุป ประวัติกิจกรรม และรีวิวคนที่ได้รับ (แยกแท็บผู้จัด/ผู้เข้าร่วม)
     * ไม่ส่งอีเมลและรหัสนักศึกษาไปที่หน้าเว็บ (ข้อมูลส่วนตัว)
     */
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();
        // ผู้ใช้ที่ถูกระงับ: คนทั่วไปเห็นเป็น 404 ส่วน Admin ยังเปิดดูได้
        abort_if($user->isSuspended() && ! $viewer->isAdmin(), 404);

        $isSelf = $viewer->id === $user->id;
        $canSeeHidden = $isSelf || $viewer->isAdmin();

        // นิยามเดียวกับสิทธิ์รีวิว: อนุมัติแล้ว + ถูกเช็กว่ามา + กิจกรรมจบแล้ว (ไม่รวมกิจกรรมที่ถูกซ่อน)
        $attendedQuery = fn (): Builder => Activity::query()
            ->whereNull('hidden_at')
            ->where('ends_at', '<=', now())
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', 'approved')->where('attendance', 'present');
            });

        // คะแนนจากรีวิวคน (user_reviews) แยกตามบทบาท ไม่นับกิจกรรมที่ถูกซ่อน
        // รีวิวกิจกรรมยังแสดงเป็นคะแนนของกิจกรรมที่การ์ดกิจกรรม แต่ไม่ใช้เป็นคะแนนของคน
        $stats = [
            'hosted' => $user->activities()->where('status', '!=', 'cancelled')->whereNull('hidden_at')->count(),
            'attended' => $attendedQuery()->count(),
            'host_rating' => UserReview::ratingsFor([$user->id], 'host')->get($user->id),
            'participant_rating' => UserReview::ratingsFor([$user->id], 'participant')->get($user->id),
            'attendance' => ActivityParticipant::attendanceFor([$user->id])->get($user->id),
        ];

        // x-activity-card ต้องมี user, category และตัวนับจาก withCount/withAvg
        $cardData = function ($query) {
            $query->with(['user', 'category'])
                ->withCount(['approvedParticipants as approved_participants_count', 'reviews'])
                ->withAvg('reviews', 'rating');
        };

        $hosted = $user->activities()
            ->tap($cardData)
            ->when(! $canSeeHidden, fn ($query) => $query->whereNull('hidden_at'))
            ->latest('starts_at')
            ->paginate(6, ['*'], 'hosted_page')
            ->withQueryString()
            ->fragment('hosted');

        $attended = $attendedQuery()
            ->tap($cardData)
            ->latest('ends_at')
            ->paginate(6, ['*'], 'attended_page')
            ->withQueryString()
            ->fragment('attended');

        // แท็บรีวิวที่ได้รับ (query string ?tab=host|participant) ค่าเริ่มต้นคือแท็บผู้จัด
        // ยกเว้นยังไม่มีรีวิวในฐานะผู้จัดแต่มีในฐานะผู้เข้าร่วม
        $tab = $request->query('tab');
        if (! in_array($tab, ['host', 'participant'], true)) {
            $tab = $stats['host_rating'] === null && $stats['participant_rating'] !== null ? 'participant' : 'host';
        }

        $receivedReviews = $user->reviewsReceived()
            ->where('reviewee_role', $tab)
            ->whereHas('activity', fn ($query) => $query->whereNull('hidden_at'))
            ->with(['reviewer', 'activity'])
            ->latest()
            ->paginate(10, ['*'], 'reviews_page')
            ->withQueryString()
            ->fragment('received-reviews');

        return view('users.show', compact('user', 'isSelf', 'stats', 'hosted', 'attended', 'tab', 'receivedReviews'));
    }
}
