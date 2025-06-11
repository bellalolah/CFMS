<?php 

namespace Cfms\Models;

class Faculty implements Models
{
    public $id;
    public $name;

    public static function toModel($data): Models {
        $facultyModel = new self();
        $facultyModel->id = $data->id ?? null;
        $facultyModel->name = $data->name ?? null;
        return $facultyModel;
    }

    public function getModel($data): Models {
        if (empty($data->name)) throw new \InvalidArgumentException("Faculty name is required.");
        $facultyModel = new self();
        $facultyModel->name = $data->name ?? null;
        return $facultyModel;
    }
}
