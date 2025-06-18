<?php
namespace Cfms\Dto;

class StudentProfileDto
{
    public int $user_id;
    public string $matric_number;
    public int $level;
    public DepartmentInfoDto $department;

    public function __construct($profile, DepartmentInfoDto $department)
    {
        $this->user_id = (int)$profile->user_id;
        $this->matric_number = $profile->matric_number;
        $this->level = (int)$profile->level;
        $this->department = $department;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'matric_number' => $this->matric_number,
            'level' => $this->level,
            'department' => $this->department->toArray(),
        ];
    }
}