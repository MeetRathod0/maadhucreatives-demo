<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Repositories\Interfaces\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    public function findByEmail(string $email): ?Admin
    {
        return Admin::where('email', $email)->first();
    }

    public function findById(int $id): Admin
    {
        return Admin::findOrFail($id);
    }

    public function updatePassword(Admin $admin, string $hashedPassword): bool
    {
        return $admin->update(['password' => $hashedPassword]);
    }
}
