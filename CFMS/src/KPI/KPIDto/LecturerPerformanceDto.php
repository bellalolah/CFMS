<?php

namespace Dell\Cfms\KPI\KPIDto;
class LecturerPerformanceDto
{
    public string $lecturerName;
    public string $department;
    public int $numberOfCourses;
    public int $numberOfReviews;
    public string $averageRating5Scale;

    public function __construct(
        string $lecturerName,
        string $department,
        int $numberOfCourses,
        int $numberOfReviews,
        float $averageRating5Scale
    ) {
        $this->lecturerName = $lecturerName;
        $this->department = $department;
        $this->numberOfCourses = $numberOfCourses;
        $this->numberOfReviews = $numberOfReviews;
        $this->averageRating5Scale = $averageRating5Scale;
    }

    public static function fromDbRow(object $row): self
    {
        return new self(
            $row->lecturer_name,
            $row->department,
            (int) $row->number_of_courses,
            (int) $row->number_of_reviews,
            $row->average_rating_5_scale
        );
    }
}
