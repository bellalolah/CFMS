<?php

namespace Cfms\Models;

class Department implements Models
{
    public $id;
    public $name;
    public $faculty_id;

    public static function toModel($data): Models {
        $departmentModel = new self();
        $departmentModel->id = $data->id ?? null;
        $departmentModel->name = $data->name ?? null;
        $departmentModel->faculty_id = $data->faculty_id ?? null;
        return $departmentModel;
    }

    public function getModel($data): Models {
        $this->validateData($data);
        $departmentModel = new self();
        $departmentModel->name = $data->name ?? null;
        $departmentModel->faculty_id = $data->faculty_id ?? null;
        return $departmentModel;
    }

    private function validateData($data) {
        if (empty($data->name)) throw new \InvalidArgumentException("Department name is required.");
        if (empty($data->faculty_id)) throw new \InvalidArgumentException("Faculty ID is required.");
    }
}
