<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'GET'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if($check_auth_client == true){
                $result=$this->MyModel->get_user_id($this->input->get_request_header('token', TRUE));
                if($result->app_user_id!="")
                {
                    $response=null;
                    $response= $this->MyModel->user($result->app_user_id);
                    $response['status']='200';
                    json_output($response['status'], $response);
                }
                else
                {

                }
            }
        }
    }


}
