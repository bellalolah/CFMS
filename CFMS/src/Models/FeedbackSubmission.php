<?php
namespace Cfms\Models;

use Cfms\Interface\Model;

class FeedbackSubmission implements Model
{
    public ?int $id = null;
    public ?int $questionnaire_id = null;
    public ?int $user_id = null;
    public ?string $submitted_at = null; // We'll use the 'created_at' column from the DB for this

    /**
     * Populates the model instance from a data object/array.
     */
    public function toModel(object|array $data): self
    {
        $data = (array)$data;

        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->questionnaire_id = isset($data['questionnaire_id']) ? (int)$data['questionnaire_id'] : null;
        $this->user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;

        // The database column is 'created_at', but we can map it to 'submitted_at' for clarity
        $this->submitted_at = $data['submitted_at'] ?? ($data['created_at'] ?? null);

        return $this;
    }

    /**
     * This method from the interface can simply call toModel.
     */
    public function getModel(array $data): Model
    {
        // For a simple model like this, direct creation is usually done in the repository,
        // so we don't need complex validation or data preparation here.
        return $this->toModel($data);
    }
}