<?php
// Uploads/plugin.php
// Allows members to upload files to the forum.

if (!defined("IN_ESO")) {

    // This is where we handle the uploads.
    if (isset($_FILES["fileToUpload"]["name"])) {

        // This should be configured to the webadmin's preference. Default is 2000000000 bytes (2 Gigabytes).
        $fileSizeLimit = "2000000000";

        $currentDirectory = getcwd();
        $target_dir = $currentDirectory . "/uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if file already exists.
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size.
        elseif ($_FILES["fileToUpload"]["size"] > $fileSizeLimit) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // if everything is ok, try to upload file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "The file ". htmlspecialchars(basename($_FILES["fileToUpload"]["name"])). " has been uploaded.";
            }
            else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
    exit();
}

class Uploads extends Plugin {
 
var $id = "Uploads";
var $name = "Uploads";
var $version = "1.0";
var $description = "Allows members to upload files to the forum";
var $author = "GigaHacer";

function init()
{
	parent::init();

    // On the conversation view add our uploading input form to the user bar *facepalm*.
    if ($this->eso->action == "conversation") {
        $this->eso->addToBar("right", "<form action='plugins/Uploads/plugin.php' method='post' enctype='multipart/form-data'>Upload a file:<input type='file' name='fileToUpload' id='fileToUpload'><input type='submit' class='button' value='Upload a file' name='submit'></form>");
    }
}

// If the uploads folder doesn't exist, create it!
function upgrade($oldVersion)
{
  if (!file_exists("plugins/Uploads/uploads") and !mkdir("plugins/Uploads/uploads")) $this->eso->fatalError("Couldn't create uploads directory.");
}

}

?>
