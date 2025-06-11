<?php

namespace Cfms\Models;

use Cfms\Interface\Models;

class Admin implements Models
{
    public $id;
    public $email;
    public $password_hash;
    public $role;

    // For retrieval (includes id)
    public static function toModel($data): Models {
        $adminModel = new self();
        $adminModel->id = $data->id ?? null;
        $adminModel->email = $data->email ?? null;
        $adminModel->password_hash = $data->password_hash ?? null;
        $adminModel->role = $data->role ?? null;
        return $adminModel;
    }

    // For insert (excludes id)
    public function getModel($data): Models {
        $this->validateData($data);

        $adminModel = new self();
        $adminModel->email = $data->email ?? null;
        $adminModel->password_hash = $data->password_hash ?? null;
        $adminModel->role = $data->role ?? null;
        return $adminModel;
    }

    private function validateData($data) {
        if (empty($data->email)) throw new \InvalidArgumentException("Email is required.");
        if (empty($data->password_hash)) throw new \InvalidArgumentException("Password is required.");
        if (empty($data->role)) throw new \InvalidArgumentException("Role is required.");
    }
}
