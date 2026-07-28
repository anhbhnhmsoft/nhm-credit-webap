<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ], [
            'phone.required' => __('auth.validation.phone_required'),
            'phone.digits' => __('auth.validation.phone_digits'),
            'otp.required' => __('auth.validation.otp_required'),
            'otp.digits' => __('auth.validation.otp_digits'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->authService->login($validator->validated());

        if ($result['status'] === false) {
            return redirect()->back()->with('error', $result['message']);
        }

        $user = $result['user'];
        $token = $result['token'];

        session()->put('user', $user);
        session()->put('token', $token);

        return redirect()->route('home')->with('success', __('auth.success.login_success'));
    }


    public function loginForm()
    {
        return view('livewire.frontend.auth.login');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'phone' => ['required', 'digits:10', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'confirm_password' => ['required', 'same:password'],
        ], [
            'phone.required' => __('auth.validation.phone_required'),
            'phone.digits' => __('auth.validation.phone_digits'),
            'phone.unique' => __('auth.validation.phone_unique'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->authService->register($validator->validated());

        if ($result['status'] === false) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('login')->with('success', __('auth.success.register_success'));
    }


    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));
        if (! $user) {
            return response()->json([
                'message' => __('auth.error.email_not_found'),
            ], 422);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => __('auth.error.invalid_code'),
            ], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('auth.success.already_verified'),
            ], 200);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => __('auth.success.verify_success'),
        ], 200);
    }

    public function resendVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ], [
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => __('auth.error.email_not_found'),
            ], 422);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('auth.success.already_verified'),
            ], 200);
        }

        $url = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        Mail::to($user->email)->send(new VerifyEmailMail($url));

        return response()->json([
            'message' => __('auth.success.verify_sent'),
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'digits:10'],
        ], [
            'phone.required' => __('auth.validation.phone_required'),
            'phone.digits' => __('auth.validation.phone_digits'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $result = $this->authService->forgotPassword($validator->validated());

        if ($result['status'] === false) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('password.reset')->with('success', __('auth.success.reset_sent'));
    }


    public function confirmPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'confirm_password' => ['required', 'same:password'],
        ], [
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email'),
            'code.required' => __('auth.validation.code_required'),
            'code.size' => __('auth.validation.code_size'),
            'password.required' => __('auth.validation.password_required'),
            'password.min' => __('auth.validation.password_min'),
            'password.regex' => __('auth.validation.password_regex'),
            'confirm_password.required' => __('auth.validation.confirm_password_required'),
            'confirm_password.same' => __('auth.validation.confirm_password_same'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->authService->confirmPassword($validator->validated());

        if (isset($result['status']) && $result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'message' => __('auth.success.password_changed'),
        ], 200);
    }

    public function getUserInfo(Request $request)
    {
        $user = $request->user()->load('activeMemberships');
        if (!$user) {
            return response()->json([
                'message' => __('auth.error.unauthorized'),
            ], 401);
        }

        return response()->json([
            'message' => __('auth.success.user_info'),
            'data' => new UserResource($user),
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->session()->forget('user');
        $request->session()->forget('token');
        
        auth()->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', __('auth.success.logout_success'));
    }

    public function registerPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20|unique:users,phone',
        ]);

        return view('livewire.frontend.auth.register-info', ['phone' => $request->phone]);
    }

    public function submitPersonalInfo(Request $request)
    {
        $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'confirmPassword' => 'required|same:password',
        ]);
    }

    public function submitCardInfo(Request $request) {}

    public function registerOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'same:password'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $existing = User::where('phone', $data['phone'])->first();
        if ($existing) {
            if (!empty($existing->phone_verified_at)) {
                return response()->json([
                    'message' => 'Số điện thoại đã đăng ký và xác thực.',
                ], 422);
            }
            return response()->json([
                'message' => 'Số điện thoại đã tồn tại. Vui lòng đăng nhập hoặc khôi phục.',
            ], 422);
        }

        $user = User::create([
            'phone' => $data['phone'],
            'name' => $data['phone'],
            'password' => Hash::make($data['password']),
            'hash_encrypt' => Crypt::encryptString($data['password']),
            'role' => RoleUser::CUSTOMER->value,
            'phone_verified_at' => now(),
        ]);

        Auth::login($user);

        return response()->json([
            'message' => 'Register success',
            'redirect' => route('home'),
        ], 200);
    }

    public function loginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // normalize input and search across 0xxxxxxxxx / +84xxxxxxxxx variants
        $phone = preg_replace('/\s+/', '', $data['phone']);
        $variants = [$phone];
        if (preg_match('/^0\d{9}$/', $phone)) {
            $variants[] = '+84' . substr($phone, 1);
        } elseif (preg_match('/^\+84\d{9}$/', $phone)) {
            $variants[] = '0' . substr($phone, 3);
        }

        $user = User::whereIn('phone', $variants)->first();
        if (! $user) {
            return response()->json([
                'message' => 'Tài khoản không tồn tại. Vui lòng đăng ký.',
            ], 404);
        }

        if (empty($user->phone_verified_at)) {
            $user->phone_verified_at = now();
            $user->save();
        }

        Auth::login($user);

        return response()->json([
            'message' => 'Login success',
            'redirect' => route('home'),
        ], 200);
    }

    public function loginOtpCheckPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $phone = preg_replace('/\s+/', '', $data['phone']);

        // Accept both 0xxxxxxxxx and +84xxxxxxxxx variants
        $variants = [$phone];
        if (preg_match('/^0\d{9}$/', $phone)) {
            $variants[] = '+84' . substr($phone, 1);
        } elseif (preg_match('/^\+84\d{9}$/', $phone)) {
            $variants[] = '0' . substr($phone, 3);
        }

        $exists = User::whereIn('phone', $variants)->exists();
        if (! $exists) {
            return response()->json([
                'message' => 'Số điện thoại không tồn tại.',
            ], 404);
        }

        return response()->json([
            'message' => 'OK',
        ], 200);
    }

    public function registerOtpCheckPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $phone = preg_replace('/\s+/', '', $data['phone']);

        $variants = [$phone];
        if (preg_match('/^0\d{9}$/', $phone)) {
            $variants[] = '+84' . substr($phone, 1);
        } elseif (preg_match('/^\+84\d{9}$/', $phone)) {
            $variants[] = '0' . substr($phone, 3);
        }

        $existing = User::whereIn('phone', $variants)->first();
        if ($existing) {
            if (!empty($existing->phone_verified_at)) {
                return response()->json([
                    'message' => 'Số điện thoại đã đăng ký và xác thực.',
                ], 422);
            }
            return response()->json([
                'message' => 'Số điện thoại đã tồn tại. Vui lòng đăng nhập hoặc khôi phục.',
            ], 422);
        }

        return response()->json([
            'message' => 'OK',
        ], 200);
    }
}
