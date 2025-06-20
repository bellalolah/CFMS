<?php

namespace Cfms\KPI\Services;

namespace Dell\Cfms\KPI\Services;

use Cfms\KPI\KPIDto\LecturerDashboardStatsDto;
use Cfms\KPI\Repositories\LecturerKPIRepository;

class LecturerKPIService
{
    public function __construct(private LecturerKPIRepository $repository)
    {

    }

    public function getDashboardStats(int $lecturerId):LecturerDashboardStatsDto
    {
        return $this->repository->getDashboardStats($lecturerId);
    }

    public function getLecturerPerformance(int $lecturerId): array
    {
        return $this->repository->getLecturerPerformanceOverview($lecturerId);
    }

    public function  getLecturerCoursesBySession(int $lecturerId, int $sessionId): array
    {
        return $this->repository->getLecturerCoursesBySession($lecturerId, $sessionId);
    }

    public function getRecentFeedback(int $lecturerId, int $limit = 2): array
    {
        return $this->repository->getRecentFeedback($lecturerId, $limit);
    }
}
