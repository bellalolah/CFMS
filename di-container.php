<?php
// File: di-container.php

use Cfms\Controllers\AuthController;
use Cfms\Controllers\CourseController;
use Cfms\Controllers\CriterionController;
use Cfms\Controllers\DepartmentController;
use Cfms\Controllers\FacultyController;
use Cfms\Controllers\FeedbackController;
use Cfms\Controllers\FeedbackSubmissionController;
use Cfms\Controllers\LecturerController;
use Cfms\Controllers\LecturerCourseController;
use Cfms\Controllers\QuestionnaireController;
use Cfms\Controllers\SessionController;
use Cfms\Controllers\StudentProfileController;
use Cfms\Controllers\UserController;
use Cfms\KPI\Controllers\LecturerKPIController;
use Cfms\KPI\Repositories\LecturerKPIRepository;
use Cfms\Repositories\CourseDepartmentRepository;
use Cfms\Repositories\CriterionRepository;
use Cfms\Repositories\FeedbackRepository;
use Cfms\Repositories\FeedbackSubmissionRepository;
use Cfms\Repositories\LecturerCourseRepository;
use Cfms\Repositories\QuestionnaireRepository;
use Cfms\Repositories\QuestionRepository;
use Cfms\Repositories\RoleRepository;
use Cfms\Repositories\user_profile\StudentProfileRepository;
use Cfms\Services\CourseService;
use Cfms\Services\CriterionService;
use Cfms\Services\DepartmentService;
use Cfms\Services\FacultyService;
use Cfms\Services\AuthService;
use Cfms\Services\FeedbackService;
use Cfms\Services\FeedbackSubmissionService;
use Cfms\Services\QuestionnaireService;
use Cfms\Services\SemesterService;
use Cfms\Services\SessionService;
use Cfms\Services\UserService;
use Cfms\Services\StudentProfileService;
use Cfms\Services\LecturerProfileService;
use Cfms\Services\LecturerCourseService;
use Cfms\Services\CourseDepartmentService;
use Cfms\Services\CourseOfferingService;
use Cfms\Controllers\CourseOfferingController;
use Cfms\Repositories\CourseOfferingRepository;
use Cfms\Repositories\CourseRepository;
use Cfms\Repositories\UserRepository;
use Cfms\Repositories\user_profile\LecturerProfileRepository;
use Cfms\Repositories\DepartmentRepository;
use Cfms\Repositories\FacultyRepository;
use Cfms\Repositories\SemesterRepository;
use Cfms\Repositories\SessionRepository;
use Dell\Cfms\KPI\Controllers\AdminKPIController;
use Dell\Cfms\KPI\Repositories\AdminKPIRepository;
use Dell\Cfms\KPI\Services\AdminKPIService;
use Dell\Cfms\KPI\Services\LecturerKPIService;

