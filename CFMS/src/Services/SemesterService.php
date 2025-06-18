<?php
namespace Cfms\Services;

use Cfms\Repositories\SemesterRepository;
use Cfms\Models\Semester;

class SemesterService
{
    public function __construct(private SemesterRepository $semesterRepo) {}

    public function createSemester(array $data): int
    {
        return $this->semesterRepo->createSemester($data);
    }

    public function updateSemester($id, array $data): bool
    {
        return $this->semesterRepo->updateSemester($id, $data);
    }

    public function deleteSemester($id): bool
    {
        return $this->semesterRepo->deleteSemester($id);
    }
}
