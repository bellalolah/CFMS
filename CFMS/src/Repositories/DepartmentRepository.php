<?php

namespace Cfms\Repositories;

use Cfms\Models\Department;

class DepartmentRepository extends BaseRepository
{
    protected $table = 'departments';

    // Get all departments and map them to Department model
    public function getAllDepartments(): array
    {
        $departmentRecords = $this->findAll($this->table);
        $departmentList = [];

        foreach ($departmentRecords as $deptData) {
            $department = new Department();
            $departmentList[] = $department->toModel((object) $deptData);
        }

        return $departmentList;
    }

    // Get a specific department by ID
    public function getDepartmentById($id): ?Department
    {
        $departmentData = $this->findById($this->table, $id);
        if ($departmentData) {
            $department = new Department();
            return $department->toModel((object) $departmentData);
        }

        return null;
    }

    // Create a new department (if needed)
    public function createDepartment(Department $department): ?Department
    {
        $insert_data = [
            'name' => $department->name,
            'faculty_id' => $department->faculty_id,
        ];

        $department->id = $this->insert($this->table, $insert_data);

        return $department->id ? $department->getModel((object) $department) : null;
    }
}
