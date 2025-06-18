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


    public function __construct(private UserRepository            $userRepo,
                                private StudentProfileRepository  $studentProfileRepo,
                                private LecturerProfileRepository $lecturerProfileRepo,
                                private LecturerCourseRepository  $lecturerCourseRepo,
                                private LecturerProfileService    $lecturerProfileService,
                                private StudentProfileService    $studentProfileService,
                                private CourseRepository          $courseRepo){}

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

    public function createLecturerWithProfile(array $input): ?UserWithProfileDto
    {
        // 1. Validate all required fields
        $required = ['full_name', 'email', 'password', 'department_id', 'faculty_id'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new \InvalidArgumentException("$field is required");
            }
        }
        if ($this->userRepo->findByEmail($input['email'])) {
            throw new \InvalidArgumentException('Email already exists');
        }

        // 2. Prepare the data for each table
        $userData = [
            'full_name' => $input['full_name'],
            'email' => $input['email'],
            'password' => PasswordUtil::hash($input['password']),
            'role_id' => 2, // Hard-code role to Lecturer
        ];

        $profileData = [
            'department_id' => $input['department_id'],
            'faculty_id' => $input['faculty_id'],
        ];

        // 3. Call the single repository method that handles everything
        $createdUser = $this->userRepo->createLecturerWithProfile($userData, $profileData);

        // 4. If successful, use the existing getProfile method to return the full DTO
        if ($createdUser) {
            return $this->getUserWithProfile($createdUser->id);
        }

        return null; // The operation failed in the repository
    }



    // 2. Add the new method to create the student
    public function createStudentWithProfile(array $input): ?UserWithProfileDto
    {
        // Validate all required fields
        $required = ['full_name', 'email', 'password', 'matric_number', 'department_id', 'faculty_id', 'level'];
        foreach ($required as $field) {
            if (empty($input[$field])) throw new \InvalidArgumentException("$field is required");
        }
        if ($this->userRepo->findByEmail($input['email'])) {
            throw new \InvalidArgumentException('Email already exists');
        }
        if ($this->studentProfileRepo->existsByMatricNumber($input['matric_number'])) {
            throw new \InvalidArgumentException('Matric number already exists');
        }

        // Prepare data for the two tables
        $userData = [
            'full_name' => $input['full_name'],
            'email' => $input['email'],
            'password' => PasswordUtil::hash($input['password']),
            'role_id' => 3, // Hard-code role to Student
        ];
        $profileData = [
            'matric_number' => $input['matric_number'],
            'department_id' => $input['department_id'],
            'faculty_id' => $input['faculty_id'],
            'level' => $input['level'],
        ];

        // Call the single repository method that handles the transaction
        $createdUser = $this->userRepo->createStudentWithProfile($userData, $profileData);

        // If successful, get the full DTO to return
        if ($createdUser) {
            return $this->getUserWithProfile($createdUser->id);
        }
        return null;
    }


// 3. UPGRADE the existing getUserWithProfile method
    public function getUserWithProfile(int $userId): ?UserWithProfileDto
    {
        $user = $this->userRepo->getUserById($userId);
        if (!$user) return null;

        $profileDto = null;
        if ($user->role_id == 2) { // Lecturer
            $profileDto = $this->lecturerProfileService->getProfile($userId);
        } elseif ($user->role_id == 3) { // Student
            // THIS IS THE UPGRADE: Use the service to get the detailed DTO
            $profileDto = $this->studentProfileService->getDetailedProfile($userId);
        }

        return new UserWithProfileDto($user, $profileDto);
    }






    /**
     * Gets a paginated list of all students with their detailed profiles.
     */
    public function getPaginatedStudentsWithProfiles(int $page = 1, int $perPage = 15): array
    {
        return $this->getPaginatedUsersByRoleWithProfiles(3, $page, $perPage); // 3 = Student
    }

    /**
     * Gets a paginated list of all lecturers with their detailed profiles.
     */
    public function getPaginatedLecturersWithProfiles(int $page = 1, int $perPage = 15): array
    {
        return $this->getPaginatedUsersByRoleWithProfiles(2, $page, $perPage); // 2 = Lecturer
    }

    /**
     * A private, reusable helper method to do the heavy lifting.
     */
    private function getPaginatedUsersByRoleWithProfiles(int $roleId, int $page, int $perPage): array
    {
        // 1. Calculate offset and get total count for pagination data
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $total = $this->userRepo->countByRoleId($roleId);

        // 2. Get the paginated list of primary users (e.g., 15 students)
        $users = $this->userRepo->findByRoleIdPaginated($roleId, $perPage, $offset);
        if (empty($users)) {
            // Return an empty but correctly structured response
            return ['data' => [], 'pagination' => ['total' => $total, /* ... */ ]];
        }

        // 3. From this small page of users, get their IDs
        $userIds = array_map(fn($u) => $u->id, $users);

        // 4. Fetch all related profiles for JUST THIS PAGE of users.
        // This is the efficient part.
        $profilesByUserId = [];
        if ($roleId === 2) { // Lecturer
            // Assumes you created getMultipleDetailedProfiles in LecturerProfileService
            $profilesByUserId = $this->lecturerProfileService->getMultipleDetailedProfiles($userIds);
        } elseif ($roleId === 3) { // Student
            // Assumes you created getMultipleDetailedProfiles in StudentProfileService
            $profilesByUserId = $this->studentProfileService->getMultipleDetailedProfiles($userIds);
        }

        // 5. Stitch the data together for the final DTOs
        $userDtos = [];
        foreach ($users as $user) {
            $profileDto = $profilesByUserId[$user->id] ?? null;
            $userDtos[] = new UserWithProfileDto($user, $profileDto);
        }

        // 6. Return the final, structured paginated response
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
}
