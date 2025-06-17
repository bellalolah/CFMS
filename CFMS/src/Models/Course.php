<?php

namespace Cfms\Models;

class Course
{
    public int $id;
    public string $course_code;
    public string $course_title;
    public int $level;
    public ?string $created_at;
    public ?string $updated_at;


    public function toModel(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->course_code = $data['course_code'] ?? null;
        $this->course_title = $data['course_title'] ?? null;
        $this->level = $data['level'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        return $this;
    }

    public function getModel(object $data): array
    {
        return [
            'course_code' => $data->course_code,
            'course_title' => $data->course_title,
            'level' => $data->level,
        ];
    }
}