<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_login extends CI_Controller {

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
        if($method != 'POST'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if($check_auth_client == true){
                $result=$this->MyModel->get_user_id($this->input->get_request_header('Authorization', TRUE));
                $token_user_id=$result->app_user_id;
                $response = $this->MyModel->auth($token_user_id);
                if($response['status'] == 200){
                    if($_REQUEST)
                    {
                        $params = $_REQUEST;
                        if($params['app_password'])
                        {
                            $app_user_id=$token_user_id;
                            $app_password=$params['app_password'];
                            $resp= $this->MyModel->app_login($app_user_id,$app_password);
                            //print_r($resp);die();
                            if($resp)
                            {
                                if($resp->user_type_id == 1)
                                {
                                    $emergency_contact = $this->MyModel->get_cg_emergency_contact($app_user_id);
                                }
                                else if($resp->user_type_id == 2)
                                {
                                    $emergency_contact = $this->MyModel->get_pt_emergency_contact($app_user_id);
                                }
                            $resp->emergency_contact = $emergency_contact->phone_number;
                            json_output($response['status'],$resp);
                            }
                            else{
                                $resp = '[]';
                                json_output($response['status'],$resp);
                            }
                        }
                    }else{
                        $resp = 'Invalid Request!';
                        json_output($response['status'],$resp);
                    }
                }
            }
        }
    }


}
