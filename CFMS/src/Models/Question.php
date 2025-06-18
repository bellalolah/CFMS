<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class Question implements Model
{
    public ?int $id = null;
    public ?int $questionnaire_id = null;
    public ?string $question_text = null;
    public ?string $question_type = 'rating'; // Default type
    public ?int $order = 1;                    // Default order
    public ?int $criteria_id = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Populates the model instance from a data object/array.
     */
    public function toModel(object|array $data): self
    {
        $data = (array)$data;

        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->questionnaire_id = isset($data['questionnaire_id']) ? (int)$data['questionnaire_id'] : null;
        $this->question_text = $data['question_text'] ?? null;
        $this->question_type = $data['question_type'] ?? 'rating';
        $this->order = isset($data['order']) ? (int)$data['order'] : 1;
        $this->criteria_id = isset($data['criteria_id']) ? (int)$data['criteria_id'] : null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;

        return $this;
    }

    /**
     * Prepares a data array for insertion into the database.
     */
    public function getModelData(array $data): array
    {
        $this->validateData($data);
        return [
            'questionnaire_id' => $data['questionnaire_id'],
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'] ?? 'rating',
            'order' => $data['order'] ?? 1,
            'criteria_id' => $data['criteria_id'] ?? null,
        ];
    }

    private function validateData(array $data): void
    {
        // This validation would happen on the whole array before creating the questions,
        // but it's good to have here for completeness.
        if (empty($data['questionnaire_id'])) {
            throw new \InvalidArgumentException("Questionnaire ID is required.");
        }
        if (empty($data['question_text'])) {
            throw new \InvalidArgumentException("Question text is required.");
        }
    }

    public function getModel(array $data): Model
    {
        $this->validateData($data);
        return $this->toModel($data);
    }
}