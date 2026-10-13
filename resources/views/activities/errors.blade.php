@if($errors->any())
<div role="alert" class="mb-6 flex gap-3 rounded-tile border border-bad/20 bg-bad-soft p-4 text-bad">
    <x-ui.icon name="alert" class="mt-0.5 h-5 w-5" />
    <div>
        <p class="font-medium">กรุณาตรวจสอบข้อมูล</p>
        <ul class="mt-1.5 list-disc space-y-0.5 pl-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
</div>
@endif
