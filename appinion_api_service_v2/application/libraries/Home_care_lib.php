<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_care_lib {

	public function get_start_date($month, $year)
	{

		if($month == 01)
		{
			$month = 12;
			$year = $year - 1;
			$start_date = $year.'-'.$month.'-'.'26';
		}
		else
		{
			$month -= 1;
			$start_date = $year.'-'.$month.'-'.'26';
		}
		return $start_date;
	}

	public function get_end_date($month, $year)
	{
		$end_date = $year.'-'.$month.'-'.'25';
		return $end_date;
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

	function decryptIt($q)
	{
		$cryptKey = 'Lf6Q5htqdgnSn0AABqlsSddj1QNu0fJs';
		$qDecoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($q), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");
		return ($qDecoded);
	}

	function encryptIt($q)
	{
		$cryptKey = 'Lf6Q5htqdgnSn0AABqlsSddj1QNu0fJs';
		$qEncoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), $q, MCRYPT_MODE_CBC, md5(md5($cryptKey))));
		return ($qEncoded);
	}
}
