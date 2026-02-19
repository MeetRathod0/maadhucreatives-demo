<?php

namespace App\Repositories\Interfaces;

use App\Models\Admin;

interface AdminRepositoryInterface
{
    public function findByEmail(string $email): ?Admin;
    public function findById(int $id): Admin;
    public function updatePassword(Admin $admin, string $hashedPassword): bool;
}
