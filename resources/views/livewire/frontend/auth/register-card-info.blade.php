@section('title', 'Đăng ký - Thông Tin Thẻ')

<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Thông Tin Thẻ</h1>

    <form id="registerCardInfoForm" class="space-y-4" wire:submit.prevent="submitCardInfo">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Tên trên thẻ</label>
            <input type="text" id="name_card" class="w-full border rounded px-3 py-2" placeholder="Tên trên thẻ" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Số thẻ</label>
            <input type="text" id="number_card" class="w-full border rounded px-3 py-2" placeholder="VD: 1234 5678 9123" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Ảnh mặt trước</label>
            <input type="file" id="front_image_card" class="block" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Ảnh CMND/CCCD Selfie</label>
            <input type="file" id="id_card_selfie_path" class="block" required>
        </div>

        <div class="flex space-x-2">
            <button type="submit" id="submit_card_info" class="flex-1 bg-blue-600 text-white font-bold py-2 px-4 rounded cursor-pointer">Hoàn Thành</button>
        </div>
    </form>
</div>