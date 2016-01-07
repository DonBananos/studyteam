<!DOCTYPE html>
<html>
<body>

<form action="image_upload.php" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="imageFile" id="fileToUpload">
    <input type="submit" value="Upload Image" name="submit">
</form>    
</body>
</html>

<?php
require_once './includes/configuration.php';

$path="/xampp/htdocs/studyteam/includes/_media/_images/";

$path="/";
$max_width = 1140;
$id= "mike";

if(isset($_POST["submit"])) {
    upload_image($path, $id, $max_width);
}
