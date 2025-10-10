<?php

namespace App\Services;

use App\Models\UserBankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ServiceException;
use Illuminate\Support\Collection;

class BankAccountService
{
    public function getUserBankAccounts(int $userId): Collection
    {
        try {
            $bankAccounts = UserBankAccount::where('user_id', $userId)
                ->with('bank')
                ->get();

            return $bankAccounts;
        } catch (\Exception $e) {
            Log::error('Error getting user bank accounts: ' . $e->getMessage());
            throw new ServiceException('Không thể lấy danh sách tài khoản ngân hàng');
        }
    }

    public function createBankAccount(int $userId, int $bankId, string $accountNumber, string $accountName): UserBankAccount
    {
        DB::beginTransaction();
        try {
            $hasAny = UserBankAccount::where('user_id', $userId)->exists();
            if ($hasAny) {
                throw new ServiceException('Bạn chỉ được thêm 1 tài khoản ngân hàng. Vui lòng sửa tài khoản hiện có.');
            }

            $existingAccount = UserBankAccount::where('account_number', $accountNumber)->first();

            if ($existingAccount) {
                throw new ServiceException('Tài khoản ngân hàng này đã được đăng ký');
            }

            $bankAccount = UserBankAccount::create([
                'user_id' => $userId,
                'bank_id' => $bankId,
                'account_number' => $accountNumber,
                'account_name' => $accountName,
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

    public function updateBankAccount(int $accountId, int $userId, int $bankId, string $accountNumber, string $accountName): UserBankAccount
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
                'account_name' => $accountName,
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


