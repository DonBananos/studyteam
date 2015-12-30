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
$crooked = false;

if (!isset($_GET['s']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
else
{
	$search_string = sanitize_text($_GET['s']);
	if($_GET['s'] !== $search_string)
	{
		//Something is not as it should be!
		$crooked = true;
		$message = "You bastard! We've logged your search with ip: ".  get_ip_address();
	}
	$sc = new Student_controller();
	$search_student_ids = $sc->search_for_student($search_string);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $student->get_firstname() ?> (Member Area)</title>
		<?php require '../includes/header.php'; ?>
    </head>
    <body>
		<?php
		require '../includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-lg-offset-4 col-md-offset-4 col-sm-offset-3">
						<div class="row">
							<div class="page-header">
								<h1>Search for '<?php echo $search_string ?>'</h1>
							</div>
							<?php
							if($crooked)
							{
								echo $message.'<hr>';
							}
							?>
						</div>
						<div class="row">
							<form action="<?php echo BASE ?>search/" method="GET">
								<label>Search for a Student</label>
								<input type="text" name="s" value="<?php echo $search_string ?>" class="form-control">
								<input type="submit" class="btn btn-primary" value="Search!">
							</form>
							<hr>
							<?php
							foreach ($search_student_ids as $student_id)
							{
								$search_student = new Student($student_id);
								?>
								<label>Username: </label>
								<?php echo $search_student->get_username() ?><br/>
								<label>Name: </label>
								<?php echo $search_student->get_fullname() ?><br/>
								<label>Email: </label>
								<?php echo $search_student->get_email() ?><br/>
								<a href="<?php echo BASE ?>student/<?php echo $search_student->get_username() ?>/" class="btn btn-default">Details</a>
								<div class="clearfix"></div>
								<hr>
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