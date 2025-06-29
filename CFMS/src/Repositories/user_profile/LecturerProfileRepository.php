<?php

namespace Cfms\Repositories\user_profile;

use Cfms\Models\LecturerProfile;
use Cfms\Repositories\BaseRepository;

class
LecturerProfileRepository extends BaseRepository
{
    protected string $table = 'lecturer_profiles';

    // Get a lecturer profile by user_id
    public function findByUserId(int $userId): ?LecturerProfile
    {
        $records = $this->findByColumn($this->table, 'user_id', $userId);

        if (!empty($records)) {
            $profile = new LecturerProfile();
            return $profile->toModel($records[0]);
        }

        return null;
    }

    //  Create a new lecturer profile
    public function createLecturerProfile(LecturerProfile $profile): ?LecturerProfile
    {
        // Prevent duplicate profile for the same user
        if ($this->findByUserId($profile->user_id)) {
            return null; // Or throw an exception, e.g., throw new \Exception("Profile already exists.");
        }

        $data = [
            'user_id' => $profile->user_id,
            'department_id' => $profile->department_id,
            'faculty_id' => $profile->faculty_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // We call insert but ignore its return value (which we know is 0)
        $this->insert($this->table, $data);

        // NOW, we just re-fetch the profile we know we just created.
        // This will return the fully populated LecturerProfile object from the DB.
        return $this->findByUserId($profile->user_id);
    }

    // Optional: update
    public function updateLecturerProfile(LecturerProfile $profile): bool
    {
        if (!$profile->user_id) {
            throw new \InvalidArgumentException("User ID is required to update profile.");
        }

        $data = [
            'department_id' => $profile->department_id,
            'faculty_id' => $profile->faculty_id
        ];

        return $this->updateByColumn(
            $this->table,
            'user_id',
            $profile->user_id,
            [
                'department_id' => $profile->department_id,
                'faculty_id' => $profile->faculty_id
            ]
        );
    }

    // ... (your existing class and methods) ...

    /**
     * Finds all lecturer profiles that belong to a given list of user IDs.
     *
     * @param array $userIds An array of user IDs.
     * @return array An array of LecturerProfile objects.
     */
    public function findByUserIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        // Ensure all IDs are integers for safety
        $sanitizedIds = array_map('intval', $userIds);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));

        // The query uses the 'user_id' column
        $sql = "SELECT * FROM {$this->table} WHERE user_id IN ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sanitizedIds);

        // Convert the raw database rows into an array of LecturerProfile models
        $profiles = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $profiles[] = (new LecturerProfile())->toModel($row);
        }

        return $profiles;
    }
}
