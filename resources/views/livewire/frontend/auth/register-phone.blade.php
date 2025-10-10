@section('title', 'Đăng ký - Số Điện Thoại')

<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Đăng Ký Số Điện Thoại</h1>

    <form id="registerPhoneForm" class="space-y-4" wire:submit.prevent="nextStep">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Số điện thoại</label>
            <input type="tel" id="phoneNumber" class="w-full border rounded px-3 py-2" placeholder="VD: 0865 643 858" required>
        </div>

        <div>
            <div id="recaptcha-container"></div>
        </div>

        <div class="flex space-x-2">
            <button type="submit" id="send_otp" class="flex-1 bg-[#fef4bf] text-black font-bold py-2 px-4 rounded cursor-pointer">Gửi OTP</button>
        </div>
    </form>
</div>

@vite('resources/js/firebase.js')