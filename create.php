<?php
require_once './includes/configuration.php';
require_once './student/student_controller.php';

$sc = new Student_controller();

if (isset($_SESSION['logged_in']) AND isset($_GET['id']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$username = "";
$firstname = "";
$lastname = "";
$email = "";

$error_message = "";
if (isset($_POST['join']))
{
	$username = sanitize_text($_POST['username']);
	$firstname = sanitize_text($_POST['firstname']);
	$lastname = sanitize_text($_POST['lastname']);
	$email = sanitize_email($_POST['email']);

	//First, let's check if token is correct!
	$form_token = $_POST['token'];
	$sess_token = retrieve_session_token();
	if ($form_token === $sess_token)
	{
		$pass1 = sanitize_text($_POST['password1']);
		$pass2 = sanitize_text($_POST['password2']);

		$answer = $sc->create_student($username, $firstname, $lastname, $email, $pass1, $pass2);
		if ($answer !== TRUE)
		{
			?>
			<script>
				window.location = "#sign-up";
			</script>
			<?php
			$error_message = "You have not filled out the form according to the requirements";
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
	else
	{
		$error_message = "It seems as if the form has expired";
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
					<?php
					if (!empty($error_message))
					{
						?>
						<div class="row">
							<div class="bs-callout bs-callout-danger">
								<h4>Creation Error</h4>
								<?php echo $error_message ?>
							</div>
						</div>
						<?php
					}
					?>
					<form name="join-form" action="" method="POST">
						<?php
						//Get a form token
						$token = generate_form_token();
						?>
						<div class="row">
							<?php
							if (!empty($answer[0]))
							{
								?>
								<div class="form-group has-error">
									<input type="text" name="username" class="form-control" placeholder="Username" required="required" value="<?php echo $username ?>">
									<label class="control-label form-error-label"><?php echo $answer[0] ?></label>
								</div>
								<?php
							}
							else
							{
								?>
								<input type="text" name="username" class="form-control" placeholder="Username" required="required" value="<?php echo $username ?>">
								<?php
							}
							?>
						</div>
						<div class="row">
							<?php
							if (!empty($answer[1]))
							{
								?>
								<div class="form-group has-error">
									<input type="text" name="firstname" class="form-control" placeholder="Firstname" required="required" value="<?php echo $firstname ?>">
									<label class="control-label form-error-label"><?php echo $answer[1] ?></label>
								</div>
								<?php
							}
							else
							{
								?>
								<input type="text" name="firstname" class="form-control" placeholder="Firstname" required="required" value="<?php echo $firstname ?>">
								<?php
							}
							?>
						</div>
						<div class="row">
							<?php
							if (!empty($answer[2]))
							{
								?>
								<div class="form-group has-error">
									<input type="text" name="lastname" class="form-control" placeholder="Lastname" required="required" value="<?php echo $lastname ?>">
									<label class="control-label form-error-label"><?php echo $answer[2] ?></label>
								</div>
								<?php
							}
							else
							{
								?>
								<input type="text" name="lastname" class="form-control" placeholder="Lastname" required="required" value="<?php echo $lastname ?>">
								<?php
							}
							?>
						</div>
						<div class="row">
							<?php
							if (!empty($answer[3]))
							{
								?>
								<div class="form-group has-error">
									<input type="text" name="email" class="form-control" placeholder="Email" required="required" value="<?php echo $email ?>">
									<label class="control-label form-error-label"><?php echo $answer[3] ?></label>
								</div>
								<?php
							}
							else
							{
								?>
								<input type="text" name="email" class="form-control" placeholder="Email" required="required" value="<?php echo $email ?>">
								<?php
							}
							?>
						</div>
						<div class="row">
							<?php
							if (!empty($answer[4]))
							{
								?>
								<div class="form-group has-error">
									<input type="password" name="password1" class="form-control" placeholder="Password" required="required">
									<label class="control-label form-error-label"><?php echo $answer[4] ?></label>
								</div>
								<?php
							}
							else
							{
								?>
								<input type="password" name="password1" class="form-control" placeholder="Password" required="required">
								<?php
							}
							?>
						</div>
						<div class="row">
							<?php
							if (!empty($answer[5]))
							{
								?>
								<div class="form-group has-error">
									<input type="password" name="password2" class="form-control" placeholder="Retype Password" required="required">
									<label class="control-label form-error-label"><?php echo $answer[4] ?></label>
								</div>
								<?php
							}
							else
							{
								?>
								<input type="password" name="password2" class="form-control" placeholder="Retype Password" required="required">
								<?php
							}
							?>
						</div>
						<br>
						<div class="row">
							<input type="tel" class="form-control" placeholder="Telephone" name="phone" style="display: none"> 
							<input type="hidden" name="token" value="<?php echo $token ?>">
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