<?php

namespace Cfms\Models;

use Cfms\Interface\Models;

class Lecturer implements Models
{
    public $id;
    public $email;
    public $password_hash;
    public $first_name;
    public $last_name;
    public $department_id;

    // For retrieval (includes id)
    public static function toModel($data): Models {
        $lecturerModel = new self();
        $lecturerModel->id = $data->id ?? null;
        $lecturerModel->email = $data->email ?? null;
        $lecturerModel->password_hash = $data->password_hash ?? null;
        $lecturerModel->first_name = $data->first_name ?? null;
        $lecturerModel->last_name = $data->last_name ?? null;
        $lecturerModel->department_id = $data->department_id ?? null;
        return $lecturerModel;
    }

    // For insert (excludes id)
    public function getModel($data): Models {
        $this->validateData($data);

        $lecturerModel = new self();
        $lecturerModel->email = $data->email ?? null;
        $lecturerModel->password_hash = $data->password_hash ?? null;
        $lecturerModel->first_name = $data->first_name ?? null;
        $lecturerModel->last_name = $data->last_name ?? null;
        $lecturerModel->department_id = $data->department_id ?? null;
        return $lecturerModel;
    }

    private function validateData($data) {
        if (empty($data->email)) throw new \InvalidArgumentException("Email is required.");
        if (empty($data->password_hash)) throw new \InvalidArgumentException("Password is required.");
        if (empty($data->first_name)) throw new \InvalidArgumentException("First name is required.");
        if (empty($data->last_name)) throw new \InvalidArgumentException("Last name is required.");
    }
}
