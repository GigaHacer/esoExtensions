<?php
// ProfilesPlus/plugin.php
// Allows users to further customize their profiles.

class ProfilesPlus extends Plugin {

var $id = "ProfilesPlus";
var $name = "ProfilesPlus";
var $version = "1.0";
var $description = "Allows users to further customize their profiles";
var $author = "GigaHacer";


function init()
{
	global $config, $language;

	parent::init();

	// Language definitions.
	$this->eso->addLanguage("Bio", "Bio");
    	$this->eso->addLanguage("Signature", "Signature");
    	$this->eso->addLanguage("BioDesc", "Bio</br><small>(appears on your profile under your avatar)</small>");
    	$this->eso->addLanguage("SignatureDesc", "Signature</br><small>(appears after all your posts)</small>");

    	// If we're on the settings view, add the extra profile settings and handle requests.
	if ($this->eso->action == "settings") {
		$this->eso->controller->addHook("settingsPageEnd", array($this, "addSettings"));

        // This is the part where we handle requests to set the bio and signature.
        $this->eso->controller->addHook("init", array($this, "handleRequests"));
	}

    	// If we're on the profile view, add the bio and the bio css.
    	if ($this->eso->action == "profile") {
        	$this->eso->addCSS("plugins/ProfilesPlus/bio.css");
        	$this->eso->controller->addHook("profileInfo", array($this, "addBio"));
    	}

    	// If we're on the conversation view, add the signatures to the posts.
    	if ($this->eso->action == "conversation") {
        	// First, get the memberIds from the conversation's posts.
        	$signatureResult = $this->eso->db->query("SELECT memberId FROM {$config["tablePrefix"]}posts WHERE conversationId='" . $_GET["q2"] . "' ORDER BY time");

        	$n = 0;

        	// Make an empty array to fill with our result.
        	$signatureArray = [];

        	while ($s = $this->eso->db->fetchAssoc($signatureResult))
        	{
            		// For whatever reason, these variables have to be global'd here. If they aren't the plugin breaks.
            		global $n, $signatureArray;
            		$n++;
            		$signatureArray[$n] = $s["memberId"];
        	}

        	$e = 0;

        	$this->eso->controller->addHook("postFooter", array($this, "addSignature"));
	}
}

function addSettings(&$settings)
{
	global $config, $language;

    	$profilesPlusInfo = $this->eso->db->query("SELECT bio, signature FROM {$config["tablePrefix"]}members WHERE memberId={$this->eso->user["memberId"]}");

    	// To avoid cosmetic issues, update the bio and signature session variables.
    	while ($p = $this->eso->db->fetchAssoc($profilesPlusInfo)) {
        	$_SESSION["user"]["bio"] = $p["bio"];
        	$_SESSION["user"]["signature"] = $p["signature"];
    	}

    	echo "<form action='" . makeLink("settings") . "' method='post'><input type='hidden' name='token' value='" . $_SESSION["token"] . "'/><fieldset id='settingsBio'><legend>{$language["Bio"]}</legend><ul class='form' id='settingsBioForm'><li><label>{$language["BioDesc"]}</label> <input type='text' name='settingsBio[bio]' class='text' value='" . $_SESSION["user"]["bio"] . "'/></li><li><label id='lbl-pass'></label> " . $this->eso->skin->button(array("value"=>$language["Save changes"],"name"=>"settingsBio[submit]")) . "</li></ul></fieldset></form>";

    	echo "<form action='" . makeLink("settings") . "' method='post'><input type='hidden' name='token' value='" . $_SESSION["token"] . "'/><fieldset id='settingsSignature'><legend>{$language["Signature"]}</legend><ul class='form' id='settingsSignatureForm'><li><label>{$language["SignatureDesc"]}</label> <input type='text' name='settingsSignature[signature]' class='text' value='" . $_SESSION["user"]["signature"] . "'/></li><li><label id='lbl-pass'></label> " . $this->eso->skin->button(array("value"=>$language["Save changes"],"name"=>"settingsSignature[submit]")) . "</li></ul></fieldset></form>";
}

function handleRequests()
{
	global $config, $language;

	if (isset($_POST["settingsBio"]["bio"])) {
		// Make sure the bio isn't too long or too short.
            	if (strlen($_POST["settingsBio"]["bio"]) < 1) {
                	$this->eso->message("bioTooShort");
            	}

            	elseif (strlen($_POST["settingsBio"]["bio"]) > 3000) {
                	$this->eso->message("bioTooLong");
            	}

            	else {
                	$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET bio='" . $_POST["settingsBio"]["bio"] . "' WHERE memberId={$this->eso->user["memberId"]}");
                	$this->eso->message("changesSaved");
                	redirect("settings");
            	}
        }

        if (isset($_POST["settingsSignature"]["signature"])) {
		// Make sure the signature isn't too long or too short.
            	if (strlen($_POST["settingsSignature"]["signature"]) < 1) {
                	$this->eso->message("signatureTooShort");
            	}

            	elseif (strlen($_POST["settingsSignature"]["signature"]) > 3000) {
                	$this->eso->message("signatureTooLong");
            	}

            	else {
                	$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET signature='" . $_POST["settingsSignature"]["signature"] . "' WHERE memberId={$this->eso->user["memberId"]}");
                	$this->eso->message("changesSaved");
                	redirect("settings");
            	}
        }
}

// Add the bio to the profile page.
function addBio()
{
	global $config;

    	// Get the memberId from the url.
    	if (isset($_GET["q2"])) {
        	$memId = $_GET["q2"];
    	}

    	// If none is specified, they must be viewing their own profile.
    	else {
        	$memId = $this->eso->user["memberId"];
    	}

    	// Make a query for the current profile's bio.
    	$bioResult = $this->eso->db->result("SELECT bio FROM {$config["tablePrefix"]}members WHERE memberId='" . $memId . "'");

    	// Print the bio if they have one set.
    	if (isset($bioResult)) {
        	echo "</p><div class='profileBio'>" . desanitize($bioResult) . "</div>";
    	}
}

// Add the signatures to the posts.
function addSignature()
{
	// All these variables need to be global for whatever reason otherwise everything breaks.
    	global $config, $e, $language, $signatureArray;

    	// Counter variable to see how many times the loop has run. Very hacky, I know.
    	$e++;

    	// This is the part that actually gets the signature from the database.
    	$signature = $this->eso->db->result("SELECT signature FROM {$config["tablePrefix"]}members WHERE memberId='" . $signatureArray[$e] . "'");

    	// If they have one set, print it!
    	if (isset($signature)) echo "<hr><p>" . desanitize($signature) . "</p>";
}

// Add the table to the database.
function upgrade($oldVersion)
{
	global $config;
 
	if (!$this->eso->db->numRows("SHOW COLUMNS FROM {$config["tablePrefix"]}members LIKE 'bio'")) {
		$this->eso->db->query("ALTER TABLE {$config["tablePrefix"]}members ADD COLUMN bio text default NULL");
	}

    if (!$this->eso->db->numRows("SHOW COLUMNS FROM {$config["tablePrefix"]}members LIKE 'signature'")) {
		$this->eso->db->query("ALTER TABLE {$config["tablePrefix"]}members ADD COLUMN signature text default NULL");
	}
}

}

?>