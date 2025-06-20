<?php

namespace Cfms\KPI\KPIDto;

class LecturerDashboardStatsDto
{
    public string $lecturerName;
    public float $averageRating;
    public int $activeCoursesCount;
    public int $activeStudents;
    public int $totalStudentsTaught;
    public float $activeSemesterResponseRate;

    public static function fromDbRow(\stdClass $row): self
    {
        $dto = new self();
        $dto->lecturerName = $row->lecturer_name ?? 'N/A';
        $dto->averageRating = (float) ($row->average_rating ?? 0.0);
        $dto->activeCoursesCount = (int) ($row->active_courses_count ?? 0);
        $dto->activeStudents = (int) ($row->active_students ?? 0);
        $dto->totalStudentsTaught = (int) ($row->total_students_taught ?? 0);
        $dto->activeSemesterResponseRate = (float) ($row->active_semester_response_rate ?? 0.0);
        return $dto;
    }
}