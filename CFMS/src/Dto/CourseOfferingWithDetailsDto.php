<?php

namespace Cfms\Dto;

class CourseOfferingWithDetailsDto
{
    public int $id;
    public CourseDto $course;
    public UserInfoDto $lecturer_user;
    public LecturerProfileDto $lecturer_profile;
    public DepartmentInfoDto $department;
    public SemesterDto $semester;
    public SessionDto $session;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data)
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->course = $data['course'];
        $this->lecturer_user = $data['lecturer_user'];
        $this->lecturer_profile = $data['lecturer_profile'];
        $this->department = $data['department'];
        $this->semester = $data['semester'];
        $this->session = $data['session'];
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course' => $this->course->toArray(),

            // Create a clean, nested 'lecturer' object
            'lecturer' => [
                'id' => $this->lecturer_user->id,
                'full_name' => $this->lecturer_user->full_name,
                'email' => $this->lecturer_user->email,
                // Nest the department here
                'department' => $this->department->toArray(),
            ],

            'semester' => $this->semester->toArray(),

            // We removed the redundant top-level 'session', 'department', and 'lecturer_profile'

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
