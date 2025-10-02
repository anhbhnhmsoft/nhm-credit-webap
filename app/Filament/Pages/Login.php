<?php

namespace App\Filament\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        // Rate limit (chống brute force)
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();

        $credentials = [
            'email'        => $data['email'],
            'password'     => $data['password'],
        ];

        if (! Auth::guard('web')->attempt($credentials, (bool)($data['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'data.email' => __('auth.error.failed'),
            ]);
        }

        return app(LoginResponse::class);
    }
}
