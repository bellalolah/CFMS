<?php

namespace Cfms\Models;

use Cfms\Interface\Model;

class StudentProfile implements Model
{
    public int $user_id;
    public string $matric_number;
    public int $department_id;
    public int $level;
    public int $faculty_id;

    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function toModel(array $data): self
    {
        $this->user_id = $data['user_id'] ?? 0;
        $this->matric_number = $data['matric_number'] ?? '';
        $this->department_id = $data['department_id'] ?? 0;
        $this->level = $data['level'] ?? 0;
        $this->faculty_id = $data['faculty_id'] ?? 0;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;

        return $this;
    }

    public function getModel(array $data): self
    {
        $this->validate($data);

        $this->user_id = $data['user_id'];
        $this->matric_number = $data['matric_number'];
        $this->department_id = $data['department_id'];
        $this->level = $data['level'];
        $this->faculty_id = $data['faculty_id'];

        return $this;
    }

    private function validate(array $data): void
    {
        if (empty($data['user_id'])) throw new \InvalidArgumentException("user_id is required.");
        if (empty($data['matric_number'])) throw new \InvalidArgumentException("matric_number is required.");
        if (empty($data['department_id'])) throw new \InvalidArgumentException("department_id is required.");
        if (empty($data['level'])) throw new \InvalidArgumentException("level is required.");
        if (empty($data['faculty_id'])) throw new \InvalidArgumentException("faculty_id is required.");
    }
}
