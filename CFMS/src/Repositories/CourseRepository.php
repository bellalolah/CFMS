<?php

namespace Cfms\Repositories;

use Cfms\Models\Course;

class CourseRepository extends BaseRepository
{
    protected string $table = 'courses';

    public function getAllCourses(): array
    {
        $records = $this->findAll($this->table);
        $courses = [];

        foreach ($records as $record) {
            $course = new Course();
            $courses[] = $course->toModel((object)$record);
        }

        return $courses;
    }

    public function getCourseById(int $id): ?Course
    {
        $record = $this->findById($this->table, $id);

        if ($record) {
            $course = new Course();
            return $course->toModel((object)$record);
        }

        return null;
    }

    public function createCourse(Course $course): ?Course
    {
        $data = $course->getModel($course);
        $course->id = $this->insert($this->table, $data);

        if ($course->id) {
            $newCourse = new Course();
            return $newCourse->toModel((object)(array_merge(['id' => $course->id], $data)));
        }

        return null;
    }

    public function updateCourse(int $id, Course $course): bool
    {
        $data = $course->getModel($course);
        return $this->update($this->table, $data, $id);
    }
    public function deleteCourse(int $id): bool
    {
        return $this->deleteById($this->table, $id);
    }
}
