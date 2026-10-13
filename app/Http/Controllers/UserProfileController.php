<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * โปรไฟล์สาธารณะ: ข้อมูลพื้นฐาน ตัวเลขสรุป ประวัติกิจกรรม และรีวิวที่ได้รับในฐานะผู้จัด
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

        // รีวิวของกิจกรรมที่คนนี้จัด ไม่นับกิจกรรมที่ถูกซ่อน
        $hostReviewsQuery = fn (): Builder => Review::query()
            ->whereHas('activity', function ($query) use ($user) {
                $query->where('user_id', $user->id)->whereNull('hidden_at');
            });

        $stats = [
            'hosted' => $user->activities()->where('status', '!=', 'cancelled')->whereNull('hidden_at')->count(),
            'attended' => $attendedQuery()->count(),
            'rating' => $hostReviewsQuery()->avg('rating'),
            'reviews' => $hostReviewsQuery()->count(),
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

        $recentReviews = $hostReviewsQuery()
            ->with(['user', 'activity'])
            ->latest()
            ->limit(10)
            ->get();

        return view('users.show', compact('user', 'isSelf', 'stats', 'hosted', 'attended', 'recentReviews'));
    }
}
