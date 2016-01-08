<?php

/*
 * Author: Mike Jensen <mikejensen2@gmail.com>
 * Purpose: StudyTeam (Web Security Exam Project)
 * 
 * This class takes care of Students that is not on one specific, and known, 
 * student object.
 */

class Student_controller
{
	/*
	 * A Constructor is needed to use the object
	 */

	function __construct()
	{
		
	}

	/*
	 * Function to create a new student. Takes the neccesary variables.
	 */

	public function create_student($username, $firstname, $lastname, $email, $pass1, $pass2)
	{

		/* 
			validate_input() is in charge of validating the input parameters
			before we do anything else. It returns an array of error messages 
		*/
		$error_array = $this->validate_input($username, $firstname, $lastname, $email, $pass1, $pass2);
		/* 
			If we receive an array that IS NOT empty then we have ERRORS. Therefore we return the array,
			for UX purposes, and EXIT the current process.
		*/
		if(count($error_array)>0)
		{
			return $error_array;
			exit();
		}
		else
		{
			$pass = $pass1;
		}

		//Secondly, we check if the email is in the system
		if ($this->check_for_email($email) !== TRUE)
		{
			//If it does not return TRUE, it returns an error message
			return $this->check_for_email($email);
		}

		//Now, we check if the username is in the system
		if ($this->check_for_username($username) !== TRUE)
		{
			//If it does not return TRUE, it returns an error message
			return $this->check_for_username($username);
		}

		//We generate a random string between 40 and 50 characters of length, to
		//use as the salt.
		$salt = generate_random_string(40, 50);

		//If we reach this far, we need to hash the password!
		//The function returns the hashed password.
		$password = $this->hash_password($pass, $salt);

		//We make use of the global dbCon that we've created in the config file
		global $dbCon;

		//This is pretty basic SQL. We use Prepared Statements
		$sql = "INSERT INTO student "
				. "(username, firstname, lastname, email, password, salt, "
				. "permission) "
				. "VALUES "
				. "(?, ?, ?, ?, ?, ?, 1);";
		//We Prepare the Statement
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			//Oh no, the statement wasn't prepared correct! Trigger the Error!
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		//So, let's bind the parameters to the prepared statement.
		//the first part means that all six parameters are strings, the rest
		//are the parameters we pass to the statement.
		$stmt->bind_param('ssssss', $username, $firstname, $lastname, $email, $password, $salt); //Bind parameters.
		//Execute the statement
		$stmt->execute();
		//Get the insertet ID (the new Student's ID in the DB)
		$id = $stmt->insert_id;
		//We check if it is an integer higher than 0
		if ($id > 0)
		{
			//It is, which means that the Student has been created and saved
			$stmt->close();
			return TRUE;
		}
		//Well, since we reached this far, the if statement wasn't executed.
		//Save the error
		$error = $stmt->error;
		//Close down the statement (good practice)
		$stmt->close();
		return $error;
	}

	/*
		Function var validating text input from registration form.
		If a parameter contains non-valid value an error message
		will be added
	*/
	private function validate_input($username, $firstname, $lastname, $email, $pass1, $pass2)
	{
		$error_array = array();

		if($this->validate_username($username) !== 1)
		{
			$error_array[0] = "Invalid username";
		}

		if($this->validate_name($firstname) !== TRUE)
		{
			$error_array[1] = "Invalid firstname";
		}

		if($this->validate_name($lastname) !== TRUE)
		{
			$error_array[2] = "Invalid lastname";
		}

		if($this->validate_email($email) == FALSE)
		{
			$error_array[3] = "Invalid email";
		}
		
		if($this->validate_password($pass1) === FALSE)
		{
			$error_array[4] = "Invalid password";
		}

		if($this->compare_passwords($pass1, $pass2) !== TRUE)
		{
			$error_array[5] = "Retyped password does not match";
		}

		return $error_array;
	}

	/*
	 * Function used to hash a password
	 */
	private function hash_password($password, $salt)
	{
		//truncate the user's salt and the defined salt from the config file
		$salt_to_use = $salt . SALT;
		//Hash it with the sha512 algorithm.
		$hashed_pass = hash_hmac('sha512', $password, $salt_to_use);

		return $hashed_pass;
	}

	/*
	 * Function that checks if two passwords match, returns error message if not
	 */
	public function compare_passwords($pass1, $pass2)
	{
		//We use 3 equal signs, which means that both the value but also the 
		//type is identical
		if ($pass1 === $pass2)
		{
			return TRUE;
		}
		return "Passwords does not match";
	}

	/*
	 * Function that checks the database for a specific email address
	 */

