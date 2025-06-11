<?php

namespace Cfms\Repositories;

use Cfms\Models\Admin;

class AdminRepository extends BaseRepository
{
    protected $table = 'admins';

    // Retrieve all admin records
    public function getAllAdmins(): array
    {
        $adminRecords = $this->findAll($this->table);
        $adminList = [];

        foreach ($adminRecords as $adminData) {
            $admin = new Admin();
            $adminList[] = $admin->toModel((object)$adminData);
        }

        return $adminList;
    }

    // Retrieve a specific admin by ID
    public function getAdminById($id): ?Admin
    {
        $adminData = $this->findById($this->table, $id);
        if ($adminData) {
            $admin = new Admin();
            return $admin->toModel((object)$adminData);
        }

        return null;
    }
    public function findByEmail(string $email): ?Admin
    {
        // Use the new findByColumn method
        $adminRecords = $this->findByColumn($this->table, 'email', $email);

        if (!empty($adminRecords)) {
            $admin = new Admin();
            return $admin->toModel((object)$adminRecords[0]); // Return the first result
        }

        return null;
    }

    // Create a new admin record
    public function createAdmin(Admin $adminData): ?Admin
    {
        $hashedPwd = $this->hashPassword($adminData->password_hash);

        $insert_data = [
            'name' => $adminData->name,
            'email' => $adminData->email,
            'password_hash' => $hashedPwd
        ];

        $adminData->id = $this->insert($this->table, $insert_data);

        if ($adminData->id) {
            $admin = new Admin();
            return $admin->getModel((object)$adminData);
        }

        return null;
    }

    private function hashPassword(string $pwd)
    {
        if (isset($pwd)) {
            $options = ['cost' => 12];
            return password_hash($pwd, PASSWORD_BCRYPT, $options);
        }
    }
}
