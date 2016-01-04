<?php

class Group
{
	private $id;
	private $name;
	private $school_id;
	private $edu_id;
	private $max_members;
	private $creator_student_id;
	private $created_time;
	private $description;
	private $category_id;
	private $category_name;
	private $category_image;
	private $public;
	
	function __construct($group_id)
	{
		$this->set_values($group_id);
	}
	
	private function set_values($id)
	{
		global $dbCon;
		
		$sql = "SELECT id, name, public, school_id, education_id, max_members, creator_student_id, created_time, description, category_id FROM `group` WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($id, $name, $public, $school_id, $education_id, $max_members, $creator_id, $created_time, $desc, $category_id);
		$stmt->fetch();
		$this->set_id($id);
		$this->set_name($name);
		$this->set_school_id($school_id);
		$this->set_edu_id($education_id);
		$this->set_max_members($max_members);
		$this->set_creator_student_id($creator_id);
		$this->set_created_time($created_time);
		$this->set_description($desc);
		$this->set_category_id($category_id);
		$this->set_public($public);
		$stmt->close();
		if ($this->get_category_id() > 0)
		{
			$this->set_category_values();
		}
		return true;
	}
	
	private function set_category_values()
	{
		global $dbCon;
		
		$sql = "SELECT name, image FROM group_category WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->category_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($category_name, $category_image);
		$stmt->fetch();
		$this->set_category_name($category_name);
		$this->set_category_image($category_image);
	}
	
	public function get_public_or_private()
	{
		if($this->get_public() == 1)
		{
			return 'Public';
		}
		return 'Private';
	}
	
	public function invite_student($student_id, $invited_by_student_id, $raw_message, $student_email, $student_name, $invited_by_student_email, $invited_by_student_name)
	{
		//Student name can be first, full or username - Haven't really decided yet
		
		$safe_message = sanitize_text($raw_message);
		
		$answer = $this->save_invite_in_db($student_id, $invited_by_student_id, $safe_message);
		if($answer === TRUE)
		{
			return $this->send_invite_by_email($student_email, $safe_message, $student_name, $invited_by_student_email, $invited_by_student_name);
		}
		else
		{
			return $answer;
		}
	}
	
	private function send_invite_by_email($student_email, $message, $student_name, $invited_by_student_email, $invited_by_student_name)
	{
		// multiple recipients
		$to = $student_email;

		// subject
		$subject = 'StudyTeam group invite';

		// message
		$message = '
			<html>
			<head>
			  <title>StudyTeam group invite</title>
			</head>
			<body>
			  <p>
				Hi '.$student_name.'!<br/>You\'ve been invited to join the group \''.$this->get_name().'\' by '.$invited_by_student_name.'.<br/>
				<a href="'.SERVER.BASE.'student/invites.php">Click here</a> to go to your invites!.<br/>
				<br/>
				Regards,<br/>
				StudyTeam
			  </p>
			</body>
			</html>
			';

		// To send HTML mail, the Content-type header must be set
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

		// Additional headers
		$headers .= 'To: <'.$student_email.'>' . "\r\n";
		$headers .= 'cc: <'.$invited_by_student_email.'>' . "\r\n";
		$headers .= 'From: StudyTeam Bot <studyteam@heibosoft.com>' . "\r\n";

		// Mail it
		if(mail($to, $subject, $message, $headers))
		{
			return "Invite has been sent!";
		}
		return "Invite created, but there was an error sending the email!";
	}
	
	private function save_invite_in_db($student_id, $invited_by_student_id, $message)
	{
		global $dbCon;
		
		$sql = "INSERT INTO group_invites (student_id, group_id, invitor_id, message) VALUES (?, ?, ?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iiis', $student_id, $this->id, $invited_by_student_id, $message); //Bind parameters.
		$stmt->execute();
		$id = $stmt->insert_id;
		if ($id > 0)
		{
			$stmt->close();
			return TRUE;
		}
		$error = $stmt->error;
		echo $error;
		$stmt->close();
		return $error;
	}
	
