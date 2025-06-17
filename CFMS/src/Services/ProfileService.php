<?php

namespace Cfms\Services;

use Cfms\Models\StudentProfile;
use Cfms\Models\LecturerProfile;
use Cfms\Repositories\user_profile\LecturerProfileRepository;
use Cfms\Repositories\user_profile\StudentProfileRepository;

class ProfileService
{
    private StudentProfileRepository $studentProfileRepo;
    private LecturerProfileRepository $lecturerProfileRepo;

    public function __construct()
    {
        $this->studentProfileRepo = new StudentProfileRepository();
        $this->lecturerProfileRepo = new LecturerProfileRepository();
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

    public function completeLecturerProfile(int $userId, array $input): array
    {
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

        if (!$this->lecturerProfileRepo->createLecturerProfile($profile)) {
            return $this->fail("Failed to complete lecturer profile");
        }

        return ['success' => true, 'message' => 'Lecturer profile completed'];
    }

    private function fail(string $msg): array
    {
        return ['success' => false, 'message' => $msg];
    }
}
