<?php

namespace Cfms\Models;

class Course implements Models
{
    public $id;
    public $course_code;
    public $title;
    public $department_id;
    public $level;

    public static function toModel($data): Models {
        $model = new self();
        $model->id = $data->id ?? null;
        $model->course_code = $data->course_code ?? null;
        $model->title = $data->title ?? null;
        $model->department_id = $data->department_id ?? null;
        $model->level = $data->level ?? null;
        return $model;
    }

    public function getModel($data): Models {
        $this->validateData($data);
        $model = new self();
        $model->course_code = $data->course_code ?? null;
        $model->title = $data->title ?? null;
        $model->department_id = $data->department_id ?? null;
        $model->level = $data->level ?? null;
        return $model;
    }

    private function validateData($data) {
        if (empty($data->course_code)) throw new \InvalidArgumentException("Course code is required.");
        if (empty($data->title)) throw new \InvalidArgumentException("Course title is required.");
    }
}
