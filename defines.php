<?php

namespace ITSAlaska\plugins\customStyling;

define(__NAMESPACE__.'\PATH',ABSPATH.preg_replace('%^'.$_SERVER['DOCUMENT_ROOT'].'/%','',dirname(__FILE__)).'/');
define(__NAMESPACE__.'\FILE',PATH.'custom-styling.php');
define(__NAMESPACE__.'\NAME','Custom Styling');
// Note that the SLUG define is used for database option names. If this is changed, all CSS and settings will be lost.
define(__NAMESPACE__.'\SLUG','custom_styling');