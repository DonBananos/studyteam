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
if (!isset($_GET['id']))
{
	$student_visited = $student;
}
else
{
	$student_visited = new Student($_GET['id']);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $student_visited->get_fullname() ?> Profile | StudyTeam</title>
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
							<h1>Profile for <?php echo $student_visited->get_fullname() ?></h1>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-2 col-md-2 col-sm-3 col-xs-6">
							<div class="row">
								<img src="<?php echo $student_visited->get_avatar() ?>" class="student-avatar">
							</div>
						</div>
						<div class="col-lg-10 col-md-10 col-sm-9 col-xs-6">
							<div class="row">
								Firstname: <label><?php echo $student_visited->get_firstname() ?></label><br/>
								Lastname: <label><?php echo $student_visited->get_lastname() ?></label><br/>
								Username: <label><?php echo $student_visited->get_username() ?></label><br/>
								Email: <label><?php echo $student_visited->get_email() ?></label><br/>
								Permission: <label><?php echo get_permission_name_from_id($student_visited->get_permission()) ?></label><br/>
								Joined: <label><?php echo $student_visited->get_joined() ?></label><br/>
							</div>
						</div>
					</div>
					<?php
					if ($student->get_id() === $student_visited->get_id())
					{
						?>
						<div class="row">
							<hr>
							<h3>School and Education</h3>

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