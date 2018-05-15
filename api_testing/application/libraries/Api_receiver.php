<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_receiver{

    function __construct()
    {
        //define( 'API_ACCESS_KEY', 'AAAAwNVn9ys:APA91bE5qfRLwntb0QSsh98EOKOq6izr1hykDP1N9l-q8W05DMvR_bbzokYYx2vQlVYsnQJvmywobmzgTLxoS5U6VA923cmTeY5NVfZRU1s_QYV5_sMcaLsDWoFFpI8M-xCazARu3Lbt' );
    }

    function curl_fun($url,$curl_data,$http_method,$user_name,$password)
	{
		$curl = curl_init();
		if($http_method=='POST')
		{
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => $http_method,
				CURLOPT_POSTFIELDS=>array(
					'username:'. $user_name,
					'password:' . $password
				),
				CURLOPT_HTTPHEADER => $curl_data,
			));
		}


		$response = curl_exec($curl);

		$err = curl_error($curl);
		curl_close($curl);

		if($err)
		{
			echo "cURL Error #:" . $err;
		}
		else
		{
			return $response;
		}
	}
    function api_init($url,$auth_key,$client_service,$content_type,$r_type,$http_method,$user_name,$password)
    {
    	$curl_data=array(
			"auth-key: ".$auth_key,
			"client-service: ".$client_service,
			"content-type: ".$content_type
		);
    	$response=$this->curl_fun($url,$curl_data,$http_method,$user_name,$password);

        if($response)
		{
			if($r_type=='object')
			{
				$res=json_decode($response);
				if($res->status=='200')
				{
					$sess_data=array('authorization'=>$res->authorization);
					$_SESSION['api_session']=$sess_data;
				}
				else
				{
					echo 'error';
				}
			}
			else if($r_type=='array')
			{
				$res=json_decode($response,true);
				if($res['status']=='200')
				{
					$_SESSION['authorization']=$res['authorization'];
				}
				else
				{
					echo 'error';
				}
			}
			else
			{
				$res=$response;
			}
			return '200';
		}

    }
    function post_data($url,$data)
	{
		$fields_string=null;
		$curl_data=array(
			"auth-key: ".$_SESSION['api_session']['auth_key'],
			"authorization: ".$_SESSION['api_session']['token'],
			"cache-control: no-cache",
			"client-service: ".$_SESSION['api_session']['client_service'],
			"content-type: ".$_SESSION['api_session']['content_type']
		);
		foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
	}
//
}
?>
