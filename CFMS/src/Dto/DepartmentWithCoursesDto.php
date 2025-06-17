<?php
namespace Cfms\Dto;

class DepartmentWithCoursesDto
{
    public int $id;
    public string $name;
    public int $faculty_id;
    /** @var CourseDto[] */
    public array $courses = [];

    public function __construct($department, array $courses = [])
    {
        $this->id = (int)$department->id;
        $this->name = $department->name;
        $this->faculty_id = (int)$department->faculty_id;

        // Convert the raw course objects into CourseDto objects
        $this->courses = array_map(fn($course) => new CourseDto($course), $courses);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'faculty_id' => $this->faculty_id,
            // Use the toArray() method from CourseDto
            'courses' => array_map(fn(CourseDto $course) => $course->toArray(), $this->courses),
        ];
    }
}