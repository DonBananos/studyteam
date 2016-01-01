<?php
require '../includes/configuration.php';
require './student.php';

if (!isset($_SESSION['logged_in']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);

if (isset($_POST['saveSelection']))
{
	$safe_avatar_id = sanitize_int($_POST['avatarSelected']);
	$student->save_new_avatar($safe_avatar_id);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>My Settings | StudyTeam</title>
		<?php require '../includes/header.php'; ?>
    </head>
    <body>
		<?php
		require '../includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="page-header">
					<h1>StudyTeam Settings</h1>
				</div>
				<div class="row" id="avatar">
					<label>Select your Avatar</label><br/>
					<?php
					$path = ROOT_PATH . "includes/_media/_images/avatars/*.png";
					$avatars = glob($path, GLOB_BRACE);
					if (count($avatars) > 0)
					{
						?>
						<form action="" method="POST" name="selectAvatar">
							<?php
							foreach ($avatars as $avatar)
							{
								$status = "";
								if (AVATAR_LOCATION . basename($avatar) == $student->get_avatar())
								{
									$status = "checked";
								}
								$avatar_id = substr(basename($avatar), 0, -4);
								?>
								<div class="col-lg-2 col-md-2 col-sm-3 col-xs-4">
									<label style="cursor: pointer">
										<img src="<?php echo AVATAR_LOCATION . basename($avatar) ?>" class="student-avatar"><br/>
										<div class="text-center">
											<input type="radio" name="avatarSelected" value="<?php echo $avatar_id ?>" <?php echo $status ?>>
										</div>
									</label>
								</div>
								<?php
							}
							?>
							<div class="clearfix"></div><br/>
							<button type="submit" class="btn btn-primary" name="saveSelection"><span class="fa fa-check"></span> Use Avatar</button>
						</form>
						<?php
					}
					else
					{
						echo "No avatars";
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
	require '../includes/footer.php';
	?>
</body>
</html>