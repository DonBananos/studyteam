<?php

$session_name = "security_course_session_id";
session_name($session_name);
session_start();
session_regenerate_id();

//General
define("BASE", "/studyteam/");
define("W1BASE", "/studyteam/");
define("ROOT_PATH", "/var/www/html/studyteam/");
define("SECURE", FALSE);
define("SERVER", "http://127.0.0.1:8080");
define("IMAGE_LOCATION", ROOT_PATH."_uploads/_images/");

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

// REGEX CREATE/EDIT USER
define("REGEX_USERNAME", "/^[a-zA-Z0-9][a-zA-Z0-9._-]{2,49}$/");
define("REGEX_PASSWORD", "/^(?=.*[^a-zA-Z])(?=.*[a-z])(?=.*[A-Z])\S{8,}$/");


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

function validate_email($email)
{
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_int($int)
{
	return filter_var($int, FILTER_VALIDATE_INT);
}

function validate_float($float)
{
	return filter_var($float, FILTER_VALIDATE_FLOAT);
}

function validate_url($url)
{
	return filter_var($url, FILTER_VALIDATE_URL);
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

function upload_image($path, $post_id = null, $max_width)
{
    $upload_directory = IMAGE_LOCATION;
    $uploaded_url = SERVER.BASE;
 
    if (substr($path, -1) == '/')
    {
        $image_path = $path;
    }
    else
    {
        $image_path = $path . '/';
    }
    if (empty($post_id))
    {
        $image_path .= "cover/";
    }
    else
    {
        $image_path .= $post_id . "/";
    }
    $upload_directory .= $image_path;
    if (!file_exists($upload_directory))
    {
        mkdir($upload_directory, 0777, true);
    }
    $image_name = get_free_image_name($upload_directory, getImageType($_FILES['imageFile']['name']));
 
    $upload_file = $upload_directory . $image_name;
    $image_path .= $image_name;
    $uploaded_url .= $image_path;
 
    if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $upload_file))
    {
        if (substr(strrchr($upload_file, "."), 1) == 'jpg' OR substr(strrchr($upload_file, "."), 1) == 'jpeg' OR substr(strrchr($upload_file, "."), 1) == 'JPG')
        {
            $image = imagecreatefromjpeg($upload_file);
        }
        elseif (substr(strrchr($upload_file, "."), 1) == 'png' OR substr(strrchr($upload_file, "."), 1) == 'PNG')
        {
            $image = imagecreatefrompng($upload_file);
        }
        elseif (substr(strrchr($upload_file, "."), 1) == 'gif' OR substr(strrchr($upload_file, "."), 1) == 'GIF')
        {
            $image = imagecreatefromgif($upload_file);
        }
        //Check for image resizing
        list($width, $height) = getimagesize($upload_file);
        if ($width > $max_width)
        {
            $new_width = $max_width;
            $new_height = $height / $width * $new_width;
 
            $tmp = imagecreatetruecolor($new_width, $new_height);
 
            imagecopyresampled($tmp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
 
            if (substr(strrchr($upload_file, "."), 1) == 'jpg' OR substr(strrchr($upload_file, "."), 1) == 'jpeg' OR substr(strrchr($upload_file, "."), 1) == 'JPG')
            {
                $exif = exif_read_data($upload_file);
                if (!empty($exif['Orientation']))
                {
                    switch ($exif['Orientation'])
                    {
                        case 8:
                            $tmp = imagerotate($tmp, 90, 0);
                            break;
                        case 3:
                            $tmp = imagerotate($tmp, 180, 0);
                            break;
                        case 6:
                            $tmp = imagerotate($tmp, -90, 0);
                            break;
                    }
                }
            }
            if (substr(strrchr($upload_file, "."), 1) == 'jpg' OR substr(strrchr($upload_file, "."), 1) == 'jpeg' OR substr(strrchr($upload_file, "."), 1) == 'JPG')
            {
                imagejpeg($tmp, $upload_file, 100);
            }
            elseif (substr(strrchr($upload_file, "."), 1) == 'png' OR substr(strrchr($upload_file, "."), 1) == 'PNG')
            {
                imagepng($tmp, $upload_file, 0);
            }
            elseif (substr(strrchr($upload_file, "."), 1) == 'gif' OR substr(strrchr($upload_file, "."), 1) == 'GIF')
            {
                imagegif($tmp, $upload_file, 100);
            }
            imagedestroy($tmp);
        }
 
        imagedestroy($image);
        return $uploaded_url;
    }
    return false;
}
 
function resizeImage($image, $max_width, $max_height)
{
    //$width = imageWidth
    //$height = imageHeight
 
    if (empty($max_height))
    {
        $optimal_height = $height * 100 / ($width / $max_width * 100);
    }
}
 
function get_free_image_name($folder, $extension)
{
    $name_exists = TRUE;
 
    while ($name_exists === TRUE)
    {
		$name = generate_random_string(40, 50);
		
        if (!file_exists($folder . $name . '.' . $extension))
        {
            $name_exists = FALSE;
            return $name . $number . '.' . $extension;
        }
    }
}
 
function getImageType($imageName)
{
    $extension = substr($imageName, strpos($imageName, '.') + 1);
    return $extension;
}
 
function turnImageNameToThumbName($image_name)
{
    if (substr($image_name, 0, strpos($image_name, "_") + 1) == "IMAGE_")
    {
        return "THUMB_" . substr($image_name, strpos($image_name, "_") + 1);
    }
    else
    {
        return 'THUMB_' . $image_name;
    }
}
 
function getThumbUrlFromUrl($url)
{
    $filename = substr(strrchr($url, "/"), 1);
    $other_part_of_url = substr($url, 0, strrpos($url, '/'));
    $thumb_url = $other_part_of_url . '/' . turnImageNameToThumbName($filename);
 
    return $thumb_url;
}
