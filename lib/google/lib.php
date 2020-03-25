<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/google/src/Google/autoload.php');
require_once($CFG->libdir . '/google/curlio.php');


function get_google_client() {
    global $CFG, $SITE;

    make_temp_directory('googleapi');
    $tempdir = $CFG->tempdir . '/googleapi';

    $config = new Google_Config();
    $config->setApplicationName('Moodle ' . $CFG->release);
    $config->setIoClass('moodle_google_curlio');
    $config->setClassConfig('Google_Cache_File', 'directory', $tempdir);
    $config->setClassConfig('Google_Auth_OAuth2', 'access_type', 'online');
    $config->setClassConfig('Google_Auth_OAuth2', 'approval_prompt', 'auto');

    return new Google_Client($config);
}
