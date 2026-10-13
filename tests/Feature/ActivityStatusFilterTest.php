<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\User;
use App\Support\Ui;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-13 12:00:00', 'Asia/Bangkok'));
    $this->owner = User::factory()->create();
    $this->category = Category::create(['name' => 'กีฬา']);
    $this->actingAs($this->owner);
    $this->activities = [];
    $cases = [
        'OpenCourt' => [now()->addHour(), now()->addHours(2), 'published', false],
        'FullCourt' => [now()->addHour(), now()->addHours(2), 'published', true],
        'OngoingCourt' => [now()->subHour(), now()->addHour(), 'published', true],
        'StartingNowCourt' => [now(), now()->addHour(), 'published', false],
        'EndedCourt' => [now()->subHours(2), now()->subHour(), 'published', true],
        'EndingNowCourt' => [now()->subHour(), now(), 'published', false],
        'CancelledFutureCourt' => [now()->addDay(), now()->addDay()->addHour(), 'cancelled', false],
        'CancelledOldCourt' => [now()->subMonth(), now()->subMonth()->addHour(), 'cancelled', true],
    ];
    foreach ($cases as $title => [$start, $end, $status, $full]) {
        $activity = new Activity;
        $activity->forceFill([
            'title' => $title, 'description' => 'Basketball practice', 'location' => 'Campus court',
            'user_id' => $this->owner->id, 'category_id' => $this->category->id,
            'starts_at' => $start, 'ends_at' => $end, 'status' => $status, 'capacity' => 1,
        ])->save();
        if ($full) {
            $participant = $activity->participants()->make(['status' => 'approved']);
            $participant->user()->associate(User::factory()->create());
            $participant->save();
        }
        $this->activities[$title] = $activity;
    }
    $this->activities['CancelledOldCourt']->forceFill(['updated_at' => now()->subDays(30)])->save();
    $hidden = $this->activities['CancelledOldCourt']->replicate();
    $hidden->forceFill(['title' => 'HiddenCourt', 'hidden_at' => now()])->save();
});

test('status filters return the matching activities and badges at time boundaries', function (?string $status, array $titles) {
    $response = $this->get(route('activities.index', $status ? ['status' => $status] : []))->assertOk();
    $actual = $response->viewData('activities')->getCollection()->pluck('title')->sort()->values()->all();
    sort($titles);
    expect($actual)->toBe($titles);
    if ($status && $status !== 'all') {
        foreach ($response->viewData('activities') as $activity) {
            expect(Ui::activityState($activity, $activity->approved_participants_count))
                ->toBe($status === 'available' ? 'open' : $status);
        }
    }
})->with([
    'default open' => [null, ['OpenCourt']],
    'open' => ['available', ['OpenCourt']],
    'full' => ['full', ['FullCourt']],
    'ongoing includes exactly the start time' => ['ongoing', ['OngoingCourt', 'StartingNowCourt']],
    'ended includes exactly the end time' => ['ended', ['EndedCourt', 'EndingNowCourt']],
    'cancelled remains visible after a month' => ['cancelled', ['CancelledFutureCourt', 'CancelledOldCourt']],
    'all includes every visible state' => ['all', [
        'OpenCourt', 'FullCourt', 'OngoingCourt', 'StartingNowCourt', 'EndedCourt', 'EndingNowCourt',
        'CancelledFutureCourt', 'CancelledOldCourt',
    ]],
]);

test('new status filters combine with search category location date and time', function (string $status, string $title) {
    $activity = $this->activities[$title];
    $filters = [
        'status' => $status, 'q' => $title, 'category_id' => $this->category->id,
        'location' => 'Campus', 'date' => $activity->starts_at->format('d/m/Y'),
        'time_from' => $activity->starts_at->format('H:i'), 'time_to' => $activity->starts_at->format('H:i'),
    ];
    $response = $this->get(route('activities.index', $filters))->assertOk();
    expect($response->viewData('activities')->pluck('title')->all())->toBe([$title]);
    $response = $this->get(route('activities.index', [...$filters, 'location' => 'Library']))->assertOk();
    expect($response->viewData('activities')->total())->toBe(0);
})->with([
    ['ongoing', 'OngoingCourt'], ['ended', 'EndedCourt'], ['cancelled', 'CancelledOldCourt'],
]);
