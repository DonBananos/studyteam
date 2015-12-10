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
if(!isset($_GET['id']))
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
		<div class="container">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-lg-offset-4 col-md-offset-4 col-sm-offset-3">
					<div class="row">
						<div class="page-header">
							<h1>Profile for <?php echo $student_visited->get_fullname() ?></h1>
						</div>
					</div>
					<div class="row">
						Firstname: <label><?php echo $student_visited->get_firstname() ?></label><br/>
						Lastname: <label><?php echo $student_visited->get_lastname() ?></label><br/>
						Username: <label><?php echo $student_visited->get_username() ?></label><br/>
						Email: <label><?php echo $student_visited->get_email() ?></label><br/>
						Permission: <label><?php echo $student_visited->get_permission() ?></label><br/>
						Joined: <label><?php echo $student_visited->get_joined() ?></label><br/>
					</div>
				</div>
			</div>
		</div>
    </body>
</html>