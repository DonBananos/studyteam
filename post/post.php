<?php
/*
 * Author: Mike Jensen <mikejensen2@gmail.com>
 * Purpose: StudyTeam (Web Security Exam Project)
 * 
 * This class takes care of all specific posts
 */

class Post
{

	private $id;
	private $student_id;
	private $group_id;
	private $time;
	private $public;
	private $post;
	private $type;
	private $img_path;

	/*
	 * The class constructor
	 * 
	 * @param int $id		The id of the post to construct
	 */

	function __construct($id)
	{
		$this->set_values_with_id($id);
	}

	/*
	 * Function to set all object variables for the specific post
	 * 
	 * @global type $dbCon		mysqli connection
	 * @param int $id			ID of the post
	 * @return boolean			TRUE if success, FALSE if not or validation error
	 */

	private function set_values_with_id($id)
	{
		global $dbCon;

		if (validate_int($id) === FALSE)
		{
			return FALSE;
		}
		$safe_id = sanitize_int($id);

		$sql = "SELECT id, student_id, group_id, time, public, post, post_type, img_path FROM group_post WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $safe_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($id, $student_id, $group_id, $time, $public, $post, $type, $img_path);
		$stmt->fetch();
		$this->set_id($id);
		$this->set_student_id($student_id);
		$this->set_group_id($group_id);
		$this->set_time($time);
		$this->set_public($public);
		$this->set_post($post);
		$this->set_type($type);
		$this->set_img_path($img_path);
		$stmt->close();
		if (validate_int($id) && $id > 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Function to give the post a thumbs up as a given student
	 * 
	 * @global type $dbCon			mysqli connection
	 * @param int $student_id		Id of the student that gives thumbs up
	 * @return boolean				TRUE if success, FALSE if not
	 */
	public function give_thumbs_up($student_id)
	{
		if (validate_int($student_id))
		{
			$safe_student_id = sanitize_int($student_id);
		}
		
		if (!$this->check_if_student_can_interact($safe_student_id))
		{
			return FALSE;
		}
		if ($this->check_if_user_has_given_thumbs_up($safe_student_id))
		{
			return FALSE;
		}
		global $dbCon;
		$sql = "INSERT INTO post_thumbs_up (student_id, post_id) VALUES (?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $safe_student_id, $this->id); //Bind parameters.
		$stmt->execute();
		$id = $stmt->insert_id;
		$stmt->close();
		if ($id > 0)
		{
			return FALSE;
		}
		return FALSE;
	}
	
	public function remove_thumbs_up($student_id)
	{
		if (validate_int($student_id))
		{
			$safe_student_id = sanitize_int($student_id);
		}
		
		if (!$this->check_if_student_can_interact($safe_student_id))
		{
			return FALSE;
		}
		if (!$this->check_if_user_has_given_thumbs_up($safe_student_id))
		{
			return FALSE;
		}
		global $dbCon;
		$sql = "UPDATE post_thumbs_up SET removed = 1 WHERE student_id = ? AND post_id = ? AND removed = 0;";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $safe_student_id, $this->id); //Bind parameters.
		$stmt->execute();
		$id = $stmt->insert_id;
		$stmt->close();
		if ($id > 0)
		{
			return FALSE;
		}
		return FALSE;
	}

	/**
	 * Function that checks if a student can interact with the post
	 * 
	 * @global type $dbCon			mysqli connection
	 * @param int $student_id		ID of the student to check
	 * @return boolean				TRUE if allowed, FALSE if not
	 */
	private function check_if_student_can_interact($student_id)
	{
		global $dbCon;

		$sql = "SELECT COUNT(*) AS memberships FROM student_group WHERE group_id = ? AND student_id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $this->group_id, $student_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($memberships);
		$stmt->fetch();
		$stmt->close();
		if ($memberships > 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Function that checks whether or not a given student has given the post
	 * a thumbs up
	 * 
	 * @param int $student_id		The id of the student to check for
	 * @return boolean				TRUE if student have given, FALSE if not
	 */
	public function check_if_user_has_given_thumbs_up($student_id)
	{
		$all_thumbs_up = $this->get_all_thumbs_up_on_post();
		if (count($all_thumbs_up) > 0)
		{
			if (isset($all_thumbs_up[$student_id]))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Function to retreive all thumbs up on the post
	 * 
	 * @global type $dbCon			mysqli connection
	 * @return array $thumbs_up		array with student_id => time of all thumbs up
	 */
	public function get_all_thumbs_up_on_post()
	{
		global $dbCon;

		$thumbs_up = array();

		$sql = "SELECT student_id, time FROM post_thumbs_up WHERE post_id = ? AND removed = 0 ORDER BY time;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($student_id, $time);
		while ($stmt->fetch())
		{
			$thumbs_up[$student_id] = $time;
		}
		return $thumbs_up;
	}

	public function get_number_of_thumbs_up()
	{
		global $dbCon;

		$sql = "SELECT COUNT(id) AS thumbs_up FROM post_thumbs_up WHERE post_id = ? AND removed = 0;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $this->id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($thumbs_up);
		$stmt->fetch();
		$stmt->close();
		return $thumbs_up;
	}

	public function get_id()
	{
		return $this->id;
	}

	public function get_student_id()
	{
		return $this->student_id;
	}

	public function get_group_id()
	{
		return $this->group_id;
	}

	public function get_time()
	{
		return $this->time;
	}

	public function get_public()
	{
		return $this->public;
	}

	public function get_post()
	{
		return $this->post;
	}

	public function get_type()
	{
		return $this->type;
	}

	public function get_img_path()
	{
		return $this->img_path;
	}

	private function set_id($id)
	{
		$this->id = $id;
	}

	private function set_student_id($student_id)
	{
		$this->student_id = $student_id;
	}

	private function set_group_id($group_id)
	{
		$this->group_id = $group_id;
	}

	private function set_time($time)
	{
		$this->time = $time;
	}

	private function set_public($public)
	{
		$this->public = $public;
	}

	private function set_post($post)
	{
		$this->post = $post;
	}

	private function set_type($type)
	{
		$this->type = $type;
	}

	private function set_img_path($img_path)
	{
		$this->img_path = $img_path;
	}

}
