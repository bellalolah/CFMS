<?php

namespace Cfms\Models;

use Cfms\Interface\Models;

class Student implements Models
{

    public $id;
    public $matric_number;	
    public $email;
    public $password_hash;	
    public $full_name;		
    public $department_id;	
    public $level;
    public $faculty_id;
    
    public function toModels($data) : Models {
        $studentModel = new self();
        $studentModel->id = $data->id ?? null;
        $studentModel->matric_number = $data->matric_number ?? null;
        $studentModel->email = $data->email ?? null;
        $studentModel->password_hash = $data->password_hash ?? null;
        $studentModel->full_name = $data->full_name ?? null;
        $studentModel->department_id = $data->department_id ?? null;
        $studentModel->level = $data->level ?? null;
        $studentModel->faculty_id = $data->faculty_id ?? null;
        return $studentModel;
    }

    public function getModel($data): Models
    {
        $this->validateData($data);

        $studentModel = new self();
        $studentModel->matric_number = $data->matric_number ?? null;
        $studentModel->email = $data->email ?? null;
        $studentModel->password_hash = $data->password_hash ?? null;
        $studentModel->full_name = $data->full_name ?? null;
        $studentModel->department_id = $data->department_id ?? null;
        $studentModel->level = $data->level ?? null;
        return $studentModel;
    }
    private function validateData($data) {
        //
    }
}

