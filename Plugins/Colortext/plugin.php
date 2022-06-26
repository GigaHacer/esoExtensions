<?php
// Colortext/plugin.php
// Allows users to color their text.

if (!defined("IN_ESO")) exit;

class Colortext extends Plugin {

var $id = "Colortext";
var $name = "Colortext";
var $version = "1.0";
var $description = "Allows users to color their text";
var $author = "GigaHacer";

var $spoiler = array();

function init()
{
    parent::init();

    // Add the colortext formatter that will format the text.
    $this->eso->formatter->addFormatter("colortext", "Formatter_Colortext");
}

}

class Formatter_Colortext {

var $formatter;
var $modes = array("greentext", "greentext_tag", "greentext_bbcode", "bluetext", "bluetext_tag", "bluetext_bbcode", "redtext", "redtext_tag", "redtext_bbcode", "browntext", "browntext_tag", "browntext_bbcode", "yellowtext", "yellowtext_tag", "yellowtext_bbcode", "whitetext", "whitetext_tag", "whitetext_bbcode", "orangetext", "orangetext_tag", "orangetext_bbcode", "purpletext", "purpletext_tag", "purpletext_bbcode", "blacktext", "blacktext_tag", "blacktext_bbcode", "pinktext", "pinktext_tag", "pinktext_bbcode");
var $revert = array("<greentext>" => "&lt;greentext&gt;", "</greentext>" => "&lt;/greentext&gt;", "<bluetext>" => "&lt;bluetext&gt;", "</bluetext>" => "&lt;/bluetext&gt;", "<redtext>" => "&lt;redtext&gt;", "</redtext>" => "&lt;/redtext&gt;", "<browntext>" => "&lt;browntext&gt;", "</browntext>" => "&lt;/browntext&gt;", "<yellowtext>" => "&lt;yellowtext&gt;", "</yellowtext>" => "&lt;/yellowtext&gt;", "<whitetext>" => "&lt;whitetext&gt;", "</whitetext>" => "&lt;/whitetext&gt;", "<orangetext>" => "&lt;orangetext&gt;", "</orangetext>" => "&lt;/orangetext&gt;", "<purpletext>" => "&lt;purpletext&gt;", "</purpletext>" => "&lt;/purpletext&gt;", "<blacktext>" => "&lt;blacktext&gt;", "</blacktext>" => "&lt;/blacktext&gt;", "<pinktext>" => "&lt;pinktext&gt;", "</pinktext>" => "&lt;/pinktext&gt;");

function Formatter_Colortext(&$formatter)
{
    $this->formatter =& $formatter;
}

function format()
{       
        // Map the different forms of colortext to the same lexer mode, and map a function for this mode.
        $this->formatter->lexer->mapFunction("greentext", array($this, "greentext"));
        $this->formatter->lexer->mapHandler("greentext_tag", "greentext");
        $this->formatter->lexer->mapHandler("greentext_bbcode", "greentext");

        $this->formatter->lexer->mapFunction("bluetext", array($this, "bluetext"));
        $this->formatter->lexer->mapHandler("bluetext_tag", "bluetext");
        $this->formatter->lexer->mapHandler("bluetext_bbcode", "bluetext");

        $this->formatter->lexer->mapFunction("redtext", array($this, "redtext"));
        $this->formatter->lexer->mapHandler("redtext_tag", "redtext");
        $this->formatter->lexer->mapHandler("redtext_bbcode", "redtext");

        $this->formatter->lexer->mapFunction("browntext", array($this, "browntext"));
        $this->formatter->lexer->mapHandler("browntext_tag", "browntext");
        $this->formatter->lexer->mapHandler("browntext_bbcode", "browntext");

        $this->formatter->lexer->mapFunction("yellowtext", array($this, "yellowtext"));
        $this->formatter->lexer->mapHandler("yellowtext_tag", "yellowtext");
        $this->formatter->lexer->mapHandler("yellowtext_bbcode", "yellowtext");

        $this->formatter->lexer->mapFunction("whitetext", array($this, "whitetext"));
        $this->formatter->lexer->mapHandler("whitetext_tag", "whitetext");
        $this->formatter->lexer->mapHandler("whitetext_bbcode", "whitetext");

        $this->formatter->lexer->mapFunction("orangetext", array($this, "orangetext"));
        $this->formatter->lexer->mapHandler("orangetext_tag", "orangetext");
        $this->formatter->lexer->mapHandler("orangetext_bbcode", "orangetext");

        $this->formatter->lexer->mapFunction("purpletext", array($this, "purpletext"));
        $this->formatter->lexer->mapHandler("purpletext_tag", "purpletext");
        $this->formatter->lexer->mapHandler("purpletext_bbcode", "purpletext");

        $this->formatter->lexer->mapFunction("blacktext", array($this, "blacktext"));
        $this->formatter->lexer->mapHandler("blacktext_tag", "blacktext");
        $this->formatter->lexer->mapHandler("blacktext_bbcode", "blacktext");

        $this->formatter->lexer->mapFunction("pinktext", array($this, "pinktext"));
        $this->formatter->lexer->mapHandler("pinktext_tag", "pinktext");
        $this->formatter->lexer->mapHandler("pinktext_bbcode", "pinktext");

        // Add these modes to the lexer.  They are allowed in all modes.
        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "greentext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;greentext&gt;(?=.*&lt;\/greentext&gt;)', $mode, "greentext_tag");
                $this->formatter->lexer->addEntryPattern('\[greentext](?=.*\[\/greentext])', $mode, "greentext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/greentext&gt;', "greentext_tag");
        $this->formatter->lexer->addExitPattern('\[\/greentext]', "greentext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "bluetext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;bluetext&gt;(?=.*&lt;\/bluetext&gt;)', $mode, "bluetext_tag");
                $this->formatter->lexer->addEntryPattern('\[bluetext](?=.*\[\/bluetext])', $mode, "bluetext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/bluetext&gt;', "bluetext_tag");
        $this->formatter->lexer->addExitPattern('\[\/bluetext]', "bluetext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "redtext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;redtext&gt;(?=.*&lt;\/redtext&gt;)', $mode, "redtext_tag");
                $this->formatter->lexer->addEntryPattern('\[redtext](?=.*\[\/redtext])', $mode, "redtext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/redtext&gt;', "redtext_tag");
        $this->formatter->lexer->addExitPattern('\[\/redtext]', "redtext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "browntext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;browntext&gt;(?=.*&lt;\/browntext&gt;)', $mode, "browntext_tag");
                $this->formatter->lexer->addEntryPattern('\[browntext](?=.*\[\/browntext])', $mode, "browntext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/browntext&gt;', "browntext_tag");
        $this->formatter->lexer->addExitPattern('\[\/browntext]', "browntext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "yellowtext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;yellowtext&gt;(?=.*&lt;\/yellowtext&gt;)', $mode, "yellowtext_tag");
                $this->formatter->lexer->addEntryPattern('\[yellowtext](?=.*\[\/yellowtext])', $mode, "yellowtext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/yellowtext&gt;', "yellowtext_tag");
        $this->formatter->lexer->addExitPattern('\[\/yellowtext]', "yellowtext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "whitetext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;whitetext&gt;(?=.*&lt;\/whitetext&gt;)', $mode, "whitetext_tag");
                $this->formatter->lexer->addEntryPattern('\[whitetext](?=.*\[\/whitetext])', $mode, "whitetext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/whitetext&gt;', "whitetext_tag");
        $this->formatter->lexer->addExitPattern('\[\/whitetext]', "whitetext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "orangetext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;orangetext&gt;(?=.*&lt;\/orangetext&gt;)', $mode, "orangetext_tag");
                $this->formatter->lexer->addEntryPattern('\[orangetext](?=.*\[\/orangetext])', $mode, "orangetext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/orangetext&gt;', "orangetext_tag");
        $this->formatter->lexer->addExitPattern('\[\/orangetext]', "orangetext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "purpletext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;purpletext&gt;(?=.*&lt;\/purpletext&gt;)', $mode, "purpletext_tag");
                $this->formatter->lexer->addEntryPattern('\[purpletext](?=.*\[\/purpletext])', $mode, "purpletext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/purpletext&gt;', "purpletext_tag");
        $this->formatter->lexer->addExitPattern('\[\/purpletext]', "purpletext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "blacktext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;blacktext&gt;(?=.*&lt;\/blacktext&gt;)', $mode, "blacktext_tag");
                $this->formatter->lexer->addEntryPattern('\[blacktext](?=.*\[\/blacktext])', $mode, "blacktext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/blacktext&gt;', "blacktext_tag");
        $this->formatter->lexer->addExitPattern('\[\/blacktext]', "blacktext_bbcode");

        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "pinktext");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;pinktext&gt;(?=.*&lt;\/pinktext&gt;)', $mode, "pinktext_tag");
                $this->formatter->lexer->addEntryPattern('\[pinktext](?=.*\[\/pinktext])', $mode, "pinktext_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/pinktext&gt;', "pinktext_tag");
        $this->formatter->lexer->addExitPattern('\[\/pinktext]', "pinktext_bbcode");
}

// Add HTML details tags to the output.
function greentext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="green">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function bluetext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="blue">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function redtext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="red">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function browntext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="brown">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function yellowtext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="yellow">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function whitetext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="white">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function orangetext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="orange">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function purpletext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="purple">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function blacktext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="black">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Add HTML details tags to the output.
function pinktext($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= '<font color="pink">'; break;
                case LEXER_EXIT: $this->formatter->output .= "</font>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Revert details tags to their formatting code.
function revert($string)
{
        if (preg_match('/<font color="green"(.*?)>/', $string)) {
        $string = str_replace('<font color="green">', "<greentext>", $string);
        $string = str_replace("</font>", "</greentext>", $string);
        }
        elseif (preg_match('/<font color="blue"(.*?)>/', $string)) {
        $string = str_replace('<font color="blue">', "<bluetext>", $string);
        $string = str_replace("</font>", "</bluetext>", $string);
        }
        elseif (preg_match('/<font color="red"(.*?)>/', $string)) {
        $string = str_replace('<font color="red">', "<redtext>", $string);
        $string = str_replace("</font>", "</redtext>", $string);
        }
        elseif (preg_match('/<font color="brown"(.*?)>/', $string)) {
        $string = str_replace('<font color="brown">', "<browntext>", $string);
        $string = str_replace("</font>", "</browntext>", $string);
        }
        elseif (preg_match('/<font color="yellow"(.*?)>/', $string)) {
        $string = str_replace('<font color="yellow">', "<yellowtext>", $string);
        $string = str_replace("</font>", "</yellowtext>", $string);
        }
        elseif (preg_match('/<font color="white"(.*?)>/', $string)) {
        $string = str_replace('<font color="white">', "<whitetext>", $string);
        $string = str_replace("</font>", "</whitetext>", $string);
        }
        elseif (preg_match('/<font color="orange"(.*?)>/', $string)) {
        $string = str_replace('<font color="orange">', "<orangetext>", $string);
        $string = str_replace("</font>", "</orangetext>", $string);
        }
        elseif (preg_match('/<font color="purple"(.*?)>/', $string)) {
        $string = str_replace('<font color="purple">', "<purpletext>", $string);
        $string = str_replace("</font>", "</purpletext>", $string);
        }
        elseif (preg_match('/<font color="black"(.*?)>/', $string)) {
        $string = str_replace('<font color="black">', "<blacktext>", $string);
        $string = str_replace("</font>", "</blacktext>", $string);
        }
        elseif (preg_match('/<font color="pink"(.*?)>/', $string)) {
        $string = str_replace('<font color="pink">', "<pinktext>", $string);
        $string = str_replace("</font>", "</pinktext>", $string);
        }
        return $string;
}

}