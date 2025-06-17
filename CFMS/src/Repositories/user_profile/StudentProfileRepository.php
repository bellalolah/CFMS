<?php

namespace Cfms\Repositories\user_profile;

use Cfms\Models\StudentProfile;
use Cfms\Repositories\BaseRepository;

class StudentProfileRepository extends BaseRepository
{
    protected string $table = 'student_profiles';

    public function create(StudentProfile $profile): ?StudentProfile
    {
        // Check for duplicate matric number
        if ($this->existsByMatricNumber($profile->matric_number)) {
            return null;
        }

        $insert_data = [
            'user_id' => $profile->user_id,
            'matric_number' => $profile->matric_number,
            'department_id' => $profile->department_id,
            'level' => $profile->level,
            'faculty_id' => $profile->faculty_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $id = $this->insert($this->table, $insert_data);

        if ($id > 0) {
            $profile->user_id = $insert_data['user_id'];
            $profile->matric_number = $insert_data['matric_number'];
            $profile->department_id = $insert_data['department_id'];
            $profile->level = $insert_data['level'];
            $profile->faculty_id = $insert_data['faculty_id'];
            return $profile;
        }

        return null;
    }

    public function findByUserId(int $userId): ?StudentProfile
    {
        $results = $this->findByColumn($this->table, 'user_id', $userId);

        if (!empty($results)) {
            $profile = new StudentProfile();
            return $profile->toModel((array)$results[0]);
        }

        return null;
    }

    public function existsByMatricNumber(string $matric): bool
    {
        $result = $this->findByColumn($this->table, 'matric_number', $matric);
        return !empty($result);
    }


    // Optional update if needed later
    public function updateByUserId(int $userId, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->updateByColumn($this->table, 'user_id', $userId, $data);
    }
}
