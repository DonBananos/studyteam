<?php

class Group_controller
{
	
	function __construct()
	{
		
	}
	
	public function create_new_group($name, $public, $category_id, $max_members, $user_id, $description)
	{
		$new_group_data = array();
		$new_group_data['name'] = sanitize_text($name);
		$new_group_data['public'] = $public;
		$new_group_data['category'] = sanitize_int($category_id);
		$new_group_data['max'] = sanitize_int($max_members);
		$new_group_data['creator'] = sanitize_int($user_id);
		$new_group_data['desc'] = sanitize_text($description);
		$result = $this->save_new_group($new_group_data);
		return $result;
	}
	
	private function save_new_group($new_group_data)
	{
		global $dbCon;
		$now = time();
		
		$sql = "INSERT INTO studyteam.`group` (`name`, `public`, `max_members`, `creator_student_id`, `description`, `category_id`) VALUES (?, ?, ?, ?, ?, ?);";
		//We Prepare the Statement
		$stmt = $dbCon->prepare($sql);
		if ($stmt === false)
		{
			//Oh no, the statement wasn't prepared correct! Trigger the Error!
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		//So, let's bind the parameters to the prepared statement.
		$stmt->bind_param('siiisi', $new_group_data['name'], 
				$new_group_data['public'], $new_group_data['max'], 
				$new_group_data['creator'], $new_group_data['desc'], $new_group_data['category']); //Bind parameters.
		//Execute the statement
		$stmt->execute();
		//Get the insertet ID (the new Group's ID in the DB)
		$id = $stmt->insert_id;
		//We check if it is an integer higher than 0
		if ($id > 0)
		{
			echo $id;
			//It is, which means that the Group has been created and saved
			$stmt->close();
			return TRUE;
		}
		//Well, since we reached this far, the if statement wasn't executed.
		//Save the error
		$error = $stmt->error;
		echo $error;
		//Close down the statement (good practice)
		$stmt->close();
		return $error;
	}
	
	public function get_category_names_and_ids()
	{
		$categories = array();
		
		global $dbCon;
		
		$sql = "SELECT id, name FROM group_category ORDER BY name ASC;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->execute(); //Execute
		$stmt->bind_result($cat_id, $cat_name); //Get ResultSet
		while ($stmt->fetch())
		{
			$categories[$cat_id] = $cat_name;
		}
		$stmt->close();
		return $categories;
	}
}