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
	    $url='http://localhost/appinion_api_service_v2/Auth/login?username=adminQc&password=adminQc';
	    $auth_key='523f260e015519f3a6da69f9ae1a94de';
	    $client_service='appinion-client';
	    $content_type='application/x-www-form-urlencoded';
	    $r_type='object';
		$http_method='POST';
	    $result=$this->api_receiver->api_init($url,$auth_key,$client_service,$content_type,$r_type,$http_method);
	    if($result=='200')
        {
            echo $_SESSION['api_session']['token'];

        }
		//$this->load->view('welcome_message');
	}
}
