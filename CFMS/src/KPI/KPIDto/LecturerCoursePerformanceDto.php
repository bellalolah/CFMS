<?php

namespace Cfms\KPI\KPIDto;

class LecturerCoursePerformanceDto
{
    public string $courseCode;
    public string $courseName;
    public string $semesterName;
    public string $departmentName;
    public int $numberOfReviews;
    public string $rating;

    public static function fromDbRow(\stdClass $row): self
    {
        $dto = new self();
        $dto->courseCode = $row->course_code ?? 'N/A';
        $dto->courseName = $row->course_name ?? 'N/A';
        $dto->semesterName = $row->semester_name ?? 'N/A';
        $dto->departmentName = $row->department_name ?? 'N/A';
        $dto->numberOfReviews = (int) ($row->number_of_reviews ?? 0);
        $dto->rating = $row->rating ?? 'N/A';
        return $dto;
    }
}