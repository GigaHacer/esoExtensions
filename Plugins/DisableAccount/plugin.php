<?php
// DisableAccount/plugin.php
// Allows users to disable their account

if (!defined("IN_ESO")) exit;

class DisableAccount extends Plugin {

var $id = "DisableAccount";
var $name = "DisableAccount";
var $version = "1.0";
var $description = "Allows users to disable their account";
var $author = "GigaHacer";

function init()
{
	parent::init();

	global $config;

    	// Add our language definitions.
    	$this->eso->addMessage("accountDisabled", "success", "Your account was successfully disabled!");
    	$this->eso->addMessage("accountDisabledEmail", "success", "Within the next minute or two you should receive an email from us containing a link to disable your account. <strong>Check your spam folder</strong> if you don't receive this email shortly!");

	// If we're on the settings page, check to see if we're getting a request to disable the user's account.
	if ($this->eso->action == "settings") {

        	// Add the disable account form.
        	$this->eso->controller->addHook("init", array($this, "addForm"));

        	// If there's a verification hash in the URL, attempt to disable the user's account.
	    	if ($_GET["q2"]) {

            		$hash = $_GET["q2"];

            		// Split the hash into the member ID and password.
	        	$memberId = (int)substr($hash, 0, strlen($hash) - 32);
	        	$password = $this->eso->db->escape(substr($hash, -32));

            		// See if there is a user with this ID and password hash. If there is, disable their account for them.
	        	if (!empty($result = $this->eso->db->result("SELECT email FROM {$config["tablePrefix"]}members WHERE memberId='$memberId' AND password='$password'"))) {

                		$newpassword = md5(generateRandomString(32));

                		$newemail = $result["email"] . ".disabled";

                		// Alright, let's disable the account.
		        	$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET password='$newpassword', email='$newemail' WHERE memberId='$memberId'");

                		// Notify the user.
		        	$this->eso->message("accountDisabled");

                		// Make things nice: log the user out.
	            		unset($_SESSION["user"]);
	            		regenerateToken();
	            		if (isset($_COOKIE[$config["cookieName"]])) setcookie($config["cookieName"], "", -1, "/");
                		redirect("");
	        	}
        	}
	}
}

// Make sure the user's entered password is correct and send them their confirmation email.
function validateDisableAccount()
{
	global $language, $config;

	$salt = $this->eso->db->result("SELECT salt FROM {$config["tablePrefix"]}members WHERE memberId={$this->eso->user["memberId"]}");

    	if (!($result = $this->eso->db->result("SELECT memberId, name, email, password from {$config["tablePrefix"]}members WHERE password='" . md5($salt . $_POST["disableAccount"]["password"]) . "'"))) {
        	$this->eso->message("incorrectPassword");
        	return false;
    	}

    	else {
        	sendEmail($result["email"], "Did you want to disable your account, {$result["name"]}?", "{$result["name"]}, somebody (hopefully you!) has submitted a disable account request for your account on {$config["forumTitle"]}. If you do not wish to disable your account, just ignore this email and nothing will happen.\n\nHowever, if you did submit the request and wish to disable your account, visit the following link:\n{$config["baseURL"]}settings/{$result["memberId"]}{$result["password"]}");
        	return true;
    	}
    
    	return false;
}

// Add the fieldset and form for account disabling.
function addForm(&$settings)
{
	global $language;

    	$settings->addFieldset("disableAccount", "Disable account", false);
    	$settings->addToForm("disableAccount", array(
        	"id" => "password",
            	"html" => "<label>{$language["My current password"]}</label> <input type='password' name='disableAccount[password]' class='text' autocomplete='current-password'/>",
            	"validate" => array($this, "validateCurrentPassword"),
            	"required" => true
    	), 50);
    	$settings->addToForm("disableAccount", array(
        	"id" => "lbl-pass",
            	"html" => "<label></label>" . $this->eso->skin->button(array("value" => $language["Save changes"], "name" => "disableAccount[submit]")),
            	"required" => true
    	), 100);

    	// Change the user's name.
    	if (isset($_POST["disableAccount"]["submit"])
        	and $this->eso->validateToken(@$_POST["token"])
            	and $this->validateDisableAccount()) {
            	$this->eso->message("accountDisabledEmail");
            	redirect("settings");
    	}
}

}

?>
