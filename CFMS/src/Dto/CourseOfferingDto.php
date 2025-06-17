<?php

namespace Cfms\Dto;

class CourseOfferingDto
{
    public int $id;
    public int $course_id;
    public int $lecturer_id;
    public int $department_id;
    public int $semester_id;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data)
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->course_id = (int)($data['course_id'] ?? 0);
        $this->lecturer_id = (int)($data['lecturer_id'] ?? 0);
        $this->department_id = (int)($data['department_id'] ?? 0);
        $this->semester_id = (int)($data['semester_id'] ?? 0);
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'lecturer_id' => $this->lecturer_id,
            'department_id' => $this->department_id,
            'semester_id' => $this->semester_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

