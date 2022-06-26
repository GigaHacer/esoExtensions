<?php
// Spoilers/plugin.php
// Allows users to place content in spoiler tags.

if (!defined("IN_ESO")) exit;

class Spoilers extends Plugin {

var $id = "Spoilers";
var $name = "Spoilers";
var $version = "1.0";
var $description = "Allows users to place content in spoiler tags";
var $author = "eso";

var $spoiler = array();

function init()
{
    parent::init();
	
    // Language definitions.
    $this->eso->addLanguage("showSpoiler", "Show spoiler");
    $this->eso->addLanguage("hideSpoiler", "Hide spoiler");

    // Add the spoiler formatter that will parse and unparse spoilers.
    $this->eso->formatter->addFormatter("spoiler", "Formatter_Spoiler");

    // If we're on the conversation view, add the spoilers CSS.
    $this->eso->addCSS("plugins/Spoilers/spoilers.css");
}

}

class Formatter_Spoiler {

var $formatter;
var $modes = array("spoiler", "spoiler_tag", "spoiler_bbcode");
var $revert = array("<spoiler>" => "&lt;spoiler&gt;", "</spoiler>" => "&lt;/spoiler&gt;");

function Formatter_Spoiler(&$formatter)
{
    $this->formatter =& $formatter;
}

function format()
{       
        // Map the different forms of spoilers to the same lexer mode, and map a function for this mode.
        $this->formatter->lexer->mapFunction("spoiler", array($this, "spoiler"));
        $this->formatter->lexer->mapHandler("spoiler_tag", "spoiler");
        $this->formatter->lexer->mapHandler("spoiler_bbcode", "spoiler");

        // Add these spoiler modes to the lexer.  They are allowed in all modes.
        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "spoiler");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;spoiler&gt;(?=.*&lt;\/spoiler&gt;)', $mode, "spoiler_tag");
                $this->formatter->lexer->addEntryPattern('\[spoiler](?=.*\[\/spoiler])', $mode, "spoiler_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/spoiler&gt;', "spoiler_tag");
        $this->formatter->lexer->addExitPattern('\[\/spoiler]', "spoiler_bbcode");
}

// Add HTML details tags to the output.
function spoiler($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= "<details><summary>Spoiler</summary>"; break;
                case LEXER_EXIT: $this->formatter->output .= "</details>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Revert details tags to their formatting code.
function revert($string)
{
        // Remove the button from the tag.
        if (preg_match("/<details(.*?)>/", $string)) $string = str_replace("<details><summary>Spoiler</summary>", "<spoiler>", $string);
        // Clean up the end of the tag.
        $string = str_replace("</details>", "</spoiler>", $string);
        return $string;
}

}