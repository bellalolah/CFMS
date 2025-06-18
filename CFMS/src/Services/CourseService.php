<?php
namespace Cfms\Services;

use Cfms\Repositories\CourseRepository;
use Cfms\Dto\CourseDto;
use Cfms\Repositories\SessionRepository;
use Cfms\Repositories\user_profile\StudentProfileRepository;

class CourseService
{
    public function __construct(private CourseRepository $courseRepo, private  StudentProfileRepository $studentProfileRepo, private SessionRepository $sessionRepo) {}

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

    // The updated method:
    public function getCoursesForStudent(int $studentUserId, int $page = 1, int $perPage = 15): ?array
    {
        // 1. Get student's department ID
        $profile = $this->studentProfileRepo->findByUserId($studentUserId);
        if (!$profile) {
            return null;
        }
        $departmentId = $profile->department_id;

        // 2. Get the current active session ID
        $activeSession = $this->sessionRepo->getActiveSession();
        $activeSessionId = $activeSession ? $activeSession->id : null;

        // 3. Handle pagination
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        // 4. Fetch the rich data from the repository, passing the active session ID
        $coursesData = $this->courseRepo->findByDepartment($departmentId, $activeSessionId, $perPage, $offset);
        $total = $this->courseRepo->countByDepartment($departmentId);

        // 5. Map the raw data to our new DTO
        $dtos = array_map(fn($course) => new \Cfms\Dto\StudentCourseDto($course), $coursesData);

        // 6. Return the final, structured paginated response
        return [
            'data' => $dtos,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
            ]
        ];
    }
}

