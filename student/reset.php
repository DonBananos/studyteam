<?php
require_once '../includes/configuration.php';
require_once './student_controller.php';
require_once './student.php';

$display_message = FALSE;
$we_have_an_answer = false;

$sc = new Student_controller();

if (isset($_SESSION['logged_in']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}

//We're using u, e and c codes from the url...
if (isset($_GET['u']) && isset($_GET['e']) && isset($_GET['c']))
{
	$ready_for_reset = true;
}
else
{
	$ready_for_reset = false;
	if (isset($_POST['sendReset']))
	{
		$answer = $sc->please_reset_my_password_because_im_stupid($_POST['user']);
		$we_have_an_answer = true;
	}
}

if ($ready_for_reset === true)
{
	$u = sanitize_text($_GET['u']);
	$e = sanitize_text($_GET['e']);
	$c = sanitize_text($_GET['c']);
	$student_id = $sc->get_student_from_u_e_and_c_codes($u, $e, $c);

	if ($student_id === false)
	{
		$ready_for_reset = false;
	}
	elseif ($student_id > 0)
	{
		$reset_student = new Student($student_id);
		if (isset($_POST['saveNewPass']))
		{
			$pass1 = sanitize_text($_POST['pass1']);
			$pass2 = sanitize_text($_POST['pass2']);

			if ($sc->compare_passwords($pass1, $pass2))
			{
				$result = $reset_student->change_password($pass1);
				echo $result;
				if ($result === true)
				{
					$sc->set_reset_request_to_used($u, $e, $c);
					?>
					<script>
						window.location = "<?php echo BASE ?>";
					</script>
					<?php
				}
				else
				{
					echo $stmt->error;
				}
			}
		}
	}
	else
	{
		$ready_for_reset = false;
		?>
		<script>
			alert("<?php echo $student_id ?>");
			window.location = "<?php echo BASE ?>";
		</script>
		<?php
	}
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Reset Password | StudyTeam</title>
		<?php require '../includes/header.php'; ?>
    </head>
    <body>
		<div class="container">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 30vh;">
				<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 col-lg-offset-4 col-md-offset-4 col-sm-offset-3">
					<?php
					if ($we_have_an_answer)
					{
						echo $answer;
					}
					?>
					<div class="row">
						<h1>Reset your Password</h1>
						<p class="lead">Forgot your password? No problem!</p>
					</div>
					<?php
					if ($ready_for_reset)
					{
						?>
						<form name="new-pass-form" action="" method="POST">
							<div class="row">
								<input type="password" name="pass1" class="form-control" placeholder="New Password">
							</div>
							<div class="row">
								<input type="password" name="pass2" class="form-control" placeholder="Retype New Password">
							</div>
							<div class="row">
								<input type="submit" class="btn btn-primary" name="saveNewPass" value="Change">
							</div>
						</form>
						<?php
					}
					else
					{
						?>
						<form name="reset-form" action="" method="POST">
							<div class="row">
								<input type="text" name="user" class="form-control" placeholder="Your Username or Email">
							</div>
							<div class="row">
								<input type="submit" name="sendReset" class="btn btn-primary" value="Reset">
							</div>
						</form>
						<?php
					}
					?>
				</div>
			</div>
		</div>
    </body>
</html>