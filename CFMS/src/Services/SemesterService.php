<?php

namespace Cfms\Services;

use Cfms\Repositories\SemesterRepository;

class SemesterService
{
    private $semesterRepository;

    public function __construct(SemesterRepository $semesterRepo)
    {
        $this->semesterRepository = $semesterRepo;
    }

    public function createSemester(array $data): ?\Cfms\Models\Semester
    {
        // Close the current semester if any
        $this->semesterRepository->closeCurrentSemester();

        // Create new semester
        return $this->semesterRepository->createSemester($data);
    }

    public function closeSemester(int $semesterId): bool
    {
        return $this->semesterRepository->closeSemester($semesterId);
    }

    public function getAllSemesters(): array
    {
        return $this->semesterRepository->getAllSemesters();
    }

    public function getCurrentSemester(): ?\Cfms\Models\Semester
    {
        return $this->semesterRepository->getCurrentSemester();
    }
}
