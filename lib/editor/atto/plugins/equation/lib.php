<?php



defined('MOODLE_INTERNAL') || die();


function atto_equation_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('saveequation',
                                          'editequation',
                                          'preview',
                                          'cursorinfo',
                                          'update',
                                          'librarygroup1',
                                          'librarygroup2',
                                          'librarygroup3',
                                          'librarygroup4'),
                                    'atto_equation');
}


function atto_equation_params_for_js($elementid, $options, $fpoptions) {
    $texexample = '$$\pi$$';

            $result = format_text($texexample, true, $options);

    $texfilteractive = ($texexample !== $result);
    $context = $options['context'];
    if (!$context) {
        $context = context_system::instance();
    }

        $library = array(
            'group1' => array(
                'groupname' => 'librarygroup1',
                'elements' => get_config('atto_equation', 'librarygroup1'),
            ),
            'group2' => array(
                'groupname' => 'librarygroup2',
                'elements' => get_config('atto_equation', 'librarygroup2'),
            ),
            'group3' => array(
                'groupname' => 'librarygroup3',
                'elements' => get_config('atto_equation', 'librarygroup3'),
            ),
            'group4' => array(
                'groupname' => 'librarygroup4',
                'elements' => get_config('atto_equation', 'librarygroup4'),
            ));

    return array('texfilteractive' => $texfilteractive,
                 'contextid' => $context->id,
                 'library' => $library,
                 'texdocsurl' => get_docs_url('Using_TeX_Notation'));
}
