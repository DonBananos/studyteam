<?php
require '../includes/configuration.php';
require './student.php';
require './student_controller.php';

if (!isset($_SESSION['logged_in']))
{
	$_SESSION['tried_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);
if (!isset($_GET['id']))
{
	if (!isset($_GET['usr']))
	{
		$student_visited = $student;
	}
	else
	{
		if (sanitize_text(strtolower($_GET['usr'])) == "buddies")
		{
			?>
			<script>window.location = "<?php echo SERVER . BASE ?>student/buddies.php";</script>
			<?php
		}
		$sc = new Student_controller();
		$user_array = $sc->get_member_with_username(sanitize_text($_GET['usr']));
		$student_visited = new Student($user_array['id']);
	}
}
else
{
	$student_visited = new Student($_GET['id']);
}

/*
 * Setting buddy statuses
 */
$buddies_pending = FALSE;
$buddies = FALSE;

if (isset($_POST['becomeBuddy']))
{
	if ($student_visited->apply_for_buddies($student->get_id()))
	{
		$buddies_pending = TRUE;
	}
	else
	{
		$buddies_pending = FALSE;
	}
}
if ($student_visited->check_if_buddies($student->get_id()))
{
	$buddies = TRUE;
}
if ($student_visited->check_if_buddies_pending($student->get_id()))
{
	$buddies_pending = TRUE;
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $student_visited->get_fullname() ?>'s Profile | StudyTeam</title>
		<?php require '../includes/header.php'; ?>
    </head>
    <body>
		<?php
		require '../includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="page-header">
					<div class="row">
						<div class="col-lg-2 col-md-2 col-sm-3 hidden-xs">
							<div class="profile-header-avatar">
								<img src="<?php echo $student_visited->get_avatar() ?>" class="student-avatar">
								<?php
								if ($student->get_id() === $student_visited->get_id())
								{
									?>
									<a href="<?php echo BASE ?>student/settings/#avatar">
										<span class="fa fa-image fa-2x"></span>
									</a>
									<?php
								}
								?>
							</div>
						</div>
						<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
							<h1><?php echo $student_visited->get_fullname() ?></h1>
						</div>
					</div>
				</div>
				<?php
				if ($student_visited->get_id() != $student->get_id() AND $buddies === FALSE)
				{
					?>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="student-options">
								<?php
								if ($buddies_pending === FALSE)
								{
									?>
									<form method="POST" action=""><button type="submit" class="btn btn-primary" name="becomeBuddy"><span class="fa fa-user-plus"></span> Become Buddies</button></form>
									<?php
								}
								elseif ($buddies_pending === TRUE)
								{
									?>
									<button class="btn btn-default disabled"><span class="fa fa-user-plus"></span> Awaiting Buddy Accept</button>
									<?php
								}
								?>
								<button class="btn btn-default disabled"><span class="fa fa-envelope"></span> Send Message</button>
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<div class="row">
					<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
						<div class="content-box" id="student-profile-general-info">
							<div class="page-info">
								<span class="fa fa-user"></span> <?php echo $student_visited->get_fullname() ?><br/>
								<span class="fa fa-tag"></span> <?php echo $student_visited->get_username() ?><br/>
								<span class="fa fa-at"></span> <?php echo $student_visited->get_email() ?><br/>
								<span class="fa fa-lock"></span> <?php echo get_permission_name_from_id($student_visited->get_permission()) ?><br/>
								<span class="fa fa-clock-o"></span> <?php echo date("d/m-Y", strtotime($student_visited->get_joined())) ?>
							</div>
						</div>
						<div class="content-box" id="student-profile-buddy-list">
							<h3>Buddies (<?php echo $student_visited->get_number_of_buddies() ?>)</h3>
							<hr class="minor-line">
							<?php
							$buddy_ids = $student_visited->get_all_buddy_ids();
							if (is_array($buddy_ids) && count($buddy_ids) > 0)
							{
								foreach ($buddy_ids as $buddy_id)
								{
									$buddy = new Student($buddy_id);
									?>
									<div class="row">
										<a href="<?php echo BASE ?>student/details.php?id=<?php echo $buddy->get_id(); ?>">
											<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
												<img src="<?php echo $buddy->get_avatar() ?>" class="student-avatar">
											</div>
											<div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
												<h4 class="student-name">
													<?php echo $buddy->get_fullname() ?>
												</h4>
											</div>
										</a>
									</div>
									<hr class="minor-line">
									<?php
								}
							}
							else
							{
								?>
								<p class="text-center">No buddies yet</p>
								<?php
							}
							?>
						</div>
					</div>
					<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
						<div class="content-box">
							<h3><?php echo $student_visited->get_firstname() ?>'s Activity</h3>
							<hr class="minor-line">
							<p class="text-center">No Public Activity</p>
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