<?php

namespace Cfms\Services;


use Cfms\Models\User;
use Cfms\Repositories\RoleRepository;
use Cfms\Repositories\UserRepository;
use Cfms\Utils\JwtSessionGenerator;
use Cfms\Utils\PasswordUtil;
use Cfms\Dto\UserWithProfileDto;
use Cfms\Dto\UserLoginDto;
use Cfms\Services\UserService;

class AuthService
{

    // Constructor Injection, just like in Spring!
    // The 'private' keyword automatically creates and assigns the properties for you.
    public function __construct(
        private UserRepository $userRepo,
        private RoleRepository $roleRepo,
        private UserService $userService
    ) {
        // The constructor body can be empty!
        // PHP automatically does the equivalent of:
        // $this->userRepo = $userRepo;
        // ...and so on.
    }

    // register as a student, lecturer.


    public function registerUser(array $input): array
    {
        $role = $input['role_id'] ?? null; // check the role;

        if (!$this->isValidRole($role)) {
            return $this->fail('Invalid role');
        }

        $fetchedRole = $this->roleRepo->fetchRoleById($role);
        if (empty($input['email']) || empty($input['password']) || empty($input['full_name'])) {
            return $this->fail('Email, password, and full name are required');
        }

        if ($this->userRepo->findByEmail($input['email'])) {
            return $this->fail('Email already exists');
        }

        $user = new User();
        $user->email = $input['email'];
        $user->password = PasswordUtil::hash($input['password']);
        $user->full_name = $input['full_name'];
        $user->role_id = $role;

        $created = $this->userRepo->createUser($user);

        if (!$created) {
            return $this->fail('Something went wrong during registration');
        }

        $token = JwtSessionGenerator::generate(
            $user->id,
            $user->email,
            $user->role_id
        );

        // Get user with profile
        $userWithProfile = $this->userService->getUserWithProfile($user->id);
        $dto = new UserLoginDto($userWithProfile, $token);
        return [
            'success' => true,
            'data' => (array)$dto
        ];
    }

    public function authenticate(array $input): array
    {
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->fail('Email, password are required');
        }

        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            return $this->fail('User not found');
        }

        if (!PasswordUtil::verify($password, $user->password)) {
            return $this->fail('Invalid password');
        }

        $token = JwtSessionGenerator::generate(
            $user->id,
            $user->email,
            $user->role_id
        );

        // Get user with profile
        $userWithProfile = $this->userService->getUserWithProfile($user->id);
        $dto = new UserLoginDto($userWithProfile, $token);
        return [
            'success' => true,
            'data' => (array)$dto
        ];
    }

    private function isValidRole(?string $role): bool
    {
        return in_array($role, [1,2, 3]);
    }

    private function fail(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}