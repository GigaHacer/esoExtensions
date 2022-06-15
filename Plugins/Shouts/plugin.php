<?php
// Shouts/plugin.php
// Allows members to leave short comments on other member profiles.
 
if (!defined("IN_ESO")) exit;
 
class Shouts extends Plugin {
 
var $id = "Shouts";
var $name = "Shouts";
var $version = "1.0";
var $description = "Allows members to leave short comments on other member profiles";
var $author = "eso";

var $limit = 50;
 
function init()
{
	parent::init();
 
	// Language definitions.
	$this->eso->addLanguage("Shouts", "Shouts");
	$this->eso->addLanguage("Shout it", "Shout it!");
	$this->eso->addLanguage("Type a shout here", "Type a shout here...");
	$this->eso->addLanguage("viewMoreShouts", "Your search found more than " . ($this->limit) . " shouts.");
	$this->eso->addLanguage("confirmDeleteShout", "Are you sure you want to delete this shout?");
	$this->eso->addLanguage("emailOnNewShout", "Email me when someone adds a shout to my profile");
	$this->eso->addLanguage(array("emails", "newShout", "subject"), "%s, someone shouted on your profile");
	$this->eso->addLanguage(array("emails", "newShout", "body"), "%s, %s has added a shout to your profile!\n\nTo view the new activity, check out the following link:\n%s");
 
	// If we're on the profile view, initiate all the shout stuff.
	if ($this->eso->action == "profile") {
		$this->eso->controller->addHook("init", array($this, "addShoutsSection"));
		$this->eso->controller->addHook("ajax", array($this, "ajax"));
		$this->eso->addScript("plugins/Shouts/shouts.js");
		$this->eso->addCSS("plugins/Shouts/shouts.css");
		$this->eso->addLanguageToJS("confirmDeleteShout");
	}
}

// Add the shouts section to the profile view.
function addShoutsSection(&$controller)
{
	global $language;
	$this->member =& $controller->member;
 
	// Do we need to add or delete a shout?
	if (isset($_POST["shoutSubmit"]) and $this->eso->validateToken(@$_POST["token"]))
	 	$this->addShout(@$_POST["shoutContent"]);
	if (isset($_GET["deleteShout"]) and $this->eso->validateToken(@$_GET["token"]) and $shoutId = (int)$_GET["deleteShout"])
		$this->deleteShout($shoutId);
 
	// Get the shouts and generate the shout HTML.
	if (!empty($_GET["limit"])) $this->limit = max(0, (int)$_GET["limit"]);
	$controller->shouts = $this->getShouts($controller->member["memberId"], $this->limit);
	$section = "
<div class='hdr'><h3>{$language["Shouts"]}</h3></div>
<div class='body shouts'>";
 
	// If the user is not logged in, they can't send a shout!  Otherwise, show the send shout form.
	if (!$this->eso->user) $section .= $this->eso->htmlMessage("loginRequired");
    elseif ($this->eso->isUnvalidated()) $section .= $this->eso->htmlMessage("noPermission");
	else $section .= "<form action='" . curLink() . "' method='post' id='shoutForm'><div>
<input type='hidden' name='token' value='{$_SESSION["token"]}'/>
<input type='text' class='text' id='shoutContent' name='shoutContent'/> " . $this->eso->skin->button(array("value" => $language["Shout it"], "id" => "shoutSubmit", "name" => "shoutSubmit")) . "
<script type='text/javascript'>// <![CDATA[
makePlaceholder(getById(\"shoutContent\"), \"{$language["Type a shout here"]}\");
// ]]></script>
</div></form>";
 
	// Loop through the shouts and output them.
	$section .= "<div id='shouts'>";
	foreach ($controller->shouts as $shout)
		$section .= "<div id='shout{$shout["shoutId"]}'>" . $this->htmlShout($shout) . "</div>";
	$section .= "</div>";
 
	// If there are more shouts, show a 'view more' link.
	if ($this->showViewMore) $section .= "<div id='more'><div class='msg info'>{$language["viewMoreShouts"]} <a href='" . makeLink("profile", $this->member["memberId"], "?limit=" . ($this->limit + 50)) . "'>View more</a></div></div>";
	$section .= "</div>";
 
	// Initialize the shout javascript.
	if ($this->eso->user) $section .= "<script type='text/javascript'>// <![CDATA[
Shouts.member={$controller->member["memberId"]};Shouts.init();
// ]]></script>";
 
	// Add the section!
	$controller->addSection($section);
}
 
// Shout AJAX handler.
function ajax(&$controller)
{
	global $config;
	switch ($_POST["action"]) {
 
		// Add a new shout.
		case "shout":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			
			// Does this member exist?
			if (!($this->member = $controller->getMember($_POST["memberTo"]))) {
				$this->eso->message("memberDoesntExist");
				return;
			}
			// Return the shout HTML and the shout ID if we successfully add the shout.
			if ($shout = $this->addShout($_POST["content"])) {
				return array(
					"html" => $this->htmlShout($shout + array("name" => $this->eso->user["name"], "color" => $this->eso->user["color"], "avatarFormat" => $this->eso->user["avatarFormat"])),
					"shoutId" => $shout["shoutId"]
				);
			}
			break;
 
		// Delete a shout. Easy!
		case "deleteShout":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			$this->deleteShout($_POST["shoutId"]);
	}
}
 
// Fetch the shouts from the database. 
function getShouts($memberId, $limit = 50)
{
	global $config;
 
	$shouts = array();
	$memberId = (int)$memberId;
	$result = $this->eso->db->query("SELECT shoutId, memberFrom, name, color, avatarFormat, time, content FROM {$config["tablePrefix"]}shouts LEFT JOIN {$config["tablePrefix"]}members ON (memberId=memberFrom) WHERE memberTo=$memberId ORDER BY time DESC, shoutId DESC LIMIT " . ($limit + 1));
 
	// We selected $limit + 1 results; if there is that +1 result, there are more results to display.
	$this->showViewMore = $this->eso->db->numRows($result) > $limit;
 
	// Put the results into an array.
	for ($i = 0; $i < $limit and $shout = $this->eso->db->fetchAssoc($result); $i++) $shouts[] = $shout;
	return $shouts;
}
 
// Generate the HTML for an individual shout.
function htmlShout($shout)
{
	global $language;
 
	// Generate the shout wrapper, avatar, name, and time.
	$output = "<div class='p c{$shout["color"]}'><div class='hdr'>
<img src='" . $this->eso->getAvatar($shout["memberFrom"], $shout["avatarFormat"], "thumb") . "' alt='' class='avatar thumb'/>
<div class='pInfo'><h4><a href='" . makeLink("profile", $shout["memberFrom"]) . "'>{$shout["name"]}</a></h4><br/><span>" . relativeTime($shout["time"]) . "</span></div>";
 
	// If the user can delete this shout, show the delete link.
	if ($this->canDeleteShout($shout["memberFrom"], $this->member["memberId"]) === true)
		$output .= "<div class='controls'><a href='" . makeLink("profile", $this->member["memberId"], "?deleteShout={$shout["shoutId"]}&token={$_SESSION["token"]}") . "' onclick='Shouts.deleteShout({$shout["shoutId"]});return false'>{$language["delete"]}</a></div>";
 
	// Finally, the shout content.
	$output .= "<p>{$shout["content"]}</p>
</div></div>";
	return $output;
}

// Validate a shout.
function validateShout($shout)
{
	global $config;
	if (function_exists('mb_strlen')) { // Use multibytes for UTF-8 support.
		if (mb_strlen($shout, "UTF-8") > $config["maxCharsPerPost"]) return "postTooLong";
		if (!mb_strlen($shout, "UTF-8")) return "emptyPost";
	} else {
		if (strlen($shout) > $config["maxCharsPerPost"]) return "postTooLong";
		if (!strlen($shout)) return "emptyPost";
	}
}
 
// Add a shout to the database.
function addShout($content)
{
	global $config;

    // Does the user have permission? Is the post content valid? Flood control?
	if (($error = !$content ? "emptyPost" : false) or ($error = $this->validateShout($content)) or (($error = $this->canAddShout()) !== true) or ($error = $this->eso->db->result("SELECT 1 FROM {$config["tablePrefix"]}shouts WHERE memberFrom={$this->eso->user["memberId"]} AND time>" . (time() - $config["timeBetweenPosts"]), 0) ? "waitToReply" : false)) {
		$this->eso->message($error);
		return;
	}
 
	// Prepare and add the shout to the database.
	$shout = array(
		"memberTo" => $this->member["memberId"],
		"memberFrom" => $this->eso->user["memberId"],
		"time" => time(),
		"content" => $this->eso->formatter->format($content, array("bold", "italic", "strikethrough", "superscript", "link", "fixedInline", "specialCharacters", "emoticons"))
	);
 
	$this->eso->db->query("INSERT INTO {$config["tablePrefix"]}shouts (memberTo, memberFrom, time, content) VALUES ({$shout["memberTo"]}, {$shout["memberFrom"]}, {$shout["time"]}, '" . $this->eso->db->escape($shout["content"]) . "')");
	$shout["shoutId"] = $this->eso->db->lastInsertId();
 
	return $shout;
}
 
// Delete a shout from the database.
function deleteShout($shoutId)
{
	global $config;
	$shoutId = (int)$shoutId;
 
	// Can we find the shout we're trying to delete?  Are we allowed to delete it?
	if (!(list($memberFrom, $memberTo) = $this->eso->db->fetchRow("SELECT memberFrom, memberTo FROM {$config["tablePrefix"]}shouts WHERE shoutId=$shoutId"))) return;
	if (($error = $this->canDeleteShout($memberFrom, $memberTo)) !== true) {
		$this->eso->message($error);
		return;
	}
 
	// Goodbye!
	$this->eso->db->query("DELETE FROM {$config["tablePrefix"]}shouts WHERE shoutId=$shoutId");
}
 
// Does the current user have permission to add a shout?  They must be logged in, not suspended, and can't have shouted 
// recently.
function canAddShout()
{
	if (!$this->eso->user) return "noPermission";
	if ($this->eso->isSuspended()) return "suspended";
    if ($this->eso->isUnvalidated()) return "noPermission";
	global $config;
	if ($this->eso->db->result("SELECT 1 FROM {$config["tablePrefix"]}shouts WHERE memberFrom={$this->eso->user["memberId"]} AND time>UNIX_TIMESTAMP()-{$config["timeBetweenPosts"]}", 0)) return "waitToReply";
	return true;
}
 
// Does the current user have permission to delete a shout?  They must be the receiver or the sender of the shout.
function canDeleteShout($memberFrom, $memberTo)
{
	if (!$this->eso->user["moderator"] and $this->eso->user["memberId"] != $memberFrom and $this->eso->user["memberId"] != $memberTo) return "noPermission";
	return true;
}
 
// Add the table to the database.
function upgrade($oldVersion)
{
	global $config;
 
	if (!$oldVersion) {
		if ($this->eso->db->numRows("SHOW TABLES LIKE '{$config["tablePrefix"]}shouts'")) return;
		$this->eso->db->query("CREATE TABLE {$config["tablePrefix"]}shouts (
			shoutId int unsigned NOT NULL auto_increment,
			memberTo int unsigned NOT NULL,
			memberFrom int unsigned NOT NULL,
			time int unsigned NOT NULL,
			content text NOT NULL,
			PRIMARY KEY  (shoutId)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}
 
}
 
}
 
?>
