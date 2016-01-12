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

	$answer = $sc->create_student($username, $firstname, $lastname, $email, $pass1, $pass2);
	if ($answer !== TRUE)
	{
		// Info is shown to the user.
	}
	else
	{
		$sc->log_member_in($username, $pass1);
		?>
		<script>
			alert('Welcome! :)');
			window.location = "<?php echo W1BASE ?>member_area.php";
		</script>
		<?php
	}
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Create Student</title>
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

						<!-- Modal -->
						<div id="credentialPolicyModal" class="modal fade modal-inverse" tabindex="-1" role="dialog" aria-labelledby="credentialPolicyModalLabel">
							<div class="modal-dialog">
								<!-- Modal content-->
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">User Credential Policy</h4>
									</div>
									<div class="modal-body">
										<p>
											<b>Username:</b><br>
										<ul>
											<li>MIN 3 characters</li>
											<li>MAX 255 characters</li>
											<li>Only use A-Z a-z 0-9</li>
											<li>Special characters allowed: - . _</li>
											<li>No whitespaces</li>
										</ul>
										</p>
										<p>
											<b>Password:</b><br>
										<ul>
											<li>Minimum 8 characters long</li>
											<li>Minimum 1 capital letter</li>
											<li>Minimum 1 number</li>
											<li>All Special characters allowed</li>
											<li>No same 3 characters in a row</li>
											<li>No whitespaces</li>
										</ul>
										</p>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<form name="join-form" action="" method="POST">
						<?php
						//This is where there will be generated a form token...
						?>
						<div class="row">
							<input type="text" name="username" class="form-control" placeholder="Username" required>
							<?php
							if (!empty($answer[0]))
							{
								echo '<label>' . $answer[0] . '</label>';
							}
							?>
						</div>
						<div class="row">
							<input type="text" name="firstname" class="form-control" placeholder="Firstname" required>
							<?php
							if (!empty($answer[1]))
							{
								echo '<label>' . $answer[1] . '</label>';
							}
							?>
						</div>
						<div class="row">
							<input type="text" name="lastname" class="form-control" placeholder="Lastname" required>
							<?php
							if (!empty($answer[2]))
							{
								echo '<label>' . $answer[2] . '</label>';
							}
							?>
						</div>
						<div class="row">
							<input type="text" name="email" class="form-control" placeholder="Email" required>
							<?php
							if (!empty($answer[3]))
							{
								echo '<label>' . $answer[3] . '</label>';
							}
							?>
						</div>
						<div class="row">
							<input type="password" name="password1" class="form-control" placeholder="Password" required>
							<?php
							if (!empty($answer[4]))
							{
								echo '<label>' . $answer[4] . '</label>';
							}
							?>
						</div>
						<div class="row">
							<input type="password" name="password2" class="form-control" placeholder="Retype Password" required>
							<?php
							if (!empty($answer[5]))
							{
								echo '<label>' . $answer[5] . '</label>';
							}
							?>
						</div>
						<br>
						<div class="row">
							<input type="tel" class="form-control" placeholder="Telephone" name="phone" style="display: none"> 
							<input type="hidden" name="token" value="">
							<input type="submit" name="join" class="btn btn-primary" value="Join the wonder">
							<button type="button" data-toggle="modal" data-target="#credentialPolicyModal" class="btn btn-default"><span class="fa fa-question"></span> User Credential Policy</button>
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