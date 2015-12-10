<?php
require './includes/configuration.php';
require './student/student.php';

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
        <title><?php echo $student->get_firstname() ?> (Member Area)</title>
		<?php require './includes/header.php'; ?>
    </head>
    <body>
		<?php
		require './includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-lg-offset-4 col-md-offset-4 col-sm-offset-3">
						<div class="row">
							<div class="page-header">
								<h1>Welcome <?php echo $student->get_firstname() ?>!</h1>
							</div>
						</div>
						<div class="row">
							<form action="<?php echo BASE ?>student/search.php" method="GET">
								<label>Search for a Student</label>
								<input type="text" name="s" placeholder="Username, Email or Name" class="form-control">
								<input type="submit" class="btn btn-primary" value="Search!">
							</form>
							<a href="<?php echo BASE ?>logout.php">Logout</a>
						</div>
					</div>
				</div>
			</div>
		</div>
    </body>
</html>