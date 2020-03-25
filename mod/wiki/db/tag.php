<?php



defined('MOODLE_INTERNAL') || die();


$tagareas = array(
    array(
        'itemtype' => 'wiki_pages',
        'component' => 'mod_wiki',
        'callback' => 'mod_wiki_get_tagged_pages',
        'callbackfile' => '/mod/wiki/locallib.php',
    ),
);
