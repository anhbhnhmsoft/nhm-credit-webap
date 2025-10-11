@section('title', 'Thanh toán trực tuyến')

<div class="h-full w-full overflow-y-auto sm:overflow-x-hidden">
    <div class="w-full flex flex-col justify-start items-start">
        <div class="bg-[#800080] w-full flex items-center justify-between px-4 py-4">
            <a href="{{ route('loan-detail', ['id' => $loanLogId]) }}" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-white text-lg font-medium">Thanh toán trực tuyến</h1>
            <div class="w-6 h-6"></div>
        </div>

        @if ($message)
            <div class="bg-red-500 text-white p-4 rounded mb-4 mx-4 mt-4">
                {{ $message }}
            </div>
        @endif

        @if ($loan && $loanLog)
            <div class="w-full px-4 py-6">
                <div class="bg-white rounded-lg p-6 shadow-sm mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin nhận tiền</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Ngân hàng:</span>
                            <span class="text-sm font-medium text-gray-800">{{ $bankName }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Tên tài khoản:</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-800">{{ $accountName }}</span>
                                <button class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded" onclick="copyToClipboard('{{ $accountName }}')">Copy</button>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Số tài khoản:</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-800">{{ $accountNumber }}</span>
                                <button class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded" onclick="copyToClipboard('{{ $accountNumber }}')">Copy</button>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Số tiền cần trả:</span>
                            <span class="text-lg font-bold text-[#800080]">{{ number_format($totalDue, 0, ',', '.') }} VND</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">QR Code</h3>
                    <p class="text-sm text-gray-600 mb-4">QUÉT MÃ QR ĐỂ THANH TOÁN - CHUYỂN KHOẢN</p>
                    
                    <div class="flex justify-center mb-4">
                        <div class="bg-white p-4 rounded-lg border-2 border-gray-200">
                            <img src="{{ route('public_image', ['file_path' => $qrImagePath]) }}" alt="QR Code" class="w-72 h-72 mx-auto">
                        </div>
                    </div>
                    
                    <div class="flex justify-center items-center space-x-4 mb-4">
                        <span class="text-xs text-gray-500">napas</span>
                        <span class="text-xs text-gray-500">VIETQR™</span>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-800">{{ $accountName }}</p>
                        <p class="text-sm text-gray-600">{{ $accountNumber }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm mb-4">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <p class="text-sm text-gray-700">
                            <strong>Thanh toán vui lòng gửi hóa đơn cho hỗ trợ viên qua zalo để được hỗ trợ</strong>
                        </p>
                    </div>
                </div>
                <h4 class="text-md font-semibold text-gray-800 mb-3">*Hướng dẫn trả tiền</h4>
                    
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex items-start space-x-2">
                            <span>1.</span>
                            <p>Bạn có thể chọn chuyển khoản, đến ngân hàng, cửa hàng Viettel,... để thực hiện thao tác thanh toán tiền.</p>
                        </div>
                        
                        <div class="flex items-start space-x-2">
                            <span>2.</span>
                            <p>Vui lòng đảm bảo nhập đầy đủ tài khoản ngân hàng khi thực hiện thao tác trả nợ. Nếu có các chữ cái trong tài khoản, hãy đảm bảo nhập đầy đủ các chữ cái đó cũng là một phần của tài khoản. Việc nhập tài khoản không đầy đủ sẽ khiến ngân hàng mất tiền của bạn hoặc bị ngân hàng từ chối.</p>
                        </div>
                        
                        <div class="flex items-start space-x-2">
                            <span>3.</span>
                            <p>Do hệ thống ngân hàng, "chuyển khoản thông thường", không có thời gian cố định để nhận tiền. Vui lòng chờ 1-3 ngày làm việc để nhận tiền.</p>
                        </div>
                    </div>
        @else
            <div class="w-full px-4 py-6">
                <div class="bg-white rounded-lg p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">{{ $message ?: 'Không tìm thấy thông tin thanh toán' }}</p>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Sao chép thành công: ' + text);
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
