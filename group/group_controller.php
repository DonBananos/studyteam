<?php
/*
 * Author: Mike Jensen <mikejensen2@gmail.com>
 * Purpose: StudyTeam (Web Security Exam Project)
 * 
 * This class takes care of Group related stuff, that is not bound to a specific
 * or already excisting group
 */
class Group_controller
{
	/*
	 * Class constructor
	 */
	function __construct()
	{
		
	}
	
	/*
	 * Function to create a new group
	 * 
	 * @param string $name			Name of the group to create
	 * @param int $public			If the group should be public (1) or private (0)
	 * @param int $category_id		Id of the group category (Points to table in DB)
	 * @param int $max_members		Maximum amount of members in a group
	 * @param int $user_id			Id of the user creating the group
	 * @param string $description	Description of the group
	 * @return type					Response from the save_new_group function
	 */
	public function create_new_group($name, $public, $category_id, $max_members, $user_id, $description)
	{
		$new_group_data = array();
		$new_group_data['name'] = sanitize_text($name);
		$new_group_data['public'] = $public;
		$new_group_data['category'] = sanitize_int($category_id);
		$new_group_data['max'] = sanitize_int($max_members);
		$new_group_data['creator'] = sanitize_int($user_id);
		$new_group_data['desc'] = sanitize_text($description); //Already sanitized from WYSIWYG
		$result = $this->save_new_group($new_group_data);
		return $result;
	}
	
	/*
	 * Function to save a new group in the database
	 * 
	 * @global type $dbCon				mysqli connection
	 * @param array $new_group_data		array of data from create_new_group function
	 * @return int id|boolean			id of new group, FALSE if failed
	 */
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
			//It is, which means that the Group has been created and saved
			$stmt->close();
			return $id;
		}
		//Well, since we reached this far, the if statement wasn't executed.
		//Save the error
		$error = $stmt->error;
		//Close down the statement (good practice)
		$stmt->close();
		return FALSE;
	}
	
	/*
	 * Function to get all group_category ids and names from the database
	 * 
	 * @global type $dbCon			mysqli connection
	 * @return array $categories	array of (group_category_id => group_category_name)
	 */
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
	
	/*
	 * Function to validate a seleceted category id
	 * Checks in database if category ID exists
	 *		
	 * @global type $dbCon			mysqli connection
	 * @param int $category_id		ID of the category to search for
	 * @return boolean				TRUE if exists, FALSE if not
	 */
	public function validate_if_category($category_id)
	{
		global $dbCon;
		
		$safe_category_id = sanitize_int($category_id);
		
		$sql = "SELECT COUNT(*) AS categories FROM group_category WHERE id = ?;";
		$stmt = $dbCon->prepare($sql); //Prepare Statement
		if ($stmt === false)
		{
			trigger_error('SQL Error: ' . $dbCon->error, E_USER_ERROR);
		}
		$stmt->bind_param('i', $safe_category_id); //Bind parameters.
		$stmt->execute(); //Execute
		$stmt->bind_result($categories);
		$stmt->fetch();
		$stmt->close();
		if($categories == 1)
		{
			return TRUE;
		}
		return FALSE;
	}
}