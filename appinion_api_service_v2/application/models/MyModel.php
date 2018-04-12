<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyModel extends CI_Model
{

    var $client_service = "appinion-client";
    var $auth_key = "523f260e015519f3a6da69f9ae1a94de";

    public function __construct()
    {
        parent::__construct();
        $this->db2 = $this->load->database('api_service', TRUE);
    }

    public function check_auth_client()
    {
        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key = $this->input->get_request_header('Auth-Key', TRUE);

        if ($client_service == $this->client_service && $auth_key == $this->auth_key) {
            return true;
        } else {
            return json_output(401, array('status' => 401, 'message' => 'Unauthorized.', 'api_user_id' => '', 'app_user_id' => '', 'token' => ''));
        }
    }

    public function api_auth($username, $password)
    {
        $q = $this->db2->select('password,authorization,status')->from('api_user')->where('username', $username)->get()->row();

        if ($q == "") {
            return array('status' => 403, 'message' => 'Username not found.');
        } else {
            $hashed_password = $q->password;
            $authorization=$q->authorization;
            $status=$q->status;
            if ($hashed_password == md5($password)) {
                if($status=='1')
                {
                    return array('status' => 200, 'message' => 'Successfully Api Connection.','authorization'=>$authorization);
                }
                else
                {
                    return array('status' => 403, 'message' => 'Restricted permission');
                }
            } else {
                echo "Wrong password";
                return array('status' => 403, 'message' => 'Wrong password.');
            }
        }

    }

    public function app_login($app_user_id, $app_password)
    {
        $q = $this->db->select('user_id,password')->from('tbl_app_user_login')->where('user_id', $app_user_id)->get()->row();

        if ($q == "") {
            return array('status' => 403, 'message' => 'UserId not found.');
        } else {
            $hashed_password = $q->password;
            $id = $q->id;
            if ($hashed_password == md5($app_password)) {
                $last_login = date('Y-m-d H:i:s');
                $token = md5(uniqid());
                $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->db2->trans_start();
                $this->db2->where('id', $id)->update('api_user', array('last_login' => $last_login));
                $qq = $this->db2->select('id,app_user_id')->from('api_users_authentication')->where('app_user_id', $app_user_id)->get()->row();
                if($qq== "")
                {
                    $this->db2->insert('api_users_authentication', array('api_user_id' => $id, 'token' => $token, 'app_user_id' => $app_user_id));
                }
                else
                {
                    $this->db2->where('id', $qq->id)->update('api_users_authentication', array('updated_at' => $last_login,'token' => $token));
                }
                if ($this->db2->trans_status() === FALSE) {
                    $this->db2->trans_rollback();
                    return array('status' => 500, 'message' => 'Internal server error.', 'api_user_id' => '', 'app_user_id' => '', 'token' => '');
                } else {
                    $this->db2->trans_commit();
                    return array('status' => 200, 'message' => 'Successfully login.', 'api_user_id' => $id, 'app_user_id' => $app_user_id, 'token' => $token);
                }
            } else {
                echo "Wrong password";
                return array('status' => 403, 'message' => 'Wrong password.');
            }
        }

    }

    public function user($user_id)
    {
        $result=$this->db->select('tbl_app_user_type_app_user_type_id,status')->from('tbl_app_user_login')->where('user_id', $user_id)->get()->row();
        if($result!="")
        {
            $user_type = $result->tbl_app_user_type_app_user_type_id;
            $status = $result->status;
            if ($user_type == '1' && $status == 1) {
                $this->db->select('caregiver_user_id as id,caregiver_name as name,NID_number as nid,DOB as dob,gender,blood_group,phone_number,email,address,picture,joining_date,educational_background as education,tbl_caregiver_engagment_type_caregiver_engagment_type_id as engagement_type,tbl_level_care_type_level_care_type_id as level_id,tbl_app_user_type_app_user_type_id as user_type_id');
                $this->db->from('tbl_caregiver_user');
                $this->db->where('caregiver_user_id', $user_id);
                return $this->db->get()->row();
            } else if($user_type == '2' && $status == 1) {
                $this->db->select('patient_id as id,patient_name as name,NID_number as nid,DOB as dob,gender,blood_group,phone_number,email,address,picture,joining_date,tbl_level_care_type_level_care_type_id as level_id,tbl_app_user_type_app_user_type_id as user_type_id');
                $this->db->from('tbl_patient_user');
                $this->db->where('patient_id', $user_id);
                return $this->db->get()->row();
            }
        }
        else
        {
            return array('status' => 404, 'message' => 'Data not found');
        }
    }



    public function token_check($username, $password, $app_user_id)
    {
        $q = $this->db2->select('password,id')->from('api_user')->where('username', $username)->get()->row();

        if ($q == "") {
            return array('status' => 403, 'message' => 'Username not found.');
        } else {
            $hashed_password = $q->password;
            $id = $q->id;

            if ($hashed_password == md5($password)) {
                $user_check = $this->db->select('user_id')->from('tbl_app_user_login')->where('user_id', $app_user_id)->get()->row();
                if($user_check=="")
                {
                    return array('status' => 403, 'message' => 'App User ID not found.');
                }
                else
                {
                    $qq = $this->db2->select('id,app_user_id,token')->from('api_users_authentication')->where('app_user_id', $app_user_id)->get()->row();
                    if($qq)
                    {
                        if ($this->db2->trans_status() === FALSE) {
                            $this->db2->trans_rollback();
                            return array('status' => 500, 'message' => 'Internal server error.', 'app_user_id' => '', 'token' => '');
                        } else {
                            $this->db2->trans_commit();
                            return array('status' => 200, 'message' => 'Token Found', 'app_user_id' => $app_user_id, 'token' => $qq->token);
                        }

                    }
                    else
                    {

                    }
                }
            } else {
                echo "Wrong password";
                return array('status' => 403, 'message' => 'Wrong password.', 'api_user_id' => '', 'app_user_id' => '', 'token' => '');
            }
        }

    }

    public function logout()
    {
        $users_id = $this->input->get_request_header('User-ID', TRUE);
        $token = $this->input->get_request_header('Authorization', TRUE);
        $this->db2->where('api_user_id', $users_id)->where('token', $token)->delete('api_users_authentication');
        return array('status' => 200, 'message' => 'Successfully logout.');
    }

    public function auth($users_id)
    {
        $token = $this->input->get_request_header('Authorization', TRUE);
        $q = $this->db2->select('expired_at')->from('api_users_authentication')->where('app_user_id ', $users_id)->where('token', $token)->get()->row();
        if ($q == "") {
            return json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
        } else {
            $updated_at = date('Y-m-d H:i:s');
            $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
            $this->db2->where('api_user_id', $users_id)->where('token', $token)->update('api_users_authentication', array('expired_at' => $expired_at, 'updated_at' => $updated_at));
            return array('status' => 200, 'message' => 'Authorized.');
        }
    }




    public function get_user_id($token)
    {
        $this->db2->select('app_user_id');
        $this->db2->from('api_users_authentication');
        $this->db2->where('token', $token);
        return $this->db2->get()->row();
    }
    public function get_api_id($authorization)
    {
        $this->db2->select('id');
        $this->db2->from('api_user');
        $this->db2->where('authorization', $authorization);
        return $this->db2->get()->row();
    }
    
}
