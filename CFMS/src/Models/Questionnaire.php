<?php

namespace Cfms\Models;

class Questionnaire implements Models
{
    public $id;
    public $course_id;
    public $lecturer_id;
    public $title;
    public $status;

    public static function toModel($data): Models {
        $questionnaireModel = new self();
        $questionnaireModel->id = $data->id ?? null;
        $questionnaireModel->course_id = $data->course_id ?? null;
        $questionnaireModel->lecturer_id = $data->lecturer_id ?? null;
        $questionnaireModel->title = $data->title ?? null;
        $questionnaireModel->status = $data->status ?? null;
        return $questionnaireModel;
    }

    public function getModel($data): Models {
        $this->validateData($data);
        $questionnaireModel = new self();
        $questionnaireModel->course_id = $data->course_id ?? null;
        $questionnaireModel->lecturer_id = $data->lecturer_id ?? null;
        $questionnaireModel->title = $data->title ?? null;
        $questionnaireModel->status = $data->status ?? 'active';
        return $questionnaireModel;
    }

    private function validateData($data) {
        if (empty($data->course_id)) throw new \InvalidArgumentException("Course ID is required.");
        if (empty($data->lecturer_id)) throw new \InvalidArgumentException("Lecturer ID is required.");
        if (empty($data->title)) throw new \InvalidArgumentException("Title is required.");
    }
}
