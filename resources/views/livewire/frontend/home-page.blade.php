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

        .range-primary-color {
            --range-thumb: black;
            color: #fef4bf;
        }
    </style>

    <div class="h-full w-full overflow-y-auto sm:overflow-x-hidden">
        <div class="space-y-3 pb-3">
            <div class="hero-bg w-full bg-contain object-fill bg-no-repeat border-none relative">
                <div class="w-full h-full md:h-auto flex flex-col justify-start items-center space-y-2 pt-12 md:pt-10">
                    <span class="text-[11.333vw] md:text-[6vw] lg:text-[4vw] text-center text-white font-medium lato-light">
                        @if($activeLoanPackage)
                            {{ number_format(data_get($activeLoanPackage->config_loans, 'max_amount', 20000000)) }}
                        @else
                            20.000.000
                        @endif
                    </span>
                    <p class="max-w-[85vw] md:max-w-[350px] text-center text-white md:text-xl">
                        2 phút nộp đơn trực tuyến · 5 phút cho vay nhanh chóng
                    </p>
                </div>
                <div class="absolute top-[200px] md:top-[34%] md:left-[5%]">
                    <div class="flex justify-center items-center w-screen md:w-[400px]">
                        <div class="p-3 bg-white shadow-md flex flex-row justify-start items-start w-[93vw] rounded-xl space-x-3">
                            <span role="img" aria-label="bell" class="anticon anticon-bell text-[#818488]">
                                <svg viewBox="64 64 896 896" focusable="false" data-icon="bell" width="1em" height="1em" fill="currentColor" aria-hidden="true">
                                    <path d="M816 768h-24V428c0-141.1-104.3-257.8-240-277.2V112c0-22.1-17.9-40-40-40s-40 17.9-40 40v38.8C336.3 170.2 232 286.9 232 428v340h-24c-17.7 0-32 14.3-32 32v32c0 4.4 3.6 8 8 8h216c0 61.8 50.2 112 112 112s112-50.2 112-112h216c4.4 0 8-3.6 8-8v-32c0-17.7-14.3-32-32-32zM512 888c-26.5 0-48-21.5-48-48h96c0 26.5-21.5 48-48 48z"></path>
                                </svg>
                            </span>
                            <p class="text-sm text-[#818488] font-normal tracking-wide">Tất cả các sản phẩm cho vay trong ứng dụng này là độc lập, được điều hành bởi các công ty riêng biệt, không ảnh hưởng đến nhau, trả nợ một sẽ không giúp các hộ gia đình khác san lần</p>
                        </div>
                    </div>
                </div>

                <div class="mt-[40px] md:mt-[180px]">
                    <div class="flex flex-col justify-center items-center w-screen md:w-full">
                        <div class="w-[93vw] md:w-[400px] bg-white rounded-lg shadow relative flex flex-col justify-start items-center px-3 pb-3" wire:ignore>
                            <div class="relative w-full">
                                <img class="w-full h-[20px]" src="images/bg-loan-app.png">
                                <span class="absolute top-0 left-0 w-full h-[20px] flex items-center justify-center text-[13px] text-black font-medium">Số tiền vay</span>
                            </div>
                            <span class="text-4xl primary-color text-center font-normal lato-light mt-8" id="loan-amount">
                                @if($activeLoanPackage)
                                {{ number_format($amount / 1000, 0, ',', '.') }}K
                                @else
                                    20.000.000
                                @endif
                            </span>
                            <div class="ant-slider w-full mt-8" wire:ignore>
                                <input type="range" id="loan-slider" class="range range-primary range-primary-color primary-color w-full"
                                       min="{{ $activeLoanPackage ? data_get($activeLoanPackage->config_loans, 'min_amount', 2000000) / 1000 : 2000 }}"
                                       max="{{ $activeLoanPackage ? data_get($activeLoanPackage->config_loans, 'max_amount', 20000000) / 1000 : 20000 }}"
                                       step="500"
                                       value="{{ $amount / 1000 }}"
                                       onchange="updateAmount(this)">
                            </div>

                            <div class="w-full flex flex-row justify-between items-center mt-4">
                                @if($activeLoanPackage)
                                    @foreach($quickAmounts as $index => $quickAmount)
                                        <div class="text-base text-[var(--primary)] {{ $index === count($quickAmounts) - 1 ? 'font-semibold' : '' }}">
                                            {{ number_format($quickAmount / 1000) }}K
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-base text-[var(--primary)]">2000K</div>
                                    <div class="text-base text-[var(--primary)]">6500K</div>
                                    <div class="text-base text-[var(--primary)]">11000K</div>
                                    <div class="text-base text-[var(--primary)]">15500K</div>
                                    <div class="text-base text-[var(--primary)] font-semibold">20000K</div>
                                @endif
                            </div>
                            <div class="w-full h-px bg-gray-200 my-4"></div>
                            <div class="flex flex-row justify-between w-full items-center mt-4">
                                <span class="text-base text-gray-600">Số ngày vay</span>
                                <div class="flex flex-row justify-center items-center space-x-3">
                                    @if($activeLoanPackage)
                                        @php
                                            $termMonths = data_get($activeLoanPackage->config_loans, 'term_month', []);
                                        @endphp
                                        @if(is_array($termMonths) && !empty($termMonths))
                                            @foreach($termMonths as $months)
                                                <button
                                                    onclick="selectTerm({{ $months }})"
                                                    class="text-sm px-5 py-1 rounded-2xl cursor-pointer term-button {{ $selectedTermMonths == $months ? 'bg-yellow-400 text-black' : 'text-gray-400 border border-solid border-gray-500 border-opacity-25' }}"
                                                    data-months="{{ $months }}">
                                                    {{ $months * 30 }} ngày
                                                </button>
                                            @endforeach
                                        @else
                                            <button class="text-sm px-5 py-1 rounded-2xl bg-yellow-400 text-black cursor-pointer">
                                                7 ngày
                                            </button>
                                        @endif
                                    @else
                                        <button
                                            onclick="selectTerm(6)"
                                            class="text-sm px-5 py-1 rounded-2xl cursor-pointer term-button {{ $selectedTermMonths == 6 ? 'bg-yellow-400 text-black' : 'text-gray-400 border border-solid border-gray-500 border-opacity-25' }}"
                                            data-months="6">7 ngày</button>
                                        <button
                                            onclick="selectTerm(12)"
                                            class="text-sm px-5 py-1 rounded-2xl cursor-pointer term-button {{ $selectedTermMonths == 12 ? 'bg-yellow-400 text-black' : 'text-gray-400 border border-solid border-gray-500 border-opacity-25' }}"
                                            data-months="12">14 ngày</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button
                            onclick="submitWithAmount()"
                            class="w-[93vw] md:w-[400px] py-3 text-black text-center btn-custom rounded-3xl mt-5 font-semibold cursor-pointer">
                            Gửi yêu cầu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateAmount(slider) {
        var amount = slider.value;
        var formattedAmount = parseInt(amount).toLocaleString('vi-VN');
        document.getElementById('loan-amount').innerText = formattedAmount + "K";

        document.getElementById('loan-slider').setAttribute('data-amount', amount * 1000);
    }

    function getSliderAmount() {
        return parseInt(document.getElementById('loan-slider').getAttribute('data-amount') || document.getElementById('loan-slider').value * 1000);
    }

    function selectTerm(months) {
        var buttons = document.querySelectorAll('.term-button');
        buttons.forEach(function(button) {
            var buttonMonths = parseInt(button.getAttribute('data-months'));
            if (buttonMonths === months) {
                button.className = 'text-sm px-5 py-1 rounded-2xl cursor-pointer term-button bg-yellow-400 text-black';
            } else {
                button.className = 'text-sm px-5 py-1 rounded-2xl cursor-pointer term-button text-gray-400 border border-solid border-gray-500 border-opacity-25';
            }
        });

        Livewire.find('{{ $this->getId() }}').call('setSelectedTerm', months);
    }

    function submitWithAmount() {
        var amount = getSliderAmount();
        console.log('Submitting with amount:', amount);

        Livewire.find('{{ $this->getId() }}').call('submitLoanRequest', amount);
    }
</script>
