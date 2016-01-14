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

	/*
	 * The class constructor
	 */
	function __construct($id)
	{
		$this->set_values_with_id($id);
	}

	/*
	 * This function receives an ID, and creates a student object with that ID
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int id			id of the student to create
	 * @return boolean			TRUE if success
	 * @return string error		string with mysql error message
	 */
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

	/*
	 * Set values, used by the set_values_with_id function.
	 * Calls all set functions in the object
	 * 
	 * @param int id				id of the student in the database
	 * @param string username		the student's username
	 * @param string firstname		the student's firstname
	 * @param string lastname		the student's lastname
	 * @param string email			the student's email
	 * @param string password		the student's hashed password
	 * @param string salt			the student's salt
	 * @param string joined			Timestamp the student joined the application
	 * @param int permission		integer pointing to a permission in the db
	 * @param string fullname		the student's fullname (first + last)
	 * @param int avatar			Number of the avatar the user is displayed with
	 */
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

	/*
	 * Function to change a password for the student
	 * Used for password reset
	 * 
	 * @param string password		the new password, from the reset password form
	 * @return function return		value depends on save_new_password() function
	 */
	public function change_password($password)
	{
		$salt_to_use = $this->get_salt() . SALT;

		$hashed_pass = hash_hmac('sha512', $password, $salt_to_use);

		return $this->save_new_password($hashed_pass);
	}

	/*
	 * Function to save a new password for a user
	 * Used in change_password function
	 * 
	 * @global type dbCon			mysqli connection
	 * @param string password		The password to save as new password
	 * @return boolean				TRUE if success
	 * @return string error			error from mysql
	 */
	private function save_new_password($password)
	{
		global $dbCon;

		$sql = "UPDATE student SET password = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('si', $password, $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		if ($stmt->affected_rows > 0)
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

	/*
	 * Function to get all group ids that a student has created
	 * Was used in prior stage to show groups, can be used later for overview
	 * 
	 * @global type dbCon			mysqli connection
	 * @return array group_ids		Array of all the group ids
	 * @return boolean				FALSE if fail or no groups
	 */
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
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($group_id);
		while ($stmt->fetch())
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

	/*
	 * Function to get all group ids that the user is part of
	 * Used on e.g. the group overview page and in the main feed
	 * 
	 * @global type dbCon			mysqli connection
	 * @param int max				maximum number of groups, default is NULL
	 * @return array group_ids		Array of all the group ids
	 * @return boolean				FALSE if fail or no groups
	 */
	public function get_group_ids_that_student_is_part_of($max = null)
	{
		global $dbCon;

		$group_ids = array();
		if ($max !== null)
		{
			$safe_max = sanitize_int($max);
			$sql = "SELECT group_id FROM student_group WHERE student_id = ? AND active = 1 ORDER BY join_datetime DESC LIMIT ?;";
			$stmt = $dbCon->prepare($sql); //Prepare Statement
			if ($stmt === false)
			{
				trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
			}
			$stmt->bind_param('ii', $this->id, $safe_max); //Bind parameters.
		}
		else
		{
			$sql = "SELECT group_id FROM student_group WHERE student_id = ? AND active = 1 ORDER BY join_datetime DESC;";
			$stmt = $dbCon->prepare($sql); //Prepare Statement
			if ($stmt === false)
			{
				trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
			}
			$stmt->bind_param('i', $this->id); //Bind parameters.
		}
		$stmt->execute(); //Execute
		$stmt->bind_result($group_id);
		while ($stmt->fetch())
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

	/*
	 * Function to get all public groups that the student did not create
	 * Used in prior stage, could possibly be used later on
	 * 
	 * @global type dbCon			mysqli connection
	 * @return array group_ids		Array of all the group ids
	 * @return boolean				FALSE if fail or no groups
	 */
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
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($group_id);
		while ($stmt->fetch())
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
	
	/*
	 * Function used to get all public group ids that student is not part of
	 * Used for suggesting new groups, on multiple pages
	 * 
	 * @global type dbCon			mysqli connection
	 * @param int limit				Limit of group ids to get, default is NULL
	 * @return array group_ids		Array of all the group ids
	 * @return boolean				FALSE if fail or no groups
	 */
	public function get_public_groups_where_student_is_not_member($limit = NULL)
	{
		global $dbCon;
		$group_ids = array();

		if(!validate_int($limit))
		{
			$limit = NULL;
		}
		
		if(!empty($limit))
		{
			$sql = "SELECT id FROM `group` WHERE id NOT IN (SELECT group_id FROM student_group WHERE student_id = ? AND active = 1) AND public = 1 LIMIT $limit;";
		}
		else
		{
			$sql = "SELECT id FROM `group` WHERE id NOT IN (SELECT group_id FROM student_group WHERE student_id = ? AND active = 1) AND public = 1;";
		}
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($group_id);
		while ($stmt->fetch())
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
	
	/*
	 * Function to get a specific number of suggestions
	 * Used in feed to suggest groups for the student
	 * The function get all public groups where the student is not a member
	 * and randomly selects the limit (if there's more or the same amount), or 
	 * returns all the possible groups
	 * 
	 * @param int limit				maximum number of group suggestions
	 * @return array suggestions	an array of suggested group ids
	 */
	public function get_group_suggestions_for_feed($limit)
	{
		$all_suggested_groups = $this->get_public_groups_where_student_is_not_member();
		$number_of_suggestions = count($all_suggested_groups);
		if($number_of_suggestions > $limit)
		{
			$random_suggestions = array_rand($all_suggested_groups, $limit);
			$suggestions = array();
			for($i = 0; $i < $limit; $i++)
			{
				$suggestions[] = $all_suggested_groups[$random_suggestions[$i]];
			}
		}
		else
		{
			$suggestions = $all_suggested_groups;
		}
		return $suggestions;
	}

	/*
	 * Function to get the path of the students avatar
	 * (for displaying purposes)
	 * 
	 * @return string		location of the students current avatar on the server
	 */
	public function get_avatar()
	{
		return AVATAR_LOCATION . $this->get_avatar_number() . '.png';
	}

	/*
	 * Function to apply a user to become buddies
	 * This function is performed on the object of the target student
	 * 
	 * @global type dbCon				mysqli connection
	 * @param int applier_student_id	id of the student applying to become buddies
	 * @return boolean					TRUE if success
	 * @return string error				error from mysql
	 */
	public function apply_for_buddies($applier_student_id)
	{
		global $dbCon;

		$sql = "INSERT INTO buddy (buddy_1_student_id, buddy_2_student_id) VALUES (?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $applier_student_id, $this->id); //Bind parameters.
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

	/*
	 * Function to check whether or not two students are buddies
	 * Can be done from either of the accounts.
	 * 
	 * @global type dbCon				mysqli connection
	 * @param int other_student_id		Id of the student that is not the object
	 * @return boolean					TRUE if they are buddies, FALSE if not
	 */
	public function check_if_buddies($other_student_id)
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS buddies FROM buddy WHERE ((buddy_1_student_id = ? AND buddy_2_student_id = ?) OR (buddy_2_student_id = ? AND buddy_1_student_id = ?)) AND buddy_status = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iiii', $other_student_id, $this->id, $other_student_id, $this->id); //Bind parameters.
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

	/*
	 * Function to check whether or not two students have a pending buddy invite
	 * Used to see if a "Apply for buddies" should be clickable or not.
	 * Can be done from either of the accounts.
	 * 
	 * @global type dbCon				mysqli connection
	 * @param int other_student_id		Id of the student that is not the object
	 * @return boolean					TRUE if buddies is pending, FALSE if not
	 */
	public function check_if_buddies_pending($other_student_id)
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS buddies FROM buddy WHERE (buddy_1_student_id = ? AND buddy_2_student_id = ?) OR (buddy_2_student_id = ? AND buddy_1_student_id = ?) AND buddy_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iiii', $other_student_id, $this->id, $other_student_id, $this->id); //Bind parameters.
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

	/*
	 * Function to get the number of current pending buddy invites for the student
	 * 
	 * @global type dbCon		mysqli connection
	 * @return int pending		Number of pending buddy invites
	 */
	public function get_number_of_buddies_pending()
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS pending FROM buddy WHERE buddy_2_student_id = ? AND buddy_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
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

	/*
	 * Function to get the number of current buddies for the student
	 * 
	 * @global type dbCon		mysqli connection
	 * @return int buddies		Number of current buddies
	 */
	public function get_number_of_buddies()
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS buddies FROM buddy WHERE (buddy_2_student_id = ? OR buddy_1_student_id = ?) AND buddy_status = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->id, $this->id); //Bind parameters.
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

	/*
	 * Function to get all the current students buddys' ids
	 * 
	 * @global type dbCon			mysqli connection
	 * @return array buddies		An array filled with the buddy ids
	 */
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
		$stmt->bind_param('ii', $this->id, $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddy_1_id, $buddy_2_id);
		while ($stmt->fetch())
		{
			if ($buddy_1_id == $this->get_id())
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

	/*
	 * Function to get all the ids of buddies currently pending
	 * 
	 * @global type dbCon			mysqli connection
	 * @return array pending		array of buddy ids currently pending
	 */
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
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddy_1_id);
		while ($stmt->fetch())
		{
			$pending[] = $buddy_1_id;
		}
		$stmt->close();
		return $pending;
	}

	/*
	 * Function to accept a buddy invite
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int buddy_id		ID of the buddy to accept an invite from
	 * @return boolean			true if success
	 * @return string error		error from mysql
	 */
	public function accept_buddy_pending($buddy_id)
	{
		global $dbCon;

		$sql = "UPDATE buddy SET buddy_status = 1 WHERE buddy_2_student_id = ? AND buddy_1_student_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->id, $buddy_id); //Bind parameters.
		$stmt->execute(); //Execute
		if ($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}

	/*
	 * Function to decline a buddy invite
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int buddy_id		ID of the buddy to decline an invite from
	 * @return boolean			true if success
	 * @return string error		error from mysql
	 */
	public function decline_buddy_pending($buddy_id)
	{
		global $dbCon;

		$sql = "UPDATE buddy SET buddy_status = 2 WHERE buddy_2_student_id = ? AND buddy_1_student_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->id, $buddy_id); //Bind parameters.
		$stmt->execute(); //Execute
		if ($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}

	/*
	 * Function to save a new selected avartar
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int avatar_id		number of the new avatar from the form
	 * @return boolean			TRUE if success, FALSE if validation error
	 * @return string error		error from mysql
	 */
	public function save_new_avatar($avatar_id)
	{
		global $dbCon;

		//Sanitize and check if is integer!
		$safe_avatar_id = sanitize_int($avatar_id);
		if (!validate_int($safe_avatar_id))
		{
			return FALSE;
		}

		$sql = "UPDATE student SET avatar = ? WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $avatar_id, $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		if ($stmt->affected_rows > 0)
		{
			$stmt->close();
			$this->set_values_with_id($this->id);
			return true;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}

	/*
	 * Function to get all ids of buddies, which can be invited to a group
	 * Used in invite modal on a specific group
	 * 
	 * @param int group_id					Id of the group student want to invite to
	 * @return array possible_invites		Array of all ids of buddies that is able to be invited
	 */
	public function get_buddies_for_possible_invite_for_group($group_id)
	{
		$possible_invites = $this->get_buddy_ids_for_group_that_can_be_invited($group_id);
		return $possible_invites;
	}

	/*
	 * Function to check whether or not a student can invite others to a specific group
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int group_id		ID of the group to check if student can invite to
	 * @return boolean			TRUE if student can invite others, FALSE on validation error, 
	 *							if student is not part of group or the student does not have
	 *							the correct permission level in the group
	 */
	public function get_if_student_can_invite_in_group($group_id)
	{
		global $dbCon;
		//Sanitize and check if is integer!
		$safe_group_id = sanitize_int($group_id);
		if (!validate_int($safe_group_id))
		{
			return FALSE;
		}
		//Check if student is even part of the group he/she is trying to invite members to
		if (!$this->get_if_student_is_part_of_group($safe_group_id))
		{
			return FALSE;
		}
		$sql = "SELECT level FROM student_group WHERE student_id = ? AND group_id = ? AND active = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->id, $safe_group_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($level);
		$stmt->fetch();
		$stmt->close();
		if ($level == 2 || $level == 3)
		{
			return TRUE;
		}
		return FALSE;
	}

	/*
	 * Function to check if a student is part of a group or not
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int group_id		Id of the group, where the user might be member
	 * $return boolean			TRUE if the student is member, FALSE if not
	 */
	public function get_if_student_is_part_of_group($group_id)
	{
		$all_groups_of_student = $this->get_group_ids_that_student_is_part_of();
		if(is_array($all_groups_of_student))
		{
			if (in_array($group_id, $all_groups_of_student))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/*
	 * Private function to select all the student buddies, which are possible to invite to a specific group
	 * Used by get_buddies_for_possible_invite_for_group() function
	 * 
	 * @global type dbCon					mysqli connection
	 * @param int group_id					ID of the group within the database
	 * @return array possible_buddy_ids		Array of all the ids of buddies that can be invited to the group
	 */
	private function get_buddy_ids_for_group_that_can_be_invited($group_id)
	{
		$possible_buddy_ids = array();
		$buddy_ids = $this->get_all_buddy_ids();
		foreach ($buddy_ids as $buddy_id)
		{
			if ($this->check_if_buddy_is_part_of_group($buddy_id, $group_id) === FALSE)
			{
				$possible_buddy_ids[] = $buddy_id;
			}
		}
		return $possible_buddy_ids;
	}

	/*
	 * Function to check whether or not a buddy is part of a group
	 * Used to see if the buddy can be invited or not
	 * Created as SELECT COUNT for performance
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int buddy_id		ID of the buddy to check on
	 * @param int group_id		ID of the group to check on
	 * @return boolean			TRUE if buddy is member, FALSE if not
	 */
	private function check_if_buddy_is_part_of_group($buddy_id, $group_id)
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS members FROM student_group WHERE group_id = ? AND student_id = ? AND active = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', sanitize_int($group_id), sanitize_int($buddy_id)); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($buddies);
		$stmt->fetch();
		if ($buddies > 0)
		{
			if ($buddies != 1)
			{
				//There's something wrong here!
				//We're not doing anything about it at the moment though!
			}
			$stmt->close();
			return TRUE;
		}
		$stmt->close();
		return FALSE;
	}

	/*
	 * Check if the current user has an invite pending for a specific group
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int group_id		ID of the group checked on
	 * @return boolean			TRUE if invite is pending, FALSE if not
	 */
	public function check_if_invite_for_group_is_pending($group_id)
	{
		global $dbCon;

		$sql = "SELECT COUNT(id) AS ids FROM group_invites WHERE student_id = ? AND group_id = ? AND response_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', sanitize_int($this->id), sanitize_int($group_id)); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($ids);
		$stmt->fetch();
		if ($ids > 0)
		{
			$stmt->close();
			return TRUE;
		}
		$stmt->close();
		return FALSE;
	}
	
	/*
	 * Function to get the current number of pending group invites for the student
	 * 
	 * @global type dbCon		mysqli connection
	 * @return int pending		number of pending invites
	 */
	public function get_number_of_pending_invites()
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS pending FROM group_invites WHERE student_id = ? AND response_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
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

	/*
	 * Function to get all pending group invites
	 * 
	 * @global type dbCon		mysqli connection
	 * @return array invites	Array of invites with invite_id = array('group_id', 'invitor_id', 'message', 'time')
	 */
	public function get_all_pending_invites()
	{
		global $dbCon;

		$invites = array();

		$sql = "SELECT id, group_id, invitor_id, message, time FROM group_invites WHERE student_id = ? AND response_status = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($id, $group_id, $invitor_id, $message, $time);
		while ($stmt->fetch())
		{
			$invite = array();
			$invite['group_id'] = $group_id;
			$invite['invitor_id'] = $invitor_id;
			$invite['message'] = $message;
			$invite['time'] = $time;
			$invites[$id] = $invite;
		}
		$stmt->close();
		return $invites;
	}

	/*
	 * Function changes invite in db to accepted
	 * 
	 * @global type dbCon		mysqli connection
	 * @param type invite_id	id of invite in database
	 * @return boolean			True if success
	 * @return string error		error from mysql
	 */
	public function accept_pending_invite($invite_id)
	{
		global $dbCon;

		$sql = "UPDATE group_invites SET response_status = 1 WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $invite_id); //Bind parameters.
		$stmt->execute(); //Execute
		if ($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}

	/*
	 * Function changes invite in db to declined
	 * 
	 * @global type dbCon		mysqli connection
	 * @param type invite_id	id of invite in database
	 * @return boolean			True if success
	 * @return string error		error from mysql
	 */
	public function decline_pending_invite($invite_id)
	{
		global $dbCon;

		$sql = "UPDATE group_invites SET response_status = 2 WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $invite_id); //Bind parameters.
		$stmt->execute(); //Execute
		if ($stmt->affected_rows > 0)
		{
			$stmt->close();
			return true;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}

	/*
	 * Function to get the current student's permission level in a specific group
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int group_id		ID of the specific group
	 * @return boolean			FALSE on validation error, student is not part of group
	 *							or student level is incorrect filled in db
	 * @return int level		permission level of student in group
	 */
	public function get_student_level_in_group($group_id)
	{
		global $dbCon;

		//Sanitize and check if is integer!
		$safe_group_id = sanitize_int($group_id);
		if (!validate_int($safe_group_id))
		{
			return FALSE;
		}
		//Check if student is even part of the group he/she is trying to invite members to
		if (!$this->get_if_student_is_part_of_group($safe_group_id))
		{
			return FALSE;
		}
		$sql = "SELECT level FROM student_group WHERE student_id = ? AND group_id = ? AND active = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->id, $safe_group_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($level);
		$stmt->fetch();
		$stmt->close();
		if ($level > 0 && validate_int($level))
		{
			return $level;
		}
		return FALSE;
	}
	
	/*
	 * Function to get all posts that should be displayed in the member feed
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int limit			Limit of posts to select, default is NULL
	 * @return array posts		Array of posts to show as post_id, post_time, post_type, img_path, post_public, post_content, student_id, group_id, group_name
	 * @return boolean			FALSE if no posts to return
	 */
	public function get_posts_for_member_feed($limit = NULL)
	{
		global $dbCon;
		
		$posts = array();
		
		if(!validate_int($limit))
		{
			$limit = NULL;
		}
		
		if(!empty($limit))
		{
			$sql = "SELECT group_post.id, group_post.`time`, group_post.post_type, group_post.img_path, group_post.public, group_post.post, student.id, `group`.id, `group`.`name` FROM group_post INNER JOIN `group` ON group_post.group_id = `group`.id INNER JOIN student ON student_id = student.id WHERE group_id IN (SELECT student_group.group_id FROM student_group WHERE student_group.student_id = ? AND student_group.active = 1) AND removed = 0 ORDER BY time DESC LIMIT $limit;";
		}
		else
		{
			$sql = "SELECT group_post.id, group_post.`time`, group_post.post_type, group_post.img_path, group_post.public, group_post.post, student.id, `group`.id, `group`.`name` FROM group_post INNER JOIN `group` ON group_post.group_id = `group`.id INNER JOIN student ON student_id = student.id WHERE group_id IN (SELECT student_group.group_id FROM student_group WHERE student_group.student_id = ? AND student_group.active = 1) AND removed = 0 ORDER BY time DESC;";
		}
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($post_id, $post_time, $post_type, $img_path, $post_public, $post_content, $student_id, $group_id, $group_name);
		while($stmt->fetch())
		{
			$post = array();
			$post['post_id'] = $post_id;
			$post['post_time'] = $post_time;
			$post['post_type'] = $post_type;
			$post['img_path'] = $img_path;
			$post['post_public'] = $post_public;
			$post['post_content'] = $post_content;
			$post['student_id'] = $student_id;
			$post['group_id'] = $group_id;
			$post['group_name'] = $group_name;
			$posts[] = $post;
		}
		$stmt->close();
		if (count($posts) > 0)
		{
			return $posts;
		}
		return FALSE;
	}
	
	/*
	 * Function to get all ids for posts to show in member feed
	 * Should be used together with the post object for each post
	 * Old version, which isn't friendly with performance.
	 * Nostalgia for the win, though!
	 * 
	 * @global type dbCon		mysqli connection
	 * @param int limit			Limit of posts to select, default is NULL
	 * @return array post_ids	Array of post_ids that should be shown in feed
	 * @return boolean			FALSE if no posts to return
	 */
	public function get_post_ids_for_member_feed($limit = NULL)
	{
		global $dbCon;
		
		$post_ids = array();
		
		if(!validate_int($limit))
		{
			$limit = NULL;
		}
		
		if(!empty($limit))
		{
			$sql = "SELECT id FROM group_post WHERE group_id IN (SELECT group_id FROM student_group WHERE student_id = ? AND active = 1) AND removed = 0 ORDER BY time DESC LIMIT $limit;";
		}
		else
		{
			$sql = "SELECT id FROM group_post WHERE group_id IN (SELECT group_id FROM student_group WHERE student_id = ? AND active = 1) AND removed = 0 ORDER BY time DESC;";
		}
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($post_id);
		while($stmt->fetch())
		{
			$post_ids[] = $post_id;
		}
		$stmt->close();
		if (count($post_ids) > 0)
		{
			return $post_ids;
		}
		return FALSE;
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