<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
	    $url='http://localhost/qc_api/Auth/login';
	    $auth_key='c74ead1dd8dc8ee8a99488453d38e371';
	    $client_service='appinion-client';
	    $content_type='application/json';
	    $r_type='object';
		$http_method='POST';
		$user_name='brac_qc';
		$password='brac_qc_api';
	    $result=$this->api_receiver->api_init($url,$auth_key,$client_service,$content_type,$r_type,$http_method,$user_name,$password);
	    if($result=='200')
        {
            echo $_SESSION['api_session']['authorization'];

        }
		//$this->load->view('welcome_message');
	}
}
