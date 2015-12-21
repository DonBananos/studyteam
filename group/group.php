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