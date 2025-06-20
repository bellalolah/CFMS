<?php

namespace Cfms\KPI\Controllers;


use Cfms\Utils\JsonResponse;
use Dell\Cfms\KPI\Services\LecturerKPIService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LecturerKPIController
{

    public function __construct(private LecturerKPIService $service)
    {

    }

    public function dashboardOverview(Request $request, Response $response): Response
    {
        $lecturerId = (int)$request->getAttribute('lecturerId');
        $stats = $this->service->getDashboardStats($lecturerId);

        return JsonResponse::withJson(
            $response,
            [
                'activeCoursesCount' => $stats->activeCoursesCount,
                'activeStudents'=> $stats->activeStudents,
                'activeSemesterResponseRate' => $stats->activeSemesterResponseRate,
                'averageRating' => $stats->averageRating,
                'totalStudentsTaught' => $stats->totalStudentsTaught,
                'lecturerName' => $stats->lecturerName,
            ]
        );
    }

    public function lecturerPerformance(Request $request, Response $response): Response
    {
        $lecturerId = (int)$request->getAttribute('lecturerId');
        $data = $this->service->getLecturerPerformance($lecturerId);

        return JsonResponse::withJson($response, array_map(fn($dto) => [
            'lecturerName' => $dto->lecturerName,
            'department' => $dto->department,
            'numberOfCourses' => $dto->numberOfCourses,
            'numberOfReviews' => $dto->numberOfReviews,
            'averageRating' => $dto->averageRating5Scale
        ], $data));
    }

    public function getLecturerCoursesBySession(Request $request, Response $response): Response
    {
        $lecturerId = (int)$request->getAttribute('lecturerId');
        $sessionId = (int)$request->getAttribute('sessionId');
        $data = $this->service->getLecturerCoursesBySession($lecturerId, $sessionId);

        return JsonResponse::withJson($response, array_map(fn($dto) => [
            'courseName' => $dto->courseName,
            'courseCode' => $dto->courseCode,
            'semesterName' => $dto->semesterName,
            'departmentName' => $dto->departmentName,
            'rating' => $dto->rating,
            'numberOfReviews' => $dto->numberOfReviews,
        ], $data));
    }

    public function getRecentFeedbacks(Request $request,Response $response): Response
    {
        $lecturerId = (int)$request->getAttribute('lecturerId');
        $data = $this->service->getRecentFeedback($lecturerId);

        return JsonResponse::withJson($response, array_map(fn($dto) => [
            'courseName' => $dto->courseName,
            'submittedAt' => $dto->submittedAt,
            'rating' => $dto->rating,
        ], $data));
    }
    }
