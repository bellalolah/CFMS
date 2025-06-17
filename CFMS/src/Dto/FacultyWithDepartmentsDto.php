<?php
namespace Cfms\Dto;

class FacultyWithDepartmentsDto
{
    public int $id;
    public string $name;
    public array $departments = [];

    public function __construct($faculty, array $departments = [])
    {
        $this->id = (int)$faculty->id;
        $this->name = $faculty->name;
        $this->departments = $departments;
    }

    public function toArray(): array
    {
        $departmentData = array_map(function($dept) {
            return [
                'id' => (int)$dept->id,
                'name' => $dept->name,
                // Add other simple department fields here if needed
            ];
        }, $this->departments);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'departments' => $departmentData,
        ];
    }
}