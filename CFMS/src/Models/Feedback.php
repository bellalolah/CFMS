<?php
namespace Cfms\Models;

use Cfms\Interface\Model;

class Feedback implements Model
{
    public ?int $id = null;
    public ?int $questionnaire_id = null;
    public ?int $question_id = null;
    public ?int $user_id = null; // Who submitted it?

    // For rating/slider questions
    public ?int $answer_value = null;

    // For open-ended text questions
    public ?string $answer_text = null;

    public ?string $created_at = null;


    public function toModel(object|array $data): self
    {
        $data = (array)$data;
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->questionnaire_id = isset($data['questionnaire_id']) ? (int)$data['questionnaire_id'] : null;
        $this->question_id = isset($data['question_id']) ? (int)$data['question_id'] : null;
        $this->user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $this->answer_value = isset($data['answer_value']) ? (int)$data['answer_value'] : null;
        $this->answer_text = $data['answer_text'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        return $this;
    }

    public function getModel(array $data): Model
    {
        return $this->toModel($data);
        // TODO: Implement getModel() method.
    }
}