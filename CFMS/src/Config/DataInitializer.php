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
        file_put_contents('data_initializer.log', "DataInitializer::initialize called\n", FILE_APPEND);
        $email = "isa@gmail.com";
        $existingAdmin = $this->userRepository->findByEmail($email);

        $outputFile = 'data_initializer.log'; // Define a log file

        if ($existingAdmin !== null) {
            $message = "Admin with email '{$email}' already exists.\n";
            file_put_contents($outputFile, $message, FILE_APPEND);
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
            file_put_contents($outputFile, $message, FILE_APPEND);
        } else {
            $message = "Admin creation failed!\n";
            file_put_contents($outputFile, $message, FILE_APPEND);
        }
    }
}