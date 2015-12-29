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
	private $avatar;

	function __construct($id)
	{
		$this->set_values_with_id($id);
	}

	private function set_values_with_id($id)
	{
		global $dbCon;

		$sql = "SELECT id, username, firstname, lastname, email, password, salt, joined, permission, concat(firstname, ' ', lastname) AS fullname, avatar FROM student WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($id, $username, $firstname, $lastname, $email, $password, $salt, $joined, $permission, $fullname, $avatar);
		$stmt->fetch();
		if ($id > 0)
		{
			$this->set_values($id, $username, $firstname, $lastname, $email, $password, $salt, $joined, $permission, $fullname, $avatar);
			$stmt->close();
			return TRUE;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}

	private function set_values($id, $username, $firstname, $lastname, $email, $password, $salt, $joined, $permission, $fullname, $avatar)
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
		$this->set_avatar_number($avatar);
	}
	
	public function change_password($password)
	{
		$salt_to_use = $this->get_salt() . SALT;
		
		$hashed_pass = hash_hmac('sha512', $password, $salt_to_use);
		
		return $this->save_new_password($hashed_pass);
	}
	
	private function save_new_password($password)
	{
		global $dbCon;
		
		$sql = "UPDATE student SET password = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('si', $password, $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		if($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		echo $stmt->error;
		$error = $stmt->error;
		echo $error;
		$stmt->close();
		return $error;
	}
	
	public function get_all_group_ids_that_student_created()
	{
		global $dbCon;
		$group_ids = array();
		
		$sql = "SELECT id FROM `group` WHERE creator_student_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($group_id);
		while($stmt->fetch())
		{
			$group_ids[] = $group_id;
		}
		if (count($group_ids) > 0)
		{
			$stmt->close();
			return $group_ids;
		}
		$stmt->close();
		return false;
	}
	
	public function get_public_groups_where_student_has_not_created()
	{
		global $dbCon;
		$group_ids = array();
		
		$sql = "SELECT id FROM `group` WHERE creator_student_id != ? AND public = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($group_id);
		while($stmt->fetch())
		{
			$group_ids[] = $group_id;
		}
		if (count($group_ids) > 0)
		{
			$stmt->close();
			return $group_ids;
		}
		$stmt->close();
		return false;
	}
	
	public function get_avatar()
	{
		return AVATAR_LOCATION.$this->get_avatar_number().'.png';
	}
	
	public function apply_for_buddies($applier_student_id)
	{
		global $dbCon;
		
		$sql = "INSERT INTO buddy (buddy_1_student_id, buddy_2_student_id) VALUES (?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $applier_student_id, $this->get_id()); //Bind parameters.
		$stmt->execute();
		$rows = $stmt->affected_rows;
		if ($rows == 1)
		{
			$stmt->close();
			return TRUE;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}
	
	public function check_if_buddies($other_student_id)
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) AS buddies FROM buddy WHERE ((buddy_1_student_id = ? AND buddy_2_student_id = ?) OR (buddy_2_student_id = ? AND buddy_1_student_id = ?)) AND buddy_status = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iiii', $other_student_id, $this->get_id(), $other_student_id, $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddies);
		$stmt->fetch();
		if ($buddies > 0)
		{
			$stmt->close();
			return TRUE;
		}
		$stmt->close();
		return FALSE;
	}
	
	public function check_if_buddies_pending($other_student_id)
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) AS buddies FROM buddy WHERE (buddy_1_student_id = ? AND buddy_2_student_id = ?) OR (buddy_2_student_id = ? AND buddy_1_student_id = ?) AND buddy_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iiii', $other_student_id, $this->get_id(), $other_student_id, $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddies);
		$stmt->fetch();
		if ($buddies > 0)
		{
			$stmt->close();
			return TRUE;
		}
		$stmt->close();
		return FALSE;
	}
	
	public function get_number_of_buddies_pending()
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) AS pending FROM buddy WHERE buddy_2_student_id = ? AND buddy_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($pending);
		$stmt->fetch();
		if ($pending > 0)
		{
			$stmt->close();
			return $pending;
		}
		$stmt->close();
		return 0;
	}
	
	public function get_number_of_buddies()
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) AS buddies FROM buddy WHERE (buddy_2_student_id = ? OR buddy_1_student_id = ?) AND buddy_status = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->get_id(), $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddies);
		$stmt->fetch();
		if ($buddies > 0)
		{
			$stmt->close();
			return $buddies;
		}
		$stmt->close();
		return 0;
	}
	
	public function get_all_buddy_ids()
	{
		global $dbCon;
		
		$buddies = array();
		
		$sql = "SELECT buddy_1_student_id, buddy_2_student_id FROM buddy WHERE (buddy_1_student_id = ? OR buddy_2_student_id = ?) AND buddy_status = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->get_id(), $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddy_1_id, $buddy_2_id);
		while($stmt->fetch())
		{
			if($buddy_1_id == $this->get_id())
			{
				$buddies[] = $buddy_2_id;
			}
			else
			{
				$buddies[] = $buddy_1_id;
			}
		}
		$stmt->close();
		return $buddies;
	}
	
	public function get_all_pending_buddy_ids()
	{
		global $dbCon;
		
		$pending = array();
		
		$sql = "SELECT buddy_1_student_id FROM buddy WHERE buddy_2_student_id = ? AND buddy_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddy_1_id);
		while($stmt->fetch())
		{
			$pending[] = $buddy_1_id;
		}
		$stmt->close();
		return $pending;
	}
	
	public function accept_buddy_pending($buddy_id)
	{
		global $dbCon;
		
		$sql = "UPDATE buddy SET buddy_status = 1 WHERE buddy_2_student_id = ? AND buddy_1_student_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->get_id(), $buddy_id); //Bind parameters.
		$stmt->execute(); //Execute
		if($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		echo $stmt->error;
		$error = $stmt->error;
		echo $error;
		$stmt->close();
		return $error;
	}
	
	public function decline_buddy_pending($buddy_id)
	{
		global $dbCon;
		
		$sql = "UPDATE buddy SET buddy_status = 2 WHERE buddy_2_student_id = ? AND buddy_1_student_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->get_id(), $buddy_id); //Bind parameters.
		$stmt->execute(); //Execute
		if($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		echo $stmt->error;
		$error = $stmt->error;
		echo $error;
		$stmt->close();
		return $error;
	}
	
	public function save_new_avatar($avatar_id)
	{
		global $dbCon;
		
		$sql = "UPDATE student SET avatar = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $avatar_id, $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		if($stmt->affected_rows > 0)
		{
			$stmt->close();
			$this->set_values_with_id($this->id);
			return true;
		}
		echo $stmt->error;
		$error = $stmt->error;
		echo $error;
		$stmt->close();
		return $error;
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
	
	private function get_avatar_number()
	{
		return $this->avatar;
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
	
	private function set_avatar_number($avatar)
	{
		$this->avatar = $avatar;
	}
}
