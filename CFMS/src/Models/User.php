<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class User implements Model
{
    public $id;
    public $full_name; // Changed from first_name and last_name
    public $email;
    public $password;
    public $role_id;
    public $created_at;
    public $updated_at;


    /**
     * Hydrates the User model from database data.
     */
    public function toModel($data): User
    {
        $userModel = new self();
        $userModel->id = $data['id'] ?? null;
        $userModel->full_name = $data['full_name'] ?? null;
        $userModel->email = $data['email'] ?? null;
        $userModel->password = $data['password'] ?? null;
        $userModel->role_id = $data['role_id'] ?? null;
        $userModel->created_at = $data['created_at'] ?? null;
        $userModel->updated_at = $data['updated_at'] ?? null;
        return $userModel;
    }

    /**
     * Creates a new User model instance from input data, ready for insertion.
     */
    public function getModel($data): User
    {
        $this->validateData($data);

        $userModel = new self();
        $userModel->full_name = $data["full_name"]; // Updated
        $userModel->email = $data["email"];
        $userModel->password = $data["password"];
        $userModel->role_id = $data["role_id"];
        return $userModel;
    }

    private function validateData($data)
    {
        // Updated validation check
        if (empty($data['full_name'])) throw new \InvalidArgumentException("Full name is required.");
        if (empty($data['email'])) throw new \InvalidArgumentException("Email is required.");
        if (empty($data['password'])) throw new \InvalidArgumentException("Password is required.");
        if (empty($data['role_id'])) throw new \InvalidArgumentException("Role ID is required.");
    }
}