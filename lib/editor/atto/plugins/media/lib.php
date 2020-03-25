<?php




function atto_media_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('createmedia',
                                          'enterurl',
                                          'entername',
                                          'browserepositories'),
                                    'atto_media');
}
