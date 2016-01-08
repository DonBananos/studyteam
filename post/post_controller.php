<?php

class Post_controller
{
	function __construct()
	{
		
	}
	
	public function create_post($student_id, $group_id, $public, $post)
	{
		if($this->validate_post($post) === FALSE)
		{
			return "Empty post. Posting aborted.";
		}
		$validation_result = $this->validate_variables($student_id, $group_id, $public);
		if($validation_result === FALSE)
		{
			return "Group Permission Error. Posting aborted.";
		}
		elseif($validation_result === 0)
		{
			$public = $validation_result;
		}
		return $this->save_post($student_id, $group_id, $public, $post);
	}
	
	private function validate_variables($student_id, $group_id, $public)
	{
		if($this->check_if_student_has_access_to_group($student_id, $group_id) === FALSE)
		{
			//Student are not allowed to post in the given group....
			return FALSE;
		}
		if($public == 1)
		{
			if($this->check_if_post_can_is_allowed_to_be_public($group_id) === FALSE)
			{
				//post are not allowed to be public.. Return new public value..
				return 0;
			}
		}
		return TRUE;
	}
	
	private function check_if_student_has_access_to_group($student_id, $group_id)
	{
		global $dbCon;
		
		//Validate and Sanitize
		if(!validate_int($student_id))
		{
			return FALSE;
		}
		if(!validate_int($group_id))
		{
			return FALSE;
		}
		$safe_student_id = sanitize_int($student_id);
		$safe_group_id = sanitize_int($group_id);
		
		$sql = "SELECT COUNT(*) AS members FROM student_group WHERE student_id = ? AND group_id = ? AND active = 1;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('ii', $safe_student_id, $safe_group_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($members);
		$stmt->fetch();
		$stmt->close();
		if($members == 1)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	private function check_if_post_can_is_allowed_to_be_public($group_id)
	{
		global $dbCon;
		
		//Validate and Sanitize
		if(!validate_int($group_id))
		{
			return FALSE;
		}
		$safe_group_id = sanitize_int($group_id);
		
		$sql = "SELECT public FROM `group` WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $safe_group_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($public);
		$stmt->fetch();
		$stmt->close();
		if($public == 1)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	private function validate_post($post)
	{
		if(strlen(trim($post)) > 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	private function save_post($student_id, $group_id, $public, $post)
	{
		//Validate and Sanitize
		if(!validate_int($student_id))
		{
			//Return is 0 or FALSE - Both are not good
			return "Student ID is incorrect";
		}
		if(!validate_int($group_id))
		{
			//Return is 0 or FALSE - Both are not good
			return "Group ID is incorrect";
		}
		if(validate_int($public) === FALSE)
		{
			//Return is FALSE - This is not good
			return "There's been an error in the public setting, public is ".$public." - should be integer.";
		}
		$safe_student_id = sanitize_int($student_id);
		$safe_group_id = sanitize_int($group_id);
		$safe_public = sanitize_int($public);
		
		global $dbCon;
		
		$sql = "INSERT INTO group_post (student_id, group_id, public, post) VALUES (?, ?, ?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iiis', $safe_student_id, $safe_group_id, $safe_public, $post); //Bind parameters.
		$stmt->execute();
		$id = $stmt->insert_id;
		if ($id > 0)
		{
			$stmt->close();
			return $id;
		}
		$error = $stmt->error;
		echo $error;
		$stmt->close();
		return $error;
	}
}