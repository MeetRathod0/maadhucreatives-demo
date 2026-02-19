<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Services\Admin\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $success = $this->authService->attemptLogin(
            $request->email,
            $request->password,
            $request->boolean('remember')
        );

        if (!$success) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        $this->authService->logout();
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    public function showProfile()
    {
        $admin = auth('admin')->user();
        return view('admin.profile.index', compact('admin'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $this->authService->changePassword(
                auth('admin')->user(),
                $request->old_password,
                $request->password
            );

            return redirect()
                ->route('admin.profile.index')
                ->with('success', 'Password changed successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
