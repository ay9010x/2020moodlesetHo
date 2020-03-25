<?php



defined('MOODLE_INTERNAL') || die();


function atto_emoticon_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('insertemoticon'), 'atto_emoticon');

        $manager = get_emoticon_manager();
    foreach ($manager->get_emoticons() as $emote) {
        $PAGE->requires->string_for_js($emote->altidentifier, $emote->altcomponent);
    }
}


function atto_emoticon_params_for_js($elementid, $options, $fpoptions) {
    $manager = get_emoticon_manager();
    return array(
        'emoticons' => $manager->get_emoticons()
    );
}
