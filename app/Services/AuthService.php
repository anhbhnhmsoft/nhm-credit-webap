<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\User;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\UserResetCode;
use App\Mail\ResetPasswordMail;
use App\Models\UserOtp;
use Illuminate\Http\Request;


class AuthService
{
    public function login(array $data): array
    {
        try {
            $user = User::query()
                ->where('phone_number', $data['phone_number'])
                ->first();

            if (!$user) {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }

            $otp = UserOtp::where('user_id', $user->id)
                ->where('otp', $data['otp'])
                ->where('expires_at', '>', now())
                ->first();

            if (!$otp) {
                throw new ServiceException(__('auth.error.invalid_otp'));
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
            $user = User::query()->create([
                'name' => trim($data['name']),
                'phone_number' => $data['phone_number'],
                'password' => Hash::make($data['password']),
                'role' => RoleUser::CUSTOMER->value,
            ]);

            $otp = rand(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            UserOtp::create([
                'user_id' => $user->id,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);


            DB::commit();

            return [
                'status' => true,
                'message' => 'OTP đã được gửi đến số điện thoại của bạn',
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

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => __('auth .success.logout_success'),
        ], 200);
    }
}
