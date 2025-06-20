<?php


namespace Dell\Cfms\KPI\KPIDto;

class AdminDashboardStatsDto
{
    public int $totalLecturers;
    public int $totalStudents;
    public string $responseRatePercentage;

    public function __construct(int $totalLecturers, int $totalStudents, float $responseRatePercentage)
    {
        $this->totalLecturers = $totalLecturers;
        $this->totalStudents = $totalStudents;
        $this->responseRatePercentage = $responseRatePercentage;
    }

    public static function fromDbRow(object $row): self
    {
        return new self(
            (int) $row->total_lecturers,
            (int) $row->total_students,
            $row->response_rate_percentage
        );
    }
}
