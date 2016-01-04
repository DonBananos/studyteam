<?php
require '../includes/configuration.php';
require './student.php';
require './student_controller.php';

if (!isset($_SESSION['logged_in']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);

if (isset($_POST['buddyAccept']))
{
	$raw_buddy_id = $_POST['buddyId'];
	$safe_buddy_id = sanitize_int($raw_buddy_id);
	$student->accept_buddy_pending($safe_buddy_id);
}
elseif (isset($_POST['buddyDecline']))
{
	$raw_buddy_id = $_POST['buddyId'];
	$safe_buddy_id = sanitize_int($raw_buddy_id);
	$student->decline_buddy_pending($safe_buddy_id);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>My Buddies | StudyTeam</title>
		<?php require '../includes/header.php'; ?>
    </head>
    <body>
		<?php
		require '../includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="page-header">
					<h1><?php echo $student->get_fullname() ?>'s buddies</h1>
				</div>
				<?php
				$buddies_pending = $student->get_number_of_buddies_pending();
				if ($buddies_pending > 0)
				{
					?>
					<div class="content-box" id="pending-buddy-list">
						<?php
						$pending_buddy_ids = $student->get_all_pending_buddy_ids();
						foreach ($pending_buddy_ids as $pending_buddy_id)
						{
							$pending_buddy = new Student($pending_buddy_id);
							?>
							<div class="buddy-pending-entry">
								<form action="" method="POST">
									<input type="hidden" name="buddyId" value="<?php echo $pending_buddy->get_id() ?>">
									<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
										<img src="<?php echo $pending_buddy->get_avatar() ?>" class="student-avatar">
									</div>
									<div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
										<a href="<?php echo BASE ?>student/<?php echo strtolower($pending_buddy->get_username()); ?>/">
											<h4 class="student-name">
												<?php echo $pending_buddy->get_fullname() ?>
											</h4>
										</a>
									</div>
									<button type="submit" class="btn btn-primary btn-sm" name="buddyAccept">Accept</button>
									<button type="submit" class="btn btn-primary btn-sm" name="buddyDecline">Decline</button>
								</form>
							</div>
							<?php
						}
						?>
					</div>
					<hr class="minor-line">
					<?php
				}
				?>

				<div class="row">
					<?php
					$buddy_ids = $student->get_all_buddy_ids();
					foreach ($buddy_ids as $buddy_id)
					{
						$buddy = new Student($buddy_id);
						?>
						<div class="buddy-list-entry col-lg-2 col-md-2 col-sm-3 col-xs-4">
							<a href="<?php echo BASE ?>student/<?php echo strtolower($buddy->get_username()) ?>/">
								<img src="<?php echo $buddy->get_avatar() ?>" class="student-avatar">
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<h3 class="student-name text-center">
										<?php echo $buddy->get_fullname() ?>
									</h3>
								</div>
							</a>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php
		require '../includes/footer.php';
		?>
	</body>
</html>