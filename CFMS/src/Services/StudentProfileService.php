<?php

namespace Cfms\Services;

use Cfms\Models\StudentProfile;
use Cfms\Repositories\user_profile\StudentProfileRepository;

class StudentProfileService
{

    public function __construct(private StudentProfileRepository $studentProfileRepo)
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
}
