<?php



defined('MOODLE_INTERNAL') || die();


function scheduler_get_user_fields($user) {

    $fields = array();

    $emailfield = new stdClass();
    $fields[] = $emailfield;
    $emailfield->title = get_string('email');
    if ($user) {
        $emailfield->value = obfuscate_mailto($user->email);
    }

    

    
    return $fields;
}
