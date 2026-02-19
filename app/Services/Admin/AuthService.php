<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private AdminRepositoryInterface $adminRepository
    ) {}

    public function attemptLogin(string $email, string $password, bool $remember = false): bool
    {
        return auth('admin')->attempt(
            ['email' => $email, 'password' => $password],
            $remember
        );
    }

    public function logout(): void
    {
        auth('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function changePassword(Admin $admin, string $oldPassword, string $newPassword): void
    {
        if (!Hash::check($oldPassword, $admin->password)) {
            throw ValidationException::withMessages([
                'old_password' => 'Current password is incorrect.',
            ]);
        }

        $this->adminRepository->updatePassword($admin, Hash::make($newPassword));
    }
}
