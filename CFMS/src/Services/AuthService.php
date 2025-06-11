<?php
namespace Cfms\Services;

use Cfms\Repositories\StudentRepository;
use Cfms\Repositories\LecturerRepository;
use Cfms\Repositories\AdminRepository;

class AuthService
{
    private StudentRepository $studentRepo;
    private LecturerRepository $lecturerRepo;
    private AdminRepository $adminRepo;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->lecturerRepo = new LecturerRepository();
        $this->adminRepo = new AdminRepository();
    }

    public function authenticate(array $input): array
    {
        $role = $input['role'] ?? null;

        if ($role === 'student') {
            $matric = $input['matric_number'] ?? '';
            $password = $input['password'] ?? '';

            $student = $this->studentRepo->findByMatric($matric);
            if (!$student || !password_verify($password, $student->password)) {
                return ['success' => false, 'message' => 'Invalid matric number or password'];
            }

            $_SESSION['user'] = [
                'id' => $student->id,
                'matric_number' => $student->matric_number,
                'role' => 'student'
            ];
            return ['success' => true, 'user' => $_SESSION['user']];
        }

        if ($role === 'lecturer') {
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';

            $lecturer = $this->lecturerRepo->findByEmail($email);
            if (!$lecturer || !password_verify($password, $lecturer->password)) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            $_SESSION['user'] = [
                'id' => $lecturer->id,
                'email' => $lecturer->email,
                'role' => 'lecturer'
            ];
            return ['success' => true, 'user' => $_SESSION['user']];
        }

        if ($role === 'admin') {
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';

            $admin = $this->adminRepo->findByEmail($email);
            if (!$admin || !password_verify($password, $admin->password)) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            $_SESSION['user'] = [
                'id' => $admin->id,
                'email' => $admin->email,
                'role' => 'admin',
                'admin_type' => $admin->role,
            ];
            return ['success' => true, 'user' => $_SESSION['user']];
        }

        return ['success' => false, 'message' => 'Invalid role specified'];
    }
}