	public function get_number_of_registered_members()
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) as members FROM student_group WHERE group_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($members);
		$stmt->fetch();
		return $members;
	}
	
	public function get_number_of_pending_invites()
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) as pending_invites FROM group_invites WHERE group_id = ? AND response_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($members);
		$stmt->fetch();
		return $members;
	}
	
	public function get_number_of_declined_invites()
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) as pending_invites FROM group_invites WHERE group_id = ? AND response_status = 2;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->get_id()); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($members);
		$stmt->fetch();
		$stmt->close();
		return $members;
	}
	
	public function get_array_with_invites()
	{
		
	}
	
	public function add_student_to_group($student_id, $level = 1)
	{
		if($this->get_if_student_is_member($student_id))
		{
			return TRUE;
		}
		global $dbCon;
		
		$sql = "INSERT INTO student_group (student_id, group_id, level) VALUES (?, ?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iii', $student_id, $this->get_id(), $level); //Bind parameters.
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
	
	public function remove_student_from_group($student_id)
	{
		if($this->get_if_student_is_member($student_id) === FALSE)
		{
			return TRUE;
		}
		global $dbCon;
		
		$sql = "UPDATE student_group SET active = 0 WHERE group_id = ? AND student_id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->get_id(), $student_id); //Bind parameters.
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
	
	public function get_array_with_members_and_levels()
	{
		global $dbCon;
	
		$members_and_levels = array();
		
		$sql = "SELECT student_id, level, join_datetime FROM student_group WHERE group_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id, $level, $join_datetime);
		while($stmt->fetch())
		{
			$member = array();
			$member['level'] = $level;
			$member['joined'] = $join_datetime;
			$members_and_levels[$student_id] = $member;
		}
		if(count($members_and_levels) > 0)
		{
			$stmt->close();
			return $members_and_levels;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}
	
	public function get_if_student_is_member($student_id)
	{
		global $dbCon;
		
		$sql = "SELECT COUNT(*) AS member FROM student_group WHERE student_id = ? AND group_id = ? AND active = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $student_id, $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($members);
		$stmt->fetch();
		$stmt->close();
		if($members > 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	public function check_if_max_is_reached()
	{
		if($this->get_number_of_registered_members() < $this->max_members)
		{
			return FALSE;
		}
		return TRUE;
	}
	
	public function get_id()
	{
		return $this->id;
	}

	public function get_name()
	{
		return $this->name;
	}

	public function get_school_id()
	{
		return $this->school_id;
	}

	public function get_edu_id()
	{
		return $this->edu_id;
	}

	public function get_max_members()
	{
		return $this->max_members;
	}

	public function get_creator_student_id()
	{
		return $this->creator_student_id;
	}

	public function get_created_time()
	{
		return $this->created_time;
	}

	public function get_description()
	{
		return $this->description;
	}

	public function get_category_id()
	{
		return $this->category_id;
	}

	public function get_category_name()
	{
		return $this->category_name;
	}

	public function get_category_image()
	{
		return $this->category_image;
	}
	
	public function get_public()
	{
		return $this->public;
	}

	private function set_id($id)
	{
		$this->id = $id;
	}

	private function set_name($name)
	{
		$this->name = $name;
	}

	private function set_school_id($school_id)
	{
		$this->school_id = $school_id;
	}

	private function set_edu_id($edu_id)
	{
		$this->edu_id = $edu_id;
	}

	private function set_max_members($max_members)
	{
		$this->max_members = $max_members;
	}

	private function set_creator_student_id($creator_student_id)
	{
		$this->creator_student_id = $creator_student_id;
	}

	private function set_created_time($created_time)
	{
		$this->created_time = $created_time;
	}

	private function set_description($description)
	{
		$this->description = $description;
	}

	private function set_category_id($category_id)
	{
		$this->category_id = $category_id;
	}

	private function set_category_name($category_name)
	{
		$this->category_name = $category_name;
	}

	private function set_category_image($category_image)
	{
		$this->category_image = $category_image;
	}
	
	private function set_public($public)
	{
		$this->public = $public;
	}
}