<?php

namespace Cfms\Repositories;

use Cfms\Models\Semester;

class SemesterRepository extends BaseRepository
{
    protected $table = 'semesters';

    public function getAllSemesters(): array
    {
        $records = $this->findAll($this->table);
        $list = [];

        foreach ($records as $record) {
            $semester = new Semester();
            $list[] = $semester->toModel((object)$record);
        }

        return $list;
    }

    public function getSemesterById(int $id): ?Semester
    {
        $data = $this->findById($this->table, $id);
        if ($data) {
            $semester = new Semester();
            return $semester->toModel((object)$data);
        }

        return null;
    }

    public function getOpenSemester(): ?Semester
    {
        $records = $this->findByColumn($this->table, 'status', 'open');
        if (!empty($records)) {
            $semester = new Semester();
            return $semester->toModel((object)$records[0]);
        }
        return null;
    }

    public function createSemester(Semester $semesterModel): ?Semester
    {
        // Check if there's already an open semester
        if ($this->getOpenSemester()) {
            throw new \Exception("Cannot open a new semester while another is still active.");
        }

        $data = [
            'name' => $semesterModel->name,
            'session_id' => $semesterModel->session_id,
            'status' => 'open',
            'start_date' => date('Y-m-d'),
            'end_date' => null
        ];

        $semesterModel->id = $this->insert($this->table, $data);

        if ($semesterModel->id) {
            return $semesterModel->getModel((object)$data);
        }

        return null;
    }

    public function closeSemester(int $id): bool
    {
        return $this->update($this->table, [
            'status' => 'closed',
            'end_date' => date('Y-m-d')
        ], $id);
    }
    
    public function getCurrentSemester(): ?Semester
    {
    $result = $this->findByColumn('semesters', 'status', 'open');
    if (!empty($result)) {
        $semester = new Semester();
        return $semester->toModel((object) $result[0]);
    }
    return null;
    }
}
