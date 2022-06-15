<?php
// Invert/plugin.php
// Gives members the ability to use an inverted dark mode.

if (!defined("IN_ESO")) exit;
class Invert extends Plugin {
	
var $id = "Invert";
var $name = "Invert";
var $version = "1.1";
var $description = "Gives members the ability to use an inverted dark mode";
var $author = "eso and GigaHacer";
var $limit = 50;

function init()
{
	parent::init();
	
	// Language definitions.
	$this->eso->addLanguage("useDarkMode", "Selectively invert colors (dark mode)");
	$this->eso->addLanguage("fixOpacity", "Tune the image (low contrast mode)");
	$this->eso->addLanguage("blackBackground", "Keeping this enabled without dark mode may result in eye strain");

	// We'll want to add an invert option on the settings page.
	if ($this->eso->action == "settings") {
		$this->eso->controller->addHook("init", array($this, "useDarkMode"));
		$this->eso->controller->addHook("init", array($this, "fixOpacity"));
	}

    $this->eso->addHook("init", array($this, "setInvert"));
}

function setInvert()
{
    if ((!empty($this->eso->user["useDarkMode"])) && (!empty($this->eso->user["fixOpacity"]))) {
        $this->eso->addCSS("plugins/Invert/both.css");
        return;
    }
    
    if (!empty($this->eso->user["useDarkMode"])) $this->eso->addCSS("plugins/Invert/dark.css");

    if (!empty($this->eso->user["fixOpacity"])) $this->eso->addCSS("plugins/Invert/contrast.css");
}

function useDarkMode(&$settings)
{
	global $language;
	if (!isset($this->eso->user["useDarkMode"])) $_SESSION["useDarkMode"] = $this->eso->user["useDarkMode"] = 0;
	$settings->addToForm("settingsOther", array(
		"id" => "useDarkMode",
		"html" => "<label for='useDarkMode' class='checkbox'>{$language["useDarkMode"]}</label> <input id='useDarkMode' type='checkbox' class='checkbox' name='useDarkMode' value='1' " . ($this->eso->user["useDarkMode"] ? "checked='checked' " : "") . "/>",
		"databaseField" => "useDarkMode",
		"checkbox" => true,
		"required" => true
	), 500);
}

function fixOpacity(&$settings)
{
	global $language;
	if (!isset($this->eso->user["fixOpacity"])) $_SESSION["fixOpacity"] = $this->eso->user["fixOpacity"] = 0;
	$settings->addToForm("settingsOther", array(
		"id" => "fixOpacity",
		"html" => "<label for='fixOpacity' class='checkbox'>{$language["fixOpacity"]}<small style='display:inline-table'>{$language["blackBackground"]}</small></label> <input id='fixOpacity' type='checkbox' class='checkbox' name='fixOpacity' value='1' " . ($this->eso->user["fixOpacity"] ? "checked='checked' " : "") . "/>",
		"databaseField" => "fixOpacity",
		"checkbox" => true,
		"required" => true
	), 600);
}

// Add the table to the database.
function upgrade($oldVersion)
{
	global $config;
	
	if (!$this->eso->db->numRows("SHOW COLUMNS FROM {$config["tablePrefix"]}members LIKE 'useDarkMode'")) {
		$this->eso->db->query("ALTER TABLE {$config["tablePrefix"]}members ADD COLUMN useDarkMode tinyint(1) NOT NULL default '0'");
	}

	if (!$this->eso->db->numRows("SHOW COLUMNS FROM {$config["tablePrefix"]}members LIKE 'fixOpacity'")) {
		$this->eso->db->query("ALTER TABLE {$config["tablePrefix"]}members ADD COLUMN fixOpacity tinyint(1) NOT NULL default '0'");
	}
	
}

}
?>