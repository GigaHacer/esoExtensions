<?php
// VidLiiEmbeds/plugin.php
// Allows users to embed videos from VidLii.com.

if (!defined("IN_ESO")) exit;

class VidLiiEmbeds extends Plugin {

var $id = "VidLiiEmbeds";
var $name = "VidLiiEmbeds";
var $version = "1.0";
var $description = "Allows users to embed videos from VidLii.com";
var $author = "GigaHacer";

var $spoiler = array();

function init()
{
    parent::init();

    // Add the vidlii formatter that will allow users to embed vidlii videos.
    $this->eso->formatter->addFormatter("vidlii", "Formatter_VidLii");
}

}

class Formatter_VidLii {

var $formatter;
var $modes = array("vidlii", "vidlii_tag", "vidlii_bbcode");
var $revert = array("<vidlii>" => "&lt;vidlii&gt;", "</vidlii>" => "&lt;/vidlii&gt;");

function Formatter_VidLii(&$formatter)
{
    $this->formatter =& $formatter;
}

function format()
{       
        // Map the different forms of vidlii embeds to the same lexer mode, and map a function for this mode.
        $this->formatter->lexer->mapFunction("vidlii", array($this, "vidlii"));
        $this->formatter->lexer->mapHandler("vidlii_tag", "vidlii");
        $this->formatter->lexer->mapHandler("vidlii_bbcode", "vidlii");

        // Add these modes to the lexer.  They are allowed in all modes.
        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "vidlii");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;vidlii&gt;https:\/\/www.vidlii.com\/embed\?v=(?=.*&lt;\/vidlii&gt;)', $mode, "vidlii_tag");
                $this->formatter->lexer->addEntryPattern('&lt;vidlii&gt;https:\/\/www.vidlii.com\/watch\?v=(?=.*&lt;\/vidlii&gt;)', $mode, "vidlii_tag");
                $this->formatter->lexer->addEntryPattern('\[vidlii\]https:\/\/www.vidlii.com\/embed\?v=(?=.*\[\/vidlii\])', $mode, "vidlii_bbcode");
                $this->formatter->lexer->addEntryPattern('\[vidlii\]https:\/\/www.vidlii.com\/watch\?v=(?=.*\[\/vidlii\])', $mode, "vidlii_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/vidlii&gt;', "vidlii_tag");
        $this->formatter->lexer->addExitPattern('\[\/vidlii]', "vidlii_bbcode");
}

// Add HTML iframe tags to the output.
function vidlii($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= "<iframe allowfullscreen src='https://www.vidlii.com/embed?v="; break;
                case LEXER_EXIT: $this->formatter->output .= "' frameborder='0' width='640' height='360'></iframe>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Revert iframe tags to their formatting code.
function revert($string)
{
        // Remove the button from the tag.
        if (preg_match("/<iframe(.*?)>/", $string)) $string = str_replace("<iframe allowfullscreen src='", "<vidlii>", $string);
        // Clean up the end of the tag.
        $string = str_replace("' frameborder='0' width='640' height='360'></iframe>", "</vidlii>", $string);
        return $string;
}

}