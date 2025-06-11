<?php
namespace Cfms\Controllers;

use Cfms\Services\AuthService;


class AuthController extends BaseController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
       
    }
    public function register()
    {
        // Get JSON input
        $input = json_decode(file_get_contents("php://input"));

        $service = new AuthService();
        $response = $service->register($input);

        JsonResponse::send($response, 201); // Respond as JSON
    }

    public function loginPage()
{
    echo json_encode(['message' => 'Login page placeholder']);
}

    public function login()
    {
        $this->requirePost();
        $input = $this->getJsonInput();

        $result = $this->authService->authenticate($input);
        if (!$result['success']) {
            return $this->jsonResponse(['success' => false, 'message' => $result['message']], 401);
        }

        return $this->jsonResponse([
            'success' => true,
            'user' => $result['user']
        ]);
    }
     public function logout()
    {
        session_start();
        session_destroy();
        echo json_encode(['success' => true]);
    }

}


   
