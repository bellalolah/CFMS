<?php

namespace Cfms\Models;

use Cfms\Interface\Models;

class Feedback implements Models
{
    public $id;
    public $student_id;
    public $question_id;
    public $response;

    public static function toModel($data): Models {
        $model = new self();
        $model->id = $data->id ?? null;
        $model->student_id = $data->student_id ?? null;
        $model->question_id = $data->question_id ?? null;
        $model->response = $data->response ?? null;
        return $model;
    }

    public function getModel($data): Models {
        $this->validateData($data);
        $model = new self();
        $model->student_id = $data->student_id ?? null;
        $model->question_id = $data->question_id ?? null;
        $model->response = $data->response ?? null;
        return $model;
    }

    private function validateData($data) {
        if (empty($data->student_id)) {
            throw new \InvalidArgumentException("Student ID is required.");
        }
        if (empty($data->question_id)) {
            throw new \InvalidArgumentException("Question ID is required.");
        }
        if (!isset($data->response)) {
            throw new \InvalidArgumentException("Response is required.");
        }
    }
}
