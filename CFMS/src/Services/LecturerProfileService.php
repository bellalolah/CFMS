<?php

namespace Cfms\Services;

use Cfms\Dto\LecturerProfileDto;
use Cfms\Models\LecturerProfile;
use Cfms\Repositories\DepartmentRepository;
use Cfms\Repositories\FacultyRepository;
use Cfms\Repositories\user_profile\LecturerProfileRepository;

class LecturerProfileService
{

    public function __construct(private LecturerProfileRepository $lecturerProfileRepo, private DepartmentRepository $departmentRepo,
                                private FacultyRepository $facultyRepo)
    {
    }

    // In LecturerProfileService.php

    public function completeLecturerProfile(int $userId, array $input): array
    {
        // All your validation is correct, leave it as is
        $required = ['department_id', 'faculty_id'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return $this->fail("$field is required for lecturer profile");
            }
        }
        if ($this->lecturerProfileRepo->findByUserId($userId)) {
            return $this->fail("Lecturer profile already completed");
        }


        $profile = new LecturerProfile();
        $profile->user_id = $userId;
        $profile->department_id = $input['department_id'];
        $profile->faculty_id = $input['faculty_id'];

        $createdProfile = $this->lecturerProfileRepo->createLecturerProfile($profile);

        // Check for failure
        if ($createdProfile === null) {
            return $this->fail("Failed to complete lecturer profile");
        }


        // Now, get the full, detailed DTO using   helper method.
        $detailedProfileDto = $this->getProfile($createdProfile->user_id);

        // Final check in case something went wrong in getProfile
        if ($detailedProfileDto === null) {
            return $this->fail("Failed to retrieve profile details after creation.");
        }

        // Wrap the DTO's data in the standard success response structure.
        return $detailedProfileDto->toArray();
    }

    public function getProfile(int $userId): ?LecturerProfileDto
    {
        $profile = $this->lecturerProfileRepo->findByUserId($userId);
        if ($profile) {
            // Fetch department and faculty
            $departmentRepo = new DepartmentRepository();
            $facultyRepo = new FacultyRepository();
            $department = $profile->department_id ? $departmentRepo->findDepartmentById($profile->department_id) : null;
            $faculty = $profile->faculty_id ? $facultyRepo->findFacultyById($profile->faculty_id) : null;
            return new LecturerProfileDto($profile, $department, $faculty);
        }
        return null;
    }

    public function updateProfile(int $userId, array $input): array
    {
        $profile = $this->lecturerProfileRepo->findByUserId($userId);
        if (!$profile) {
            return $this->fail("Lecturer profile not found");
        }
        $updateData = array_intersect_key($input, array_flip(['department_id', 'faculty_id']));
        if (empty($updateData)) {
            return $this->fail("No valid fields to update");
        }
        $profile->department_id = $updateData['department_id'] ?? $profile->department_id;
        $profile->faculty_id = $updateData['faculty_id'] ?? $profile->faculty_id;
        $success = $this->lecturerProfileRepo->updateLecturerProfile($profile);
        if ($success) {
            return ['success' => true, 'message' => 'Lecturer profile updated'];
        }
        return $this->fail("Failed to update lecturer profile");
    }

    private function fail(string $msg): array
    {
        return ['success' => false, 'message' => $msg];
    }

    /**
     * Gets multiple detailed lecturer profiles for a given list of user IDs.
     *
     * @param array $userIds An array of user IDs.
     * @return array An array of LecturerProfileDto objects, indexed by user_id.
     */
    public function getMultipleDetailedProfiles(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        // Get all the basic lecturer profiles in one query

        $profiles = $this->lecturerProfileRepo->findByUserIds($userIds);
        if (empty($profiles)) {
            return [];
        }

        // 2. Get all the necessary department and faculty IDs from the profiles
        $departmentIds = array_unique(array_map(fn($p) => $p->department_id, $profiles));
        $facultyIds = array_unique(array_map(fn($p) => $p->faculty_id, $profiles));

        // 3. Fetch all related departments and faculties using our new findByIds methods
        $departments = $this->departmentRepo->findByIds($departmentIds);
        $faculties = $this->facultyRepo->findByIds($facultyIds);

        // Map everything for easy lookup
        $departmentsById = array_column($departments, null, 'id');
        $facultiesById = array_column($faculties, null, 'id');

        // Build the final array of DTOs, indexed by user_id
        $dtosByUserId = [];
        foreach ($profiles as $profile) {
            $dept = $departmentsById[$profile->department_id] ?? null;
            $faculty = $facultiesById[$profile->faculty_id] ?? null;

            if ($dept && $faculty) {
                // Note: We pass the full department and faculty objects to the DTO constructor
                $dtosByUserId[$profile->user_id] = new \Cfms\Dto\LecturerProfileDto($profile, $dept, $faculty);
            }
        }

        return $dtosByUserId;
    }
}
