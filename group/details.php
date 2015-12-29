<?php
require_once '../includes/configuration.php';
require_once '../student/student.php';
require_once './group.php';

if (!isset($_SESSION['logged_in']) OR ! isset($_GET['id']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);

$group = new Group($_GET['id']);

$membership = FALSE;
if($group->get_if_student_is_member($student->get_id()))
{
	$membership = TRUE;
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
										if($membership)
										{
											?>
											<button class="btn btn-default btn-sm" data-toggle="modal" data-target="#inviteModal"><span class="fa fa-plus"></span> Invite</button>
											<button class="btn btn-danger btn-sm"><span class="fa fa-sign-out"></span> Leave</button>
											<button class="btn btn-warning btn-sm"><span class="fa fa-pencil"></span> Edit</button>
											<?php
										}
										else
										{
											?>
											<button class="btn btn-primary btn-sm"><span class="fa fa-plus"></span> Join Group</button>
											<?php
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
											<a href="<?php echo BASE ?>student/<?php echo strtolower($member->get_username()); //This is a Comment ?>">
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
		<!-- Invite Modal -->
		<div class="modal fade modal-inverse" id="inviteModal" tabindex="-1" role="dialog" aria-labelledby="inviteModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="inviteModalLabel">Invite a Buddy</h4>
					</div>
					<form>
						<div class="modal-body">

						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="button" class="btn btn-primary">Send Invite</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
		require '../includes/footer.php';
		?>
    </body>
</html>