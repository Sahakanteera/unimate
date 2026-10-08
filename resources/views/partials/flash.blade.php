{{-- ข้อความแจ้งผลแบบ toast ลอยใต้ header ปิดเองหลัง 4 วินาที (สคริปต์อยู่ท้าย layouts.app) --}}
@foreach(['success' => ['check-circle', 'text-tint-mint'], 'error' => ['alert', 'text-tint-coral']] as $type => [$icon, $iconColor])
    @if(session($type))
        <div id="flash-{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" data-flash
             class="fixed inset-x-0 top-24 z-50 mx-auto flex w-[calc(100%-2rem)] max-w-md animate-drop-in items-start gap-3 rounded-2xl bg-night px-4 py-3.5 text-white shadow-float transition-opacity duration-500">
            <x-ui.icon :name="$icon" class="mt-0.5 h-5 w-5 {{ $iconColor }}" />
            <p class="flex-1 text-sm font-medium leading-relaxed">{{ session($type) }}</p>
            <button type="button" onclick="this.closest('[data-flash]').remove()" class="-m-1 rounded-full p-1 text-white/60 transition hover:bg-white/10 hover:text-white" aria-label="ปิดข้อความ">
                <x-ui.icon name="x" class="h-4 w-4" />
            </button>
        </div>
    @endif
@endforeach
