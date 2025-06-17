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
}