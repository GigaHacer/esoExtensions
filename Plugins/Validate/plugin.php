<?php
// Validate/plugin.php
// Automatically validates new user accounts.

class Validate extends Plugin {

var $id = "Validate";
var $name = "Validate";
var $version = "1.0";
var $description = "Automatically validates new user accounts";
var $author = "eso";


function init()
{
	parent::init();

	// Add hooks to the join controller so we can automatically validate accounts.
	if ($this->eso->action == "join") {
		$this->eso->controller->addHook("beforeAddMember", array($this, "addAccountField"));
		$this->eso->controller->addHook("afterAddMember", array($this, "logInUser"));
	}
}

function addAccountField(&$controller, &$insertData)
{
	$insertData["account"] = "'Member'";
}

function logInUser(&$controller)
{
	global $config;
	$hash = md5($config["salt"] . $_POST["join"]["password"]);
	$controller->eso->login($_POST["join"]["name"], false, $hash);
	redirect("");
}

}
?>
