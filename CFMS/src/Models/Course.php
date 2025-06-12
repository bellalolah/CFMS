<?php

namespace Cfms\Models;

class Course
{
    public int $id;
    public string $course_code;
    public string $course_title;
    public int $department_id;
    public int $level;
    

    public function toModel(object $data): self
    {
        $this->id = $data->id;
        $this->course_code = $data->course_code;
        $this->course_title = $data->course_title;
        $this->department_id = $data->department_id;
        $this->level = $data->level;
        return $this;
    }

    public function getModel(object $data): array
    {
        return [
            'course_code' => $data->course_code,
            'course_title' => $data->course_title,
            'department_id' => $data->department_id,
            'level' => $data->level,
        ];
    }
}
