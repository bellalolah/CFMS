<?php

namespace Cfms\Services;

use Cfms\Repositories\CourseRepository;
use Cfms\Repositories\SemesterRepository;
use Cfms\Models\Course;

class CourseService
{
    private $courseRepository;
    private $semesterRepository;

    public function __construct(CourseRepository $courseRepo, SemesterRepository $semesterRepo)
    {
        $this->courseRepository = $courseRepo;
        $this->semesterRepository = $semesterRepo;
    }

    public function createCourse(array $data): ?Course
    {
    return $this->courseRepository->createCourse($data);
    }
}
