<?php
namespace Cfms\Dto;

class StudentCourseDto
{
    public int $id;
    public string $course_code;
    public string $course_title;
    public int $level;
    public ?array $current_offering; // Can be null if not offered this session

    public function __construct(object $data)
    {
        $this->id = (int)$data->id;
        $this->course_code = $data->course_code;
        $this->course_title = $data->course_title;
        $this->level = (int)$data->level;

        // Check if there was a lecturer for the current session
        if ($data->lecturer_id && $data->lecturer_name) {
            $this->current_offering = [
                'lecturer_id' => (int)$data->lecturer_id,
                'lecturer_name' => $data->lecturer_name
            ];
        } else {
            $this->current_offering = null;
        }
    }

    public function toArray(): array
    {
        return (array)$this;
    }
}