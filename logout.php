<?php

require './includes/configuration.php';
require './student/student_controller.php';

$sc = new Student_controller();
$sc->log_member_out();
?>
<script>
	window.location = "<?php echo W1BASE ?>";
</script>