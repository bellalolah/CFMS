<?php 
namespace Cfms\Repositories;

class StudentRepository extends BaseRepository{

    protected $table = 'students';

     // Retrieve all student records from the database and map them to the student model
    public function getAllStudent(): array
    {
        $studentRecords = $this->findAll($this->table);
        $studentList = [];

        foreach ($studentRecords as $studentData) {
            $student = new Student();
            $studentList[] = $student->toModel((object)$studentData); // Convert each record to a student object
        }

        return $studentList;
    }

    // Retrieve a specific student record by ID and map it to the student model
    public function getStudentById($id): ?Student
    {
        $studentData = $this->findById($this->table, $id);
        if ($studentData) {
            $student = new Student();
            return $student->toModel((object)$studentData); // Map data to the student model
        }

        return null;
    }
    public function findbyMatric(string $matric_number): ?Lecturer
    {
        // Use the new findByColumn method
        $studentRecords = $this->findByColumn($this->table, 'matric_number', $matric_number);

        if (!empty($studentRecords)) {
            $student = new Student();
            return $lecturer->toModel((object)$studentRecords[0]); // Return the first result
        }

        return null;
    }

    // Create a new student record in the database and return the created student object
    public function createStudent(Student $studentData): ?Student
    {
        $hashedPwd = $this->hashPassword($studentData->password_hash);

        // Prepare the data to be inserted
        $insert_data = [
            'name' => $studentData->name,
            'matric_number' => $studentData->matric_number,
            'email' => $studentData->email,
            'password_hash' => $hashedPwd,
            'full_name' => $studentData->full_name,
            'department_id' => $studentData->department_id,
            'level' => $studentData->level,
            'faculty_id' => $studentData->faculty_id
	
        ];

        // Use the BaseRepository's insert method
        $studentData->id = $this->insert($this->table, $insert_data);

        if ($studentData->id) {
            $student = new Student();
            return $student->getModel((object)$studentData);
        }

        return null;
    }
    private function hashPassword(string $pwd)
    {
        if (isset($pwd)) {
            $options = ['cost' => 12];
            $hashedPwd = password_hash($pwd, PASSWORD_BCRYPT, $options);
            return $hashedPwd;
        }
    }
}