return function($container) {
    $container->set(CourseOfferingRepository::class, fn() => new CourseOfferingRepository());
    $container->set(CourseRepository::class, fn() => new CourseRepository());
    $container->set(UserRepository::class, fn() => new UserRepository());
    $container->set(LecturerProfileRepository::class, fn() => new LecturerProfileRepository());
    $container->set(DepartmentRepository::class, fn() => new DepartmentRepository());
    $container->set(FacultyRepository::class, fn() => new FacultyRepository());
    $container->set(SemesterRepository::class, fn() => new SemesterRepository());
    $container->set(SessionRepository::class, fn() => new SessionRepository());
    $container->set(RoleRepository::class, fn() => new RoleRepository());
    $container->set(LecturerCourseRepository::class, fn() => new LecturerCourseRepository());
    $container->set(CourseDepartmentRepository::class, fn() => new CourseDepartmentRepository());
    $container->set(StudentProfileRepository::class, fn() => new StudentProfileRepository());
    $container->set(CourseOfferingService::class, function($c) {
        return new CourseOfferingService(
            $c->get(CourseOfferingRepository::class),
            $c->get(CourseRepository::class),
            $c->get(UserRepository::class),
            $c->get(LecturerProfileRepository::class),
            $c->get(DepartmentRepository::class),
            $c->get(FacultyRepository::class),
            $c->get(SemesterRepository::class),
            $c->get(SessionRepository::class)
        );
    });
    $container->set(CriterionRepository::class, fn() => new CriterionRepository());
    $container->set(QuestionnaireRepository::class, fn() => new QuestionnaireRepository());
    $container->set(QuestionRepository::class, fn() => new QuestionRepository());
    $container->set(FeedbackRepository::class, fn() => new FeedbackRepository());



    $container->set(QuestionnaireService::class,
        fn($c) => new QuestionnaireService($c->get(QuestionnaireRepository::class),
            $c->get(QuestionRepository::class), $c->get(CriterionRepository::class), $c->get(CourseOfferingRepository::class)
        , $c->get(FeedbackRepository::class),$c->get(CourseRepository::class),$c->get(StudentProfileService::class),$c->get(UserRepository::class),$c->get(SemesterRepository::class)));
    $container->set(FeedbackService::class,fn($c) => new FeedbackService($c->get(FeedbackRepository::class), $c->get(QuestionnaireRepository::class), $c->get(QuestionRepository::class), $c->get(CriterionRepository::class), $c->get(CourseOfferingRepository::class)));
    $container->set(CriterionService::class, fn($c) => new CriterionService($c->get(CriterionRepository::class)));
    $container->set(CourseOfferingController::class, fn($c) => new CourseOfferingController($c->get(CourseOfferingService::class)));
    $container->set(CourseService::class, fn($c) => new CourseService($c->get(CourseRepository::class), $c->get(StudentProfileRepository::class),$c->get(SessionRepository::class)));
    $container->set(DepartmentService::class, fn($c) => new DepartmentService($c->get(DepartmentRepository::class), $c->get(FacultyRepository::class),$c->get(CourseRepository::class)));
    $container->set(FacultyService::class, fn($c) => new FacultyService($c->get(FacultyRepository::class), $c->get(DepartmentRepository::class)));
    $container->set(AuthService::class, fn($c) => new AuthService($c->get(UserRepository::class), $c->get(RoleRepository::class), $c->get(UserService::class)));
    $container->set(UserService::class, fn($c) => new UserService($c->get(UserRepository::class), $c->get(StudentProfileRepository::class), $c->get(LecturerProfileRepository::class), $c->get(LecturerCourseRepository::class), $c->get(LecturerProfileService::class),$c->get(StudentProfileService::class),$c->get(CourseRepository::class)));
    $container->set(StudentProfileService::class, fn($c) => new StudentProfileService(
        $c->get(StudentProfileRepository::class),
        $c->get(DepartmentRepository::class),
        $c->get(FacultyRepository::class)
    ));
    $container->set(FeedbackSubmissionService::class, fn() => new FeedbackSubmissionService($container->get(FeedbackSubmissionRepository::class)));
    $container->set(LecturerProfileService::class, fn($c) => new LecturerProfileService($c->get(LecturerProfileRepository::class),$c->get(DepartmentRepository::class), $c->get(FacultyRepository::class)));
    $container->set(LecturerCourseService::class, fn($c) => new LecturerCourseService($c->get(LecturerCourseRepository::class), $c->get(CourseRepository::class)));
    $container->set(CourseDepartmentService::class, fn($c) => new CourseDepartmentService($c->get(CourseDepartmentRepository::class)));
    $container->set(SessionService::class, fn($c) => new SessionService(
        $c->get(SessionRepository::class),
        $c->get(SemesterRepository::class)
    ));
    $container->set(SemesterService::class, fn($c) => new SemesterService(
        $c->get(SemesterRepository::class)
    ));
    $container->set(SessionController::class, fn($c) => new SessionController(
        $c->get(SessionService::class)
    ));
    $container->set(UserController::class, fn($c) => new UserController(
        $c->get(UserService::class)
    ));
    $container->set(StudentProfileController::class, fn($c) => new StudentProfileController(
        $c->get(StudentProfileService::class)
    ));
    $container->set(CourseController::class, fn($c) => new CourseController(
        $c->get(CourseService::class)
    ));
    $container->set(DepartmentController::class, fn($c) => new DepartmentController(
        $c->get(DepartmentService::class)
    ));
    $container->set(FacultyController::class, fn($c) => new FacultyController(
        $c->get(FacultyService::class)
    ));
    $container->set(LecturerCourseController::class, fn($c) => new LecturerCourseController(
        $c->get(LecturerCourseService::class)
    ));
    $container->set(AuthController::class, fn($c) => new AuthController(
        $c->get(AuthService::class)
    ));
    $container->set(CriterionController::class, fn($c) => new CriterionController(
        $c->get(CriterionService::class)
    ));
    $container->set(QuestionnaireController::class, fn($c) => new QuestionnaireController(
        $c->get(QuestionnaireService::class)
    ));
    $container->set(FeedbackController::class, fn($c) => new FeedbackController(
        $c->get(FeedbackService::class)));
    $container->set(LecturerController::class, fn($c) => new LecturerController(
        $c->get(LecturerProfileService::class),
        $c->get(QuestionnaireService::class)
    ));
    $container->set(FeedbackSubmissionController::class, fn($c) => new FeedbackSubmissionController(
        $c->get(FeedbackSubmissionService::class)
    ));
    $container->set(StudentProfileService::class, fn($c) => new StudentProfileService(
        $c->get(StudentProfileRepository::class),
        $c->get(DepartmentRepository::class),
        $c->get(FacultyRepository::class)
    ));


    // ------------------------ KPIS

    $container->set(AdminKPIRepository::class, fn()=> new AdminKPIRepository());
    $container->set(AdminKPIService::class, fn($c) => new AdminKPIService($c->get(AdminKPIRepository::class)));
    $container->set(AdminKPIController::class, fn($c) => new AdminKPIController($c->get(AdminKPIService::class)));

    $container->set(LecturerKPIRepository::class,fn()=>new LecturerKPIRepository());
    $container->set(LecturerKPIService::class, fn($c) => new LecturerKPIService($c->get(LecturerKPIRepository::class),$c->get(FeedbackRepository::class)));
    $container->set(LecturerKPIController::class, fn($c) => new LecturerKPIController($c->get(LecturerKPIService::class)));
    // ------------------------ END KPIS

};
