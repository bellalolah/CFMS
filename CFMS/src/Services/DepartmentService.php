<?php

namespace Cfms\Services;

use Cfms\Dto\DepartmentInfoDto;
use Cfms\Repositories\CourseRepository;
use Cfms\Repositories\DepartmentRepository;
use Cfms\Repositories\FacultyRepository;

class DepartmentService
{

    public function __construct(private DepartmentRepository $departmentRepository, private FacultyRepository $facultyRepository, private CourseRepository $courseRepository )
    {

    }

    public function getAllDepartments(): array
    {
        return $this->departmentRepository->findAllDepartments();
    }

    public function getDepartmentById(int $id)
    {
        return $this->departmentRepository->findDepartmentById($id);
    }

    public function getDepartmentByName(string $name): array
    {
        return $this->departmentRepository->findDepartmentByName($name);
    }

    public function getDepartmentsByFacultyId(int $facultyId): array
    {
        return $this->departmentRepository->findDepartmentsByFacultyId($facultyId);
    }

    public function createDepartment(array $data): int
    {
        return $this->departmentRepository->createDepartment($data);
    }

    public function createDepartments(array $departments): array
    {
        return $this->departmentRepository->createDepartments($departments);
    }

    public function updateDepartment(int $id, array $data): bool
    {
        return $this->departmentRepository->updateDepartment($id, $data);
    }

    public function deleteDepartment(int $id): bool
    {
        return $this->departmentRepository->deleteDepartment($id);
    }

    public function getAllDepartmentInfo(): array
    {
        $departments = $this->departmentRepository->findAllDepartments();
        $faculties = $this->facultyRepository->findAllFaculty();
        $facultyMap = [];
        foreach ($faculties as $faculty) {
            $facultyMap[$faculty->id] = $faculty;
        }
        $result = [];
        foreach ($departments as $department) {
            $faculty = $facultyMap[$department->faculty_id] ?? null;
            $result[] = new \Cfms\Dto\DepartmentInfoDto($department, $faculty);
        }
        return $result;
    }

    public function getDepartmentsWithCourses(): array
    {
        // 1. Get all departments
        $departments = $this->departmentRepository->findAllDepartments();
        if (empty($departments)) {
            return [];
        }

        // 2. Get an array of just the department IDs
        $departmentIds = array_map(fn($d) => $d->id, $departments);

        // 3. Get all courses for those departments in a SINGLE query
        $allCourses = $this->courseRepository->findByDepartmentIds($departmentIds);

        // 4. Group the courses by their department_id for easy lookup
        $coursesByDeptId = [];
        foreach ($allCourses as $course) {
            $coursesByDeptId[$course->department_id][] = $course;
        }

        // 5. Build the final DTOs
        $result = [];
        foreach ($departments as $dept) {
            // Find the courses for the current department, or default to an empty array
            $deptCourses = $coursesByDeptId[$dept->id] ?? [];
            $result[] = new \Cfms\Dto\DepartmentWithCoursesDto($dept, $deptCourses);
        }

        return $result;
    }
}