<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password extends CI_Controller
{

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
    
    public function change_password()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if ($check_auth_client == true) {
                $result=$this->MyModel->get_user_id($this->input->get_request_header('Authorization', TRUE));
                $token_user_id=$result->app_user_id;
                $response = $this->MyModel->auth($token_user_id);
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_type = $params['user_type'];
                  //print_r($this->MyModel->check_user($token_user_id, $user_type));die();
                    if($this->MyModel->check_user($token_user_id, $user_type) == 1)
                    {

                        $old_password = md5($params['old_password']);
                        $new_password = md5($params['new_password']);
                        $check_password = $this->MyModel->get_Password($token_user_id);
                        // print_r(($check_password));die();
                        $data['password'] = $new_password;
                        if(($check_password[0]->password) == $old_password)
                        {
                            $resp = $this->MyModel->change_password($token_user_id,$data);
                            $response = "Successful";
                        }
                        else
                        {
                            $response = "Failed";
                        }
                        echo $response;
                    }
                    else {
                        $response = "Failed";
                        echo $response;
                    }

                }
            }
        }
    }


}
