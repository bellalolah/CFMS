<?php

namespace Cfms\Services;

use Cfms\Dto\StudentProfileDto;
use Cfms\Models\StudentProfile;
use Cfms\Repositories\DepartmentRepository;
use Cfms\Repositories\FacultyRepository;
use Cfms\Repositories\user_profile\StudentProfileRepository;

class StudentProfileService
{

    public function __construct(private StudentProfileRepository $studentProfileRepo,
                                private DepartmentRepository $departmentRepo,
                                private FacultyRepository $facultyRepo)
    {

    }

    public function completeStudentProfile(int $userId, array $input): array
    {
        $required = ['matric_number', 'department_id', 'faculty_id', 'level'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return $this->fail("$field is required for student profile");
            }
        }

        if ($this->studentProfileRepo->findByUserId($userId)) {
            return $this->fail("Student profile already completed");
        }

        $profile = new StudentProfile();
        $profile->user_id = $userId;
        $profile->matric_number = $input['matric_number'];
        $profile->department_id = $input['department_id'];
        $profile->faculty_id = $input['faculty_id'];
        $profile->level = $input['level'];

        if (!$this->studentProfileRepo->create($profile)) {
            return $this->fail("Failed to complete student profile");
        }

        return ['success' => true, 'message' => 'Student profile completed'];
    }

    public function getProfile(int $userId): ?array
    {
        $profile = $this->studentProfileRepo->findByUserId($userId);
        if ($profile) {
            return [
                'user_id' => $profile->user_id,
                'matric_number' => $profile->matric_number,
                'department_id' => $profile->department_id,
                'faculty_id' => $profile->faculty_id,
                'level' => $profile->level,
            ];
        }
        return null;
    }

    public function updateProfile(int $userId, array $input): array
    {
        $profile = $this->studentProfileRepo->findByUserId($userId);
        if (!$profile) {
            return $this->fail("Student profile not found");
        }
        $updateData = array_intersect_key($input, array_flip(['matric_number', 'department_id', 'faculty_id', 'level']));
        if (empty($updateData)) {
            return $this->fail("No valid fields to update");
        }
        $success = $this->studentProfileRepo->updateByUserId($userId, $updateData);
        if ($success) {
            return ['success' => true, 'message' => 'Student profile updated'];
        }
        return $this->fail("Failed to update student profile");
    }

    private function fail(string $msg): array
    {
        return ['success' => false, 'message' => $msg];
    }
    public function getDetailedProfile(int $userId): ?StudentProfileDto
    {
        $profile = $this->studentProfileRepo->findByUserId($userId);
        if (!$profile) {
            return null;
        }

        // Fetch the detailed department and faculty info
        $department = $this->departmentRepo->findDepartmentById($profile->department_id);
        $faculty = $department ? $this->facultyRepo->findFacultyById($department->faculty_id) : null;

        if (!$department || !$faculty) {
            // Data integrity issue, profile exists but department/faculty doesn't
            return null;
        }

        // Assemble the DTOs
        $departmentDto = new \Cfms\Dto\DepartmentInfoDto($department, $faculty);
        return new StudentProfileDto($profile, $departmentDto);
    }

    // In Cfms\Services\StudentProfileService.php

    public function getMultipleDetailedProfiles(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        // 1. Get all the basic profiles in one query
        $profiles = $this->studentProfileRepo->findByUserIds($userIds);
        if (empty($profiles)) {
            return [];
        }

        // 2. Get all the necessary department and faculty IDs
        $departmentIds = array_unique(array_map(fn($p) => $p->department_id, $profiles));

        // 3. Fetch all departments and faculties in just two more queries
        // NOTE: You'll need to add `findByIds` methods to these repositories if they don't exist.
        $departments = $this->departmentRepo->findByIds($departmentIds);
        $facultyIds = array_unique(array_map(fn($d) => $d->faculty_id, $departments));
        $faculties = $this->facultyRepo->findByIds($facultyIds);

        // 4. Map everything for easy lookup
        $departmentsById = array_column($departments, null, 'id');
        $facultiesById = array_column($faculties, null, 'id');

        // 5. Build the final array of DTOs, indexed by user_id
        $dtosByUserId = [];
        foreach ($profiles as $profile) {
            $dept = $departmentsById[$profile->department_id] ?? null;
            if ($dept) {
                $faculty = $facultiesById[$dept->faculty_id] ?? null;
                if ($faculty) {
                    $departmentDto = new \Cfms\Dto\DepartmentInfoDto($dept, $faculty);
                    $dtosByUserId[$profile->user_id] = new \Cfms\Dto\StudentProfileDto($profile, $departmentDto);
                }
            }
        }

        return $dtosByUserId;
    }
}
