<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schedule extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Home_care_lib');
        /*
        $check_auth_client = $this->MyModel->check_auth_client();
		if($check_auth_client != true){
			die($this->output->get_output());
		}
		*/
    }

    public function schedule()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'GET') {
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
                    $month = $params['month'];
                    $year = $params['year'];
                    $resp = $this->MyModel->find_schedule($user_type, $token_user_id, $month, $year);
                    // print_r($resp);die();
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function clock_in()
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
                    $schedule_id = $params['schedule_id'];
                    // print_r($user_id);die();
                    $check_schedule = $this->MyModel->check_schedule($schedule_id, $token_user_id);
                    // print_r($check_schedule);die();
//                    print_r($check_schedule);die();
                    $check_date = date('Y-m-d', $params['time'] / 1000);
                    $current_date = date('Y-m-d');
                    $start_date = date('Y-m-d', $check_schedule->start_time / 1000);


                    if (isset($check_schedule)) {
                        if (($check_date == $current_date) && ($check_date == $start_date) &&
                            ($params['time'] >= ($check_schedule->start_time - 1800000)) &&
                            (($params['time'] <= $check_schedule->end_time))
                        ) {
                            // echo 'matched';die();
                            if ($check_schedule->status == 0) {
                                // print_r($check_schedule);die();
                                $data['status'] = 1;
                                $data['clock_in_time'] = $params['time'];
                                $this->MyModel->clock_in($schedule_id, $data);
                                $resp = "Successful";
                            } else if ($check_schedule->status == 1) {
                                $resp = "Failed. Already Clocked In";
                            } else if ($check_schedule->status == 2) {
                                $resp = "Failed. Already Clocked Out";
                            } else {
                                // echo 'failed';die();
                                $resp = "Request Failed. Status: $check_schedule->status";
                            }
                        } else {
                            $resp = "Request Failed. Invalid Time";
                        }
                    } else {
                        $resp = "Request Failed. Schedule Not Found";
                    }
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function clock_out()
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
                    $schedule_id = $params['schedule_id'];
                    $reason = $params['reason'];
                    // print_r($reason);die();
                    $check_schedule = $this->MyModel->check_schedule($schedule_id, $token_user_id);
                    $check_date = date('Y-m-d', $params['time'] / 1000);
                    $current_date = date('Y-m-d');
                    $end_date = date('Y-m-d', ($check_schedule->end_time + 86400000) / 1000); //1 day = 86400000 milliseconds
                    if (isset($check_schedule)) {
                        if (($check_date == $current_date) && $check_date < $end_date) {
                            if ($check_schedule->status == 1) {
                                $data['status'] = 2;
                                $data['clock_out_time'] = $params['time'];
                                $data['carehours'] = $data['clock_out_time'] - $check_schedule->clock_in_time;
                                $data['feedback_to_be_given'] = 1;
                                $this->MyModel->clock_out($schedule_id, $data);
                                if ($reason != null) {
                                    // echo 'not empty';die();
                                    $data1['tbl_schedule_maker_schedule_maker_id'] = $schedule_id;
                                    $data1['caregiver_schedule_feedback'] = $this->db->escape_str($params['reason']);
                                    $this->MyModel->show_cause('tbl_caregiver_schedule_feedback', $data1);
                                }
                                $resp = "Successful";
                            } else if ($check_schedule->status == 2) {
                                $resp = "Request Failed. Already Clocked Out";
                            } else if ($check_schedule->status == 0) {
                                $resp = "Request Failed. Not Clocked In Yet";
                            } else {
                                $resp = "Request Failed. Status: $check_schedule->status";
                            }
                        } else {
                            $resp = "Request Failed. Status: $check_schedule->status";
                        }
                    } else {
                        $resp = "Request Failed. Schedule Not Found";
                    }
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function feedback()
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
                    $schedule_id = $params['schedule_id'];
                    $rating = $params['rating'];
                    $feedback = $params['feedback'];
                    $check_schedule = $this->MyModel->check_schedule_feedback($schedule_id);
                    // print_r($check_schedule);die();
                    if (isset($check_schedule)) {
                        if ($check_schedule->rating == 0) {
                            $data['rating'] = $rating;
                            $data['feedback'] = $this->db->escape_str($feedback);
                            $data['is_feedback_given'] = 1;
                            $data['feedback_to_be_given'] = 0;
                            $this->MyModel->feedback($schedule_id, $data);
                            $resp = "Successful";
                        } else {
                            $resp = "Failed";
                        }
                    } else {
                        $resp = "Failed";
                    }
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function showCause()
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
                    $schedule_id = $params['schedule_id'];
                    // print_r($user_id);die();
                    $check_schedule = $this->MyModel->check_schedule_feedback($schedule_id);
                    // print_r($check_schedule);die();
                    if (isset($check_schedule)) {
                        // echo 'schedule exists';die();
                        $check_existing = $this->MyModel->check_existing_show_cause($schedule_id);
                        if (isset($check_existing)) {
                            $resp = "Failed";
                        } else {
                            $data['tbl_schedule_maker_schedule_maker_id'] = $schedule_id;
                            $data['caregiver_schedule_feedback'] = $params['reason'];
                            $this->MyModel->show_cause('tbl_caregiver_schedule_feedback', $data);
                            $resp = "Successful";
                        }
                    } else {
                        $resp = "Failed";
                    }
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function care_hours()
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
                $resp = array();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_type = $params['user_type'];
                    $month = $params['month'];
                    $year = $params['year'];
                    // $start_date = "$year-$prev_month-26";
                    //  $end_date = "$year-$month-25";
                    $start_date = $this->home_care_lib->get_start_date($params['month'], $params['year']);
                    $end_date = $this->home_care_lib->get_end_date($params['month'], $params['year']);
                    //print_r($start_date.' '.$end_date);die();
                    $duty_hours = 0;
                    $other_hours = 0;
                    $actual_consulting_hours = 0;
                    $real_consulting_hours = 0;
                    $actual_care_hours = 0;
                    $real_care_hours = 0;
                    if ($user_type == 1) {
                        $get_carehours = $this->MyModel->find_caregiver_carehours($token_user_id, $start_date, $end_date);
                        //print_r($get_carehours);die();
                        if($get_carehours)
                        {
                            foreach ($get_carehours as $row) {
                                if ($row->other_hours == null) {
                                    $row->other_hours = "0";
                                }
                            }
                            // print_r($resp);die();
                            if (sizeof($get_carehours > 0)) {
                                foreach ($get_carehours as $row) {
                                    $duty_hours += $row->duty_hours;
                                    if ($row->other_hours != null) {
                                        $other_hours += $row->other_hours;
                                    }
                                }
                            }
                            if ($other_hours > $duty_hours) {
                                $other_hours = $other_hours - $duty_hours;
                            } else {
                                $other_hours = 0;
                            }
                            $resp['duty_hours'] = "$duty_hours";
                            $resp['other_hours'] = "$other_hours";
                        }
                        else{
                            $resp['duty_hours'] = "0";
                            $resp['other_hours'] = "0";
                        }

                    } else if ($user_type == 2) {
                        $care_hours = $this->MyModel->find_patient_carehours($token_user_id, $start_date, $end_date);
                        $consulting_hours = $this->MyModel->find_patient_consulting_hours($token_user_id, $start_date, $end_date);
                        // print_r($care_hours);die();
                        if ($care_hours) {
                            foreach ($care_hours as $row) {
                                $actual_care_hours += $row['actual_hours'];
                                if ($row['real_hours'] != null) {
                                    $real_care_hours += $row['real_hours'];
                                }
                            }
                        } else {
                            $actual_care_hours = "0";
                            $real_care_hours = "0";
                        }
                        if ($consulting_hours) {
                            foreach ($consulting_hours as $row) {
                                $actual_consulting_hours += $row['actual_consulting_hours'];
                                if ($row['real_consulting_hours'] != null) {
                                    $real_consulting_hours += $row['real_consulting_hours'];
                                }
                            }
                        } else {
                            $actual_consulting_hours = "0";
                            $real_consulting_hours = "0";
                        }

                        $resp['duty_hours'] = "$actual_care_hours";
                        $resp['other_hours'] = "$real_consulting_hours";
                        //print_r($real_care_hours);die();
                    }
                    // print_r($resp);die();
                    json_output($response['status'], $resp);
                }
            }
        }
    }



    public function raw_care_hours()
    {
        //echo 'yes';die();
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
                    $start_date = $this->home_care_lib->get_start_date($params['month'], $params['year']);
                    $end_date = $this->home_care_lib->get_end_date($params['month'], $params['year']);
                    // print_r($start_date.' '.$end_date);die();
                    $care_hours = array();
                    $resp = array();
                    if ($user_type == 1) {
                        $resp = $this->MyModel->find_caregiver_raw_data($token_user_id, $start_date, $end_date);
                        // print_r($resp);die();
                        foreach ($resp as $key => $row) {
                            if ($resp[$key]['other_hours'] < 0) {
                                $resp[$key]['other_hours'] = "0";
                            }
                        }
                    } else if ($user_type == 2) {
                        // $test = $this->MyModel->find_patient_duty_hours($user_id, $start_date, $end_date);
                        $resp = array();
                        $care_hours = $this->MyModel->find_patient_raw_carehours($token_user_id, $start_date, $end_date);
                        // print_r($care_hours);die();
                        $consulting_hours = $this->MyModel->find_patient_consulting_hours($token_user_id, $start_date, $end_date);
                        // print_r($care_hours);die();
                        $index = 0;
                        $temp = array();
                        if ($care_hours) {
                            for ($row = 0; $row < sizeof($care_hours); $row++) {
                                $temp[$index]['schedule_date'] = $care_hours[$row]['schedule_date'];
                                $temp[$index]['duty_hours'] = $care_hours[$row]['duty_hours'];
                                $temp[$index]['other_hours'] = "0";
                                $index++;
                            }
                        }
                       // print_r($temp);die();
                        if ($consulting_hours) {
                            for ($column = 0; $column < sizeof($consulting_hours); $column++) {
                                $temp[$index]['schedule_date'] = $consulting_hours[$column]['schedule_date'];
                                $temp[$index]['duty_hours'] = "0";
                                $temp[$index]['other_hours'] = $consulting_hours[$column]['duty_hours'];
                                $index++;
                            }
                        }
                        //print_r($temp);die();
                        if (sizeof($temp) > 1) {
                           // print_r(sizeof($temp));die();
                            for ($new_row = 0; $new_row < sizeof($temp)-1; $new_row++) {
                                if ($temp[$new_row]['schedule_date'] == $temp[$new_row + 1]['schedule_date']) {
                                    $resp[$new_row]['schedule_date'] = $temp[$new_row]['schedule_date'];
                                    if ($temp[$new_row]['duty_hours'] > 0) {
                                        $resp[$new_row]['duty_hours'] = $temp[$new_row]['duty_hours'];
                                    } else if ($temp[$new_row + 1]['duty_hours'] > 0) {
                                        $resp[$new_row]['duty_hours'] = $temp[$new_row + 1]['duty_hours'];
                                    } else {
                                        $resp[$new_row]['duty_hours'] = "0";
                                    }
                                    if ($temp[$new_row]['other_hours'] > 0) {
                                        $resp[$new_row]['other_hours'] = $temp[$new_row]['other_hours'];
                                    } else if ($temp[$new_row + 1]['other_hours'] > 0) {
                                        $resp[$new_row]['other_hours'] = $temp[$new_row + 1]['other_hours'];
                                    } else {
                                        $resp[$new_row]['other_hours'] = "0";
                                    }
                                } else {
                                    $resp[$new_row]['schedule_date'] = $temp[$new_row]['schedule_date'];
                                    $resp[$new_row]['duty_hours'] = $temp[$new_row]['duty_hours'];
                                    $resp[$new_row]['other_hours'] = $temp[$new_row]['other_hours'];
                                }

                            }
                        }
                        else if (sizeof($temp) == 1) {
                            $resp = $temp;
                        }
                        // print_r(sizeof($temp));die();
                        // print_r($resp);die();
                        //  print_r($resp);die();
                    }
                    json_output($response['status'], $resp);
                }
            }
        }
    }
}
