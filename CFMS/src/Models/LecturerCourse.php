<?php

namespace Cfms\Models;

class LecturerCourse
{
    public int $id;
    public int $user_id; // Lecturer's user ID
    public int $course_id;
    public ?string $created_at;
    public ?string $updated_at;

    public function toModel(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->course_id = $data['course_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        return $this;
    }

    public function getModel(object $data): array
    {
        return [
            'user_id' => $data->user_id,
            'course_id' => $data->course_id,
        ];
    }
}
