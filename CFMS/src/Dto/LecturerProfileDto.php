<?php
namespace Cfms\Dto;

use Cfms\Dto\DepartmentInfoDto;
use Cfms\Dto\FacultyInfoDto;

class LecturerProfileDto
{
    public int $user_id;
    public DepartmentInfoDto $department;
    public FacultyInfoDto $faculty;

    public function __construct($profile, $department, $faculty)
    {
        $this->user_id = isset($profile->user_id) && $profile->user_id !== null ? (int)$profile->user_id : 0;
        $this->department = $department instanceof DepartmentInfoDto ? $department : new DepartmentInfoDto($department, $faculty);
       /* $this->faculty = $faculty instanceof FacultyInfoDto ? $faculty : new FacultyInfoDto($faculty);*/
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            // Make sure your DepartmentInfoDto and FacultyInfoDto also have toArray() methods
            'department' => $this->department->toArray(),
            /*'faculty' => $this->faculty->toArray(),*/
        ];
    }
}
