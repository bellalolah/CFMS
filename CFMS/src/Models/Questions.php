<?php

namespace Cfms\Models;

use Cfms\Interface\Models;

class Question implements Models
{
    public $id;
    public $questionnaire_id;
    public $text;
    public $type; // e.g., 'rating', 'text'

    public static function toModel($data): Models {
        $model = new self();
        $model->id = $data->id ?? null;
        $model->questionnaire_id = $data->questionnaire_id ?? null;
        $model->text = $data->text ?? null;
        $model->type = $data->type ?? null;
        return $model;
    }

    public function getModel($data): Models {
        $this->validateData($data);
        $model = new self();
        $model->questionnaire_id = $data->questionnaire_id ?? null;
        $model->text = $data->text ?? null;
        $model->type = $data->type ?? 'text'; // default to 'text'
        return $model;
    }

    private function validateData($data) {
        if (empty($data->questionnaire_id)) {
            throw new \InvalidArgumentException("Questionnaire ID is required.");
        }
        if (empty($data->text)) {
            throw new \InvalidArgumentException("Question text is required.");
        }
    }
}
