<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class Question implements Model
{
    public $id;
    public $questionnaire_id;
    public $question_text;
    public $question_type;
    public $order;
    public $created_at;
    public $updated_at;
    public $criteria_id;

    public  function toModel($data): Model {
        $model = new self();
        $model->id = $data->id ?? null;
        $model->questionnaire_id = $data->questionnaire_id ?? null;
        $model->question_text = $data->question_text ?? null;
        $model->question_type = $data->question_type ?? 'rating';
        $model->order = $data->order ?? 1;
        $model->created_at = $data->created_at ?? null;
        $model->updated_at = $data->updated_at ?? null;
        $model->criteria_id = $data->criteria_id ?? null;
        return $model;
    }

    public function getModel($data): Model {
        $this->validateData($data);
        $model = new self();
        $model->questionnaire_id = $data->questionnaire_id ?? null;
        $model->question_text = $data->question_text ?? null;
        $model->question_type = $data->question_type ?? 'rating';
        $model->order = $data->order ?? 1;
        $model->criteria_id = $data->criteria_id ?? null;
        return $model;
    }

    private function validateData($data) {
        if (empty($data->questionnaire_id)) {
            throw new \InvalidArgumentException("Questionnaire ID is required.");
        }
        if (empty($data->question_text)) {
            throw new \InvalidArgumentException("Question text is required.");
        }
    }
}