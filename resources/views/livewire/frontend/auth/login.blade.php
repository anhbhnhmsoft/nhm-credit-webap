@section('title', 'Đăng Nhập')

<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Đăng Nhập</h1>

    <form wire:submit.prevent="submit">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Số điện thoại</label>
            <input type="tel" wire:model="phone" class="w-full border rounded px-3 py-2" placeholder="VD: 0865 643 858" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Mật khẩu</label>
            <input type="password" wire:model="password" class="w-full border rounded px-3 py-2" placeholder="Nhập mật khẩu" required>
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">Đăng Nhập</button>
        </div>
    </form>

    @if (session()->has('error'))
        <div class="mt-4 text-red-600 text-center">{{ session('error') }}</div>
    @endif

    <div class="mt-4 text-center">
        <a href="{{ route('register') }}" class="text-black hover:underline">Chưa có tài khoản? Đăng ký</a>
    </div>
</div>