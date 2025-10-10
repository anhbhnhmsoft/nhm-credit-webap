@section('title', 'Đăng ký - Thông Tin Cá Nhân')

<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Thông Tin Cá Nhân</h1>

    <form id="registerInfoForm" class="space-y-4" wire:submit.prevent="submitPersonalInfo">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Họ và tên</label>
            <input type="text" id="fullName" class="w-full border rounded px-3 py-2" placeholder="Nhập họ và tên" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" id="email" class="w-full border rounded px-3 py-2" placeholder="Nhập email" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Mật khẩu</label>
            <input type="password" id="password" class="w-full border rounded px-3 py-2" placeholder="Nhập mật khẩu" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Xác nhận mật khẩu</label>
            <input type="password" id="confirmPassword" class="w-full border rounded px-3 py-2" placeholder="Xác nhận mật khẩu" required>
        </div>

        <div class="flex space-x-2">
            <button type="submit" id="submit_personal_info" class="flex-1 bg-blue-600 text-white font-bold py-2 px-4 rounded cursor-pointer">Tiếp Tục</button>
        </div>
    </form>
</div>