<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Bank;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\UserResetCode;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function login(array $data): array
    {
        try {
            $user = User::query()
                ->where('email', $data['email'])
                ->first();
            if (!$user || ! Hash::check($data['password'], $user->password)) {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }
            if (!$user->hasVerifiedEmail()) {
                throw new ServiceException(__('auth.error.unverified_email'));
            }
            $user->save();
            $token = $user->createToken('api')->plainTextToken;
            return [
                'status' => true,
                'token' => $token,
                'user' => $user,
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function register(array $data): array
    {
        DB::beginTransaction();
        try {
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => RoleUser::CUSTOMER->value,
            ]);
            $url = URL::temporarySignedRoute(
                'api.verification.verify',
                now()->addMinutes(60),
                ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
            );
            Mail::raw(__('auth.success.verify_email_body') . " {$url}", fn($m) => $m->to($user->email)->subject('Verify Email'));
            DB::commit();
            return [
                'status' => true,
            ];
        }  catch (\Throwable $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function forgotPassword(array $data): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            UserResetCode::where('user_id', $user->id)
                ->where('email', $data['email'])
                ->whereNull('deleted_at')
                ->delete();

            UserResetCode::create([
                'user_id' => $user->id,
                'email' => $data['email'],
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]);


            Mail::to($user->email)->send(new ResetPasswordMail($code));

            return [
                'status' => true,
                'message' => __('auth.success.reset_sent'),
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function confirmPassword(array $data): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

            $resetCode = UserResetCode::where('user_id', $user->id)
                ->where('email', $data['email'])
                ->where('code', $data['code'])
                ->where('expires_at', '>', now())
                ->whereNull('deleted_at')
                ->first();

            if (!$resetCode) {
                return [
                    'status' => false,
                    'message' => __('auth.error.invalid_code'),
                ];
            }

            $user->password = Hash::make($data['password']);
            $user->save();

            $resetCode->delete();

            return [
                'status' => true,
                'message' => __('auth.success.password_changed'),
            ];

        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function checkExpiresAtUser(): array
    {
        try {
            $count = UserResetCode::where('expires_at', '<', now())
                ->forceDelete();

            return [
                'status' => true,
                'message' => __('common.common_success.update_success'),
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }


    public function quickRegister(array $data): array
    {
        DB::beginTransaction();
        try {
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => RoleUser::CUSTOMER->value,
                'phone' => $data['phone'],
                'email_verified_at' => now(),
                'phone_verified_at' => now()
            ]);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function getUserBankAccounts(int $userId): array
    {
        try {
            $bankAccounts = UserBankAccount::where('user_id', $userId)
                ->with('bank')
                ->get()
                ->toArray();

            return $bankAccounts;
        } catch (\Exception $e) {
            Log::error('Error getting user bank accounts: ' . $e->getMessage());
            throw new ServiceException('Không thể lấy danh sách tài khoản ngân hàng');
        }
    }

    public function createBankAccount(int $userId, int $bankId, string $accountNumber, string $accountHolderName): UserBankAccount
    {
        DB::beginTransaction();
        try {
            $existingAccount = UserBankAccount::where('user_id', $userId)
                ->where('account_number', $accountNumber)
                ->first();

            if ($existingAccount) {
                throw new ServiceException('Tài khoản ngân hàng này đã được đăng ký');
            }

            $bankAccount = UserBankAccount::create([
                'user_id' => $userId,
                'bank_id' => $bankId,
                'account_number' => $accountNumber,
                'account_holder_name' => $accountHolderName,
                'is_primary' => false,
            ]);

            DB::commit();
            return $bankAccount;
        } catch (ServiceException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bank account: ' . $e->getMessage());
            throw new ServiceException('Không thể tạo tài khoản ngân hàng');
        }
    }

    public function updateBankAccount(int $accountId, int $userId, int $bankId, string $accountNumber, string $accountHolderName): UserBankAccount
    {
        DB::beginTransaction();
        try {
            $bankAccount = UserBankAccount::where('id', $accountId)
                ->where('user_id', $userId)
                ->first();

            if (!$bankAccount) {
                throw new ServiceException('Tài khoản ngân hàng không tồn tại');
            }

            $existingAccount = UserBankAccount::where('user_id', $userId)
                ->where('account_number', $accountNumber)
                ->where('id', '!=', $accountId)
                ->first();

            if ($existingAccount) {
                throw new ServiceException('Số tài khoản này đã được sử dụng bởi tài khoản khác');
            }

            $bankAccount->update([
                'bank_id' => $bankId,
                'account_number' => $accountNumber,
                'account_holder_name' => $accountHolderName,
            ]);

            DB::commit();
            return $bankAccount->fresh();
        } catch (ServiceException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating bank account: ' . $e->getMessage());
            throw new ServiceException('Không thể cập nhật tài khoản ngân hàng');
        }
    }

    public function deleteBankAccount(int $accountId, int $userId): bool
    {
        DB::beginTransaction();
        try {
            $bankAccount = UserBankAccount::where('id', $accountId)
                ->where('user_id', $userId)
                ->first();

            if (!$bankAccount) {
                throw new ServiceException('Tài khoản ngân hàng không tồn tại');
            }

            if ($bankAccount->is_primary) {
                throw new ServiceException('Không thể xóa tài khoản ngân hàng chính');
            }

            $bankAccount->delete();

            DB::commit();
            return true;
        } catch (ServiceException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting bank account: ' . $e->getMessage());
            throw new ServiceException('Không thể xóa tài khoản ngân hàng');
        }
    }

    public function setPrimaryAccount(int $accountId, int $userId): UserBankAccount
    {
        DB::beginTransaction();
        try {
            $bankAccount = UserBankAccount::where('id', $accountId)
                ->where('user_id', $userId)
                ->first();

            if (!$bankAccount) {
                throw new ServiceException('Tài khoản ngân hàng không tồn tại');
            }

            UserBankAccount::where('user_id', $userId)
                ->update(['is_primary' => false]);

            $bankAccount->update(['is_primary' => true]);

            DB::commit();
            return $bankAccount->fresh();
        } catch (ServiceException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting primary account: ' . $e->getMessage());
            throw new ServiceException('Không thể đặt tài khoản chính');
        }
    }
}
