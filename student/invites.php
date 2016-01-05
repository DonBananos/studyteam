<?php
require '../includes/configuration.php';
require './student.php';
require '../group/group_controller.php';
require '../group/group.php';

if (!isset($_SESSION['logged_in']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);

/*
 * Handle Reply's
 */
$theres_a_message = FALSE;
if (isset($_POST['acceptAndGo']) || isset($_POST['accept']))
{
	$invite_id = sanitize_int($_POST['invid']);
	$group_id = sanitize_int($_POST['gid']);
	if (!validate_int($invite_id) || !validate_int($group_id))
	{
		die("You naughty bastard!");
	}
	if ($student->check_if_invite_for_group_is_pending($group_id))
	{
		if ($student->accept_pending_invite($invite_id) === true)
		{
			$group_for_accept = new Group($group_id);
			if ($group_for_accept->add_student_to_group($student->get_id()))
			{
				if (isset($_POST['acceptAndGo']))
				{
					?>
					<script>window.location = "<?php echo BASE ?>group/<?php echo $group_id ?>/"</script>
					<?php
					die();
				}
				else
				{
					$message = "You have been added to the group!";
					$theres_a_message = TRUE;
				}
			}
		}
	}
}
if (isset($_POST['decline']))
{
	$invite_id = sanitize_int($_POST['invid']);
	$group_id = sanitize_int($_POST['gid']);
	if (!validate_int($invite_id) || !validate_int($group_id))
	{
		die("You naughty bastard!");
	}
	if ($student->check_if_invite_for_group_is_pending($group_id))
	{
		if ($student->decline_pending_invite($invite_id))
		{
			$message = "You have declined the invite to the group!";
			$theres_a_message = TRUE;
		}
	}
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Group Invites | StudyTeam</title>
		<?php require '../includes/header.php'; ?>
    </head>
    <body>
		<?php
		require '../includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="row">
						<div class="page-header">
							<h1>Group Invites</h1>
						</div>
					</div>
					<?php
					if ($theres_a_message === TRUE)
					{
						?>
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<p><?php echo $message ?></p>
							</div>
						</div>
						<?php
					}
					?>
					<div class="row">
						<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
							<?php
							$group_invites = $student->get_all_pending_invites();
							foreach ($group_invites as $group_invite_id => $group_invite)
							{
								$group = new Group($group_invite['group_id']);
								$invitor = new Student($group_invite['invitor_id']);
								?>
								<div class="row">
									<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
										<a href="<?php echo BASE ?>group/<?php echo $group->get_id() ?>/">
											<div class="group" id="group-1" style="background-image: url(<?php echo $group->get_category_image() ?>);">
												<div class="group-header">
													<h3><?php echo $group->get_name() ?></h3>
												</div>
											</div>
										</a>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
										<div class="invite-info">
											<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#replyModal<?php echo $group_invite_id ?>">Reply</button>
											<hr class="minor-line" style="margin-top: 5px">
											<div class="overflow-auto invite-list-info">
												Invited by: <a href=""><?php echo $invitor->get_fullname() ?></a><br/>
												Invited at: <?php echo $group_invite['time'] ?><br/>
												<?php
												if (!empty($group_invite['message']))
												{
													echo 'Invite Message: ' . $group_invite['message'] . '<br/>';
												}
												?>
												<br/>
												<?php echo $group->get_public_or_private() ?> Group<br/>
												<?php echo $group->get_number_of_registered_members() ?> / <?php echo $group->get_max_members() ?> Members<br/>
											</div>
										</div>
									</div>
								</div>
								<!-- Reply Modal for Invite <?php echo $group_invite_id ?> -->
								<div class="modal fade modal-inverse" id="replyModal<?php echo $group_invite_id ?>" tabindex="-1" role="dialog" aria-labelledby="replyModal<?php echo $group_invite_id ?>Label">
									<div class="modal-dialog" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
												<h4 class="modal-title" id="replyModal<?php echo $group_invite_id ?>Label">Reply to Invite</h4>
											</div>
											<form action="" method="POST" name="inviteReplyForm">
												<div class="modal-body">
													You have been invited to join the <?php echo $group->get_public_or_private() ?> group <span class="invite-modal-group-name"><?php echo $group->get_name() ?></span><br/>
													Group Description:<br/>
													<?php echo $group->get_description() ?><br/>
													<input type="hidden" name="invid" value="<?php echo $group_invite_id ?>">
													<input type="hidden" name="gid" value="<?php echo $group_invite['group_id'] ?>">
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-default hidden-xs" data-dismiss="modal">Close</button>
													<button type="submit" class="btn btn-primary hidden-xs" name="acceptAndGo">Accept Invite and Go to Group</button>
													<button type="submit" class="btn btn-primary" name="accept">Accept Invite</button>
													<button type="submit" class="btn btn-danger" name="decline">Decline Invite</button>
												</div>
											</form>
										</div>
									</div>
								</div>
								<?php
							}
							if (count($group_invites) === 0)
							{
								?>
								<div class="row">
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										No pending invites
									</div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		require '../includes/footer.php';
		?>
    </body>
</html>