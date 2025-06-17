<?php
namespace Cfms\Dto;

class SemesterDto
{
    public int $id;
    public string $name;
    public int $session_id;
    public string $start_date;
    public ?string $end_date;

    public function __construct($semester)
    {
        $this->id = $semester->id;
        $this->name = $semester->name;
        $this->session_id = $semester->session_id;
        $this->start_date = $semester->start_date;
        $this->end_date = $semester->end_date;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'session_id' => $this->session_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];
    }
}

