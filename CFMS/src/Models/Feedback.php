<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class Feedback implements Model
{
    public $id;
    public $feedback_submission_id;
    public $question_id;
    public $answer_text;
    public $created_at;

    public function toModel($data): Model {
        $model = new self();
        $model->id = $data->id ?? null;
        $model->feedback_submission_id = $data->feedback_submission_id ?? null;
        $model->question_id = $data->question_id ?? null;
        $model->answer_text = $data->answer_text ?? null;
        $model->created_at = $data->created_at ?? null;
        return $model;
    }

    public function getModel($data): Model {
        $this->validateData($data);
        $model = new self();
        $model->feedback_submission_id = $data->feedback_submission_id ?? null;
        $model->question_id = $data->question_id ?? null;
        $model->answer_text = $data->answer_text ?? null;
        return $model;
    }

    private function validateData($data) {
        if (empty($data->feedback_submission_id)) {
            throw new \InvalidArgumentException("Feedback submission ID is required.");
        }
        if (empty($data->question_id)) {
            throw new \InvalidArgumentException("Question ID is required.");
        }
        if (!isset($data->answer_text)) {
            throw new \InvalidArgumentException("Answer text is required.");
        }
    }
}