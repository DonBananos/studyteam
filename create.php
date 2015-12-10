<?php
require_once './includes/configuration.php';
require_once './student/student_controller.php';

$sc = new Student_controller();

if (isset($_POST['join']))
{
	$username = sanitize_text($_POST['username']);
	$firstname = sanitize_text($_POST['firstname']);
	$lastname = sanitize_text($_POST['lastname']);
	$email = sanitize_email($_POST['email']);
	$pass1 = sanitize_text($_POST['password1']);
	$pass2 = sanitize_text($_POST['password2']);
	if (strlen($_POST['phone']) > 0)
	{
		echo 'FREAKING BOT!';
		die();
	}
	$answer = $sc->create_student($username, $firstname, $lastname, $email, $pass1, $pass2);
	if ($answer !== TRUE)
	{
		echo $answer;
	}
	else
	{
		?>
		<script>
			alert("Welcome");
			window.location = "<?php echo W1BASE ?>member_area.php";
		</script>
		<?php
	}
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Create Student)</title>
		<?php require './includes/header.php'; ?>
    </head>
    <body>
		<div class="container">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-lg-offset-4 col-md-offset-4 col-sm-offset-3">
					<div class="row">
						<div class="page-header">
							<h1>Well Hello Stranger!</h1>
							<p class="lead">Why don't you join the party?</p>
						</div>
					</div>
					<form name="join-form" action="" method="POST">
						<div class="row">
							<input type="text" name="username" class="form-control" placeholder="Username">
						</div>
						<div class="row">
							<input type="text" name="firstname" class="form-control" placeholder="Firstname">
						</div>
						<div class="row">
							<input type="text" name="lastname" class="form-control" placeholder="Lastname">
						</div>
						<div class="row">
							<input type="email" name="email" class="form-control" placeholder="Email">
						</div>
						<div class="row">
							<input type="password" name="password1" class="form-control" placeholder="Password">
						</div>
						<div class="row">
							<input type="password" name="password2" class="form-control" placeholder="Retype Password">
						</div>
						<div class="row">
							<input type="tel" class="form-control" placeholder="Telephone" name="phone" style="display: none"> 
							<input type="submit" name="join" class="btn btn-primary" value="Join the wonder">
						</div>
					</form>
					<div class="row">
						<hr>
						<small>
							Already have a user? - Then simply <a href="<?php echo BASE ?>#login">login</a> to have some fun!
						</small>
					</div>
				</div>
			</div>
		</div>
    </body>
</html>