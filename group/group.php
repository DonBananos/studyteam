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
		if ($this->get_public() == 1)
		{
			return 'Public';
		}
		return 'Private';
	}

	public function invite_student($student_id, $invited_by_student_id, $raw_message, $student_email, $student_name, $invited_by_student_email, $invited_by_student_name)
	{
		//Student name can be first, full or username - Haven't really decided yet

		$safe_message = sanitize_text($raw_message);

		if ($this->check_if_max_is_reached())
		{
			return "It's not possible to invite new members, since the group is full";
		}

		$answer = $this->save_invite_in_db($student_id, $invited_by_student_id, $safe_message);
		if ($answer === TRUE)
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
				Hi ' . $student_name . '!<br/>You\'ve been invited to join the group \'' . $this->get_name() . '\' by ' . $invited_by_student_name . '.<br/>
				<a href="' . SERVER . BASE . 'group/my-invites/">Click here</a> to go to your invites!.<br/>
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
		$headers .= 'To: <' . $student_email . '>' . "\r\n";
		$headers .= 'cc: <' . $invited_by_student_email . '>' . "\r\n";
		$headers .= 'From: StudyTeam Bot <studyteam@heibosoft.com>' . "\r\n";

		// Mail it
		if (mail($to, $subject, $message, $headers))
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

	public function get_number_of_registered_members($active = TRUE)
	{
		global $dbCon;

		if ($active != TRUE)
		{
			$sql = "SELECT COUNT(*) as members FROM student_group WHERE group_id = ?;";
		}
		else
		{
			$sql = "SELECT COUNT(*) as members FROM student_group WHERE group_id = ? AND active = 1;";
		}
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
		$stmt->bind_param('i', $this->id); //Bind parameters.
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
		if ($this->get_if_student_is_member($student_id))
		{
			return TRUE;
		}
		if ($this->get_if_student_was_member($student_id))
		{
			return $this->re_add_student_in_group($student_id);
		}
		global $dbCon;

		$sql = "INSERT INTO student_group (student_id, group_id, level) VALUES (?, ?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iii', $student_id, $this->id, $level); //Bind parameters.
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
		if (!validate_int($student_id))
		{
			return FALSE;
		}
		$safe_student_id = sanitize_int($student_id);
		if ($this->get_if_student_is_member($safe_student_id) === FALSE)
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
		$stmt->bind_param('ii', $this->id, $safe_student_id); //Bind parameters.
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
	
	private function re_add_student_in_group($student_id)
	{
		if (!validate_int($student_id))
		{
			return FALSE;
		}
		$safe_student_id = sanitize_int($student_id);
		if ($this->get_if_student_is_member($safe_student_id) === TRUE)
		{
			return TRUE;
		}
		if($this->get_if_student_was_member($student_id) === FALSE)
		{
			return FALSE;
		}
		global $dbCon;

		$sql = "UPDATE student_group SET active = 1, join_datetime = CURRENT_TIMESTAMP WHERE group_id = ? AND student_id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->id, $safe_student_id); //Bind parameters.
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

	public function get_array_with_members_and_levels($active = TRUE, $max = 4)
	{
		if ($max !== FALSE)
		{
			if (validate_int($max) != TRUE)
			{
				return "There was an error in the sql syntax. When using a limit, please make sure the limit is an integer";
			}
		}
		global $dbCon;

		$members_and_levels = array();

		if ($active === TRUE)
		{
			if ($max === FALSE)
			{
				$sql = "SELECT student_id, level, join_datetime, active FROM student_group WHERE group_id = ? AND active = 1;";
			}
			else
			{
				$sql = "SELECT student_id, level, join_datetime, active FROM student_group WHERE group_id = ? AND active = 1 LIMIT ?;";
			}
		}
		else
		{
			if ($max === FALSE)
			{
				$sql = "SELECT student_id, level, join_datetime, active FROM student_group WHERE group_id = ?;";
			}
			else
			{
				$sql = "SELECT student_id, level, join_datetime, active FROM student_group WHERE group_id = ? LIMIT ?;";
			}
		}
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		if ($max === FALSE)
		{
			$stmt->bind_param('i', $this->id); //Bind parameters.
		}
		else
		{
			$stmt->bind_param('ii', $this->id, $max); //Bind parameters.
		}
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id, $level, $join_datetime, $active);
		while ($stmt->fetch())
		{
			$member = array();
			$member['level'] = $level;
			$member['joined'] = $join_datetime;
			$member['active'] = $active;
			$members_and_levels[$student_id] = $member;
		}
		if (count($members_and_levels) > 0)
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
		if ($members > 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	public function get_if_student_was_member($student_id)
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS member FROM student_group WHERE student_id = ? AND group_id = ? AND active = 0;";
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
		if ($members > 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	public function check_if_max_is_reached()
	{
		if ($this->get_number_of_registered_members() < $this->max_members)
		{
			return FALSE;
		}
		return TRUE;
	}

	public function update_group($name, $max_size, $category, $description)
	{
		$return_message = "You have updated ";
		$updated_values = 0;
		$safe_name = sanitize_text($name);
		$safe_max_size = sanitize_int($max_size);
		$safe_category = sanitize_int($category);
		$safe_description = $description; //WYSIWYG ALREADY SANITIZED
		if ($safe_name != $this->name)
		{
			if ($this->save_new_group_name($safe_name))
			{
				$this->set_name($safe_name);
				$return_message .= "Group name";
				$updated_values++;
			}
		}
		if ($safe_max_size != $this->max_members)
		{
			if ($this->save_new_group_max_size($safe_max_size))
			{
				$this->set_max_members($safe_max_size);
				if ($updated_values > 0)
				{
					$return_message .= ", ";
				}
				$return_message .= "Maximum number of members";
				$updated_values++;
			}
		}
		if ($safe_category != $this->category_id)
		{
			if ($this->save_new_group_category($safe_category))
			{
				$this->set_category_id($safe_category);
				$this->set_category_values();
				if ($updated_values > 0)
				{
					$return_message .= ", ";
				}
				$return_message .= "Group category";
				$updated_values++;
			}
		}
		if ($safe_description != $this->description)
		{
			if ($this->save_new_group_description($safe_description))
			{
				$this->set_description($safe_description);
				if ($updated_values > 0)
				{
					$return_message .= ", ";
				}
				$return_message .= "Group description";
				$updated_values++;
			}
		}
		if ($updated_values == 0)
		{
			$return_message = "No changes were saved.";
		}
		return $return_message;
	}

	private function save_new_group_description($description)
	{
		global $dbCon;

		$safe_description = sanitize_text($description);

		$sql = "UPDATE `group` SET description = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('si', $safe_description, $this->id); //Bind parameters.
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

	private function save_new_group_name($group_name)
	{
		global $dbCon;

		$safe_name = sanitize_text($group_name);

		$sql = "UPDATE `group` SET name = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('si', $safe_name, $this->id); //Bind parameters.
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

	private function save_new_group_max_size($max_size)
	{
		global $dbCon;

		$safe_max_size = sanitize_int($max_size);

		$sql = "UPDATE `group` SET max_members = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $safe_max_size, $this->id); //Bind parameters.
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

	private function save_new_group_category($category_id)
	{
		global $dbCon;

		$safe_category_id = sanitize_int($category_id);

		$sql = "UPDATE `group` SET category_id = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $safe_category_id, $this->id); //Bind parameters.
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

	public function update_student_level_in_group($student_id, $new_level, $responsible_id)
	{
		if ($student_id === $responsible_id)
		{
			//You can't update your own level..
			return FALSE;
		}
		if ($this->check_if_student_has_owner_rights($responsible_id) === FALSE)
		{
			//Only a owner can update and downgrade members.
			return FALSE;
		}
		$to_admin = FALSE;
		$from_admin = FALSE;
		if ($new_level == 1)
		{
			$from_admin = TRUE;
		}
		elseif ($new_level == 2)
		{
			$to_admin = TRUE;
		}
		if($to_admin === TRUE)
		{
			if($this->check_if_student_has_admin_rights($student_id) === TRUE)
			{
				//Can't give an admin the admin level!
				return FALSE;
			}
			return $this->give_student_admin_rights_in_group($student_id);
		}
		elseif($from_admin === TRUE)
		{
			if($this->check_if_student_has_admin_rights($student_id) !== TRUE)
			{
				//Can't downgrade a student that is not admin
				return FALSE;
			}
			return $this->revoke_admin_rights_from_student_in_group($student_id);
		}
	}

	private function give_student_admin_rights_in_group($student_id)
	{
		if (!validate_int($student_id))
		{
			return FALSE;
		}
		$safe_student_id = sanitize_int($student_id);
		global $dbCon;
		
		$sql = "UPDATE student_group SET level = 2 WHERE student_id = ? AND group_id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $safe_student_id, $this->id); //Bind parameters.
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

	private function revoke_admin_rights_from_student_in_group($student_id)
	{
		if (!validate_int($student_id))
		{
			return FALSE;
		}
		$safe_student_id = sanitize_int($student_id);
		global $dbCon;
		
		$sql = "UPDATE student_group SET level = 1 WHERE student_id = ? AND group_id = ?;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $safe_student_id, $this->id); //Bind parameters.
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

	public function kick_user_from_group($student_id, $responsible_id)
	{
		$responsible_is_allowed_to_do_action = FALSE;
		if($this->check_if_student_has_admin_rights($student_id))
		{
			if($this->check_if_student_has_owner_rights($responsible_id))
			{
				$responsible_is_allowed_to_do_action = TRUE;
			}
		}
		elseif($this->check_if_student_has_owner_rights($student_id))
		{
			//Owners can't be kicked! - yet....
			return FALSE;
		}
		else
		{
			if($this->get_student_level_in_group($student_id) != 1)
			{
				//Somehow the user does not have a correct level.. abort mission....
				return FALSE;
			}
			if($this->check_if_student_has_admin_rights($responsible_id))
			{
				$responsible_is_allowed_to_do_action = TRUE;
			}
			elseif($this->check_if_student_has_owner_rights($responsible_id))
			{
				$responsible_is_allowed_to_do_action = TRUE;
			}
		}
		
		if($responsible_is_allowed_to_do_action === TRUE)
		{
			return $this->remove_student_from_group($student_id);
		}
	}

	private function check_if_student_has_admin_rights($student_id)
	{
		$student_level_in_group = $this->get_student_level_in_group($student_id);
		if ($student_level_in_group == 2)
		{
			return TRUE;
		}
		return FALSE;
	}

	private function check_if_student_has_owner_rights($student_id)
	{
		$student_level_in_group = $this->get_student_level_in_group($student_id);
		if ($student_level_in_group == 3)
		{
			return TRUE;
		}
		return FALSE;
	}

	private function get_student_level_in_group($student_id)
	{
		if (!validate_int($student_id))
		{
			return FALSE;
		}
		$safe_student_id = sanitize_int($student_id);
		global $dbCon;

		$sql = "SELECT level FROM student_group WHERE group_id = ? AND student_id = ? AND active = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->id, $safe_student_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($level);
		$stmt->fetch();
		if (validate_int($level))
		{
			$stmt->close();
			return $level;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}
	
	public function get_posts($limit = NULL)
	{
		global $dbCon;
		
		$post_ids = array();
		
		if($limit === NULL)
		{
			$sql = "SELECT id FROM group_post WHERE group_id = ? ORDER BY time DESC;";
		}
		else
		{
			$sql = "SELECT id FROM group_post WHERE group_id = ? LIMIT ? ORDER BY time DESC;";
		}
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		if($limit === NULL)
		{
			$stmt->bind_param('i', $this->id); //Bind parameters.
		}
		else
		{
			$stmt->bind_param('ii', $this->id, $limit); //Bind parameters.
		}
		$stmt->execute(); //Execute
		$stmt->bind_result($post_id);
		while($stmt->fetch())
		{
			$post_ids[] = $post_id;
		}
		if (count($post_ids) > 0)
		{
			$stmt->close();
			return $post_ids;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
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
		return htmlspecialchars_decode($this->description);
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
