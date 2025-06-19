<?php

namespace Cfms\Config;

use Cfms\Models\User;
use Cfms\Repositories\UserRepository;
use Cfms\Utils\JwtSessionGenerator;
use Cfms\Utils\PasswordUtil;

class DataInitializer
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $adminRepo)
    {
        $this->userRepository = $adminRepo;
    }

    public function initialize(): void
    {
         $email = "isa@gmail.com";
        $existingAdmin = $this->userRepository->findByEmail($email);


        if ($existingAdmin !== null) {
            $message = "Admin with email '{$email}' already exists.\n";
             return;
        }

        $admin = new User();
        $admin->full_name= "Isabella Afolabi";
        $admin->password = PasswordUtil::hash('1234');
        $admin->email = $email;
        $admin->role_id = 1;

        $created = $this->userRepository->createUser($admin);

        if ($created) {
            $message = "Admin created successfully!\n";
         } else {
            $message = "Admin creation failed!\n";
         }
    }
}