	private function check_for_email($email)
	{
		//We make use of the global dbCon that we've created in the config file
		global $dbCon;

		//SQL statement where we Count the occurances (it's faster!)
		$sql = "SELECT COUNT(id) AS students FROM student WHERE email = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('s', $email);
		$stmt->execute(); //Execute
		$stmt->bind_result($students); //Get ResultSet
		$stmt->fetch();
		$stmt->close();

		if ($students > 0)
		{
			return "Email is already in system. Try to log in!";
		}
		return TRUE;
	}

	/*
	 * Function that checks the database for a specific username
	 * See check_for_email and create_student for explanations on the code
	 */

	private function check_for_username($username)
	{
		global $dbCon;

		$sql = "SELECT COUNT(id) AS students FROM student WHERE username = ?;";
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

	/*
		Function that checks if username is valid.
		See defined constant "REGEX_USERNAME" in configuration.php around line 34.
	*/
	function validate_username($username)
	{
		//return preg_match(REGEX_USERNAME, $username);
		return preg_match(REGEX_USERNAME, $username);
	}

	/*
		Function that checks if the username does not
		contain 3 of the same chars in a row
	*/
	function dont_allow_3_in_a_row($text)
	{
		$arr = str_split($text);
		$s = sizeof($arr);
		for($i = 1; $i < $s; $i++)
		{
			if($i !== $s)
			{
				if($arr[$i] === $arr[$i-1] && $arr[$i] === $arr[$i+1])
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	/*
		Function that checks if password is valid.
		See defined constant "REGEX_PASSWORD" in configuration.php around line 35,
		and dont_allow_3_in_a_row()
	*/
	function validate_password($password)
	{
		if($this->dont_allow_3_in_a_row($password) === FALSE)
		{
			return FALSE;
		}
		if(preg_match(REGEX_PASSWORD, $password) !== 1)
		{
			return FALSE;
		}
	}

	/*
		Function that checks if email is valid.
	*/
	function validate_email($string)
	{
		//We use the built in filter_var function to validate the email.
		//filter_var returns either the filtered data or false, which means we
		//can simply return the answer of the function! simple!
		return filter_var($string, FILTER_VALIDATE_EMAIL);
	}

	/*
		Function that checks if firstname is valid.
		1) Trim input
		2) check if string length > 1
	*/
	function validate_name($name)
	{
		$trim = trim($name);
		if(strlen($trim) > 1)
		{
			return TRUE;
		}
		return FALSE;
	}

	/*
	 * Function used to log a student in. We can't use the Student object though
	 * Since we do not have the ID of the student trying to log in - yet!
	 */

	function log_member_in($user, $password)
	{
		//Security 101 - Never tell the user which part is incorrect!
		$failed_message = "Wrong Username/Email or Password";
		//We check if the value given from the user is an email
		
		if ($this->validate_email($user))
		{
			//die("It's an email!");
			//And so it is! We get the member details with the email address.
			$user_array = $this->get_member_with_email($user);
		}
		else
		{
			//die("It's a username!");
			//Oh, it's not an email, maybe a username then?
			$user_array = $this->get_member_with_username($user);
		}

		//die("TEST");

		//If the id is not set in the user array, or ID is less than one, we
		//can't log the login attempt for a specific user
		//die(var_dump($user_array));
		if (!isset($user_array['id']) && !validate_int($user_array['id']))
		{
			//We save the login attempt, without passing a user id, and passing
			//the 0 value as response (0 == false in SQL)
			$this->save_login_attempt(0);
			return $failed_message; //Return the string created in the beginning
		}
		//Since we've reached this far, we know that a user exists with the 
		//username OR email. We now hash the given password with the user's salt
		$hashed_password = $this->hash_password($password, $user_array['salt']);
		//We check if the saved password matches the given password
		//die($hashed_password . '<br>' . $user_array['password']);
		if ($this->compare_passwords($hashed_password, $user_array['password']) === TRUE)
		{
			//We check if the user is banned
			if ($this->check_if_ban_is_in_order($user_array['id']) === TRUE)
			{
				//The account is banned. We save the login attemp with a failed
				//respons, and returns an error message stating this.
				$this->save_login_attempt(0, $user_array['id']);
				//If the ban is more than 3 (which is where the user is banned)
				//We annoy the user a bit by redirecting them!
				//Here we check if it's only three! 
				//(remember we just saved one more, so it's +1)
				if (!$this->check_if_ban_is_in_order($id, 5))
				{
					return "This account has been locked due to too many failed "
							. "logins. Please try again later!";
				}
				return "This account has been locked due to too many failed "
						. "logins. You are now being redirected because you've tried "
						. "to log in much more than allowed! "
						. "<script>"
						. "setTimeout(function()"
						. "{window.location = 'http://www.heibosoft.com'}"
						. ", 2000);"
						. "</script>";
			}
			//Now, let's sign the user in!
			//We save a session cookie stating the user is signed in
			$_SESSION['logged_in'] = TRUE;
			//And a session cookie with the user id
			$_SESSION['user_id'] = $user_array['id'];
			//The user succesfully logged in, let's save the attempt anyway!
			$this->save_login_attempt(1, $user_array['id']);
			return TRUE;
		}
		else
		{
			$this->save_login_attempt(0, $user_array['id']);
			if ($this->check_if_ban_is_in_order($user_array['id']) === TRUE)
			{
				if (!$this->check_if_ban_is_in_order($id, 5))
				{
					return "This account has been locked due to too many failed "
							. "logins. Please try again later!";
				}
				return "This account has been locked due to too many failed "
						. "logins. You are now being redirected because you've tried "
						. "to log in much more than allowed! "
						. "<script>"
						. "setTimeout(function()"
						. "{window.location = 'http://www.heibosoft.com'}"
						. ", 2000);"
						. "</script>";
			}
			return $failed_message;
		}
		return FALSE;
	}

	/*
	 * Function to log a log in attempt
	 */

	function save_login_attempt($response, $id = 0)
	{
		//We get the user's ip address from the function in the config file
		$ip = get_ip_address();

		//Rest is covered in prior functions
		global $dbCon;

		$sql = "INSERT INTO login_attempts (student_id, ip_address, login_success) VALUES (?, ?, ?);";
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

	/*
	 * Function to get an array of user details with an email address
	 * This function is pretty basic, and most is covered prior to this.
	 */

	function get_member_with_email($email)
	{
		global $dbCon;

		$sql = "SELECT id, username, firstname, lastname, email, password, salt, permission FROM student WHERE email = ?;";
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
			//Create an array with the user data.
			$user_array = array("id" => $id, "username" => $username, "firstname" => $firstname, "lastname" => $lastname, "email" => $email, "password" => $password, "salt" => $salt, "permission" => $permission);
			return $user_array;
		}
		return TRUE;
	}

	/*
	 * Function to get an array of user details with a username.
	 * See above for explanations.
	 */

	function get_member_with_username($username)
	{
		global $dbCon;

		$sql = "SELECT id, username, firstname, lastname, email, password, salt, permission FROM student WHERE username = ?;";
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
			$user_array = array("id" => $id, "username" => $username, "firstname" => $firstname, "lastname" => $lastname, "email" => $email, "password" => $password, "salt" => $salt, "permission" => $permission);
			return $user_array;
		}
		return TRUE;
	}

	//Should probably be moved to student object.
	function log_member_out()
	{
		session_destroy();
	}

	/*
	 * Function that counts the number of logins which has been marked as 
	 * unsuccesful. If this is more than the max allowed, it returns true which
	 * means the user should be banned..
	 * This function only checks back for the last 5 minutes, and this should
	 * probably be a variable as well.. But not now, to tired!
	 */

	function check_if_ban_is_in_order($id, $max = 3)
	{
		global $dbCon;

		$sql = "SELECT COUNT(id) AS bad_logins FROM login_attempts WHERE student_id = ? AND time > SUBDATE(NOW(), INTERVAL 5 MINUTE) AND login_success = 0;";
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

		if ($bad_logins >= $max)
		{
			return TRUE;
		}
		return FALSE;
	}

	public function search_for_student($search_string)
	{
		$results = array();
		if ($this->validate_email($search_string))
		{
			$results = $this->search_for_email(sanitize_email($search_string));
		}
		else
		{
			$username_results = $this->search_for_username(sanitize_text($search_string));
			$name_results = $this->search_for_name(sanitize_text($search_string));
			$email_results = $this->search_for_first_part_of_email($search_string);
			foreach ($username_results as $username_result)
			{
				$results[] = $username_result;
			}
			foreach ($name_results as $name_result)
			{
				if (!in_array($name_result, $results))
				{
					$results[] = $name_result;
				}
			}
			foreach ($email_results as $email_result)
			{
				if (!in_array($email_result, $results))
				{
					$results[] = $email_result;
				}
			}
		}
		return $results;
	}

	private function search_for_username($string)
	{
		$search_string = '%' . $string . '%';
		$results = array();
		global $dbCon;

		$sql = "SELECT id FROM student WHERE username LIKE ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('s', $search_string);
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id); //Get ResultSet
		while ($stmt->fetch())
		{
			$results[] = $student_id;
		}
		$stmt->close();

		return $results;
	}

	private function search_for_name($string)
	{
		$search_string = '%' . $string . '%';
		$results = array();
		global $dbCon;

		$sql = "SELECT id FROM student WHERE concat(firstname, ' ', lastname) LIKE ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('s', $search_string);
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id); //Get ResultSet
		while ($stmt->fetch())
		{
			$results[] = $student_id;
		}
		$stmt->close();

		return $results;
	}

	private function search_for_email($string)
	{
		$search_string = '%' . $string . '%';
		$results = array();
		global $dbCon;
		$sql = "SELECT id FROM student WHERE email LIKE ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('s', $search_string);
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id); //Get ResultSet
		while ($stmt->fetch())
		{
			$results[] = $student_id;
		}
		$stmt->close();

		return $results;
	}

