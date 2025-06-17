<?php
namespace Cfms\Services;

use Cfms\Repositories\LecturerCourseRepository;
use Cfms\Repositories\CourseRepository;
use Cfms\Dto\CourseDto;

class LecturerCourseService
{
    public function __construct(
        private LecturerCourseRepository $lecturerCourseRepo,
        private CourseRepository $courseRepo
    ) {}

    public function assignCourses(int $lecturerId, array $courseIds): array
    {
        return $this->lecturerCourseRepo->assignCoursesToLecturer($lecturerId, $courseIds);
    }

    public function unassignCourses(int $lecturerId, array $courseIds): int
    {
        return $this->lecturerCourseRepo->unassignCoursesFromLecturer($lecturerId, $courseIds);
    }

    public function getCoursesForLecturer(int $lecturerId): array
    {
        $courseIds = $this->lecturerCourseRepo->getCoursesForLecturer($lecturerId);
        $courses = [];
        foreach ($courseIds as $courseId) {
            $course = $this->courseRepo->getCourseById($courseId);
            if ($course) {
                $courses[] = new CourseDto($course);
            }
        }
        return $courses;
    }
}

