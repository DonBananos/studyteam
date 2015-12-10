<?php

class Student
{

	private $id;
	private $username;
	private $firstname;
	private $lastname;
	private $fullname;
	private $email;
	private $password;
	private $salt;
	private $joined;
	private $permission;

	function __construct($id)
	{
		$this->set_values_with_id($id);
	}

	private function set_values_with_id($id)
	{
		global $dbCon;

		$sql = "SELECT id, username, firstname, lastname, email, password, salt, joined, permission, concat(firstname, ' ', lastname) AS fullname FROM student WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($id, $username, $firstname, $lastname, $email, $password, $salt, $joined, $permission, $fullname);
		$stmt->fetch();
		if ($id > 0)
		{
			$this->set_values($id, $username, $firstname, $lastname, $email, $password, $salt, $joined, $permission, $fullname);
			$stmt->close();
			return TRUE;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}

	private function set_values($id, $username, $firstname, $lastname, $email, $password, $salt, $joined, $permission, $fullname)
	{
		$this->set_id($id);
		$this->set_username($username);
		$this->set_firstname($firstname);
		$this->set_lastname($lastname);
		$this->set_email($email);
		$this->set_password($password);
		$this->set_salt($salt);
		$this->set_joined($joined);
		$this->set_permission($permission);
		$this->set_fullname($fullname);
	}
	
	public function get_id()
	{
		return $this->id;
	}

	public function get_username()
	{
		return $this->username;
	}

	public function get_firstname()
	{
		return $this->firstname;
	}

	public function get_lastname()
	{
		return $this->lastname;
	}

	public function get_email()
	{
		return $this->email;
	}

	public function get_password()
	{
		return $this->password;
	}

	public function get_salt()
	{
		return $this->salt;
	}

	public function get_joined()
	{
		return $this->joined;
	}

	public function get_permission()
	{
		return $this->permission;
	}
	
	public function get_fullname()
	{
		return $this->fullname;
	}

	private function set_id($id)
	{
		$this->id = $id;
	}

	private function set_username($username)
	{
		$this->username = $username;
	}

	private function set_firstname($firstname)
	{
		$this->firstname = $firstname;
	}

	private function set_lastname($lastname)
	{
		$this->lastname = $lastname;
	}

	private function set_email($email)
	{
		$this->email = $email;
	}

	private function set_password($password)
	{
		$this->password = $password;
	}

	private function set_salt($salt)
	{
		$this->salt = $salt;
	}

	private function set_joined($joined)
	{
		$this->joined = $joined;
	}

	private function set_permission($permission)
	{
		$this->permission = $permission;
	}
	
	private function set_fullname($fullname)
	{
		$this->fullname = $fullname;
	}
}
