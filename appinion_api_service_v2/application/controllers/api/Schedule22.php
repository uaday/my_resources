<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schedule22 extends CI_Controller
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

    public function millisecond_to_time($millisecond_time)
    {
        date_default_timezone_set('Asia/Dhaka');

        $datetime = $millisecond_time / 1000;
        $datetime = date('h:i A', $datetime);

        return $datetime;
    }

    public function millisecond_to_date($millisecond_time)
    {
        date_default_timezone_set('Asia/Dhaka');

        $datetime = $millisecond_time / 1000;
        $datetime = date('Y-m-d', $datetime);

        return $datetime;
    }

    public function millisecond_to_full_time($millisecond_time)
    {
        date_default_timezone_set('Asia/Dhaka');

        $datetime = $millisecond_time / 1000;
        $hours = floor($datetime / 3600);
        $minutes = floor(($datetime / 60) - ($hours * 60));
        $seconds = round($datetime - ($hours * 3600) - ($minutes * 60));
        $care_hours = $hours . ':' . $minutes . ':' . $seconds;

        return $care_hours;
    }

    public function schedule()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_type = $params['user_type'];
                    $user_id = $params['user_id'];
                    $month = $params['month'];
                    $year = $params['year'];
                    $resp = $this->MyModel->find_schedule($user_type, $user_id, $month, $year);
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
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_id = $params['user_id'];

                    $schedule_id = $params['schedule_id'];
                    // print_r($user_id);die();
                    $check_schedule = $this->MyModel->check_schedule($schedule_id, $user_id);
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
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_id = $params['user_id'];
                    $schedule_id = $params['schedule_id'];
                    $reason = $params['reason'];
                    // print_r($reason);die();
                    $check_schedule = $this->MyModel->check_schedule($schedule_id, $user_id);
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
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    // $patient_id = $params['patient_id'];
                    //   $caregiver_id = $params['caregiver_id'];
                    $schedule_id = $params['schedule_id'];
                    $rating = $params['rating'];
                    $feedback = $params['feedback'];
                    // print_r($user_id);die();
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
                $response = $this->MyModel->auth();
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
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_type = $params['user_type'];
                    $user_id = $params['user_id'];
                    $month = $params['month'];
                    $year = $params['year'];
                    $prev_month = $month - 1;
                    $start_date = "$year-$prev_month-26";
                    $end_date = "$year-$month-25";
                    $actual_care_hours = 0;
                    $real_care_hours = 0;
                    $actual_consulting_hours = 0;
                    $real_consulting_hours = 0;
                    if ($user_type == 1) {
                        $get_carehours = $this->MyModel->find_caregiver_carehours($user_id, $start_date, $end_date);
                        foreach ($get_carehours as $row) {
                            if ($row->real_hours == null) {
                                $row->real_hours = "0";
                            }
                        }
                        // print_r($resp);die();
                        if (sizeof($get_carehours > 0)) {
                            foreach ($get_carehours as $row) {
                                $actual_care_hours += $row->actual_hours;
                                if ($row->real_hours != null) {
                                    $real_care_hours += $row->real_hours;
                                }
                            }
                        }
                        if ($real_care_hours > $actual_care_hours) {
                            $real_care_hours = $real_care_hours - $actual_care_hours;
                        } else {
                            $real_care_hours = 0;
                        }
                        $resp['care_hours'] = "$actual_care_hours";
                        $resp['real_hours'] = "$real_care_hours";

                    } else if ($user_type == 2) {
                        $care_hours = $this->MyModel->find_patient_carehours($user_id, $start_date, $end_date);
                        $consulting_hours = $this->MyModel->find_patient_consulting_hours($user_id, $start_date, $end_date);
                        // print_r($resp);die();
                        if (sizeof($care_hours > 0)) {
                            foreach ($care_hours as $row) {
                                $actual_care_hours += $row->actual_hours;
                                if ($row->real_hours != null) {
                                    $real_care_hours += $row->real_hours;
                                }
                            }
                        }
                        if (sizeof($consulting_hours) > 0) {
                            foreach ($consulting_hours as $row) {
                                $actual_consulting_hours += $row->actual_consulting_hours;
                                if ($row->real_consulting_hours != null) {
                                    $real_consulting_hours += $row->real_consulting_hours;
                                }
                            }
                        }

                        $resp['care_hours'] = "$actual_care_hours";
                        $resp['real_hours'] = "$real_consulting_hours";
                        //print_r($real_care_hours);die();
                    }
                    // print_r($resp);die();
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function raw_care_hours_old()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_type = $params['user_type'];
                    $user_id = $params['user_id'];
                    $month = $params['month'];
                    $year = $params['year'];
                    $prev_month = $month - 1;
                    $start_date = "$year-$prev_month-26";
                    $end_date = "$year-$month-25";
                    $actual_care_hours = 0;
                    $real_care_hours = 0;
                    $actual_consulting_hours = 0;
                    $real_consulting_hours = 0;
                    if ($user_type == 1) {
                        $resp = $this->MyModel->find_caregiver_raw_data($user_id, $start_date, $end_date);
//                        foreach ($get_raw_data as $new_row)
//                        {
//                            if($new_row->extra_hours == null)
//                            {
//                                $new_row->extra_hours = "0";
//                            }
//                        }
//                        $resp['raw_data'] = $get_raw_data;
                        // print_r($resp);die();
                        foreach ($resp as $extra_row) {
                            if ($extra_row->extra_hours > $extra_row->actual_hours) {
                                $extra_row->extra_hours -= $extra_row->actual_hours;
                            } else {
                                $extra_row->extra_hours = 0;
                            }
                        }
                    } else if ($user_type == 2) {
                        $resp = $this->MyModel->find_patient_raw_carehours($user_id, $start_date, $end_date);
                        $consulting_hours = $this->MyModel->find_patient_consulting_hours($user_id, $start_date, $end_date);
//                        if(sizeof($raw_care_hours > 0))
//                        {
//                            foreach ($raw_care_hours as $row_care_hours)
//                            {
//                                $resp['schedule_date'] = $row_care_hours->schedule_date;
//                                //$resp['']
//                                if($row_care_hours->extra_hours == null)
//                                {
//                                    $row_care_hours->extra_hours = "0";
//                                }
//                            }
//                        }
                        //$resp['raw_data'] = $raw_care_hours;
                        // print_r($resp);die();
                        //print_r($real_care_hours);die();
                    }
                    // print_r($resp);die();
                    json_output($response['status'], $resp);
                    //print_r($resp);
                }
            }
        }
    }

    public function raw_care_hours()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->MyModel->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->MyModel->auth();
                if ($response['status'] == 200) {
                    $params = $_REQUEST;
                    $user_type = $params['user_type'];
                    $user_id = $params['user_id'];
                    $month = $params['month'];
                    $year = $params['year'];
                    $prev_month = $month - 1;
                    $start_date = "$year-$prev_month-26";
                    $end_date = "$year-$month-25";
                    if ($user_type == 1) {
                        $resp = $this->MyModel->find_caregiver_raw_data($user_id, $start_date, $end_date);
                        //print_r($resp);die();
                        $res_updated = array();
                        $index = 0;
                        for ($row = 0; $row < sizeof($resp); $row++) {
                            $matched = false;
                            for ($column = $row + 1; $column < sizeof($resp); $column++) {

                                if ($resp[$row]['schedule_date'] == $resp[$column]['schedule_date']) {
                                    $matched = true;
                                    $res_updated[$index]['schedule_date'] = $resp[$row]['schedule_date'];
                                    $res_updated[$index]['duty_hours'] += $resp[$row]['duty_hours'] + $resp[$column]['duty_hours'];
                                 //   $res_updated[$index]['overtime'] += $resp[$row]['overtime'] + $resp[$column]['overtime'];

                                    //print_r($resp[$column]['overtime']);die();
                                    if (($resp[$row]['overtime'] > 0) && ($resp[$column]['overtime'] > 0))
                                        $res_updated[$index]['overtime'] += $resp[$row]['overtime'] + $resp[$column]['overtime'];
                                    else
                                        $res_updated[$index]['overtime'] = "0";


                                    $index++;
                                    //unset($resp[$column]);
                                    break;
                                }
                                else {
                                    $matched = false;
                                }
                            }
                            if (!$matched)
                                $res_updated[$row] = $resp[$row];

                        }
                        $resp = $res_updated;
                        unset($res_updated);

                        // print_r($resp);die();
//                        foreach ($resp as $key=>$row)
//                        {
//                            if($resp[$key]['schedule_date'] == $row['schedule_date'])
//                            {
//                               // echo 'yes';die();
//                                $resp[$key]['duty_hours'] += $row['duty_hours'];
//                                $resp[$key]['overtime'] += $row['overtime'];
//                                if($resp[$key]['overtime'] > 0)
//                                {
//                                    $resp[$key]['overtime'] += $row['overtime'];
//                                }
//                                else{
//                                    $resp[$key]['overtime'] = "0";
//                                }
//                            }
//                            else{
//                               // echo 'yes';
//                                if($resp[$key]['overtime'] > 0)
//                                {
//                                    $resp[$key]['overtime'] = $row['overtime'];
//                                }
//                                else
//                                {
//                                    $resp[$key]['overtime'] = "0";
//                                }
//                            }
//                        }
                    } else if ($user_type == 2) {
                        $resp = $this->MyModel->find_patient_raw_carehours($user_id, $start_date, $end_date);
                        foreach ($resp as $key => $row) {
                            if ($resp[$key]['overtime'] > 0) {
                                $resp[$key]['overtime'] = $row['overtime'];
                            } else {
                                $resp[$key]['overtime'] = "0";
                            }
                        }
                        $consulting_hours = $this->MyModel->find_patient_consulting_hours($user_id, $start_date, $end_date);
                        //print_r($resp);
                    }
                    json_output($response['status'], $resp);
                }
            }
        }
    }
}
