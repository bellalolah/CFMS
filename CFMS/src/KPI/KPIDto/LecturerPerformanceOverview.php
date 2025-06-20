<?php

namespace Cfms\KPI\KPIDto;

class LecturerPerformanceOverview
{

    public string $lecturerName;
    public string $department;
    public int $numberOfCourses;
    public int $numberOfReviews;
    public float $averageRating;

    public static function fromDbRow(\stdClass $row): self
    {
        $dto = new self();
        $dto->lecturerName = $row->lecturer_name ?? 'N/A';
        $dto->department = $row->department ?? 'N/A';
        $dto->numberOfCourses = (int) ($row->number_of_courses ?? 0);
        $dto->numberOfReviews = (int) ($row->number_of_reviews ?? 0);
        $dto->averageRating = (float) ($row->average_rating_5_scale ?? 0.0);
        return $dto;
    }

}