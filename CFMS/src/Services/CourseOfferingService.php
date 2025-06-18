<?php

namespace Cfms\Services;

use Cfms\Repositories\CourseOfferingRepository;
use Cfms\Models\CourseOffering;
use Cfms\Dto\CourseOfferingWithDetailsDto;
use Cfms\Dto\CourseDto;
use Cfms\Dto\UserInfoDto;
use Cfms\Dto\LecturerProfileDto;
use Cfms\Dto\DepartmentInfoDto;
use Cfms\Dto\SemesterDto;
use Cfms\Dto\SessionDto;
use Cfms\Repositories\CourseRepository;
use Cfms\Repositories\user_profile\LecturerProfileRepository;
use Cfms\Repositories\UserRepository;
use Cfms\Repositories\DepartmentRepository;
use Cfms\Repositories\FacultyRepository;
use Cfms\Repositories\SemesterRepository;
use Cfms\Repositories\SessionRepository;

class CourseOfferingService
{
    private CourseOfferingRepository $courseOfferingRepository;
    private CourseRepository $courseRepository;
    private UserRepository $userRepository;
    private LecturerProfileRepository $lecturerProfileRepository;
    private DepartmentRepository $departmentRepository;
    private FacultyRepository $facultyRepository;
    private SemesterRepository $semesterRepository;
    private SessionRepository $sessionRepository;

    public function __construct(
        CourseOfferingRepository $courseOfferingRepository,
        CourseRepository $courseRepository,
        UserRepository $userRepository,
        LecturerProfileRepository $lecturerProfileRepository,
        DepartmentRepository $departmentRepository,
        FacultyRepository $facultyRepository,
        SemesterRepository $semesterRepository,
        SessionRepository $sessionRepository
    ) {
        $this->courseOfferingRepository = $courseOfferingRepository;
        $this->courseRepository = $courseRepository;
        $this->userRepository = $userRepository;
        $this->lecturerProfileRepository = $lecturerProfileRepository;
        $this->departmentRepository = $departmentRepository;
        $this->facultyRepository = $facultyRepository;
        $this->semesterRepository = $semesterRepository;
        $this->sessionRepository = $sessionRepository;
    }

    public function createCourseOffering(array $data): int
    {
        // Validate lecturer_id is a lecturer
        $lecturer = $this->userRepository->getUserById($data['lecturer_id'] ?? 0);
        if (!$lecturer || ($lecturer->role_id ?? null) != 2) { // assuming 2 = lecturer
            throw new \InvalidArgumentException('lecturer_id must be a valid lecturer user ID');
        }
        return $this->courseOfferingRepository->createCourseOffering($data);
    }

    public function updateCourseOffering(int $id, array $data): bool
    {
        return $this->courseOfferingRepository->updateCourseOffering($id, $data);
    }

    public function deleteCourseOffering(int $id): bool
    {
        return $this->courseOfferingRepository->deleteCourseOffering($id);
    }

   /* public function createBulkCourseOfferings(array $offerings): array
    {
        $results = [];
        foreach ($offerings as $data) {
            $lecturer = $this->userRepository->getUserById($data['lecturer_id'] ?? 0);
            if (!$lecturer || ($lecturer->role_id ?? null) != 2) {
                $results[] = [
                    'error' => 'lecturer_id must be a valid lecturer user ID',
                    'data' => $data
                ];
                continue;
            }
            $id = $this->createCourseOffering($data);
            $results[] = $id;
        }
        return $results;
    }*/

    public function getAllByLecturer(int $lecturerId): array
    {
        $offerings = $this->courseOfferingRepository->findByLecturer($lecturerId);

        return array_values(array_filter(array_map(fn($o) => $this->toDetailsDto($o), $offerings)));
    }

    public function getAllBySession(int $sessionId): array
    {

        $offerings = $this->courseOfferingRepository->findBySession($sessionId);
        return array_values(array_filter(array_map(fn($o) => $this->toDetailsDto($o), $offerings)));
    }

    // Converts a CourseOffering model to CourseOfferingWithDetailsDto
    // Add this new method inside the CourseOfferingService class

