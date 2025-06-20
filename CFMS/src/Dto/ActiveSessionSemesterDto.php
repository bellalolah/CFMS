<?php

namespace Cfms\Dto;

class ActiveSessionSemesterDto
{
    public int $sessionId;
    public string $sessionName;
    public string $sessionStatus;
    public bool $sessionIsActive;
    public int $semesterId;
    public string $semesterName;
    public string $startDate;
    public ?string $endDate;
    public bool $isCurrent; // <-- NEW FIELD

    public static function fromDbRow(\stdClass $row): self
    {
        $dto = new self();
        $dto->sessionId = (int) $row->session_id;
        $dto->sessionName = $row->session_name;
        $dto->sessionStatus = $row->session_status;
        $dto->sessionIsActive = (bool) $row->session_is_active;
        $dto->semesterId = (int) $row->semester_id;
        $dto->semesterName = $row->semester_name;
        $dto->startDate = $row->start_date;
        $dto->endDate = $row->end_date;
        // Populate the new field by casting the SQL result (0 or 1) to a boolean.
        $dto->isCurrent = (bool) ($row->is_current ?? false); // <-- NEW MAPPING
        return $dto;
    }
}