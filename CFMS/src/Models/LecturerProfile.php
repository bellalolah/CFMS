<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class LecturerProfile implements Model
{
    public int $user_id;
    public int $department_id;
    public int $faculty_id;

    public function toModel($data): self
    {
        if (is_object($data)) {
            $data = (array)$data;
        }
        $this->user_id = $data['user_id'] ?? 0;
        $this->department_id = $data['department_id'] ?? 0;
        $this->faculty_id = $data['faculty_id'] ?? 0;

        return $this;
    }

    public function getModel(array $data): self
    {
        $this->validateData($data);
        return $this->toModel($data);
    }

    private function validateData(array $data): void
    {
        if (empty($data['user_id'])) throw new \InvalidArgumentException("User ID is required.");
        if (empty($data['department_id'])) throw new \InvalidArgumentException("Department ID is required.");
        if (empty($data['faculty_id'])) throw new \InvalidArgumentException("Faculty ID is required.");
    }
}
