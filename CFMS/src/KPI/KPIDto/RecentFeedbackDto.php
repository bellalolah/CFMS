<?php

namespace Cfms\KPI\KPIDto;

class RecentFeedbackDto
{
    public string $courseName;
    public string $submittedAt;
    public string $rating;

    public static function fromDbRow(\stdClass $row): self
    {
        $dto = new self();
        $dto->courseName = $row->course_name ?? 'N/A';
        // You might want to format this date nicely in the frontend
        $dto->submittedAt = $row->submitted_at;
        $dto->rating = $row->rating;
        return $dto;
    }
}