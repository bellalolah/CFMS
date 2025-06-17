<?php
namespace Cfms\Dto;

class UserWithProfileDto
{
    public int $id;
    public string $full_name;
    public string $email;
    public int $role_id;
    public $profile; // Can be array or object, nullable

    public function __construct($user, $profile = null)
    {
        $this->id = $user->id;
        $this->full_name = $user->full_name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->profile = $profile;
    }
}

