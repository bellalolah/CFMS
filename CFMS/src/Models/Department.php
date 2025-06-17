<?php

namespace Cfms\Models;

use Cfms\Interface\Model;



class Department implements Model
{
    public $id;
    public $name;
    public $faculty_id;
    public $created_at;
    public $updated_at;

    public function toModel($data): Department {
        $departmentModel = new self();
        $departmentModel->id = $data->id ?? null;
        $departmentModel->name = $data->name ?? null;
        $departmentModel->faculty_id = $data->faculty_id ?? null;
        $departmentModel->created_at = $data->created_at ?? null;
        $departmentModel->updated_at = $data->updated_at ?? null;
        return $departmentModel;
    }

    public function getModel($data): Department {
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