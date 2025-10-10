@section('title', 'Đăng Nhập')

<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Đăng Nhập</h1>

    <form wire:submit.prevent="submit">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Số điện thoại</label>
            <input type="tel" wire:model="phone" class="w-full border rounded px-3 py-2" placeholder="VD: 0865 643 858" required>
        </div>

        <div id="recaptcha-container" class="my-3"></div>

        <div>
            <label class="block text-sm font-medium mb-1">Mã OTP</label>
            <input type="text" wire:model="otp" class="w-full border rounded px-3 py-2" placeholder="Nhập mã OTP" required maxlength="6">
        </div>

        <div class="flex space-x-2 mt-4">
            <button type="button" id="send_otp" class="flex-1 bg-[#fef4bf] text-black font-bold py-2 px-4 rounded cursor-pointer" disabled onclick="sendOTP()">Gửi OTP</button>
            <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2 px-4 rounded">Đăng Nhập</button>
            <button type="button" id="resend_otp" class="flex-1 bg-gray-500 text-white font-bold py-2 px-4 rounded hidden" onclick="resendOTP()">Gửi lại OTP</button>
        </div>
    </form>

    @if (session()->has('error'))
        <div class="mt-4 text-red-600 text-center">{{ session('error') }}</div>
    @endif

    <div class="mt-4 text-center">
        <a href="{{ route('register') }}" class="text-black hover:underline">Chưa có tài khoản? Đăng ký</a>
    </div>
</div>