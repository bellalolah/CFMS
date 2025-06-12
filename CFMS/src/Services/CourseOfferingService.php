<?php

namespace Cfms\Services;

class CourseOfferingService
{
    private $courseOfferingRepo;
    private $lecturerRepo;
    private $semesterRepo;

    public function __construct(
        CourseOfferingRepository $courseOfferingRepo,
        LecturerRepository $lecturerRepo,
        SemesterRepository $semesterRepo

    ) {
        $this->courseOfferingRepo = $courseOfferingRepo;
        $this->lecturerRepo = $lecturerRepo;
        $this->semesterRepo = $semesterRepo;
    }

    public function assignLecturerToCourse(array $data): ?CourseOffering
    {
        $currentSemester = $this->semesterRepo->getCurrentSemester();
        $data['semester_id'] = $currentSemester?->id;

        return $this->courseOfferingRepo->create($data);
    }

    public function getOfferingsByDepartment(int $departmentId): array
    {
        return $this->courseOfferingRepo->findByDepartment($departmentId);
    }

    public function updateLecturerAssignment(int $offeringId, int $lecturerId): bool
    {
        return $this->courseOfferingRepo->updateLecturer($offeringId, $lecturerId);
    }
    public function getLecturersByDepartment(int $departmentId): array
    {
        return $this->lecturerRepo->getByDepartment($departmentId);
    }

    public function getOfferingsBySemester(int $semesterId): array
    {
        return $this->offeringRepo->getBySemester($semesterId);
    }
}
