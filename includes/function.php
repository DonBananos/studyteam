<?php

function sanitize_text($text)
{
	return filter_var($text, FILTER_SANITIZE_SPECIAL_CHARS);
}

function sanitize_email($email)
{
	return filter_var($email, FILTER_SANITIZE_EMAIL);
}

function create_member($username, $firstname, $lastname, $email, $pass1, $pass2)
{
	$salt = generate_random_string(40, 50);

	if (compare_passwords($pass1, $pass2) === TRUE)
	{
		$pass = $pass1;
	}
	else
	{
		return compare_passwords($pass1, $pass2);
	}

	if (check_for_email($email) !== TRUE)
	{
		return check_for_email($email);
	}

	if (check_for_username($username) !== TRUE)
	{
		return check_for_username($username);
	}

	$password = hash_password($pass, $salt);

	global $dbCon;

	$sql = "INSERT INTO member (username, firstname, lastname, email, password, salt, permission) VALUES (?, ?, ?, ?, ?, ?, 1);";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('ssssss', $username, $firstname, $lastname, $email, $password, $salt); //Bind parameters.
	$stmt->execute(); //Execute
	$id = $stmt->insert_id;
	if ($id > 0)
	{
		$stmt->close();
		return TRUE;
	}
	$error = $stmt->error;
	$stmt->close();
	return $error;
}

function hash_password($password, $salt)
{
	$salt_to_use = $salt . SALT;
	$hashed_pass = hash_hmac('sha512', $password, $salt_to_use);

	return $hashed_pass;
}

function compare_passwords($pass1, $pass2)
{
	if ($pass1 === $pass2)
	{
		return TRUE;
	}
	return "Passwords does not match";
}

function check_for_email($email)
{
	global $dbCon;

	$sql = "SELECT COUNT(id) AS members FROM member WHERE email = ?;";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('s', $email);
	$stmt->execute(); //Execute
	$stmt->bind_result($members); //Get ResultSet
	$stmt->fetch();
	$stmt->close();

	if ($members > 0)
	{
		return "Email is already in system. Try to log in!";
	}
	return TRUE;
}

function check_for_username($username)
{
	global $dbCon;

	$sql = "SELECT COUNT(id) AS members FROM member WHERE username = ?;";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('s', $username);
	$stmt->execute(); //Execute
	$stmt->bind_result($members); //Get ResultSet
	$stmt->fetch();
	$stmt->close();

	if ($members > 0)
	{
		return "Username is already in system. Try something else!";
	}
	return TRUE;
}

function check_if_email($text)
{
	$email_regex = "/^[a-zA-Z0-9_.+-]+@[a-z0-9A-Z]+\.[a-z0-9A-Z]*\.?[a-zA-Z]{2,}$/";
	if (preg_match($email_regex, $text))
	{
		return TRUE;
	}
	return FALSE;
}

function log_member_in($user, $password)
{
	$failed_message = "Wrong Username/Email or Password";
	if (check_if_email($user))
	{
		$user_array = get_member_with_email($user);
	}
	else
	{
		$user_array = get_member_with_username($user);
	}
	if ($user_array['id'] < 1)
	{
		save_login_attempt(0);
		return $failed_message.'1';
	}
	$hashed_password = hash_password($password, $user_array['salt']);
	if (compare_passwords($hashed_password, $user_array['password']) === TRUE)
	{
		if (check_if_ban_is_in_order($user_array['id']) === TRUE)
		{
			save_login_attempt(0, $user_array['id']);
			return "This account has been locked due to too many failed logins. Please try again later!";
		}
		$_SESSION['logged_in'] = TRUE;
		$_SESSION['user_id'] = $user_array['id'];
		save_login_attempt(1, $user_array['id']);
		return TRUE;
	}
	else
	{
		save_login_attempt(0, $user_array['id']);
		if (check_if_ban_is_in_order($user_array['id']) === TRUE)
		{
			return "This account has been locked due to too many failed logins. Please try again later!";
		}
		return $failed_message;
	}
	return FALSE;
}

function save_login_attempt($response, $id = 0)
{
	$ip = get_ip_address();
	global $dbCon;

	$sql = "INSERT INTO login_attempts (member_id, ip_address, login_success) VALUES (?, ?, ?);";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('isi', $id, $ip, $response); //Bind parameters.
	$stmt->execute(); //Execute
	$id = $stmt->insert_id;
	if ($id > 0)
	{
		$stmt->close();
		return TRUE;
	}
	$error = $stmt->error;
	$stmt->close();
	return $error;
}

function get_member_with_email($email)
{
	global $dbCon;

	$sql = "SELECT id, username, firstname, lastname, email, password, salt, permission FROM member WHERE email = ?;";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('s', $email);
	$stmt->execute(); //Execute
	$stmt->bind_result($id, $username, $firstname, $lastname, $email, $password, $salt, $permission); //Get ResultSet
	$stmt->fetch();
	$stmt->close();

	if ($id > 0)
	{
		$user_array = array("id" => $id, "username" => $username, "firstname" => $firstname, "lastname" => $lastname, "password" => $password, "salt" => $salt, "permission" => $permission);
		return $user_array;
	}
	return TRUE;
}

function get_member_with_username($username)
{
	global $dbCon;

	$sql = "SELECT id, username, firstname, lastname, email, password, salt, permission FROM member WHERE username = ?;";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('s', $username);
	$stmt->execute(); //Execute
	$stmt->bind_result($id, $username, $firstname, $lastname, $email, $password, $salt, $permission); //Get ResultSet
	$stmt->fetch();
	$stmt->close();

	if ($id > 0)
	{
		$user_array = array("id" => $id, "username" => $username, "firstname" => $firstname, "lastname" => $lastname, "password" => $password, "salt" => $salt, "permission" => $permission);
		return $user_array;
	}
	return TRUE;
}

function get_member_with_id($id)
{
	global $dbCon;

	$sql = "SELECT id, username, firstname, lastname, email, password, salt, permission FROM member WHERE id = ?;";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('i', $id);
	$stmt->execute(); //Execute
	$stmt->bind_result($id, $username, $firstname, $lastname, $email, $password, $salt, $permission); //Get ResultSet
	$stmt->fetch();
	$stmt->close();

	if ($id > 0)
	{
		$user_array = array("id" => $id, "username" => $username, "firstname" => $firstname, "lastname" => $lastname, "password" => $password, "salt" => $salt, "permission" => $permission);
		return $user_array;
	}
	return TRUE;
}

function log_member_out()
{
	session_destroy();
}

function check_if_ban_is_in_order($id)
{
	global $dbCon;

	$sql = "SELECT COUNT(id) AS bad_logins FROM login_attempts WHERE member_id = ? AND time > SUBDATE(NOW(), INTERVAL 5 MINUTE) AND login_success = 0;";
	$stmt = $dbCon->prepare($sql); //Prepare Statement
	if ($stmt === false)
	{
		trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
	}
	$stmt->bind_param('i', $id);
	$stmt->execute(); //Execute
	$stmt->bind_result($bad_logins); //Get ResultSet
	$stmt->fetch();
	$stmt->close();

	if ($bad_logins > 2)
	{
		return TRUE;
	}
	return FALSE;
}
