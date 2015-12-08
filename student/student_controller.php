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
		//We generate a random string between 40 and 50 characters of length, to
		//use as the salt.
		$salt = generate_random_string(40, 50);

		//First we check if passwords match
		if ($this->compare_passwords($pass1, $pass2) === TRUE)
		{
			//If it is, we only need one of them!
			$pass = $pass1;
		}
		else
		{
			//If not, we return the answer, which should be an error message
			return $this->compare_passwords($pass1, $pass2);
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

		//If we reach this far, we need to has the password!
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
		$stmt->bind_param('ssssss', $username, $firstname, $lastname, $email, 
				$password, $salt); //Bind parameters.
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
	private function compare_passwords($pass1, $pass2)
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
	 * Function that checks if a string is an email
	 */
	function check_if_email($string)
	{
		//We use the built in filter_var function to validate the email.
		//filter_var returns either the filtered data or false, which means we
		//can simply return the answer of the function! simple!
		return filter_var($string, FILTER_VALIDATE_EMAIL);
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
		//If the id is not set in the user array, or ID is less than one, we
		//can't log the login attempt for a specific user
		if (!isset($user_array['id']) || $user_array['id'] < 1)
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
		if ($this->compare_passwords($hashed_password, $user_array['password']) 
				=== TRUE)
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
				if(!$this->check_if_ban_is_in_order($id, 5))
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
				if(!$this->check_if_ban_is_in_order($id, 5))
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
			$user_array = array("id" => $id, "username" => $username, "firstname" => $firstname, "lastname" => $lastname, "password" => $password, "salt" => $salt, "permission" => $permission);
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
			$user_array = array("id" => $id, "username" => $username, "firstname" => $firstname, "lastname" => $lastname, "password" => $password, "salt" => $salt, "permission" => $permission);
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

}
