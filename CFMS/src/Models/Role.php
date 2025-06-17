<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class Role implements Model
{
    public $id;
    public $name;
    public $created_at;
    public $updated_at;

    /**
     * Hydrates the model from database data.
     */
    public function toModel($data):Role
    {
        $roleModel = new self();
        $roleModel->id = $data->id ?? null;
        $roleModel->name = $data->name ?? null;
        $roleModel->created_at = $data->created_at ?? null;
        $roleModel->updated_at = $data->updated_at ?? null;
        return $roleModel;
    }

    /**
     * Creates a new model instance from input data, ready for insertion.
     */
    public function getModel($data): Role
    {
        $this->validateData($data);
        $roleModel = new self();
        $roleModel->name = $data->name ?? null;
        return $roleModel;
    }

    /**
     * Validates the data for creating a new role.
     */
    private function validateData($data)
    {
        if (empty($data->name)) {
            throw new \InvalidArgumentException("Role name is required.");
        }
    }
}