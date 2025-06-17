<?php
namespace Cfms\Services;

use Cfms\Repositories\CourseRepository;
use Cfms\Dto\CourseDto;

class CourseService
{
    public function __construct(private CourseRepository $courseRepo) {}

    public function getAllCourses(): array
    {
        $courses = $this->courseRepo->getAllCourses();
        return array_map(fn($course) => new CourseDto($course), $courses);
    }

    public function getCourseById(int $id): ?CourseDto
    {
        $course = $this->courseRepo->getCourseById($id);
        return $course ? new CourseDto($course) : null;
    }

    public function createCourse(array $data): int
    {
        return $this->courseRepo->createCourse($data);
    }

    public function updateCourse(int $id, array $data): bool
    {
        return $this->courseRepo->updateCourse($id, $data);
    }

    public function deleteCourse(int $id): bool
    {
        return $this->courseRepo->deleteCourse($id);
    }
}

