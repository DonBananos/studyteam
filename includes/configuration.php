<?php

$session_name = "security_course_session_id";
session_name($session_name);
session_start();
session_regenerate_id();

//General
define("BASE", "/studyteam/");
define("W1BASE", "/studyteam/");
define("SECURE", FALSE);

//Database definitions
define("HOST", "localhost");
define("USER", "sec_user");
define("PASS", "koA9fJro%s8Jc0hvJ9ss62");
define("DATABASE", "studyteam");

//Hardcoded Salt
define("SALT", "d89F6O3CAdaok593Hvo6aG51sR");

//Let's do some connecting yo!
$dbCon = new mysqli(HOST, USER, PASS, DATABASE);
if ($dbCon->connect_errno)
{
	printf("Connect failed: %s\n", $dbCon->connect_error);
	exit();
}
$dbCon->set_charset("utf8");

function generate_random_string($min, $max)
{
	$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$number_of_characters = rand($min, $max);
	$random_string = "";
	for ($i = 0; $i < $number_of_characters; $i++)
	{
		$random_string .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $random_string;
}

function get_ip_address()
{
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP'))
		$ipaddress = getenv('HTTP_CLIENT_IP');
	else if (getenv('HTTP_X_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	else if (getenv('HTTP_X_FORWARDED'))
		$ipaddress = getenv('HTTP_X_FORWARDED');
	else if (getenv('HTTP_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	else if (getenv('HTTP_FORWARDED'))
		$ipaddress = getenv('HTTP_FORWARDED');
	else if (getenv('REMOTE_ADDR'))
		$ipaddress = getenv('REMOTE_ADDR');
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}

function sanitize_text($text)
{
	return filter_var($text, FILTER_SANITIZE_SPECIAL_CHARS);
}

function sanitize_email($email)
{
	return filter_var($email, FILTER_SANITIZE_EMAIL);
}