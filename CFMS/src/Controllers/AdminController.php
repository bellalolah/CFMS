<?php

namespace Cfms\Controllers;

use Cfms\Services\AdminService;


class AdminController extends BaseController{

    //Display lecturers and departments in front end 
    //Then pass the ids to create the course
    //It was automatically select current semester as the id for the semsester

    private $adminService;

    public function __construct(){
        $this->adminService = new AdminService();

    }
}