<?php

namespace App\Livewire\HomePage;

use App\Models\Bank;
use App\Models\LoanPackage;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\UserLoan;
use App\Utils\Constants\LoanStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class HomePage extends Component
{
    use WithFileUploads;

    private const DEFAULT_LOAN_CONFIG = [
        'name' => 'Gói vay mặc định',
        'term_month' => [7, 14],
        'interest_rate' => 0,
        'penalty_rate' => 0,
        'min_amount' => 2000000,
        'max_amount' => 20000000,
        'active' => true,
    ];

    private const TERM_OPTIONS = [7, 14];

    public $amount = 2000000;
    public array $quickAmounts = [2000000, 5000000, 10000000, 15000000, 20000000];
    public array $banks = [];

    public $activeLoanPackage = null;
    public int $selectedTermDays = 7;
    public int $currentStep = 1;

    public $bank_id = '';
    public $account_number = '';
    public $account_name = '';

    public $address = '';
    public $name_card = '';
    public $user_number_card = '';
    public $front_image_card;
    public $back_image_card;
    public $id_card_selfie_path;

    public ?string $existing_front_image_card = null;
    public ?string $existing_back_image_card = null;
    public ?string $existing_id_card_selfie_path = null;

    public function setAmount(int $value): void
    {
        $this->amount = $value;
    }

    public function setSelectedTermDays(int $days): void
    {
        if (in_array($days, self::TERM_OPTIONS, true)) {
            $this->selectedTermDays = $days;
        }
    }

    public function updatedAmount(): void
    {
        [$minAmount, $maxAmount] = $this->getLoanAmountRange();
        $this->amount = max($minAmount, min((int) $this->amount, $maxAmount));
    }

    public function mount(): void
    {
        $this->activeLoanPackage = LoanPackage::whereJsonContains('config_loans->active', true)->first();
        $this->banks = Bank::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Bank $bank) => [
                'id' => $bank->id,
                'name' => $bank->name,
            ])
            ->all();

        $config = $this->getLoanConfig();
        $this->amount = (int) data_get($config, 'min_amount', self::DEFAULT_LOAN_CONFIG['min_amount']);

        [$minAmount, $maxAmount] = $this->getLoanAmountRange();
        $minK = $minAmount / 1000;
        $maxK = $maxAmount / 1000;

        $this->quickAmounts = [
            floor($minK / 1000) * 1000 * 1000,
            floor(($minK + ($maxK - $minK) * 0.25) / 1000) * 1000 * 1000,
            floor(($minK + ($maxK - $minK) * 0.5) / 1000) * 1000 * 1000,
            floor(($minK + ($maxK - $minK) * 0.75) / 1000) * 1000 * 1000,
            floor($maxK / 1000) * 1000 * 1000,
        ];

        $this->selectedTermDays = self::TERM_OPTIONS[0];
        $this->hydrateUserState();
    }

    public function submitLoanRequest()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            session()->flash('error', 'Bạn cần đăng nhập để gửi yêu cầu vay.');
            return redirect()->route('register');
        }

        if (!$this->validateLoanSelection()) {
            return;
        }

        if ($this->needsBankStep($user)) {
            $this->currentStep = 2;
            return;
        }

        if ($this->needsProfileStep($user)) {
            $this->currentStep = 3;
            return;
        }

        return $this->createLoanForUser($user);
    }

    public function submitBankStep()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            session()->flash('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
            return redirect()->route('register');
        }

        $existingAccount = $user->userBankAccounts()->first();

        $validated = $this->validate([
            'bank_id' => ['required', 'exists:banks,id'],
            'account_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('user_bank_accounts', 'account_number')->ignore($existingAccount?->id),
            ],
            'account_name' => ['required', 'string', 'max:255'],
        ], [
            'bank_id.required' => 'Vui lòng chọn ngân hàng.',
            'bank_id.exists' => 'Ngân hàng không hợp lệ.',
            'account_number.required' => 'Vui lòng nhập số tài khoản.',
            'account_number.max' => 'Số tài khoản không được quá 20 ký tự.',
            'account_number.unique' => 'Số tài khoản này đã được sử dụng.',
            'account_name.required' => 'Vui lòng nhập tên chủ tài khoản.',
            'account_name.max' => 'Tên chủ tài khoản không được quá 255 ký tự.',
        ]);

        $bank = Bank::query()->find($validated['bank_id']);
        $bankPayload = [
            'bank_id' => $validated['bank_id'],
            'bank_name' => $bank?->name,
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'is_verified' => false,
        ];

        if ($existingAccount) {
            $existingAccount->update($bankPayload);
        } else {
            $user->userBankAccounts()->create($bankPayload);
        }

        $user = $user->fresh(['userBankAccounts']);
        $this->syncSessionUser($user);
        $this->hydrateUserState();

        if ($this->needsProfileStep($user)) {
            $this->currentStep = 3;
            return;
        }

        return $this->createLoanForUser($user);
    }

    public function submitProfileStep()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            session()->flash('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
            return redirect()->route('register');
        }

        if ($this->needsBankStep($user)) {
            $this->currentStep = 2;
            session()->flash('error', 'Vui lòng nhập tài khoản ngân hàng trước.');
            return null;
        }

        $validated = $this->validate([
            'address' => ['required', 'string', 'max:255'],
            'name_card' => ['required', 'string', 'max:255'],
            'user_number_card' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'number_card')->ignore($user->id),
            ],
            'front_image_card' => [$this->existing_front_image_card ? 'nullable' : 'required', 'image', 'max:2048'],
            'back_image_card' => [$this->existing_back_image_card ? 'nullable' : 'required', 'image', 'max:2048'],
            'id_card_selfie_path' => [$this->existing_id_card_selfie_path ? 'nullable' : 'required', 'image', 'max:2048'],
        ], [
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'name_card.required' => 'Vui lòng nhập tên trên giấy tờ.',
            'user_number_card.required' => 'Vui lòng nhập số CMND/CCCD.',
            'user_number_card.unique' => 'Số CMND/CCCD đã được sử dụng.',
            'front_image_card.required' => 'Vui lòng tải ảnh mặt trước CMND/CCCD.',
            'front_image_card.image' => 'Ảnh mặt trước phải là tệp hình ảnh.',
            'back_image_card.required' => 'Vui lòng tải ảnh mặt sau CMND/CCCD.',
            'back_image_card.image' => 'Ảnh mặt sau phải là tệp hình ảnh.',
            'id_card_selfie_path.required' => 'Vui lòng tải ảnh chụp chính chủ.',
            'id_card_selfie_path.image' => 'Ảnh chụp chính chủ phải là tệp hình ảnh.',
        ]);

        $user->update([
            'address' => $validated['address'],
            'name_card' => $validated['name_card'],
            'number_card' => $validated['user_number_card'],
            'front_image_card' => $this->storeUploadedFile($this->front_image_card, $this->existing_front_image_card),
            'back_image_card' => $this->storeUploadedFile($this->back_image_card, $this->existing_back_image_card),
            'id_card_selfie_path' => $this->storeUploadedFile($this->id_card_selfie_path, $this->existing_id_card_selfie_path),
        ]);

        $user = $user->fresh(['userBankAccounts']);
        $this->syncSessionUser($user);
        $this->hydrateUserState();

        return $this->createLoanForUser($user);
    }

    public function goToStep(int $step): void
    {
        if (!in_array($step, [1, 2, 3], true)) {
            return;
        }

        if ($step === 3) {
            $user = $this->getCurrentUser();
            if ($user && $this->needsBankStep($user)) {
                $this->currentStep = 2;
                return;
            }
        }

        $this->currentStep = $step;
    }

    private function getLoanConfig(): array
    {
        $config = $this->activeLoanPackage?->config_loans;

        if (!is_array($config)) {
            $config = [];
        }

        return array_replace(self::DEFAULT_LOAN_CONFIG, $config);
    }

    private function getLoanAmountRange(): array
    {
        $config = $this->getLoanConfig();

        return [
            (int) data_get($config, 'min_amount', self::DEFAULT_LOAN_CONFIG['min_amount']),
            (int) data_get($config, 'max_amount', self::DEFAULT_LOAN_CONFIG['max_amount']),
        ];
    }

    private function validateLoanSelection(): bool
    {
        [$minAmount, $maxAmount] = $this->getLoanAmountRange();

        if ($this->amount < $minAmount || $this->amount > $maxAmount) {
            session()->flash('error', "Số tiền vay phải từ " . number_format($minAmount) . " đến " . number_format($maxAmount) . " VNĐ.");
            return false;
        }

        if (!in_array($this->selectedTermDays, self::TERM_OPTIONS, true)) {
            session()->flash('error', 'Thời lượng vay không hợp lệ.');
            return false;
        }

        return true;
    }

    private function createLoanForUser(User $user)
    {
        try {
            $config = $this->getLoanConfig();
            UserLoan::create([
                'user_id' => $user->id,
                'loan_package_id' => $this->activeLoanPackage?->id,
                'principal_amount' => $this->amount,
                'term_months' => $this->selectedTermDays,
                'interest_rate_year' => (float) data_get($config, 'interest_rate', 0),
                'service_fee_amount' => 0,
                'disbursed_amount' => 0,
                'total_due_amount' => $this->amount,
                'total_paid_amount' => 0,
                'status' => LoanStatus::PENDING->value,
            ]);

            $this->currentStep = 1;
            session()->flash('success', 'Yêu cầu vay đã được gửi thành công! Chúng tôi sẽ xem xét và phản hồi trong thời gian sớm nhất.');
            return redirect()->route('loan-application', ['tab' => 'approved']);
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra khi gửi yêu cầu vay. Vui lòng thử lại.');
            return null;
        }
    }

    private function getCurrentUser(): ?User
    {
        if (Auth::check()) {
            return Auth::user()?->loadMissing('userBankAccounts');
        }

        $sessionUser = session('user');
        if (!$sessionUser) {
            return null;
        }

        return User::query()
            ->with('userBankAccounts')
            ->find($sessionUser->id);
    }

    private function hydrateUserState(): void
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return;
        }

        $this->address = (string) ($user->address ?? '');
        $this->name_card = (string) ($user->name_card ?? '');
        $this->user_number_card = (string) ($user->number_card ?? '');
        $this->existing_front_image_card = $user->front_image_card;
        $this->existing_back_image_card = $user->back_image_card;
        $this->existing_id_card_selfie_path = $user->id_card_selfie_path;
        $this->front_image_card = null;
        $this->back_image_card = null;
        $this->id_card_selfie_path = null;

        $bankAccount = $user->userBankAccounts->first();
        $this->bank_id = (string) ($bankAccount?->bank_id ?? '');
        $this->account_number = (string) ($bankAccount?->account_number ?? '');
        $this->account_name = (string) ($bankAccount?->account_name ?? '');
    }

    private function needsBankStep(User $user): bool
    {
        $bankAccount = $user->userBankAccounts->first();

        return !$bankAccount
            || empty($bankAccount->bank_id)
            || empty($bankAccount->account_number)
            || empty($bankAccount->account_name);
    }

    private function needsProfileStep(User $user): bool
    {
        return empty($user->address)
            || empty($user->name_card)
            || empty($user->number_card)
            || empty($user->front_image_card)
            || empty($user->back_image_card)
            || empty($user->id_card_selfie_path);
    }

    private function storeUploadedFile($file, ?string $existingPath = null): ?string
    {
        if (empty($file)) {
            return $existingPath;
        }

        if (is_array($file)) {
            $file = $file[0] ?? null;
        }

        if (is_string($file)) {
            return $file;
        }

        if (is_object($file) && method_exists($file, 'store')) {
            return $file->store('user-documents', 'private');
        }

        return $existingPath;
    }

    private function syncSessionUser(User $user): void
    {
        if (session()->has('user')) {
            session()->put('user', $user);
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.frontend.home-page');
    }
}
