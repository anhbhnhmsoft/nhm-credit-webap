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

    <form id="registerForm" class="space-y-4">
        @csrf
        <div id="phone_input">
            <label class="block text-sm font-medium mb-1">Số điện thoại</label>
            <input type="tel" id="phoneNumber" class="w-full border rounded px-3 py-2"
                   placeholder="VD: 0865 643 858" required>
        </div>

        <div class="mt-3">
            <label class="block text-sm font-medium mb-1">Họ và tên</label>
            <input type="text" id="fullName" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập họ và tên" required>
        </div>

        <div class="mt-3">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" id="email" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập email">
        </div>

        <div class="mt-3">
            <label class="block text-sm font-medium mb-1">Mật khẩu</label>
            <input type="password" id="password" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập mật khẩu" required>
        </div>

        <div id="recaptcha-container" class="my-3"></div>

        <div class="flex space-x-2">
            <button type="button" id="send_otp" onclick="sendOTP()" class="flex-1 bg-[#fef4bf] text-black font-bold py-2 px-4 rounded cursor-pointer" disabled>Gửi OTP</button>
        </div>

        <div id="verification_input" class="hidden mt-4">
            <label class="block text-sm font-medium mb-1">Mã OTP</label>
            <input type="text" id="verification_code" class="w-full border rounded px-3 py-2" placeholder="Nhập mã OTP 6 số" maxlength="6">
            <div class="flex space-x-2 mt-3">
                <button type="button" class="flex-1 bg-green-600 text-white py-2 px-4 rounded" onclick="verifyOTP()">Xác thực OTP</button>
                <button type="button" class="flex-1 bg-gray-200 text-black py-2 px-4 rounded" onclick="tryAgain()">Gửi lại OTP</button>
            </div>
        </div>

        <div id="status" class="text-sm"></div>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-black hover:underline">
            Đã có tài khoản? Đăng nhập
        </a>
    </div>
</div>
@vite('resources/js/firebase.js')