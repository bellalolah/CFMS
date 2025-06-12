<?php

namespace Cfms\Services;

use Cfms\Services\CourseService;
use Cfms\Services\SessionService;
use Cfms\Services\SemesterService;
use Cfms\Repositories\LecturerRepository;
use Cfms\Repositories\DepartmentRepository;

class AdminService
{
    private $lecturerRepository;
    private $departmentRepository;
    private $courseService;
    private $sessionService;
    private $semesterService;

    public function __construct(
        LecturerRepository $lecturerRepo,
        DepartmentRepository $departmentRepo,
        CourseService $courseService,
        SessionService $sessionService,
        SemesterService $semesterService,
        CourseOfferingService $offeringService
    ) {
        $this->lecturerRepository = $lecturerRepo;
        $this->departmentRepository = $departmentRepo;
        $this->courseService = $courseService;
        $this->sessionService = $sessionService;
        $this->semesterService = $semesterService;
        $this->offeringService = $offeringService;
    }

    // Utilities for admin dashboard population
    public function getAllLecturers(): array
    {
        return $this->lecturerRepository->getAllLecturers();
    }

    public function getAllDepartments(): array
    {
        return $this->departmentRepository->getAllDepartments();
    }

    // Optional: Shortcut methods that just delegate (if you want)
    public function createSession(array $data): ?\Cfms\Models\Session
    {
        return $this->sessionService->createSession($data);
    }

    public function createSemester(array $data): ?\Cfms\Models\Semester
    {
        return $this->semesterService->createSemester($data);
    }

    public function createCourse(array $data): ?\Cfms\Models\Course
    {
        return $this->courseService->createCourse($data);
    }
    public function assignLecturerToCourse(array $data): bool
{
    return $this->offeringService->createOffering($data);
}

public function getLecturersByDepartment(int $departmentId): array
{
    return $this->offeringService->getLecturersByDepartment($departmentId);
}

    // Example of coordination: get summary data for admin dashboard
    public function getDashboardStats(): array
    {
        return [
            'lecturers_count' => count($this->getAllLecturers()),
            'departments_count' => count($this->getAllDepartments()),
            'active_session' => $this->sessionService->getActiveSession(),
            'current_semester' => $this->semesterService->getCurrentSemester(),
        ];
    }
}