    private function toDetailsDto(CourseOffering $offering): ?CourseOfferingWithDetailsDto
    {
        // Fetch all related data for ONE offering
        $course = $this->courseRepository->getCourseById($offering->course_id);
        $lecturerUser = $this->userRepository->getUserById($offering->lecturer_id); // Assuming this is the correct method name
        $lecturerProfile = $this->lecturerProfileRepository->findByUserId($offering->lecturer_id);
        $semester = $this->semesterRepository->findSemesterById($offering->semester_id);

        // IMPORTANT: Check if things were actually found before using them!
        // If any essential part is missing, we can't build the DTO, so we return null.
        if (!$course || !$lecturerUser || !$lecturerProfile || !$semester) {
            // You might want to log an error here about inconsistent data for offering ID: $offering->id
            return null;
        }

        // Now fetch data related to the other data
        $department = $this->departmentRepository->findDepartmentById($lecturerProfile->department_id);
        $faculty = $this->facultyRepository->findFacultyById($lecturerProfile->faculty_id);
        $session = $this->sessionRepository->findSessionById($semester->session_id); // Get session from the semester

        if (!$department || !$faculty || !$session) {
            // Log another potential data integrity issue
            return null;
        }

        // Now, assemble the final DTO
        $dtoData = [
            'id' => $offering->id,
            'course' => new CourseDto($course),
            'lecturer_user' => new UserInfoDto($lecturerUser),
            'lecturer_profile' => new LecturerProfileDto($lecturerProfile, $department, $faculty),
            'department' => new DepartmentInfoDto($department, $faculty),
            'semester' => new SemesterDto($semester),
            'session' => new SessionDto($session), // Create the SessionDto
            'created_at' => $offering->created_at,
            'updated_at' => $offering->updated_at,
        ];

        return new CourseOfferingWithDetailsDto($dtoData);
    }

    // In Cfms\Services\CourseOfferingService.php

    public function unassignBulkCourseOfferings(array $offerings): int
    {
        // You can add validation here if you want, for example:
        if (empty($offerings)) {
            return 0;
        }

        // Call the repository to perform the bulk deletion
        return $this->courseOfferingRepository->deleteBulk($offerings);
    }

    public function unassignBulkByIds(array $offeringIds): int
    {
        if (empty($offeringIds)) {
            return 0;
        }

        // The service can add more complex validation if needed in the future.
        // For now, it just calls the repository.
        return $this->courseOfferingRepository->deleteByIds($offeringIds);
    }

    // In Cfms\Services\CourseOfferingService.php

    public function createBulkCourseOfferings(array $offerings): array
    {
        $results = [
            'successful' => [],
            'duplicates' => [],
            'errors' => []
        ];

        foreach ($offerings as $data) {
            // A) Basic Validation
            if (!isset($data['course_id'], $data['lecturer_id'], $data['semester_id'])) {
                $results['errors'][] = ['error' => 'Missing required fields.', 'data' => $data];
                continue;
            }

            // B) Check if lecturer is valid (your existing logic)
            $lecturer = $this->userRepository->getUserById($data['lecturer_id']);
            if (!$lecturer || ($lecturer->role_id ?? null) != 2) {
                $results['errors'][] = ['error' => 'lecturer_id must be a valid lecturer user ID.', 'data' => $data];
                continue;
            }

            // C) Check for Duplicates using our new method
            if ($this->courseOfferingRepository->offeringExists($data['course_id'], $data['lecturer_id'], $data['semester_id'])) {
                $results['duplicates'][] = $data;
                continue; // Skip to the next item
            }

            // D) If not a duplicate, try to create it
            try {
                $id = $this->courseOfferingRepository->createCourseOffering($data);
                $data['id'] = $id; // Add the new ID to the successful data
                $results['successful'][] = $data;
            } catch (\Exception $e) {
                // Catch any other unexpected errors during creation
                $results['errors'][] = ['error' => $e->getMessage(), 'data' => $data];
            }
        }
        return $results;
    }
}
