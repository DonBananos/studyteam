<?php
require '../includes/configuration.php';
require '../student/student.php';
require './group_controller.php';
require './group.php';

if (!isset($_SESSION['logged_in']))
{
	$_SESSION['tried_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);
$gc = new Group_controller();

if (isset($_POST['addGroupSubmit']))
{
	$name = $_POST['groupName'];
	$category = $_POST['category'];
	$public = $_POST['privacy'];
	$max = $_POST['maxMembers'];
	$desc = $_POST['desc'];

	$new_group_result = $gc->create_new_group($name, $public, $category, $max, $student->get_id(), $desc);
	if ($new_group_result !== FALSE)
	{
		$new_group = new Group($new_group_result);
		//Since group is created, we save the creator as a member with 'owner' level.
		$new_group->add_student_to_group($student->get_id(), 3);
	}
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Groups | StudyTeam</title>
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
							<h1>StudyTeam Groups</h1>
						</div>
						<div class="pull-right">
							<button class="btn btn-success" data-toggle="modal" data-target="#addGroupModal">
								<span class="fa fa-plus"></span> Create Group
							</button>
							<div class="modal fade modal-inverse" id="addGroupModal" tabindex="-1" role="dialog" aria-labelledby="addGroupModalLabel">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="addGroupModalLabel">Create a new Group</h4>
										</div>
										<form name="group-form" action="" method="POST">
											<div class="modal-body">
												<div class="row">
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="text" class="form-control" placeholder="Group Name" name="groupName" required="required">
													</div>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<select class="form-control" name="category" required="required">
															<?php
															$categories = $gc->get_category_names_and_ids();
															foreach ($categories as $cat_id => $cat_name)
															{
																?>
																<option value="<?php echo $cat_id ?>"><?php echo $cat_name ?></option>
																<?php
															}
															?>
														</select>
													</div>
												</div>
												<div class="row">
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 small-pad">
														<label class="radio-inline scouch-right">
															<input type="radio" name="privacy" id="privacy1" value="0" checked="checked"> Private
														</label>
														<label class="radio-inline">
															<input type="radio" name="privacy" id="privacy2" value="1"> Public
														</label>
													</div>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="row">
															<div class="col-lg-5 col-md-5 col-sm-5 col-xs-6 small-pad">
																<label class="pull-right">Max members</label>
															</div>
															<div class="col-lg-7 col-md-7 col-sm-7 col-xs-6">
																<input type="number" min="1" name="maxMembers" class="form-control" required="required">
															</div>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
														<textarea class="form-control textarea" placeholder="Description" name="desc" rows="5" required="required"></textarea>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
												<button type="reset" class="btn btn-warning">Reset</button>
												<input type="submit" class="btn btn-success" name="addGroupSubmit" value="Create Group">
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
						<br/>
						<br/>
					</div>
					<div class="row">
						<h3>Your groups</h3>
						<?php
						$group_ids = $student->get_group_ids_that_student_is_part_of();
						$printed_groups = 0;
						foreach ($group_ids as $group_id)
						{
							$group = new Group($group_id);
							?>
							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="row">
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<a href="<?php echo BASE ?>group/<?php echo $group->get_id() ?>/">
											<div class="group" id="group-<?php echo $group->get_id() ?>" style="background-image: url(<?php echo $group->get_category_image() ?>);">
												<div class="group-header">
													<h3><?php echo $group->get_name() ?></h3>
													<div class="publicity">
														<?php
														if ($group->get_public() == 1)
														{
															?>
															<span class="fa fa-unlock fa-2x"></span>
															<?php
														}
														else
														{
															?>
															<span class="fa fa-lock fa-2x"></span>
															<?php
														}
														?>
													</div>
												</div>
											</div>
										</a>
									</div>
								</div>
							</div>
							<?php
							$printed_groups++;
						}
						if ($printed_groups < 1)
						{
							?>
						<p>You are not member of a group yet. Try to find a group, or <a data-toggle="modal" data-target="#addGroupModal" style="cursor: pointer;">create one now!</a></p>
							<?php
						}
						?>
					</div>
					<?php
					$suggested_group_ids = $student->get_public_groups_where_student_is_not_member();
					if (count($suggested_group_ids) > 0)
					{
						?>
						<div class="row">
							<h3>Suggested public groups</h3>
							<?php
							foreach ($suggested_group_ids as $suggested_group_id)
							{
								$suggested_group = new Group($suggested_group_id);
								?>
								<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
									<div class="row">
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
											<a href="<?php echo BASE ?>group/<?php echo $suggested_group->get_id() ?>/">
												<div class="group" id="group-<?php echo $group->get_id() ?>" style="background-image: url(<?php echo $suggested_group->get_category_image() ?>);">
													<div class="group-header">
														<h3><?php echo $suggested_group->get_name() ?></h3>
														<div class="publicity">
															<?php
															if ($suggested_group->get_public() == 1)
															{
																?>
																<span class="fa fa-unlock fa-2x"></span>
																<?php
															}
															else
															{
																?>
																<span class="fa fa-lock fa-2x"></span>
																<?php
															}
															?>
														</div>
													</div>
												</div>
											</a>
										</div>
									</div>
								</div>
								<?php
							}
							?>
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