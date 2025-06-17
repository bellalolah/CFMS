<?php
namespace Cfms\Dto;

class UserLoginDto
{
    public UserWithProfileDto $user;
    public string $token;

    public function __construct(UserWithProfileDto $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }
}