	private function search_for_first_part_of_email($string)
	{
		$search_string = '%' . $string . '%';
		$results = array();
		global $dbCon;
		$sql = "SELECT id FROM student WHERE SUBSTRING_INDEX(email, '@', 1) LIKE ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('s', $search_string);
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id); //Get ResultSet
		while ($stmt->fetch())
		{
			$results[] = $student_id;
		}
		$stmt->close();

		return $results;
	}

	public function please_reset_my_password_because_im_stupid($user)
	{
		$failed_message = "Ooops... Get a grip boy!";
		if ($this->check_if_email($user))
		{
			//And so it is! We get the member details with the email address.
			$user_array = $this->get_member_with_email($user);
		}
		else
		{
			//Oh, it's not an email, maybe a username then?
			$user_array = $this->get_member_with_username($user);
		}
		if (!isset($user_array['id']) || $user_array['id'] < 1)
		{
			//We save the login attempt, without passing a user id, and passing
			//the 0 value as response (0 == false in SQL)
			return $failed_message; //Return the string created in the beginning
		}
		else
		{
			$user_id = $user_array['id'];
			$u = generate_random_string(80, 120);
			$e = generate_random_string(80, 120);
			$c = generate_random_string(80, 120);
			if ($this->reset_password_request($user_id, $u, $e, $c))
			{
				return $this->send_reset_email($user_array['email'], $u, $e, $c);
			}
		}
	}

	private function reset_password_request($user_id, $u, $e, $c)
	{
		$time = date("Y-m-d H:i:s", time());
		global $dbCon;

		$sql = "INSERT INTO reset_password_request (user_id, time, u, e, c) VALUES (?, ?, ?, ?, ?);";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('issss', $user_id, $time, $u, $e, $c); //Bind parameters.
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

	private function send_reset_email($email, $u, $e, $c)
	{
		// multiple recipients
		$to = $email;

		// subject
		$subject = 'Reset your password';

		// message
		$message = '
			<html>
			<head>
			  <title>Reset your password</title>
			</head>
			<body>
			  <p>Hi buddy!<br/>You\'ve requested a password reset, please click the link below to reset your password.</p>
			  <p>
				<a href="'.SERVER.BASE.'student/reset.php?u='.$u.'&e='.$e.'&c='.$c.'">'.SERVER.BASE.'student/reset.php?u='.$u.'&e='.$e.'&c='.$c.'</a>
			  </p>
			  <small>
				If you have not requested a password reset, please ignore this email.<br/>You\'ll be able to use your normal credentials as long as you don\'t reset your password.
			  </small>
			</body>
			</html>
			';

		// To send HTML mail, the Content-type header must be set
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

		// Additional headers
		$headers .= 'To: <'.$email.'>' . "\r\n";
		$headers .= 'From: StudyTeam Bot <studyteam@heibosoft.com>' . "\r\n";

		// Mail it
		if(mail($to, $subject, $message, $headers))
		{
			return "Please check your email client. Also check the spam folder!";
		}
		return "Ooooops. Something went wrong!<br/>Please try again!";
	}
	
	public function get_student_from_u_e_and_c_codes($u, $e, $c)
	{
		global $dbCon;
		
		$sql = "SELECT user_id, used, time FROM reset_password_request WHERE u = ? AND e = ? AND c = ? ORDER BY time DESC LIMIT 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('sss', $u, $e, $c);
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id, $used, $time); //Get ResultSet
		$stmt->fetch();
		$stmt->close();
		
		if($student_id > 0 AND $used == 0)
		{
			$now = time();
			$requested = strtotime($time);
			$difference = abs($requested-$now);
			$difference_in_hours = round($difference/60/60);
			if($difference_in_hours <= RESET_LIMIT)
			{
				return $student_id;
			}
			else
			{
				return "You only have ".RESET_LIMIT." hours, from you've requested the reset. Unfortunately it's been $difference_in_hours hours since you requested the reset.";
			}
		}
		elseif($student_id > 0)
		{
			return "You have already reset your password from that email. Still can't remember? Pull yourself together man!";
		}
		return false;
	}

	public function set_reset_request_to_used($u, $e, $c)
	{
		global $dbCon;
		
		$sql = "UPDATE reset_password_request SET used = 1 WHERE u = ? AND e = ? AND c = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('sss', $u, $e, $c); //Bind parameters.
		$stmt->execute(); //Execute
		if($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		$stmt->close();
		echo $stmt->error;
		return $stmt->error;
	}
}
