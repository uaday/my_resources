<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_login extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

    public function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if($check_auth_client == true){
                $result=$this->MyModel->get_api_id($this->input->get_request_header('authorization', TRUE));
                if($result->id!="")
                {
                    if($response['status'] == 200){
                        if($_REQUEST)
                        {
                            $params = $_REQUEST;
                            if($params['app_password']& $params['app_user_id'])
                            {
                                $app_user_id=$params['app_user_id'];
                                $app_password=$params['app_password'];
                                $response= $this->MyModel->app_login($app_user_id,$app_password);
                                json_output($response['status'], $response);

                            }
                        }else{
                            $resp = 'Invalid Request!';
                            json_output($response['status'],$resp);
                        }
                    }
                }
                else
                {

                }
            }
        }
    }


}
