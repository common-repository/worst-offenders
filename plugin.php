<?php
/*
Plugin Name: Worst Offenders
Plugin URI: http://boakes.org/worst-offenders-plugin
Description: Worst Offenders helps identify (and remove) the most insistent spammers, making the search for false-positives far easier.
Author: Rich Boakes
Version: 3.0.0 pre-alpha
Author URI: http://boakes.org/
*/

$wo3_title="WorstOffenders";

include("functions.php");

include("classes/litmus.php");
include("classes/all_litmus.php");
include("classes/ip_litmus.php");
include("classes/multilink_litmus.php");
include("classes/domain_litmus.php");
include("classes/email_litmus.php");
include("classes/md5_litmus.php");
include("classes/name_length_litmus.php");
include("classes/obvious_name_litmus.php");
include("classes/obvious_text_litmus.php");
include("classes/first_word_litmus.php");

?>
