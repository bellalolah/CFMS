<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class Questionnaire implements Model
{
    public $id;
    public $course_offering_id;
    public $title;
    public $status;
    public $created_at;
    public $updated_at;
    public $feedback_round;

    public function toModel($data): StudentProfile {
        $model = new self();
        $model->id = $data->id ?? null;
        $model->course_offering_id = $data->course_offering_id ?? null;
        $model->title = $data->title ?? null;
        $model->status = $data->status ?? null;
        $model->created_at = $data->created_at ?? null;
        $model->updated_at = $data->updated_at ?? null;
        $model->feedback_round = $data->feedback_round ?? 1;
        return $model;
    }

    public function getModel($data): StudentProfile {
        $this->validateData($data);
        $model = new self();
        $model->course_offering_id = $data->course_offering_id ?? null;
        $model->title = $data->title ?? null;
        $model->status = $data->status ?? 'inactive';
        $model->feedback_round = $data->feedback_round ?? 1;
        return $model;
    }

    private function validateData($data) {
        if (empty($data->course_offering_id)) {
            throw new \InvalidArgumentException("Course offering ID is required.");
        }
        if (empty($data->title)) {
            throw new \InvalidArgumentException("Title is required.");
        }
    }
}