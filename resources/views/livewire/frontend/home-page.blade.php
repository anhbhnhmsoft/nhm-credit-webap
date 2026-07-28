@section('title', 'Trang chủ')

<div>
    <style>
        .hero-bg {
            background-image: url('/images/bg-home.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .lato-light {
            font-family: Lato, serif;
            font-weight: 300;
            font-style: normal;
        }

        .btn-custom {
            background-color: #fef4bf;
        }

        .btn-custom:hover {
            background-color: #fef4bf;
        }

        .range-primary-color {
            --range-thumb: #645b5b;
            color: #fef4bf;
        }
        /* Fix overlap for widths <= 770px: place banner info in normal flow */
        @media (max-width: 770px) {
            .hero-info { position: static !important; top: auto !important; left: auto !important; width: 100% !important; }
            .hero-pad { padding-bottom: 1rem; }
        }
    </style>

    <div class="h-full w-full overflow-y-auto sm:overflow-x-hidden">
        <div class="space-y-3 pb-3 max-w-[768px] mx-auto">
            <div class="hero-bg w-full bg-contain object-fill bg-no-repeat border-none relative hero-pad">
                <div class="w-full h-full md:h-auto flex flex-col justify-start items-center space-y-2 pt-12 md:pt-10">
                    <span class="text-[11.333vw] md:text-[6vw] lg:text-[4vw] text-center text-white font-medium lato-light">
                        {{ number_format(max($quickAmounts)) }}
                    </span>
                    <p class="max-w-[85vw] md:max-w-[350px] text-center text-white md:text-xl">
                        2 phút nộp đơn trực tuyến · 5 phút cho vay nhanh chóng
                    </p>
                </div>
                <div class="hero-info absolute top-[200px] md:top-[34%] md:left-[5%]">
                    <div class="flex justify-center items-center w-full md:w-[400px]">
                        <div
                            class="p-3 bg-white shadow-md flex flex-row justify-start items-start w-[93vw] rounded-xl space-x-3 m-5 mb-0 md:m-0">
                            <span role="img" aria-label="bell" class="anticon anticon-bell text-[#818488]">
                                <svg viewBox="64 64 896 896" focusable="false" data-icon="bell" width="1em" height="1em" fill="currentColor" aria-hidden="true">
                                    <path d="M816 768h-24V428c0-141.1-104.3-257.8-240-277.2V112c0-22.1-17.9-40-40-40s-40 17.9-40 40v38.8C336.3 170.2 232 286.9 232 428v340h-24c-17.7 0-32 14.3-32 32v32c0 4.4 3.6 8 8 8h216c0 61.8 50.2 112 112 112s112-50.2 112-112h216c4.4 0 8-3.6 8-8v-32c0-17.7-14.3-32-32-32zM512 888c-26.5 0-48-21.5-48-48h96c0 26.5-21.5 48-48 48z"></path>
                                </svg>
                            </span>
                            <p class="text-sm text-[#818488] font-normal tracking-wide">
                                Tất cả các sản phẩm cho vay trong ứng dụng này là độc lập, được điều hành bởi các công ty riêng biệt, không ảnh hưởng đến nhau, trả nợ một sẽ không giúp các hộ gia đình khác san lần
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-[40px] md:mt-[220px]">
                    <div class="flex flex-col justify-center items-center w-full">
                        <div class="w-[90vw] md:w-[400px] max-w-[400px] overflow-hidden bg-white rounded-lg shadow relative flex flex-col justify-start px-3 pb-3">

                            @if ($currentStep === 1)
                                <form wire:submit.prevent="submitLoanRequest" class="space-y-5 mt-5">
                                    <div class="relative w-full">
                                        <img class="w-full h-[20px]" src="images/bg-loan-app.png" alt="">
                                        <span class="absolute top-0 left-0 w-full h-[20px] flex items-center justify-center text-[13px] text-black font-medium">
                                            Số tiền vay
                                        </span>
                                    </div>

                                    <div class="text-center">
                                        <div class="text-4xl primary-color font-normal lato-light">
                                            {{ number_format($amount, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">VNĐ</div>
                                    </div>

                                    <div class="ant-slider w-full">
                                        <input type="range"
                                            wire:model.live="amount"
                                            class="range range-primary range-primary-color primary-color w-full"
                                            min="{{ min($quickAmounts) }}"
                                            max="{{ max($quickAmounts) }}"
                                            step="100000">
                                    </div>

                                    <div class="grid grid-cols-5 gap-2">
                                        @foreach ($quickAmounts as $quickAmount)
                                            <button type="button" wire:click="setAmount({{ $quickAmount }})"
                                                class="rounded-lg border px-2 py-2 text-xs font-semibold transition-colors {{ $amount == $quickAmount ? 'border-black bg-[#fef4bf] text-black' : 'border-gray-200 bg-white text-gray-600' }}">
                                                {{ number_format($quickAmount / 1000) }}K
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-base text-gray-600">Thời lượng vay</span>
                                            <div class="flex items-center gap-2">
                                                @foreach ([7, 14] as $dayOption)
                                                    <button type="button" wire:click="setSelectedTermDays({{ $dayOption }})"
                                                        class="rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ $selectedTermDays === $dayOption ? 'bg-[#fef4bf] text-black' : 'border border-gray-200 text-gray-500' }}">
                                                        {{ $dayOption }} ngày
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" wire:loading.attr="disabled" wire:target="submitLoanRequest"
                                        class="w-full py-3 text-black text-center btn-custom rounded-3xl font-semibold cursor-pointer disabled:opacity-70">
                                        Tiếp tục
                                    </button>
                                </form>
                            @endif

                            @if ($currentStep === 2)
                                <form wire:submit.prevent="submitBankStep" class="space-y-4 mt-5">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Ngân hàng</label>
                                        <select wire:model="bank_id" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                            <option value="">Chọn ngân hàng</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank['id'] }}">{{ $bank['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('bank_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Số tài khoản</label>
                                        <input type="text" wire:model="account_number" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                        @error('account_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Tên chủ tài khoản</label>
                                        <input type="text" wire:model="account_name" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                        @error('account_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex gap-3">
                                        <button type="button" wire:click="goToStep(1)"
                                            class="flex-1 rounded-3xl border border-gray-200 py-3 text-sm font-semibold text-gray-600">
                                            Quay lại
                                        </button>
                                        <button type="submit" wire:loading.attr="disabled" wire:target="submitBankStep"
                                            class="flex-1 rounded-3xl btn-custom py-3 text-sm font-semibold text-black disabled:opacity-70">
                                            Tiếp tục
                                        </button>
                                    </div>
                                </form>
                            @endif

                            @if ($currentStep === 3)
                                <form wire:submit.prevent="submitProfileStep" class="space-y-4 mt-5">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Địa chỉ</label>
                                        <input type="text" wire:model="address" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                        @error('address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Tên trên giấy tờ</label>
                                        <input type="text" wire:model="name_card" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                        @error('name_card') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Số CMND/CCCD</label>
                                        <input type="text" wire:model="user_number_card" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                        @error('user_number_card') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Ảnh mặt trước CMND/CCCD</label>
                                        @if ($front_image_card)
                                            <img src="{{ $front_image_card->temporaryUrl() }}" alt="" class="mb-2 h-28 w-full rounded-lg object-cover">
                                        @elseif ($existing_front_image_card)
                                            <img src="{{ route('public_image', ['file_path' => $existing_front_image_card]) }}" alt="" class="mb-2 h-28 w-full rounded-lg object-cover">
                                        @endif
                                        <input id="front-image-card" type="file" wire:model="front_image_card" accept="image/*" class="sr-only">
                                        <label for="front-image-card"
                                            class="flex min-h-20 cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-center transition hover:border-amber-400 hover:bg-amber-100">
                                            <span>
                                                <span class="block text-sm font-semibold text-gray-800">{{ $front_image_card || $existing_front_image_card ? 'Chọn ảnh khác' : 'Chọn ảnh mặt trước' }}</span>
                                                <span class="mt-1 block text-xs text-gray-500" wire:loading.remove wire:target="front_image_card">JPG, PNG hoặc WEBP - tối đa 2 MB</span>
                                                <span class="mt-1 block text-xs font-medium text-amber-700" wire:loading wire:target="front_image_card">Đang tải ảnh...</span>
                                            </span>
                                        </label>
                                        @error('front_image_card') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Ảnh mặt sau CMND/CCCD</label>
                                        @if ($back_image_card)
                                            <img src="{{ $back_image_card->temporaryUrl() }}" alt="" class="mb-2 h-28 w-full rounded-lg object-cover">
                                        @elseif ($existing_back_image_card)
                                            <img src="{{ route('public_image', ['file_path' => $existing_back_image_card]) }}" alt="" class="mb-2 h-28 w-full rounded-lg object-cover">
                                        @endif
                                        <input id="back-image-card" type="file" wire:model="back_image_card" accept="image/*" class="sr-only">
                                        <label for="back-image-card"
                                            class="flex min-h-20 cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-center transition hover:border-amber-400 hover:bg-amber-100">
                                            <span>
                                                <span class="block text-sm font-semibold text-gray-800">{{ $back_image_card || $existing_back_image_card ? 'Chọn ảnh khác' : 'Chọn ảnh mặt sau' }}</span>
                                                <span class="mt-1 block text-xs text-gray-500" wire:loading.remove wire:target="back_image_card">JPG, PNG hoặc WEBP - tối đa 2 MB</span>
                                                <span class="mt-1 block text-xs font-medium text-amber-700" wire:loading wire:target="back_image_card">Đang tải ảnh...</span>
                                            </span>
                                        </label>
                                        @error('back_image_card') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Ảnh chụp chính chủ</label>
                                        @if ($id_card_selfie_path)
                                            <img src="{{ $id_card_selfie_path->temporaryUrl() }}" alt="" class="mb-2 h-28 w-full rounded-lg object-cover">
                                        @elseif ($existing_id_card_selfie_path)
                                            <img src="{{ route('public_image', ['file_path' => $existing_id_card_selfie_path]) }}" alt="" class="mb-2 h-28 w-full rounded-lg object-cover">
                                        @endif
                                        <input id="id-card-selfie" type="file" wire:model="id_card_selfie_path" accept="image/*" class="sr-only">
                                        <label for="id-card-selfie"
                                            class="flex min-h-20 cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-center transition hover:border-amber-400 hover:bg-amber-100">
                                            <span>
                                                <span class="block text-sm font-semibold text-gray-800">{{ $id_card_selfie_path || $existing_id_card_selfie_path ? 'Chọn ảnh khác' : 'Chọn ảnh chụp chính chủ' }}</span>
                                                <span class="mt-1 block text-xs text-gray-500" wire:loading.remove wire:target="id_card_selfie_path">JPG, PNG hoặc WEBP - tối đa 2 MB</span>
                                                <span class="mt-1 block text-xs font-medium text-amber-700" wire:loading wire:target="id_card_selfie_path">Đang tải ảnh...</span>
                                            </span>
                                        </label>
                                        @error('id_card_selfie_path') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex gap-3">
                                        <button type="button" wire:click="goToStep(2)"
                                            class="flex-1 rounded-3xl border border-gray-200 py-3 text-sm font-semibold text-gray-600">
                                            Quay lại
                                        </button>
                                        <button type="submit" wire:loading.attr="disabled" wire:target="submitProfileStep"
                                            class="flex-1 rounded-3xl btn-custom py-3 text-sm font-semibold text-black disabled:opacity-70">
                                            Tạo khoản vay
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
