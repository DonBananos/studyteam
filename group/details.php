<?php
require_once '../includes/configuration.php';
require_once '../student/student.php';
require_once './group.php';
require_once './group_controller.php';
require_once '../post/post_controller.php';
require_once '../post/post.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['logged_in']) OR ! isset($_GET['id']))
{
	$_SESSION['tried_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);

$group = new Group($_GET['id']);
$gc = new Group_controller();
$pc = new Post_controller();

$membership = FALSE;
$edited = FALSE;
$l_answer = FALSE;
$k_answer = FALSE;
if ($group->get_if_student_is_member($student->get_id()))
{
	$membership = TRUE;
	if (isset($_POST['leave']))
	{
		$group->remove_student_from_group($student->get_id());
		$membership = FALSE;
	}
	elseif (isset($_POST['invite']))
	{
		if (validate_int($_POST['buddy']))
		{
			$raw_student_id = $_POST['buddy'];
			$safe_student_id = sanitize_int($raw_student_id);
			$safe_message = sanitize_text($_POST['message']);
			$invite_student = new Student($safe_student_id);
			$answer = $group->invite_student($safe_student_id, $student->get_id(), $safe_message, $invite_student->get_email(), $invite_student->get_fullname(), $student->get_email(), $student->get_fullname());
		}
		else
		{
			$answer = "Something went wrong. Please reload page and try again!";
		}
		?>
		<script>alert("<?php echo $answer ?>");</script>
		<?php
	}
	elseif (isset($_POST['edit']))
	{
		$student_level_in_group = $student->get_student_level_in_group($group->get_id());
		if ($student_level_in_group === 2 || $student_level_in_group === 3)
		{
			$safe_name = sanitize_text($_POST['editGroupName']);
			$safe_max_size = sanitize_int($_POST['editGroupSize']);
			$safe_category = sanitize_int($_POST['editGroupCategory']);
			$safe_description = sanitize_text($_POST['editGroupDescription']);
			if ($student_level_in_group === 2)
			{
				$safe_name = $group->get_name();
				$safe_max_size = $group->get_max_members();
				$safe_category = $group->get_category_id();
				$edit_message = $group->update_group($safe_name, $safe_max_size, $safe_category, $safe_description);
				$edited = TRUE;
			}
			elseif ($student_level_in_group === 3)
			{
				if ($gc->validate_if_category($safe_category) === FALSE)
				{
					$safe_category = $group->get_category_id();
				}
				$edit_message = $group->update_group($safe_name, $safe_max_size, $safe_category, $safe_description);
				$edited = TRUE;
			}
		}
	}
	if (isset($_POST['madmin']))
	{
		//Make the given user admin in group!

		$user_to_be_admin = $_POST['sid'];
		if (validate_int($user_to_be_admin))
		{
			$l_answer = $group->update_student_level_in_group($user_to_be_admin, 2, $student->get_id());
		}
		if ($l_answer === TRUE)
		{
			$new_admin = new Student($user_to_be_admin);
			$level_message = $new_admin->get_fullname() . " is now an Administrator.";
		}
	}
	elseif (isset($_POST['dadmin']))
	{
		//remove the admin rights from a given user!

		$user_to_be_member = $_POST['sid'];
		if (validate_int($user_to_be_member))
		{
			$l_answer = $group->update_student_level_in_group($user_to_be_member, 1, $student->get_id());
		}
		if ($l_answer === TRUE)
		{
			$old_admin = new Student($user_to_be_member);
			$level_message = $old_admin->get_fullname() . " is no longer an Administrator.";
		}
	}
	elseif (isset($_POST['kuser']))
	{
		//kick the user from the group

		$user_to_be_kicked = $_POST['sid'];
		if (validate_int($user_to_be_kicked))
		{
			$k_answer = $group->kick_user_from_group($user_to_be_kicked, $student->get_id());
		}
		if ($k_answer === TRUE)
		{
			$kicked_student = new Student($user_to_be_kicked);
			$kicked_message = $kicked_student->get_fullname() . " is no longer part of the group.";
		}
	}
}
else
{
	if (isset($_POST['join']))
	{
		$group->add_student_to_group($student->get_id());
		$membership = TRUE;
	}
}
if ($membership === FALSE && $group->get_public() == 0)
{
	?>
	<script>
		window.location = "<?php echo BASE ?>group/";
	</script>
	<?php
	die();
}
if (isset($_POST['post-message']))
{
	$public = 0;
	$student_id = $student->get_id();
	$group_id = $group->get_id();
	if ($group->get_public() == 1)
	{
		if (isset($_POST['post-privacy']))
		{
			$public = sanitize_int($_POST['post-privacy']);
		}
	}
	$post = sanitize_text($_POST['post-text-message']);

	$post_result = $pc->create_post($student_id, $group_id, $public, $post);
	if (!validate_int($post_result))
	{
		?>
		<script>alert("Error: <?php echo $post_result ?>");</script>
		<?php
	}
}
elseif (isset($_POST['post-image-message']))
{
	$public = 0;
	$student_id = $student->get_id();
	$group_id = $group->get_id();
	if ($group->get_public() == 1)
	{
		if (isset($_POST['post-image-privacy']))
		{
			$public = sanitize_int($_POST['post-image-privacy']);
		}
	}
	$post = sanitize_text($_POST['post-image-text-message']);

	$image_path = upload_image(950);
	if ($image_path === FALSE)
	{
		//Sikke en bandit!
	}
	else
	{
		$post .= "<p><img src='" . $image_path . "' alt='" . $group->get_name() . " image upload'/></p>";

		$post_result = $pc->create_post($student_id, $group_id, $public, $post);
		if (!validate_int($post_result))
		{
			?>
			<script>alert("Error: <?php echo $post_result ?>");</script>
			<?php
		}
	}
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $group->get_name() ?> | StudyTeam</title>
		<?php require '../includes/header.php'; ?>
    </head>
    <body>
		<?php
		require '../includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="page-header">
									<div class="group-cover" style="background-image: url(<?php echo $group->get_category_image() ?>); background-size: cover; background-position: center">
										<h1 class="group-cover-header"><?php echo $group->get_name() ?></h1>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
								<?php
								if ($membership === TRUE)
								{
									?>
									<div class="content-box">
										<div class="post-box">
											<!-- Nav tabs -->
											<ul class="nav nav-tabs" role="tablist">
												<li role="presentation" class="active"><a href="#message" aria-controls="message" role="tab" data-toggle="tab"><span class="fa fa-pencil-square-o"></span> Message</a></li>
												<li role="presentation"><a href="#picture" aria-controls="picture" role="tab" data-toggle="tab"><span class="fa fa-image"></span> Picture</a></li>
											</ul>
											<!-- Tab panes -->
											<div class="tab-content group-post-tab-content">
												<div role="tabpanel" class="tab-pane active" id="message">
													<form action="" method="POST" id="post-message-form">
														<textarea class="form-control textarea" id="post-textarea" name="post-text-message" required="required"></textarea>
														<div class="clearfix"></div>
														<div class="post-options pull-right">
															<div title="Choose whether the post is visible for non-members or not">
																<?php
																if ($group->get_public() == 1)
																{
																	?>
																	<label class="radio-inline">
																		<input type="radio" name="post-privacy" id="post-privacy-public" value="1" checked="checked">Public
																	</label>
																	<label class="radio-inline">
																		<input type="radio" name="post-privacy" id="post-privacy-private" value="0">Private
																	</label>
																	<?php
																}
																else
																{
																	//Posts can only be private, since the group is private..
																}
																?>
															</div>
															<button class="btn btn-primary" name="post-message" type="submit" id="post-message-button">Post</button>
														</div>
														<div class="clearfix"></div>
													</form>
												</div>
												<div role="tabpanel" class="tab-pane" id="picture">
													<form action="" method="POST" enctype="multipart/form-data">
														<span class="btn btn-default btn-file btn-primary">
															Browse <input type="file" name="imageFile" required="required">
														</span>
														<textarea class="form-control textarea" id="post-image-textarea" name="post-image-text-message" required="required"></textarea>
														<div class="clearfix"></div>
														<div class="post-options pull-right">
															<div title="Choose whether the post is visible for non-members or not">
																<?php
																if ($group->get_public() == 1)
																{
																	?>
																	<label class="radio-inline">
																		<input type="radio" name="post-image-privacy" id="post-image-privacy-public" value="1" checked="checked">Public
																	</label>
																	<label class="radio-inline">
																		<input type="radio" name="post-image-privacy" id="post-image-privacy-private" value="0">Private
																	</label>
																	<?php
																}
																else
																{
																	//Posts can only be private, since the group is private..
																}
																?>
															</div>
															<button class="btn btn-primary" name="post-image-message" type="submit" id="post-image-message-button">Post</button>
														</div>
														<div class="clearfix"></div>
													</form>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								$all_post_ids = $group->get_posts();
								if (is_array($all_post_ids) && count($all_post_ids) > 0)
								{
									foreach ($all_post_ids as $post_id)
									{
										$post = new Post($post_id);
										$poster = new Student($post->get_student_id());
										if ($membership === FALSE && $post->get_public() === 0)
										{
											//Dont show
										}
										else
										{
											?>
											<div class="content-box" id="post_<?php echo $post_id ?>">
												<div class="row">
													<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
														<img src="<?php echo $poster->get_avatar() ?>" class="student-avatar-thumb">
														<div class="post-header">
															<a href="<?php echo BASE ?>student/<?php echo strtolower($poster->get_username()) ?>/"><?php echo $poster->get_username() ?></a>
														</div>
														<div class="post-meta">
															<?php
															if ($group->get_public() === 1)
															{
																if ($post->get_public() === 1)
																{
																	?>
																	<span class="fa fa-unlock"></span>
																	<?php
																}
																else
																{
																	?>
																	<span class="fa fa-lock"></span>
																	<?php
																}
															}
															echo date("Y-m-d H:i", strtotime($post->get_time()));
															?>
														</div>
														<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
															<div class="post-content">
																<?php echo $post->get_post() ?>
															</div>
														</div>
													</div>
												</div>
											</div>
											<?php
										}
									}
								}
								$group_creator = new Student($group->get_creator_student_id());
								if ($group_creator->get_id() !== null && $group_creator->get_id() > 0)
								{
									?>
									<div class="content-box" id="post_c">
										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
												<img src="<?php echo $group_creator->get_avatar() ?>" class="student-avatar-thumb">
												<div class="post-header">
													<a href="<?php echo BASE ?>student/<?php echo strtolower($group_creator->get_username()) ?>/"><?php echo $group_creator->get_username() ?></a> created the group
												</div>
												<div class="post-meta">
													<?php echo date("Y-m-d H:i", strtotime($group->get_created_time())); ?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								else
								{
									?>
									<div class="content-box" id="post_c">
										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
												<div class="post-header">
													Group created
												</div>
												<div class="post-meta">
													<?php echo date("Y-m-d H:i", strtotime($group->get_created_time())); ?>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								?>
							</div>
							<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
								<div class="content-box">
									<div class="group-admin-options">
										<?php
										if ($membership)
										{
											if (($student->get_if_student_can_invite_in_group($group->get_id()) || $group->get_public() === 1) && $group->check_if_max_is_reached() === FALSE)
											{
												?>
												<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#inviteModal"><span class="fa fa-plus"></span> Invite</button>
												<?php
											}
											?>
											<button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#leaveModal"><span class="fa fa-sign-out"></span> Leave</button>
											<?php
											$student_level_in_group = $student->get_student_level_in_group($group->get_id());
											if ($student_level_in_group === 2 || $student_level_in_group === 3)
											{
												//If student level is 2 (admin) or 3 (owner)
												?>
												<button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal"><span class="fa fa-pencil"></span> Edit</button>
												<?php
											}
										}
										else
										{
											if ($group->check_if_max_is_reached() === FALSE)
											{
												?>
												<form action="" method="POST"><button class="btn btn-primary btn-sm" type="submit" name="join"><span class="fa fa-plus"></span> Join Group</button></form>
												<?php
											}
										}
										?>
									</div>
									<hr class="minor-line">
									<div class="group-info">
										<?php echo $group->get_public_or_private() ?> group<br/>
										<?php echo $group->get_number_of_registered_members() ?> / <?php echo $group->get_max_members() ?> members<br/>
										Category: <?php echo $group->get_category_name() ?><br/>
									</div>
									<hr class="minor-line">
									<?php echo $group->get_description(); ?>
									<hr class="minor-line">
									<?php
									$num_pending_invites = $group->get_number_of_pending_invites();
									if ($num_pending_invites > 0)
									{
										?>
										<button class="btn btn-default btn-sm"><span class="fa fa-plus-circle"></span> Invites</button>
										<?php
									}
									?>
								</div>
								<div class="content-box">
									<h3 class="info-headline">Members</h3>
									<hr class="minor-line">
									<?php
									$members_array = $group->get_array_with_members_and_levels();
									if (is_array($members_array))
									{
										foreach ($members_array as $student_id => $member_data)
										{
											$member = new Student($student_id);
											?>
											<div class="row">
												<a href="<?php echo BASE ?>student/<?php echo strtolower($member->get_username()); ?>">
													<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
														<img src="<?php echo $member->get_avatar() ?>" class="student-avatar">
													</div>
													<div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
														<h4 class="student-name">
															<?php echo $member->get_username() ?>
														</h4>
														<span class="student-info">
															<?php echo get_member_level_name_from_level($member_data['level']) ?>
														</span>
													</div>
												</a>
											</div>
											<hr class="minor-line">
											<?php
										}
									}
									?>
									<div class="group-admin-options no-btm-pad">
										<button class="btn btn-default btn-sm" data-toggle="modal" data-target="#membersModal"><span class="fa fa-group"></span> All Members</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		if ($membership)
		{
			if (($student->get_if_student_can_invite_in_group($group->get_id()) || $group->get_public() === 1) && $group->check_if_max_is_reached() === FALSE)
			{
				?>
				<!-- Invite Modal -->
				<div class="modal fade modal-inverse" id="inviteModal" tabindex="-1" role="dialog" aria-labelledby="inviteModalLabel">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="inviteModalLabel">Invite a Buddy to join <?php echo $group->get_name() ?></h4>
							</div>
							<form action="" method="POST" name="group_invite_form">
								<div class="modal-body">
									<div class="row">
										<div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-lg-offset-2 col-md-offset-2 col-sm-offset-1">
											<select class="form-control" required="required" name="buddy">
												<option selected="selected" disabled="disabled">Pick a buddy</option>
												<?php
												$possible_invites = $student->get_buddies_for_possible_invite_for_group($group->get_id());
												foreach ($possible_invites AS $possible_invite_student_id)
												{
													$possible_invite_student = new Student($possible_invite_student_id);
													$disabled = "";
													if ($possible_invite_student->check_if_invite_for_group_is_pending($group->get_id()))
													{
														$disabled = "disabled='disable'";
													}
													?>
													<option <?php echo $disabled ?> value="<?php echo $possible_invite_student->get_id() ?>">
														<?php echo $possible_invite_student->get_fullname(); ?> (<?php echo $possible_invite_student->get_username() ?>)
													</option>
													<?php
												}
												?>
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-lg-offset-2 col-md-offset-2 col-sm-offset-1">
											<textarea class="form-control textarea" placeholder="Write a short Invite Message" name="message"></textarea>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									<input type="submit" class="btn btn-primary" value="Send Invite" name="invite">
								</div>
							</form>
						</div>
					</div>
				</div>
				<?php
			}
			?>
			<?php
			$student_level_in_group = $student->get_student_level_in_group($group->get_id());
			if ($student_level_in_group === 2 || $student_level_in_group === 3)
			{
				//If student level is 2 (admin) or 3 (owner)
				?>
				<!-- Edit Modal -->
				<div class="modal fade modal-inverse" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="editModalLabel">Edit '<?php echo $group->get_name() ?>'</h4>
							</div>
							<form action="" method="POST" name="editGroupForm">
								<div class="modal-body">
									<?php
									if ($student_level_in_group === 3)
									{
										?>
										<div class="row form-group" id="editGroupName">
											<div class="col-lg-3 col-md-3 col-sm-3 col-xs-4">
												<label for="groupNameInput" class="form-left-label">Group name</label>
											</div>
											<div class="col-lg-9 col-md-9 col-sm-9 col-xs-8">
												<input type="text" name="editGroupName" class="form-control" value="<?php echo $group->get_name() ?>" id="groupNameInput" required="required">
											</div>
										</div>
										<div class="row form-group" id="editGroupMaxSize">
											<div class="col-lg-3 col-md-3 col-sm-3 col-xs-4">
												<label for="groupSizeInput" class="form-left-label">Max size</label>
											</div>
											<div class="col-lg-9 col-md-9 col-sm-9 col-xs-8">
												<input type="number" name="editGroupSize" class="form-control" value="<?php echo $group->get_max_members() ?>" id="groupSizeInput" required="required" min="<?php echo $group->get_number_of_registered_members() ?>">
											</div>
										</div>
										<div class="row form-group" id="editGroupCategory">
											<div class="col-lg-3 col-md-3 col-sm-3 col-xs-4">
												<label for="groupCategorySelect" class="form-left-label">Category</label>
											</div>
											<div class="col-lg-9 col-md-9 col-sm-9 col-xs-8">
												<select class="form-control" name="editGroupCategory" id="groupCategorySelect" required="required">
													<?php
													$categories = $gc->get_category_names_and_ids();
													foreach ($categories as $cat_id => $cat_name)
													{
														if ($cat_id === $group->get_category_id())
														{
															?>
															<option value="<?php echo $cat_id ?>" selected="selected"><?php echo $cat_name ?></option>
															<?php
														}
														else
														{
															?>
															<option value="<?php echo $cat_id ?>"><?php echo $cat_name ?></option>
															<?php
														}
													}
													?>
												</select>
											</div>
										</div>
										<hr class="small-line">
										<?php
									}
									if ($student_level_in_group === 2 || $student_level_in_group === 3)
									{
										?>
										<div class="row form-group" id="editGroupMaxSize">
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
												<label for="groupDescriptionTextArea" class="form-left-label">Description</label>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
												<textarea name="editGroupDescription" class="form-control textarea" id="groupDescriptionTextArea" required="required" rows="5"><?php echo $group->get_description() ?></textarea>
											</div>
										</div>
										<?php
									}
									?>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									<button type="reset" class="btn btn-warning">Discard Edits</button>
									<button type="submit" class="btn btn-primary" name="edit">Save Edits</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<?php
			}
			?>
			<!-- Leave Prompt Modal -->
			<div class="modal fade modal-inverse" id="leaveModal" tabindex="-1" role="dialog" aria-labelledby="leaveModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="leaveModalLabel">Are you sure?</h4>
						</div>
						<form action="" method="POST">
							<div class="modal-body">
								<?php
								if ($group->get_public() == 0)
								{
									?>
									This group is private, and you former invitation does no longer work.<br/>
									<?php
								}
								else
								{
									?>
									Eventhough this is a public group, there's a limited amount of memberships.<br/>
									<?php
								}
								?>
								Are you sure you want to leave the group?
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-primary" data-dismiss="modal">Nope!</button>
								<button type="submit" class="btn btn-danger" name="leave">Yes</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<!-- All Members Modal -->
		<div class="modal fade modal-inverse" id="membersModal" tabindex="-1" role="dialog" aria-labelledby="membersModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="membersModalLabel">All Members of <?php echo $group->get_name() ?></h4>
					</div>
					<div class="modal-body">
						<div id="m-members-overview">
							<?php
							$student_level_in_group = $student->get_student_level_in_group($group->get_id());
							if ($student_level_in_group === 3)
							{
								$all_members_array = $group->get_array_with_members_and_levels(FALSE, FALSE);
							}
							else
							{
								$all_members_array = $group->get_array_with_members_and_levels(TRUE, FALSE);
							}
							$runs = 1;
							$inactive_members_array = array();
							foreach ($all_members_array as $student_id => $member_data)
							{
								if ($member_data['active'] != 1)
								{
									$inactive_members_array[$student_id] = $member_data;
								}
								else
								{
									$member = new Student($student_id);
									?>
									<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
										<div class="row">
											<div class="group-member-block">
												<a href="<?php echo BASE ?>student/<?php echo strtolower($member->get_username()); ?>/">
													<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
														<div class="row">
															<img src="<?php echo $member->get_avatar() ?>" class="student-avatar">
														</div>
													</div>
													<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
														<div class="row">
															<h4 class="student-name">
																<?php echo $member->get_username() ?>
															</h4>
															<span class="student-info">
																<?php echo get_member_level_name_from_level($member_data['level']) ?> | Joined <?php echo date("Y-m-d", strtotime($member_data['joined'])) ?>
															</span>
														</div>
													</div>
													<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
														<div class="student-options">
															<?php
															$member_level_in_group = $member->get_student_level_in_group($group->get_id());
															if ($student_level_in_group == 3 && $member_level_in_group == 1)
															{
																?>
																<form class="form-inline" action="" method="POST">
																	<input type="hidden" name="sid" value="<?php echo $member->get_id() ?>">
																	<input type="submit" name="madmin" value="Upgrade to Admin" class="btn btn-warning">
																</form>
																<?php
															}
															elseif ($student_level_in_group == 3 && $member_level_in_group == 2)
															{
																?>
																<form class="form-inline" action="" method="POST">
																	<input type="hidden" name="sid" value="<?php echo $member->get_id() ?>">
																	<input type="submit" name="dadmin" value="Downgrade to Member" class="btn btn-warning">
																</form>
																<?php
															}
															if (($student_level_in_group == 2 && $member_level_in_group == 1) || ($student_level_in_group == 3 && ($member_level_in_group == 1 || $member_level_in_group == 2)))
															{
																?>
																<form class="form-inline" action="" method="POST">
																	<input type="hidden" name="sid" value="<?php echo $member->get_id() ?>">
																	<input type="submit" name="kuser" value="Kick" class="btn btn-danger">
																</form>
																<?php
															}
															?>
														</div>
													</div>
												</a>
											</div>
										</div>
									</div>
									<?php
									if ($runs % 2 == 0 && $runs != count($all_members_array))
									{
										?>
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
											<div class="row">
												<hr class="minor-line">
											</div>
										</div>
										<?php
									}
								}
								$runs++;
							}
							if (is_array($inactive_members_array))
							{
								if (count($inactive_members_array) > 0)
								{
									?>
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<hr>
										<h3>Former members of group</h3>
									</div>
									<?php
									$new_runs = 1;
									foreach ($inactive_members_array as $student_id => $member_data)
									{
										if ($member_data['active'] != 1)
										{
											$member = new Student($student_id);
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<div class="row">
													<div class="group-member-block">
														<a href="<?php echo BASE ?>student/<?php echo strtolower($member->get_username()); ?>/">
															<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
																<div class="row">
																	<img src="<?php echo $member->get_avatar() ?>" class="student-avatar">
																</div>
															</div>
															<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
																<div class="row">
																	<h4 class="student-name">
																		<?php echo $member->get_username() ?>
																	</h4>
																	<span class="student-info">
																		Former <?php echo get_member_level_name_from_level($member_data['level']) ?> | Joined <?php echo date("Y-m-d", strtotime($member_data['joined'])) ?>
																	</span>
																</div>
															</div>
														</a>
													</div>
												</div>
											</div>
											<?php
											if ($new_runs % 2 == 0 && $new_runs != count($all_members_array))
											{
												?>
												<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
													<div class="row">
														<hr class="minor-line">
													</div>
												</div>
												<?php
											}
										}
										$new_runs++;
									}
								}
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		require '../includes/footer.php';
		if ($edited === TRUE)
		{
			?>
			<script>alert("<?php echo $edit_message ?>");</script>
			<?php
		}
		if ($l_answer === TRUE)
		{
			?>
			<script>alert("<?php echo $level_message ?>");</script>
			<?php
		}
		if ($k_answer === TRUE)
		{
			?>
			<script>alert("<?php echo $kicked_message ?>");</script>
			<?php
		}
		?>
		<script>
			$(document).ready(function () {
				.replace('post-textarea', {
					toolbar: [
						{name: 'basicstyles', groups: ['basicstyles', 'cleanup'], items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']},
						{name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'], items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language']},
						{name: 'links', items: ['Link', 'Unlink']},
						{name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
						{name: 'colors', items: ['TextColor', 'BGColor']}
					]
				});
				.replace('post-image-textarea', {
					toolbar: [
						{name: 'basicstyles', groups: ['basicstyles', 'cleanup'], items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']},
						{name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'], items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language']},
						{name: 'links', items: ['Link', 'Unlink']},
						{name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
						{name: 'colors', items: ['TextColor', 'BGColor']}
					]
				});
			});
		</script>
    </body>
</html>