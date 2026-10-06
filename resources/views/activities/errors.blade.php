@if($errors->any())
<div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 text-rose-800 p-4">
    <p class="font-semibold">กรุณาตรวจสอบข้อมูล</p>
    <ul class="list-disc pl-5 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
