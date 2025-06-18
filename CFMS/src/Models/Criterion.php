<?php
namespace Cfms\Models;


use Cfms\Interface\Model;

class Criterion implements Model
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Populates the model instance from a data object/array.
     */
    public function toModel(object|array $data): self
    {
        $data = (array)$data; // Ensure data is an array for consistent access

        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;

        return $this;
    }

    /**
     * Prepares a data array for insertion into the database.
     * Note: This method isn't typically used for getting a model,
     * but rather for preparing data for a create operation.
     */
    public function getModelData(array $data): array
    {
        $this->validateData($data);
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ];
    }

    private function validateData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException("Criterion name is required.");
        }
    }

    public function getModel(array $data): Model
    {
       $this->validateData($data);
           return $this->toModel($data);
    }
}