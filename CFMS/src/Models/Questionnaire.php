<?php
namespace Cfms\Models;

use Cfms\Interface\Model;

class Questionnaire implements Model
{
    public ?int $id = null;
    public ?int $course_offering_id = null;
    public ?string $title = null;
    public ?string $status = 'inactive'; // Default status


    public ?int $created_by_user_id = null;
    public ?int $feedback_count = 0;
    public ?int $feedback_round = 1;      // Default round
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Populates the model instance from a data object/array.
     */
    public function toModel(object|array $data): self
    {
        $data = (array)$data;

        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->course_offering_id = isset($data['course_offering_id']) ? (int)$data['course_offering_id'] : null;
        $this->title = $data['title'] ?? null;
        $this->status = $data['status'] ?? 'inactive';
        $this->feedback_round = isset($data['feedback_round']) ? (int)$data['feedback_round'] : 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->created_by_user_id = isset($data['created_by_user_id']) ? (int)$data['created_by_user_id'] : null;
        $this->feedback_count = isset($data['feedback_count']) ? (int)$data['feedback_count'] : 0;

        return $this;
    }

    /**
     * Prepares a data array for insertion into the database.
     */
    public function getModelData(array $data): array
    {
        $this->validateData($data);
        return [
            'course_offering_id' => $data['course_offering_id'],
            'title' => $data['title'],
            'status' => $data['status'] ?? 'inactive',
            'feedback_round' => $data['feedback_round'] ?? 1,
            'feedback_count' => $data['feedback_count'] ?? 0,
        ];
    }

    private function validateData(array $data): void
    {
        if (empty($data['title'])) {
            throw new \InvalidArgumentException("Title is required.");
        }
    }

    public function getModel(array $data): Model
    {
        $this->validateData($data);
        return $this->toModel($data);
    }
}