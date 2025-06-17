<?php

namespace Cfms\Models;

class CourseDepartment
{
    public int $id;
    public int $course_id;
    public int $department_id;
    public ?string $created_at;
    public ?string $updated_at;

    public function toModel(object $data): self
    {
        $this->id = $data->id;
        $this->course_id = $data->course_id;
        $this->department_id = $data->department_id;
        $this->created_at = $data->created_at ?? null;
        $this->updated_at = $data->updated_at ?? null;
        return $this;
    }

    public function getModel(object $data): array
    {
        return [
            'course_id' => $data->course_id,
            'department_id' => $data->department_id,
        ];
    }
}

