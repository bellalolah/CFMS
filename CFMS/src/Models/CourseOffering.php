<?php

namespace Cfms\Models;

class CourseOffering
{
    public int $id;
    public int $course_id;
    public int $lecturer_id;
    public int $department_id;
    public int $semester_id;

    public static function toModel(array $data): self
    {
        $model = new self();
        $model->id = (int) $data['id'];
        $model->course_id = (int) $data['course_id'];
        $model->lecturer_id = (int) $data['lecturer_id'];
        $model->department_id = (int) $data['department_id'];
        $model->semester_id = (int) $data['semester_id'];

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
