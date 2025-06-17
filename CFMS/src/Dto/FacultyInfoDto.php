<?php
namespace Cfms\Dto;

class FacultyInfoDto
{
    public int $id;
    public string $name;

    public function __construct($faculty)
    {
        $this->id = $faculty->id;
        $this->name = $faculty->name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}

