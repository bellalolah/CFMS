<?php

namespace Dell\Cfms\KPI\Services;

namespace Dell\Cfms\KPI\Services;


use Dell\Cfms\KPI\KPIDto\AdminDashboardStatsDto;
use Dell\Cfms\KPI\Repositories\AdminKPIRepository;


class AdminKPIService
{

    public function __construct(private AdminKPIRepository $repository)
    {

    }

    public function getDashboardStats(): AdminDashboardStatsDto
    {

        $data = $this->repository->getGeneralDashboardStats();
        return AdminDashboardStatsDto::fromDbRow($data);
    }

    public function getLecturerPerformance(): array
    {
        return $this->repository->getLecturerPerformanceOverview();
    }
}
