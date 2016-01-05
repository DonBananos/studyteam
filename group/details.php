<?php
require_once '../includes/configuration.php';
require_once '../student/student.php';
require_once './group.php';
require_once './group_controller.php';

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

$membership = FALSE;
$edited = FALSE;
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
	die();
	<?php
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
								<div class="content-box">
									<div class="post-box">
										<!-- Nav tabs -->
										<ul class="nav nav-tabs" role="tablist">
											<li role="presentation" class="active"><a href="#message" aria-controls="message" role="tab" data-toggle="tab"><span class="fa fa-pencil-square-o"></span> Post Message</a></li>
											<li role="presentation"><a href="#file" aria-controls="file" role="tab" data-toggle="tab"><span class="fa fa-file"></span> File</a></li>
											<li role="presentation"><a href="#picture" aria-controls="picture" role="tab" data-toggle="tab"><span class="fa fa-image"></span> Picture</a></li>
										</ul>
										<!-- Tab panes -->
										<div class="tab-content">
											<div role="tabpanel" class="tab-pane active" id="message">
												<form>
													<textarea class="form-control textarea"></textarea>
												</form>
											</div>
											<div role="tabpanel" class="tab-pane" id="file">
												<form>
													<input type="file">
												</form>
											</div>
											<div role="tabpanel" class="tab-pane" id="picture">...</div>
										</div>
									</div>
								</div>
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
									<?php echo $group->get_description() ?>
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
														<?php echo $member->get_fullname() ?>
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
									?>
									<div class="group-admin-options no-btm-pad">
										<button class="btn btn-default btn-sm"><span class="fa fa-group"></span> All Members</button>
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
											<div class="col-lg-3 col-md-3 col-sm-3 col-xs-4">
												<label for="groupDescriptionTextArea" class="form-left-label">Description</label>
											</div>
											<div class="col-lg-9 col-md-9 col-sm-9 col-xs-8">
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
		require '../includes/footer.php';
		if ($edited === TRUE)
		{
			?>
			<script>alert("<?php echo $edit_message ?>");</script>
			<?php
		}
		?>
    </body>
</html>