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

    public function toArray(): array
    {
        $profileData = null;
        if ($this->profile) {
            // Check if the profile object has a toArray method (like our DTOs do)
            if (method_exists($this->profile, 'toArray')) {
                $profileData = $this->profile->toArray();
            } else {
                // Otherwise, just cast it to an array (for basic profiles)
                $profileData = (array)$this->profile;
            }
        }

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'profile' => $profileData,
        ];
    }
}

