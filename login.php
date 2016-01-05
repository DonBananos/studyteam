<?php
require_once './includes/configuration.php';
require_once './student/student_controller.php';

$display_message = FALSE;

$sc = new Student_controller();

if (isset($_POST['login']))
{
	$user = sanitize_text($_POST['user']);
	$pass = sanitize_text($_POST['pass']);
	$answer = $sc->log_member_in($user, $pass);
	if ($answer !== FALSE && $answer !== TRUE)
	{
		$display_message = TRUE;
	}
	elseif ($answer === FALSE)
	{
		$display_message = TRUE;
	}
}

if (isset($_SESSION['logged_in']))
{
	if (!empty($_SESSION['tried_url']))
	{
		$tried_url = $_SESSION['tried_url'];
		$_SESSION['tried_url'] = null;
		?>
		<script>window.location = "<?php echo $tried_url ?>";</script>
		<?php
		die();
	}
	if ($_SESSION['logged_in'] == TRUE)
	{
		?>
		<script>
			window.location = "<?php echo BASE ?>";
			die();
		</script>
		<?php
	}
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Login to StudyTeam</title>
		<?php require './includes/header.php'; ?>
    </head>
    <body>
		<div class="container">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-lg-offset-4 col-md-offset-4 col-sm-offset-3">
					<?php
					if ($display_message === TRUE)
					{
						?>
						<div class="row">
							<div class="page-header">
								<h1>Error!</h1>
								<p class="lead"><?php echo $answer ?></p>
							</div>
						</div>
						<div class="row">
							<p>
								Please <a href="<?php echo BASE ?>">try again</a>.
							</p>
						</div>
						<?php
					}
					else
					{
						?>
						<div class="row">
							<h1>Welcome!</h1>
							<p class="lead">Don't be shy! Come in!</p>
						</div>
						<form name="login-form" action="" method="POST">
							<!-- These are for browsers not to place remembered text -->
							<input style="display:none" type="text" name="fakeusernameremembered"/>
							<input style="display:none" type="password" name="fakepasswordremembered"/>
							<!-- Now for the real form -->
							<div class="row">
								<input type="text" name="user" class="form-control" placeholder="Username or Email">
							</div>
							<div class="row">
								<input type="password" name="pass" class="form-control" placeholder="Password">
							</div>
							<div class="row">
								<input type="submit" name="login" class="btn btn-primary" value="Login">
							</div>
						</form>
						<div class="row">
							<hr>
							<small>
								Forgot your password? <a href="<?php echo BASE ?>student/reset.php">Click here to reset!</a>
							</small>
							<br/>
							<small>
								Don't have a user yet? - Why don't you <a href="<?php echo BASE ?>#sign-up">sign up</a> then!
							</small>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
    </body>
</html>