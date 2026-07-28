<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\User;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\UserResetCode;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AuthService
{
    public function login(array $data): array
    {
        try {
            $user = User::query()
                ->where('phone', $data['phone'])
                ->first();

            if (!$user) {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }

            if (!Hash::check($data['password'], $user->password)) {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }

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
            $plainPassword = $data['password'];
            
            $user = User::query()->create([
                'name' => trim($data['name']),
                'phone' => $data['phone'],
                'password' => Hash::make($plainPassword),
                'hash_encrypt' => Crypt::encryptString($plainPassword),
                'role' => RoleUser::CUSTOMER->value,
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'Đăng ký thành công',
                'user' => $user,
            ];
        } catch (\Throwable $e) {
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

            $plainPassword = $data['password'];
            $user->password = Hash::make($plainPassword);
            $user->hash_encrypt = Crypt::encryptString($plainPassword);
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

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => __('auth .success.logout_success'),
        ], 200);
    }
}
