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
											<button class="btn btn-primary btn-sm">Reply</button>
											<hr class="minor-line" style="margin-top: 5px">
											<div class="overflow-auto invite-list-info">
												Invited by: <a href=""><?php echo $invitor->get_fullname() ?></a><br/>
												Invited at: <?php echo $group_invite['time'] ?><br/>
												<?php
												if(!empty($group_invite['message']))
												{
													echo 'Invite Message: '.$group_invite['message'].'<br/>';
												}
												?>
												<br/>
												<?php echo $group->get_public_or_private() ?> Group<br/>
												<?php echo $group->get_number_of_registered_members() ?> / <?php echo $group->get_max_members() ?> Members<br/>
											</div>
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