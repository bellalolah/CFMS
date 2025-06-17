<?php

namespace Cfms\Services;

use Cfms\Repositories\CourseDepartmentRepository;

class CourseDepartmentService
{
    private CourseDepartmentRepository $courseDepartmentRepository;

    public function __construct(CourseDepartmentRepository $courseDepartmentRepository)
    {
        $this->courseDepartmentRepository = $courseDepartmentRepository;
    }

    public function assignCourseToDepartment(int $courseId, int $departmentId): int
    {
        return $this->courseDepartmentRepository->assignCourseToDepartment($courseId, $departmentId);
    }

    public function removeCourseFromDepartment(int $courseId, int $departmentId): bool
    {
        return $this->courseDepartmentRepository->removeCourseFromDepartment($courseId, $departmentId);
    }

    public function getDepartmentsForCourse(int $courseId): array
    {
        return $this->courseDepartmentRepository->getDepartmentsForCourse($courseId);
    }

    public function getCoursesForDepartment(int $departmentId): array
    {
        return $this->courseDepartmentRepository->getCoursesForDepartment($departmentId);
    }
}

