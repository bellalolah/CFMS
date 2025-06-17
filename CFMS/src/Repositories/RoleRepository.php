<?php

namespace Cfms\Repositories;

use Cfms\Models\Role;

class RoleRepository extends BaseRepository
{
    protected string $table = 'roles';

    public function createRole(Role $role): ?Role
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            'name' => $role->name,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        try {
            $role->id = $this->insert($this->table, $data);
            $role->created_at = $now;
            $role->updated_at = $now;
            return $role;
        } catch (\Exception $e) {
            // Log the error in real projects
            return null;
        }
    }

    /**
     * @param Role[] $roles
     * @return array ['success' => true, 'created' => Role[]] or ['success' => false, 'message' => 'error']
     */
    public function createRoles(array $roles): array
    {
        $createdRoles = [];
        $now = date('Y-m-d H:i:s');

        foreach ($roles as $role) {
            if (!($role instanceof Role)) {
                continue; // skip invalid input
            }

            $data = [
                'name' => $role->name,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            try {
                $role->id = $this->insert($this->table, $data);
                $role->created_at = $now;
                $role->updated_at = $now;
                $createdRoles[] = $role;
            } catch (\Exception $e) {
                // optionally log and continue
                continue;
            }
        }

        if (empty($createdRoles)) {
            return ['success' => false, 'message' => 'No roles were created'];
        }

        return ['success' => true, 'created' => $createdRoles];
    }

    public function fetchRoleByName(string $name): ?Role
    {
        $result = $this->findByColumn($this->table, 'name', $name);

        if (empty($result)) {
            return null;
        }

        // If multiple roles with same name exist (shouldn’t happen), grab the first
        $data = $result[0];

        $role = new Role();
        return $role->toModel($data);
    }

    public function fetchRoleById(int $id): ?Role
    {
        $data = $this->findById($this->table, $id);

        if (!$data) {
            return null;
        }

        $role = new Role();
        return $role->toModel($data);
    }


}
