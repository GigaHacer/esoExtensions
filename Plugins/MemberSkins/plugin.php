<?php
// MemberSkins/plugin.php
// Allows users to change their skins.

if (!defined("IN_ESO")) exit;

class MemberSkins extends Plugin {

var $id = "MemberSkins";
var $name = "MemberSkins";
var $version = "1.0";
var $description = "Allows users to change their skins";
var $author = "grntbg";

function init()
{
	parent::init();
	
	// Language definitions.
	$this->eso->addLanguage("forumSkin", "Forum skin");

	// If we're on the settings view, add the skin settings!
	if ($this->eso->action == "settings") {
		$this->eso->controller->addHook("init", array($this, "addSkinSettings"));
	}

	$this->eso->addHook("init", array($this, "setSkin"));
}

function setSkin()
{
    // Apply the stylesheet for the skin that the user has selected.
	if (isset($this->eso->user["skin"])) $this->eso->addCSS("skins/" . $this->eso->user["skin"] . "/styles.css");
}

// Loop through the skins directory to create a string of options to go in the skin <select> tag.
function addSkinSettings(&$settings)
{
	global $language, $config;
	$this->skins = $this->eso->getSkins();

	$skinOptions = "";
	$memberId = $this->eso->user["memberId"];
//	if (in_array(@$_POST["validateSkin"], $this->skins)) $skinOptions["skin"] = $_POST["validateSkin"];

	foreach ($this->skins as $v) {
		$value = ($v == $config["skin"]) ? "" : $v;
		$skinOptions .= "<option value='$value'" . ($this->eso->db->result("SELECT skin FROM {$config["tablePrefix"]}members WHERE memberId=$memberId", 0) == $value ? " selected='selected'" : "") . ">$v</option>";
	}

	$settings->addToForm("settingsOther", array(
		"id" => "skin",
		"html" => "<label>{$language["forumSkin"]}</label> <select id='skin' name='skin'>$skinOptions</select>",
		"databaseField" => "skin",
		"required" => true,
		"validate" => array($this, "validateSkin")
	), 150);
}

// Validate the skin field: make sure the selected skin actually exists.
function validateSkin(&$skin)
{
	global $config;
	if (!in_array($skin, $this->skins)) $skin = "";

	// Change the user's skin.
	$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET skin='$skin' WHERE memberId={$this->eso->user["memberId"]}");
	$this->eso->user["skin"] = $_SESSION["user"]["skin"] = $skin;
}

// Add the table to the database.
function upgrade($oldVersion)
{
	global $config;
	if (!$this->eso->db->numRows("SHOW COLUMNS FROM {$config["tablePrefix"]}members LIKE 'skin'")) {
		$this->eso->db->query("ALTER TABLE {$config["tablePrefix"]}members ADD COLUMN skin varchar(255) default NULL");
	}
}

}

?>
