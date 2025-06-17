<?php
namespace Cfms\Dto;

class DepartmentInfoDto
{
    public int $id;
    public string $name;
    public array $faculty; // ['id' => ..., 'name' => ...]

    public function __construct($department, $faculty)
    {
        $this->id = $department->id;
        $this->name = $department->name;
        $this->faculty = [
            'id' => $faculty->id ?? (is_array($faculty) ? $faculty['id'] : null),
            'name' => $faculty->name ?? (is_array($faculty) ? $faculty['name'] : null)
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'faculty' => $this->faculty
        ];
    }
}
