<?php
namespace Cfms\Service;

use Cfms\Repository\StudentRepository;
use Cfms\Repository\LecturerRepository;
use Cfms\Repository\AdminRepository;

class AuthService
{
    private $studentRepo;
    private $lecturerRepo;
    private $adminRepo;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->lecturerRepo = new LecturerRepository();
        $this->adminRepo = new AdminRepository();
    }

    public function authenticate(string $role, string $username, string $password)
    {
        $user = null;

        switch ($role) {
            case 'student':
                $user = $this->studentRepo->findByMatricNumber($username);
                break;
            case 'lecturer':
                $user = $this->lecturerRepo->findByEmail($username);
                break;
            case 'admin':
                $user = $this->adminRepo->findByEmail($username);
                break;
            default:
                return null;
        }

        if (!$user) return null;

        // Verify password using password_verify if hashed, or simple == if plain text (not recommended)
        if (password_verify($password, $user->password_hash)) {
            return $user;
        }

        return null;
    }
}
