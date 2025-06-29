<?php

namespace Cfms\Models;

class CourseOffering
{
    public int $id;
    public int $course_id;
    public int $lecturer_id;
    public int $department_id;
    public int $semester_id;
    public ?string $created_at;
    public ?string $updated_at;

    public static function toModel(array $data): self
    {
        $model = new self();
        $model->id = (int) $data['id'];
        $model->course_id = (int) $data['course_id'];
        $model->lecturer_id = (int) $data['lecturer_id'];
        $model->semester_id = (int) $data['semester_id'];
        // REMOVED the line for department_id
        $model->created_at = $data['created_at'] ?? null;
        $model->updated_at = $data['updated_at'] ?? null;

        return $model;
    }

    public function getModel(): array
    {
        return [
            'course_id' => $this->course_id,
            'department_id' => $this->department_id,
            'lecturer_id' => $this->lecturer_id,
            'semester_id' => $this->semester_id
        ];
    }
}