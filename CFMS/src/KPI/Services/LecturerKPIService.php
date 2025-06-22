<?php

namespace Cfms\KPI\Services;

namespace Dell\Cfms\KPI\Services;

use Cfms\KPI\KPIDto\CriterionPerformanceDto;
use Cfms\KPI\KPIDto\LecturerDashboardStatsDto;
use Cfms\KPI\Repositories\LecturerKPIRepository;
use Cfms\Repositories\FeedbackRepository;

class LecturerKPIService
{
    public function __construct(private LecturerKPIRepository $repository, private FeedbackRepository $feedbackRepo)
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

    public function getRecentTextFeedback(int $lecturerId, int $limit = 2): array
    {
        return $this->repository->getRecentTextFeedback($lecturerId, $limit);
    }

    /**
     * Gets the data needed to render a performance chart for a lecturer's dashboard.
     *
     * @param int $lecturerId
     * @return CriterionPerformanceDto[]
     */
    public function getLecturerPerformanceChartData(int $lecturerId): array
    {
        // 1. Call the new, powerful repository method to get the aggregated data
        $rawData = $this->feedbackRepo->getLecturerAverageScoresByCriterion($lecturerId);

        // 2. Map the raw associative array from the DB into our clean DTOs
        $performanceDtos = array_map(
            fn($row) => new CriterionPerformanceDto(
                $row['criterion_name'],
                (float)$row['average_score']
            ),
            $rawData
        );

        return $performanceDtos;
    }

}
