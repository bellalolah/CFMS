<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class Faculty implements Model
{
    public $id;
    public $name;
    public $created_at;
    public $updated_at;

    public function toModel($data): Faculty {
        $facultyModel = new self();
        $facultyModel->id = $data->id ?? null;
        $facultyModel->name = $data->name ?? null;
        $facultyModel->created_at = $data->created_at ?? null;
        $facultyModel->updated_at = $data->updated_at ?? null;
        return $facultyModel;
    }

    public function getModel($data): Faculty {
        if (empty($data->name)) throw new \InvalidArgumentException("Faculty name is required.");
        $facultyModel = new self();
        $facultyModel->name = $data->name ?? null;
        return $facultyModel;
    }
}