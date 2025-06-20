<?php

namespace Cfms\KPI\KPIDto;


namespace Cfms\KPI\KPIDto;

class RecentTextFeedbackDto
{
    public string $courseName;
    public string $feedbackText;
    public string $createdAt; // Renamed from submittedAt for clarity
    public string $overallRating;

    public static function fromDbRow(\stdClass $row): self
    {
        $dto = new self();
        $dto->courseName = $row->course_name ?? 'N/A';
        $dto->feedbackText = $row->answer_text ?? '';
        $dto->createdAt = $row->created_at; // Using the feedback's creation time
        $dto->overallRating = $row->overall_rating ?? 'N/A'; // Assuming overall_rating is a string, adjust if it's numeric
        return $dto;
    }
}