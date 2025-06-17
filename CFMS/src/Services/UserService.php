<?php

namespace Cfms\Services;

use Cfms\Dto\UserInfoDto;
use Cfms\Dto\UserWithProfileDto;
use Cfms\Models\User;
use Cfms\Repositories\user_profile\LecturerProfileRepository;
use Cfms\Repositories\user_profile\StudentProfileRepository;
use Cfms\Repositories\UserRepository;
use Cfms\Utils\PasswordUtil;
use Cfms\Dto\LecturerWithCoursesDto;
use Cfms\Repositories\LecturerCourseRepository;
use Cfms\Repositories\CourseRepository;

class UserService
{

    public function __construct(private UserRepository $userRepo,
                                private StudentProfileRepository $studentProfileRepo,
                                private LecturerProfileRepository $lecturerProfileRepo,
                                private LecturerCourseRepository $lecturerCourseRepo,
                                private CourseRepository $courseRepo){}

    public function registerUser(array $input): array
    {
        $required = ['full_name', 'email', 'password', 'role_id'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }
        if ($this->userRepo->findByEmail($input['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        $user = new User();
        $user = $user->getModel($input);
        $user->password = PasswordUtil::hash($user->password);
        $created = $this->userRepo->createUser($user);
        if ($created) {
            return ['success' => true, 'user_id' => $created->id];
        }
        return ['success' => false, 'message' => 'Failed to register user'];
    }

    public function getUserWithProfile(int $userId): ?UserWithProfileDto
    {
        $user = $this->userRepo->getUserById($userId);
        if (!$user) return null;
        $profile = null;
        if ($user->role_id == 3) { // Student (3 is student)
            $profile = $this->studentProfileRepo->findByUserId($userId);
        } elseif ($user->role_id == 2) { // Lecturer
            $profile = $this->lecturerProfileRepo->findByUserId($userId);
        }
        return new UserWithProfileDto($user, $profile ? (array)$profile : null);
    }

    public function getUserInfo(int $userId): ?UserInfoDto
    {
        $user = $this->userRepo->getUserById($userId);
        if (!$user) return null;
        return new UserInfoDto($user);
    }

    public function getPaginatedUsers(int $page = 1, int $perPage = 10): array
    {
        // Ensure page and perPage are at least 1
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $users = $this->userRepo->getUsersPaginated($perPage, $offset);
        $total = $this->userRepo->getTotalUserCount();
        $userDtos = array_map(fn($user) => new UserInfoDto($user), $users);
        return [
            'data' => $userDtos,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
            ]
        ];
    }

    public function getLecturers(): array
    {
        $lecturers = $this->userRepo->findByRoleId(2); // 2 is lecturer
        return array_map(fn($user) => new UserInfoDto($user), $lecturers);
    }

    public function getLecturersWithCourses(): array
    {
        $lecturers = $this->userRepo->findByRoleId(2); // 2 is lecturer
        $result = [];
        foreach ($lecturers as $lecturer) {
            $profile = $this->lecturerProfileRepo->findByUserId($lecturer->id);
            $courseIds = $this->lecturerCourseRepo->getCoursesForLecturer($lecturer->id);
            $courses = [];
            foreach ($courseIds as $courseId) {
                $course = $this->courseRepo->getCourseById($courseId);
                if ($course) {
                    $courses[] = new \Cfms\Dto\CourseDto($course);
                }
            }
            $result[] = new LecturerWithCoursesDto($lecturer, $profile ? (array)$profile : null, $courses);
        }
        return $result;
    }

    public function deleteUser(int $userId): bool
    {
        return $this->userRepo->deleteUser($userId);
    }
}
