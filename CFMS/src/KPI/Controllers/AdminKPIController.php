<?php

namespace Dell\Cfms\KPI\Controllers;



use Cfms\Utils\JsonResponse;
use Dell\Cfms\KPI\Services\AdminKPIService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminKPIController
{

    public function __construct(private AdminKPIService $service)
    {
    }

    public function dashboardOverview(Request $request, Response $response): Response
    {
        $stats = $this->service->getDashboardStats();

        return JsonResponse::withJson(
            $response,
            [
                'totalLecturers' => $stats->totalLecturers,
                'totalStudents' => $stats->totalStudents,
                'responseRate' => round($stats->responseRatePercentage, 2) . '%'
            ]
        );
    }

    public function lecturerPerformance(Request $request, Response $response): Response
    {
        $data = $this->service->getLecturerPerformance();

        return JsonResponse::withJson($response,array_map(fn($dto) => [
            'lecturerName' => $dto->lecturerName,
            'department' => $dto->department,
            'numberOfCourses' => $dto->numberOfCourses,
            'numberOfReviews' => $dto->numberOfReviews,
            'averageRating' => $dto->averageRating5Scale
        ], $data));
    }
}
