<?php
require_once '../includes/configuration.php';
require_once '../student/student.php';
require_once './group.php';

if (!isset($_SESSION['logged_in']) OR ! isset($_GET['id']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);

$group = new Group($_GET['id']);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $group->get_name() ?> | StudyTeam</title>
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
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="page-header">
								<div class="group-cover" style="background-image: url(http://www.movemag.ca/sites/movemag.ca/files/styles/image_with_story/public/field/image/GlenMajor-IMG_3709.jpg?itok=zr1d9epE);">
									<h1 class="group-cover-header"><?php echo $group->get_name() ?></h1>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
							<div class="content-box">
								<div class="post-box">
									<!-- Nav tabs -->
									<ul class="nav nav-tabs" role="tablist">
										<li role="presentation" class="active"><a href="#message" aria-controls="message" role="tab" data-toggle="tab"><span class="fa fa-pencil-square-o"></span> Post Message</a></li>
										<li role="presentation"><a href="#file" aria-controls="file" role="tab" data-toggle="tab"><span class="fa fa-file-archive-o"></span> File</a></li>
										<li role="presentation"><a href="#picture" aria-controls="picture" role="tab" data-toggle="tab"><span class="fa fa-image"></span> Picture</a></li>
									</ul>

									<!-- Tab panes -->
									<div class="tab-content">
										<div role="tabpanel" class="tab-pane active" id="message">
											<form>
												<textarea class="form-control textarea"></textarea>
											</form>
										</div>
										<div role="tabpanel" class="tab-pane" id="file">
											<form>
												<input type="file">
											</form>
										</div>
										<div role="tabpanel" class="tab-pane" id="picture">...</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
							<div class="content-box">
								<?php echo $group->get_public_or_private() ?> group<br/>
								Maximum <?php echo $group->get_max_members() ?> members<br/>
								<hr>
								<?php echo $group->get_description() ?>
							</div>
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