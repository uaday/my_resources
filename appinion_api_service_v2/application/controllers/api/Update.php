<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Update extends CI_Controller
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

    public function update_data()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'PUT') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_type = $params['user_type'];
                    $user_id = $params['user_id'];
                    if($this->MyModel->check_user($user_id,$user_type) == 1)
                    {
                        $data['email'] = $params['email'];
                        $data['address'] = $params['address'];
                        $data['phone_number'] = $params['phone_number'];
                        $resp = $this->MyModel->update_patient_data($user_id,$data);
                       // print_r($resp);die();
                       // json_output($response['status'], $resp);
                        $response = "Successful";
                    }
                    else
                    {
                        $response = "Failed";
                    }
                    echo $response;
                }
                else
                {
                    $response = "Failed";
                    echo $response;
                }
                
            }
        }
    }


}
