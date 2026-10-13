{{--
    ลิงก์ไปหน้าโปรไฟล์สาธารณะ ใช้: <x-ui.user-link :user="$user">{{ $user->name }}</x-ui.user-link>
    ผู้ใช้ที่ถูกระงับเปิดได้เฉพาะ Admin คนอื่นจะเห็นเป็นข้อความธรรมดาแทนลิงก์ที่พาไปหน้า 404
--}}
@props(['user'])
@if($user->isSuspended() && ! (Auth::user()?->isAdmin() ?? false))
    <span {{ $attributes }}>{{ $slot }}</span>
@else
    <a href="{{ route('users.show', $user) }}" {{ $attributes->class('hover:underline underline-offset-4') }}>{{ $slot }}</a>
@endif
