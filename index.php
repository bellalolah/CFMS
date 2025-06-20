<?php


use Cfms\Middlewares\JsonErrorRenderer;
use Slim\Factory\AppFactory;


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

// Create Container using PHP-DI
$container = new \DI\Container();
(require __DIR__ . '/di-container.php')($container);
AppFactory::setContainer($container);

// --App & MIDDLEWARE SETUP ---
$app = AppFactory::create();


// CORS Middlewares

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

// The routing Middlewares should be added before any Routes are defined
// it determines which route is being called.
$app->addRoutingMiddleware();

// The body parsing middleware.
// This should be added before the error middleware so that the error handler
// can get access to the parsed body if it needs to.
$app->addBodyParsingMiddleware();

// The body parsing middleware.
// This should be added before the error middleware so that the error
// handler can get access to the parsed body if it needs to.

// -- REGISTER ROUTES FROM SEPARATE FILES ---
$authRoutes = require __DIR__ . '/CFMS/src/Routes/auth.php';
$authRoutes($app);

$healthRoutes = require __DIR__ . '/CFMS/src/Routes/health.php';
$healthRoutes($app);

$departmentRoutes = require __DIR__ . '/CFMS/src/Routes/departments.php';
$departmentRoutes($app);

$facultyRoutes = require __DIR__ . '/CFMS/src/Routes/faculties.php';
$facultyRoutes($app);

$lecturerProfileRoutes = require __DIR__ . '/CFMS/src/Routes/lecturer_profiles.php';
$lecturerProfileRoutes($app);

$courseRoutes = require __DIR__ . '/CFMS/src/Routes/course.php';
$courseRoutes($app);

$lecturerCoursesRoutes = require __DIR__ . '/CFMS/src/Routes/lecturer_courses.php';
$lecturerCoursesRoutes($app);

$courseOfferingRoutes = require __DIR__ . '/CFMS/src/Routes/course_offerings.php';
$courseOfferingRoutes($app);

$userRoutes = require __DIR__ . '/CFMS/src/Routes/users.php';
$userRoutes($app);

/*$semesterRoutes = require __DIR__ . '/CFMS/src/Routes/semester.php';
$semesterRoutes($app);*/

$sessionRoutes = require __DIR__ . '/CFMS/src/Routes/session.php';
$sessionRoutes($app);

$criterionRoutes = require __DIR__ . '/CFMS/src/Routes/criterias.php';
$criterionRoutes($app);

$questionnaireRoutes = require __DIR__ . '/CFMS/src/Routes/questionnaires.php';
$questionnaireRoutes($app);

$lecturerRoutes = require __DIR__ . '/CFMS/src/Routes/lecturers.php';
$lecturerRoutes($app);

$feedbackSubmissionRoutes = require __DIR__ . '/CFMS/src/Routes/feedbacks.php';
$feedbackSubmissionRoutes($app);

$studentsRoutes = require __DIR__ . '/CFMS/src/Routes/students.php';
$studentsRoutes($app);

$kpiRoutes = require __DIR__ . '/CFMS/src/Routes/Kpi/admin_kpi.php';
$kpiRoutes($app);

$lecturerKpiRoutes = require __DIR__ . '/CFMS/src/Routes/Kpi/lecturer_kpi.php';
$lecturerKpiRoutes($app);

/*$feedbackRoutes = require __DIR__ . '/CFMS/src/Routes/feedbacks.php';
$feedbackRoutes($app);*/


// -- ERROR HANDLING MIDDLEWARES --


// The error middleware should be added last.
// It will catch exceptions from all middleware and Routes defined before it.

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);

// -- RUN THE APP --
$app->run();
