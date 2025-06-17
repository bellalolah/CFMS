<?php
namespace Cfms\Dto;

class CourseDto
{
    public int $id;
    public string $course_code;
    public string $course_title;
    public int $level;

    public function __construct($course)
    {
        $this->id = $course->id;
        $this->course_code = $course->course_code;
        $this->course_title = $course->course_title;
        $this->level = $course->level;
    }

    // ADD THIS METHOD
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_code' => $this->course_code,
            'course_title' => $this->course_title,
            'level' => $this->level,
        ];
    }
}