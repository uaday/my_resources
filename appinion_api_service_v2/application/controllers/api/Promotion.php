<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion extends CI_Controller {

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

    public function find_all_active_promotion()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method != 'GET'){
            json_output(400,array('status' => 400,'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if($check_auth_client == true){
                $result=$this->MyModel->get_user_id($this->input->get_request_header('Authorization', TRUE));
                $token_user_id=$result->app_user_id;
                $response = $this->MyModel->auth($token_user_id);
                if($response['status'] == 200){
                    $resp = $this->MyModel->find_all_active_promotion();
                    json_output($response['status'],$resp);
                }
            }
        }
    }
    public function request_items()
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
                    $params = $_REQUEST;
                    $item_id = $params['item_id'];
                    $check_item = $this->MyModel->get_item($item_id);
                   // print_r($check_item);die();
                    if(isset($check_item))
                    {
                        $data['is_accepted'] = 0;
                        $data['tbl_promotional_items_pomotional_item_id'] = $item_id;
                        $data['tbl_patient_user_patient_id'] = $token_user_id;
                        $data['tbl_admin_user_admin_user_id'] = "appinion";
                      //  echo 'isset';die();
                        $check_patient = $this->MyModel->check_patient($token_user_id);
                        if(isset($check_patient))
                        {
                            $check_request = $this->MyModel->get_purchase_item($item_id,$token_user_id);
                            if(isset($check_request))
                            {
                                $resp = "Failed. Already Requested";
                            }
                            else
                            {
                                $this->MyModel->insert_ret('tbl_promotional_item_request', $data);
                                $resp = "Successful";
                            }
                        }
                        else
                        {
                            $resp = "Failed";
                        }
                    }else{
                        $resp = "Failed";
                    }
                    json_output($response['status'],$resp);
                }
            }
        }
    }
}
