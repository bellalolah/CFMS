<?php

namespace Cfms\KPI\Controllers;


use Cfms\KPI\KPIDto\CriterionPerformanceDto;
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

        error_log("Entering getRecentFeedbacks method");
        $lecturerId = (int)$request->getAttribute('lecturerId');
        $data = $this->service->getRecentFeedback($lecturerId);

        return JsonResponse::withJson($response, array_map(fn($dto) => [
            'courseName' => $dto->courseName,
            'submittedAt' => $dto->submittedAt,
            'rating' => $dto->rating,
        ],$data));
    }

    public function getRecentTextFeedbacks(Request $request, Response $response): Response
    {
        $lecturerId = (int)$request->getAttribute('lecturerId');
        $data = $this->service->getRecentTextFeedback($lecturerId);

        return JsonResponse::withJson($response, array_map(fn($dto) => [
            'courseName' => $dto->courseName,
            'submittedAt' => $dto->createdAt,
            'feedbackText' => $dto->feedbackText,
            'rating' => $dto->overallRating,
        ],$data));
    }

    /**
     * Endpoint to fetch data for the lecturer's performance chart.
     */
    public function getPerformanceChart(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        // Ensure the user is a lecturer
        if ($user['role_id'] != 2) { // Assuming 2 is the lecturer role
            return JsonResponse::withJson($response,['error' => 'Unauthorized'], 403);
        }

        try {
            // Call the service method to get the DTOs
            $chartDataDtos = $this->service->getLecturerPerformanceChartData($user['id']);

            // Convert the array of DTOs into a simple array for the JSON response
            $responseData = array_map(fn(CriterionPerformanceDto $dto) => $dto->toArray(), $chartDataDtos);

            return JsonResponse::withJson($response, $responseData);

        } catch (\Exception $e) {
            error_log("Failed to get lecturer chart data: " . $e->getMessage());
            return JsonResponse::withJson($response,['error' => 'Could not retrieve performance data'], 500);
        }
    }
}
