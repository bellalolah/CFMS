<?php
namespace Cfms\Dto;

class LecturerWithCoursesDto
{
    public int $id;
    public string $full_name;
    public string $email;
    public int $role_id;
    public $profile; // LecturerProfile or array
    public array $courses; // List of CourseDto

    public function __construct($lecturer, $profile, array $courses)
    {
        $this->id = $lecturer->id;
        $this->full_name = $lecturer->full_name;
        $this->email = $lecturer->email;
        $this->role_id = $lecturer->role_id;
        $this->profile = $profile;
        $this->courses = $courses;
    }
}

