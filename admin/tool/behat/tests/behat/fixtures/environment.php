<?php



require_once(__DIR__.'/../../../../../../config.php');

defined('BEHAT_SITE_RUNNING') ||  die();

require_once($CFG->libdir.'/behat/classes/util.php');
echo json_encode(behat_util::get_environment(), true);
