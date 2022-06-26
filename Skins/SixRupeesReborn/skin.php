<?php
// SixRupeesReborn/skin.php
// SixRupeesReborn skin file.
// Created by GigaHacer
// Inspired by the original 6rupees skin recovered from archive.org.

if (!defined("IN_ESO")) exit;

class SixRupeesReborn extends Skin {

var $name = "SixRupeesReborn";
var $version = "1.0";
var $author = "GigaHacer";
var $numberOfColors = 27;

// Add stylesheets and a favicon to the page header.
function init()
{
	global $config;
	$this->eso->addCSS("skins/{$config["skin"]}/styles.css");
    $this->eso->addToHead("<link rel='shortcut icon' type='image/ico' href='skins/{$config["skin"]}/favicon.ico'/>");
	$this->eso->addCSS("skins/{$config["skin"]}/base.css");
	$this->eso->addCSS("skins/ie6.css", "ie6");
	$this->eso->addCSS("skins/ie7.css", "ie7");
}

// Generate button HTML.
function button($attributes)
{
	$class = $id = $style = ""; $attr = " type='submit'";
	foreach ($attributes as $k => $v) {
		if ($k == "class") $class = " $v";
		elseif ($k == "id") $id = " id='$v'";
		elseif ($k == "style") $style = " style='$v'";
		else $attr .= " $k='$v'";
	}
	return "<span class='button$class'$id$style><input$attr/></span>";
}

}

?>