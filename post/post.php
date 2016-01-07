<?php

class Post
{
	private $id;
	private $student_id;
	private $group_id;
	private $time;
	private $public;
	private $post;
	
	function __construct($id)
	{
		$this->set_values_with_id($id);
	}
	
	private function set_values_with_id($id)
	{
		global $dbCon;
		
		if(validate_int($id) === FALSE)
		{
			return FALSE;
		}
		$safe_id = sanitize_int($id);
		
		$sql = "SELECT id, student_id, group_id, time, public, post FROM group_post WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $safe_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($id, $student_id, $group_id, $time, $public, $post);
		$stmt->fetch();
		$this->set_id($id);
		$this->set_student_id($student_id);
		$this->set_group_id($group_id);
		$this->set_time($time);
		$this->set_public($public);
		$this->set_post($post);
		if(validate_int($id) && $id > 0)
		{
			return TRUE;
		}
		return FALSE;
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
}