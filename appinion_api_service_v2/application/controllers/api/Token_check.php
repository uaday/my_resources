<?php
/**
 * Created by PhpStorm.
 * User: Sudipta Ghosh
 * Date: 2/28/2018
 * Time: 4:25 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Token_check extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        /*
        $check_auth_client = $this->MyModel->check_auth_client();
        if($check_auth_client != true){
            die($this->output->get_output());
        }
        */
    }

    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

//        $params = $_REQUEST;
//        $username = $params['username'];
//        $password = $params['password'];
//        $app_user_id = $params['app_user_id'];
//        $response = $this->MyModel->login($username, $password, $app_user_id);
//        json_output($response['status'], $response);

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.', 'api_user_id' => '', 'app_user_id' => '', 'token' => ''));
        } else {

            $check_auth_client = $this->MyModel->check_auth_client();

            if ($check_auth_client == true) {
                $params = $_REQUEST;
                $username = $params['username'];
                $password = $params['password'];
                $app_user_id = $params['app_user_id'];
                $response = $this->MyModel->token_check($username, $password, $app_user_id);
                json_output($response['status'], $response);
            }
        }
    }


}
