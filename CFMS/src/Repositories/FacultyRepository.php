<?php

namespace Cfms\Repositories;

use Cfms\Models\Faculty;

class FacultyRepository extends BaseRepository
{
    protected $table = 'faculties';

    // Retrieve all faculties
    public function getAllFaculties(): array
    {
        $facultyRecords = $this->findAll($this->table);
        $facultyList = [];

        foreach ($facultyRecords as $facultyData) {
            $faculty = new Faculty();
            $facultyList[] = $faculty->toModel((object)$facultyData);
        }

        return $facultyList;
    }

    // Retrieve a specific faculty by ID
    public function getFacultyById($id): ?Faculty
    {
        $facultyData = $this->findById($this->table, $id);
        if ($facultyData) {
            $faculty = new Faculty();
            return $faculty->toModel((object)$facultyData);
        }

        return null;
    }

    // Create a new faculty record
    public function createFaculty(Faculty $facultyData): ?Faculty
    {
        $insert_data = [
            'name' => $facultyData->name,
            'code' => $facultyData->code
        ];

        $facultyData->id = $this->insert($this->table, $insert_data);

        if ($facultyData->id) {
            $faculty = new Faculty();
            return $faculty->getModel((object)$facultyData);
        }

        return null;
    }
}
