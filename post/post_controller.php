<?php
/*
 * Author: Mike Jensen <mikejensen2@gmail.com>
 * Purpose: StudyTeam (Web Security Exam Project)
 * 
 * This class takes care of Posts in groups, that is not a specific post.
 * So creating new and such is done here!
 */
class Post_controller
{
	/*
	 * The class constructor
	 */
	function __construct()
	{
		
	}
	
	/*
	 * Function to create a new post in the database
	 * 
	 * @param int $student_id		Id of the student creating the new post
	 * @param int $group_id			Id of the group in which the post is posted
	 * @param int $public			1 for public, 0 for private (actually anything but 1 is false)
	 * @param string $post			The message to be posted
	 * @param int $type				1 for regular post, 2 for image post (Username posted a new image in groupname). Default is 1
	 * @param string $img_path		Image Path if type is 2. Default is NULL.
	 * @return string				Error message if validation failed, response from save_post if not
	 */
	public function create_post($student_id, $group_id, $public, $post, $type = 1, $img_path = NULL)
	{
		if($this->validate_post($post) === FALSE)
		{
			return "Empty post. Posting aborted.";
		}
		$safe_post = $this->make_post_safe($post);
		$validation_result = $this->validate_variables($student_id, $group_id, $public);
		if($validation_result === FALSE)
		{
			return "Group Permission Error. Posting aborted.";
		}
		elseif($validation_result === 0)
		{
			$public = $validation_result;
		}
		
		//Check if type is accepted
		if(is_int($type) && ($type === 1 || $type === 2))
		{
			//Type is either 1 or 2 (Regular post or Image post)
		}
		else
		{
			$type = 1; //Set type as 1!
		}
		
		$safe_image_path = NULL;
		if(!empty($img_path))
		{
			if($type !== 2)
			{
				$type = 2;
			}
			if(filter_var($img_path, FILTER_VALIDATE_URL))
			{
				$safe_image_path = sanitize_url($img_path);
			}
			else
			{
				return "There was an error with the uploaded image";
			}
		}
		
		return $this->save_post($student_id, $group_id, $public, $safe_post, $type, $safe_image_path);
	}
	
	/*
	 * Function to check if a student is allowed to post in a group, and if that post
	 * is allowed to be the public/private status it has. If not, the status is changed
	 * to the allowed.
	 * 
	 * @param int $student_id		The id of the student trying to post
	 * @param int $group_id			The id of the group in which the post is intended
	 * @param int $public			1 for public, anything else for private
	 * @return boolean|int			return false if student is not allowed to post, 
	 *								true if everything is ok, and 0 (for private) 
	 *								if student is trying to post public in a private group
	 */
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
	
	/*
	 * Function to check if a specific student has access to post in a specific group
	 * 
	 * @global type $dbCon			mysqli connection
	 * @param int $student_id		Id of the student
	 * @param int $group_id			Id of the group
	 * @return boolean				TRUE if allowed, FALSE if not - or if validation error
	 */
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
	
	/*
	 * Function to check whether or not a post is allowed to be public
	 * Used for all public posts, and check whether or not the group is private
	 * (You're not allowed to make public posts in a private group)
	 * 
	 * @global type $dbCon		mysqli connection
	 * @param int $group_id		Id of the group
	 * @return boolean			TRUE if allowed, FALSE if not or if validation error
	 */
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
	
	/*
	 * Function to validate a post's length
	 * 
	 * @param string $post		post to validate
	 * @return boolean			TRUE if ok, FALSE if not
	 */
	private function validate_post($post)
	{
		if(strlen(trim($post)) > 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	/*
	 * Function to make a post safe for posting
	 * Changes all \n (from textarea) to <br/> and then strips all other html
	 * tags than <br/> (and <br>)
	 * 
	 * @param string $post			The post to make safe
	 * @return string $safe_post	The processed post
	 */
	private function make_post_safe($post)
	{
		//Change all newlines to <br> (HTML breaks)
		$br_post = nl2br($post);
		//Remove all html tags, except <br>
		$safe_post = strip_tags($br_post, '<br>');
		
		return $safe_post;
	}
	
	/**
	 * 
	 * @global type $dbCon				mysqli connection
	 * @param int $student_id			Id of student posting
	 * @param int $group_id				Id of group in which the post is intended
	 * @param int $public				1 if public, 0 (anything else) if private
	 * @param string $post				the post message
	 * @param int $type					1 if regular post, 2 if image post
	 * @param string $image_path		Image path (used for type 2)
	 * @return string error|int id		Error message if failed, Id of post if succeded
	 */
	private function save_post($student_id, $group_id, $public, $post, $type, $image_path)
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
		
		$sql = "INSERT INTO group_post (student_id, group_id, public, post, post_type, img_path) VALUES (?, ?, ?, ?, ?, ?);";
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('iiisis', $safe_student_id, $safe_group_id, $safe_public, $post, $type, $image_path); //Bind parameters.
		$stmt->execute();
		$id = $stmt->insert_id;
		if ($id > 0)
		{
			$stmt->close();
			return $id;
		}
		$error = $stmt->error;
		$stmt->close();
		return $error;
	}
}