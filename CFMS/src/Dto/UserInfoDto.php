<?php
namespace Cfms\Dto;

class UserInfoDto
{
    public int $id;
    public string $full_name;
    public string $email;
    public int $role_id;

    public function __construct($user)
    {
        $this->id = $user->id;
        $this->full_name = $user->full_name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
    }
}

