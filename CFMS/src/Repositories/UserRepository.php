<?php
namespace Cfms\Repositories;

use Cfms\Models\User;
use Cfms\Utils\PasswordUtil;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    //  Get all users
    public function getAllUsers(): array
    {
        $userRecords = $this->findAll($this->table);
        $userList = [];

        foreach ($userRecords as $userData) {
            $user = new User();
            $userList[] = $user->toModel((array)$userData);
        }

        return $userList;
    }

    // 🔍 Get one user by ID
    public function getUserById(int $id): ?User
    {
        $userData = $this->findById($this->table, $id);
        if ($userData) {
            $user = new User();
            return $user->toModel((array)$userData);
        }
        return null;
    }

    // 🔍 Get user by email
    public function findByEmail(string $email): ?User
    {
        $records = $this->findByColumn($this->table, 'email', $email);
        if (!empty($records)) {
            $user = new User();
            $userData = $records[0];
            $user = $user->toModel((array)$userData); // Use the toModel method to populate the user object
            return $user;
        }
        return null;
    }
    // Create new user
    public function createUser(User $user): ?User
    {
        $insert_data = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'password' => $user->password,
            'role_id' => $user->role_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $user->id = $this->insert($this->table, $insert_data);

        if ($user->id) {
            return $user; // or re-fetch from DB
        }

        return null;
    }

    //  Update user (optional helper)
    public function updateUser(User $user): bool
    {
        if (!$user->id) {
            throw new \InvalidArgumentException("User ID is required to update");
        }

        $update_data = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($this->table, $update_data,$user->id);
    }

    // Get users with pagination
    public function getUsersPaginated(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $userRecords = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $userList = [];
        foreach ($userRecords as $userData) {
            $user = new User();
            $userList[] = $user->toModel((array)$userData);
        }
        return $userList;
    }

    //  Get total user count
    public function getTotalUserCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    // Get users by role_id
    public function findByRoleId(int $roleId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE role_id = :role_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role_id', $roleId, \PDO::PARAM_INT);
        $stmt->execute();
        $userRecords = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $userList = [];
        foreach ($userRecords as $userData) {
            $user = new User();
            $userList[] = $user->toModel((array)$userData);
        }
        return $userList;
    }

    // Delete user by ID
    public function deleteUser(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function createLecturerWithProfile(array $userData, array $profileData): ?User
    {
        // Start the transaction directly on the db connection object.
        $this->db->beginTransaction();

        try {
            // 1. Create the User
            // We use the BaseRepository's insert method, which this class inherits.
            $newUserId = $this->insert($this->table, $userData);
            if (!$newUserId) {
                $this->db->rollBack();
                return null;
            }

            // 2. Create the Lecturer Profile
            // Link the profile to the new user ID.
            $profileData['user_id'] = $newUserId;

            // We use the BaseRepository's insert method again, but specify the 'lecturer_profiles' table.
            // This is safe because we are inside the data layer.
            $this->insert('lecturer_profiles', $profileData);
            // Note: We don't need the return value here. If it fails, the catch block will handle it.

            // 3. If everything worked, commit the transaction.
            $this->db->commit();

            // 4. Return the full user object by fetching it again.
            return $this->getUserById($newUserId);

        } catch (\Exception $e) {
            // 5. If any error occurred, roll back the entire operation.
            $this->db->rollBack();
            error_log("Lecturer/Profile creation failed: " . $e->getMessage());
            return null; // Signal failure
        }
    }

    // In Cfms\Repositories\UserRepository.php

    public function createStudentWithProfile(array $userData, array $profileData): ?User
    {
        $this->db->beginTransaction();
        try {
            // 1. Create the User record
            $newUserId = $this->insert($this->table, $userData);
            if (!$newUserId) {
                $this->db->rollBack();
                return null;
            }

            // 2. Create the Student Profile record
            $profileData['user_id'] = $newUserId;
            $this->insert('student_profiles', $profileData); // Insert into the correct table

            // 3. Commit if successful
            $this->db->commit();

            // 4. Return the newly created user object
            return $this->getUserById($newUserId);

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Student/Profile creation failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets a paginated list of users for a specific role.
     *
     * @param int $roleId The ID of the role to filter by.
     * @param int $limit The number of records per page.
     * @param int $offset The starting record number.
     * @return array An array of User objects.
     */
    public function findByRoleIdPaginated(int $roleId, int $limit, int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE role_id = :role_id ORDER BY id LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role_id', $roleId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $row) {
            $users[] = (new User())->toModel((array)$row);
        }
        return $users;
    }

    /**
     * Gets the total count of users for a specific role.
     *
     * @param int $roleId The ID of the role to count.
     * @return int The total number of users with that role.
     */
    public function countByRoleId(int $roleId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE role_id = :role_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role_id', $roleId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

}