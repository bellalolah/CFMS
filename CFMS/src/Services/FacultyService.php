<?php

namespace Cfms\Services;

use Cfms\Repositories\FacultyRepository;
use Cfms\Dto\FacultyInfoDto;

class FacultyService
{

    public function __construct(private FacultyRepository $facultyRepository, private \Cfms\Repositories\DepartmentRepository $departmentRepository )
    {

    }

    public function getAllFaculty(): array
    {
        return $this->facultyRepository->findAllFaculty();
    }

    public function getAllFacultyWithoutDates(): array
    {
        return $this->facultyRepository->findAllFacultyWithoutDates();
    }

    public function getFacultyById(int $id)
    {
        return $this->facultyRepository->findFacultyById($id);
    }

    public function getFacultyByName(string $name): array
    {
        return $this->facultyRepository->findFacultyByName($name);
    }

    public function createFaculty(array $data): int
    {
        return $this->facultyRepository->createFaculty($data);
    }

    public function createFaculties(array $faculties): array
    {
        return $this->facultyRepository->createFaculties($faculties);
    }

    public function updateFaculty(int $id, array $data): bool
    {
        return $this->facultyRepository->updateFaculty($id, $data);
    }

    public function deleteFaculty(int $id): bool
    {
        return $this->facultyRepository->deleteFaculty($id);
    }

    public function getAllFacultyInfo(): array
    {
        $faculties = $this->facultyRepository->findAllFaculty();
        return array_map(fn($faculty) => new FacultyInfoDto($faculty), $faculties);
    }


    public function getFacultiesWithDepartments(): array
    {
        //  Get all faculties
        $faculties = $this->facultyRepository->findAllFaculty();
        if (empty($faculties)) {
            return [];
        }

        //  Get an array of just the faculty IDs
        $facultyIds = array_map(fn($f) => $f->id, $faculties);

        //  Get all departments for those faculties in a SINGLE query
        $allDepartments = $this->departmentRepository->findByFacultyIds($facultyIds);

        //  Group the departments by their faculty_id for easy lookup
        $deptsByFacultyId = [];
        foreach ($allDepartments as $dept) {
            $deptsByFacultyId[$dept->faculty_id][] = $dept;
        }

        //   Build the final DTOs
        $result = [];
        foreach ($faculties as $faculty) {
            // Find the departments for the current faculty, or default to an empty array
            $facultyDepts = $deptsByFacultyId[$faculty->id] ?? [];
            $result[] = new \Cfms\Dto\FacultyWithDepartmentsDto($faculty, $facultyDepts);
        }

        return $result;
    }
}