@section('title', 'Đăng ký tài khoản')

<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <div class="w-full flex justify-start items-center">
        <a href="javascript:history.back()" class="flex items-center space-x-2">
        <span role="img" aria-label="arrow-left" tabindex="-1" class="anticon anticon-arrow-left h-6 w-6">
            <svg viewBox="64 64 896 896" focusable="false" data-icon="arrow-left" width="1em" height="1em" fill="currentColor" aria-hidden="true">
                <path d="M872 474H286.9l350.2-304c5.6-4.9 2.2-14-5.2-14h-88.5c-3.9 0-7.6 1.4-10.5 3.9L155 487.8a31.96 31.96 0 000 48.3L535.1 866c1.5 1.3 3.3 2 5.2 2h91.5c7.4 0 10.8-9.2 5.2-14L286.9 550H872c4.4 0 8-3.6 8-8v-60c0-4.4-3.6-8-8-8z">
                </path>
            </svg>
        </span>
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-6 text-center">Đăng ký tài khoản</h1>

    <form wire:submit.prevent="submit" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Số điện thoại</label>
            <input type="tel" wire:model="phone" class="w-full border rounded px-3 py-2"
                   placeholder="VD: 0865 643 858" required>
            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Họ và tên</label>
            <input type="text" wire:model="fullName" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập họ và tên" required>
            @error('fullName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email (tùy chọn)</label>
            <input type="email" wire:model="email" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập email">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Mật khẩu</label>
            <input type="password" wire:model="password" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required>
            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Xác nhận mật khẩu</label>
            <input type="password" wire:model="confirmPassword" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập lại mật khẩu" required>
            @error('confirmPassword') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">Đăng ký</button>
        </div>
    </form>

    @if (session()->has('success'))
        <div class="mt-4 text-green-600 text-center">{{ session('success') }}</div>
    @endif

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-black hover:underline">
            Đã có tài khoản? Đăng nhập
        </a>
    </div>
</div>