<?php

$session_name = "security_course_session_id";
session_name($session_name);
session_start();
session_regenerate_id();

//General
define("BASE", "/studyteam/");
define("W1BASE", "/studyteam/");
define("SECURE", FALSE);
define("SERVER", "http://127.0.0.1:8080");

//Database definitions
//This is now pointing to the vps..
//The user only has access to a dev database, and only INSERT, SELECT, UPDATE
//from that one schema
define("HOST", "85.119.155.19"); //85.119.155.19
define("USER", "studyteam");
define("PASS", "MangeLange3lastikker42");
define("DATABASE", "studyteam");

//Hardcoded Salt
define("SALT", "d89F6O3CAdaok593Hvo6aG51sR");

//Reset Password Time Limit From Email sent to Reset Done (in hours)
define("RESET_LIMIT", "24");

//Set Avatar Location
define("AVATAR_LOCATION", SERVER.BASE."includes/_media/_images/avatars/");

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

function sanitize_int($int)
{
	return filter_var($int, FILTER_SANITIZE_NUMBER_INT);
}

function sanitize_float($float)
{
	return filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT);
}

function sanitize_url($url)
{
	return filter_var($url, FILTER_SANITIZE_URL);
}

function get_permission_name_from_id($id)
{
	global $dbCon;

	$sql = "SELECT title FROM permission WHERE id = ?;";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('i', $id); //Bind parameters.
	$stmt->execute(); //Execute
	$stmt->bind_result($title);
	$stmt->fetch();
	if (strlen($title) > 0)
	{
		return $title;
	}
	$error = $stmt->error;
	$stmt->close();
	return $error;
}

function get_member_level_name_from_level($level)
{
	if($level === 1)
	{
		return "Member";
	}
	elseif($level === 2)
	{
		return "Administrator";
	}
	elseif($level === 3)
	{
		return "Owner";
	}
}