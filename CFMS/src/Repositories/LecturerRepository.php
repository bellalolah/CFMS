<?php

namespace Cfms\Repositories;

use Cfms\Models\Lecturer;

class LecturerRepository extends BaseRepository
{
    protected $table = 'lecturers';

    // Retrieve all lecturer records
    public function getAllLecturers(): array
    {
        $lecturerRecords = $this->findAll($this->table);
        $lecturerList = [];

        foreach ($lecturerRecords as $lecturerData) {
            $lecturer = new Lecturer();
            $lecturerList[] = $lecturer->toModel((object)$lecturerData);
        }

        return $lecturerList;
    }

    // Retrieve a specific lecturer by ID
    public function getLecturerById($id): ?Lecturer
    {
        $lecturerData = $this->findById($this->table, $id);
        if ($lecturerData) {
            $lecturer = new Lecturer();
            return $lecturer->toModel((object)$lecturerData);
        }

        return null;
    }

    // Create a new lecturer record
    public function createLecturer(Lecturer $lecturerData): ?Lecturer
    {
        $hashedPwd = $this->hashPassword($lecturerData->password_hash);

        $insert_data = [
            'name' => $lecturerData->name,
            'email' => $lecturerData->email,
            'password_hash' => $hashedPwd,
            'department_id' => $lecturerData->department_id,
            'faculty_id' => $lecturerData->faculty_id
        ];

        $lecturerData->id = $this->insert($this->table, $insert_data);

        if ($lecturerData->id) {
            $lecturer = new Lecturer();
            return $lecturer->getModel((object)$lecturerData);
        }

        return null;
    }

    private function hashPassword(string $pwd)
    {
        if (isset($pwd)) {
            $options = ['cost' => 12];
            return password_hash($pwd, PASSWORD_BCRYPT, $options);
        }
    }
}
