<?php
namespace Cfms\Dto;

class SessionDto
{
    public int $id;
    public string $name;
    public string $start_date;
    public ?string $end_date;
    public string $status;
    public bool $is_active;
    public array $semesters = [];

    public function __construct($session, array $semesters = [])
    {
        $this->id = $session->id;
        $this->name = $session->name;
        $this->start_date = $session->start_date;
        $this->end_date = $session->end_date;
        $this->status = $session->status;
        $this->is_active = $session->is_active;
        $this->semesters = $semesters;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'semesters' => $this->semesters, // Assuming semesters is already an array
        ];
    }
}

