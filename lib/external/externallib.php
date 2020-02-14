<?php




defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");


class core_external extends external_api {


    
    public static function format_string_parameters($stringparams) {
                $strparams = new stdClass();
        if (!empty($stringparams)) {
                        if (count($stringparams) == 1) {
                $stringparam = array_pop($stringparams);
                if (isset($stringparam['name'])) {
                    $strparams->{$stringparam['name']} = $stringparam['value'];
                } else {
                                        $strparams = $stringparam['value'];
                }
            }  else {
                                foreach ($stringparams as $stringparam) {

                                                            if (empty($stringparam['name'])) {
                        throw new moodle_exception('unnamedstringparam', 'webservice');
                    }

                    $strparams->{$stringparam['name']} = $stringparam['value'];
                }
            }
        }
        return $strparams;
    }

    
    public static function get_string_parameters() {
        return new external_function_parameters(
            array('stringid' => new external_value(PARAM_STRINGID, 'string identifier'),
                  'component' => new external_value(PARAM_COMPONENT,'component', VALUE_DEFAULT, 'moodle'),
                  'lang' => new external_value(PARAM_LANG, 'lang', VALUE_DEFAULT, null),
                  'stringparams' => new external_multiple_structure (
                      new external_single_structure(array(
                          'name' => new external_value(PARAM_ALPHANUMEXT, 'param name
                            - if the string expect only one $a parameter then don\'t send this field, just send the value.', VALUE_OPTIONAL),
                          'value' => new external_value(PARAM_RAW,'param value'))),
                          'the definition of a string param (i.e. {$a->name})', VALUE_DEFAULT, array()
                   )
            )
        );
    }

    
    public static function get_string($stringid, $component = 'moodle', $lang = null, $stringparams = array()) {
        $params = self::validate_parameters(self::get_string_parameters(),
                      array('stringid'=>$stringid, 'component' => $component, 'lang' => $lang, 'stringparams' => $stringparams));

        $stringmanager = get_string_manager();
        return $stringmanager->get_string($params['stringid'], $params['component'],
            core_external::format_string_parameters($params['stringparams']), $params['lang']);
    }

    
    public static function get_string_returns() {
        return new external_value(PARAM_RAW, 'translated string');
    }

    
    public static function get_strings_parameters() {
        return new external_function_parameters(
            array('strings' => new external_multiple_structure (
                    new external_single_structure (array(
                        'stringid' => new external_value(PARAM_STRINGID, 'string identifier'),
                        'component' => new external_value(PARAM_COMPONENT, 'component', VALUE_DEFAULT, 'moodle'),
                        'lang' => new external_value(PARAM_LANG, 'lang', VALUE_DEFAULT, null),
                        'stringparams' => new external_multiple_structure (
                            new external_single_structure(array(
                                'name' => new external_value(PARAM_ALPHANUMEXT, 'param name
                                    - if the string expect only one $a parameter then don\'t send this field, just send the value.', VALUE_OPTIONAL),
                                'value' => new external_value(PARAM_RAW, 'param value'))),
                                'the definition of a string param (i.e. {$a->name})', VALUE_DEFAULT, array()
                        ))
                    )
                )
            )
        );
    }

    
    public static function get_strings($strings) {
        $params = self::validate_parameters(self::get_strings_parameters(),
                      array('strings'=>$strings));
        $stringmanager = get_string_manager();

        $translatedstrings = array();
        foreach($params['strings'] as $string) {

            if (!empty($string['lang'])) {
                $lang = $string['lang'];
            } else {
                $lang = current_language();
            }

            $translatedstrings[] = array(
                'stringid' => $string['stringid'],
                'component' => $string['component'],
                'lang' => $lang,
                'string' => $stringmanager->get_string($string['stringid'], $string['component'],
                    core_external::format_string_parameters($string['stringparams']), $lang));
        }

        return $translatedstrings;
    }

    
    public static function get_strings_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'stringid' => new external_value(PARAM_STRINGID, 'string id'),
                'component' => new external_value(PARAM_COMPONENT, 'string component'),
                'lang' => new external_value(PARAM_LANG, 'lang'),
                'string' => new external_value(PARAM_RAW, 'translated string'))
            ));
    }

     
    public static function get_component_strings_parameters() {
        return new external_function_parameters(
            array('component' => new external_value(PARAM_COMPONENT, 'component'),
                  'lang' => new external_value(PARAM_LANG, 'lang', VALUE_DEFAULT, null),
            )
        );
    }

    
    public static function get_component_strings($component, $lang = null) {

        if (empty($lang)) {
            $lang = current_language();
        }

        $params = self::validate_parameters(self::get_component_strings_parameters(),
                      array('component'=>$component, 'lang' => $lang));

        $stringmanager = get_string_manager();

        $wsstrings = array();
        $componentstrings = $stringmanager->load_component_strings($params['component'], $params['lang']);
        foreach($componentstrings as $stringid => $string) {
            $wsstring = array();
            $wsstring['stringid'] = $stringid;
            $wsstring['string'] = $string;
            $wsstrings[] = $wsstring;
        }

        return $wsstrings;
    }

    
    public static function get_component_strings_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'stringid' => new external_value(PARAM_STRINGID, 'string id'),
                'string' => new external_value(PARAM_RAW, 'translated string'))
            ));
    }

    
    public static function get_fragment_parameters() {
        return new external_function_parameters(
            array(
                'component' => new external_value(PARAM_COMPONENT, 'Component for the callback e.g. mod_assign'),
                'callback' => new external_value(PARAM_ALPHANUMEXT, 'Name of the callback to execute'),
                'contextid' => new external_value(PARAM_INT, 'Context ID that the fragment is from'),
                'args' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_ALPHANUMEXT, 'param name'),
                            'value' => new external_value(PARAM_RAW, 'param value')
                        )
                    ), 'args for the callback are optional', VALUE_OPTIONAL
                )
            )
        );
    }

    
    public static function get_fragment($component, $callback, $contextid, $args = null) {
        global $OUTPUT, $PAGE;

        $params = self::validate_parameters(self::get_fragment_parameters(),
                array(
                    'component' => $component,
                    'callback' => $callback,
                    'contextid' => $contextid,
                    'args' => $args
                )
        );

                $arguments = array();
        foreach ($params['args'] as $paramargument) {
            $arguments[$paramargument['name']] = $paramargument['value'];
        }

        $context = context::instance_by_id($contextid);
        self::validate_context($context);
        $arguments['context'] = $context;

                $OUTPUT->header();

                        $PAGE->start_collecting_javascript_requirements();
        $data = component_callback($params['component'], 'output_fragment_' . $params['callback'], array($arguments));
        $jsfooter = $PAGE->requires->get_end_code();
        $output = array('html' => $data, 'javascript' => $jsfooter);
        return $output;
    }

    
    public static function get_fragment_returns() {
        return new external_single_structure(
            array(
                'html' => new external_value(PARAM_RAW, 'HTML fragment.'),
                'javascript' => new external_value(PARAM_RAW, 'JavaScript fragment')
            )
        );
    }

    
    public static function update_inplace_editable_parameters() {
        return new external_function_parameters(
            array(
                'component' => new external_value(PARAM_COMPONENT, 'component responsible for the update', VALUE_REQUIRED),
                'itemtype' => new external_value(PARAM_NOTAGS, 'type of the updated item inside the component', VALUE_REQUIRED),
                'itemid' => new external_value(PARAM_INT, 'identifier of the updated item', VALUE_REQUIRED),
                'value' => new external_value(PARAM_RAW, 'new value', VALUE_REQUIRED),
            ));
    }

    
    public static function update_inplace_editable($component, $itemtype, $itemid, $value) {
        global $PAGE;
                $params = self::validate_parameters(self::update_inplace_editable_parameters(),
                      array('component' => $component, 'itemtype' => $itemtype, 'itemid' => $itemid, 'value' => $value));
        if (!$functionname = component_callback_exists($component, 'inplace_editable')) {
            throw new \moodle_exception('inplaceeditableerror');
        }
        $tmpl = component_callback($params['component'], 'inplace_editable',
            array($params['itemtype'], $params['itemid'], $params['value']));
        if (!$tmpl || !($tmpl instanceof \core\output\inplace_editable)) {
            throw new \moodle_exception('inplaceeditableerror');
        }
        return $tmpl->export_for_template($PAGE->get_renderer('core'));
    }

    
    public static function update_inplace_editable_returns() {
        return new external_single_structure(
            array(
                'displayvalue' => new external_value(PARAM_RAW, 'display value (may contain link or other html tags)'),
                'component' => new external_value(PARAM_NOTAGS, 'component responsible for the update', VALUE_OPTIONAL),
                'itemtype' => new external_value(PARAM_NOTAGS, 'itemtype', VALUE_OPTIONAL),
                'value' => new external_value(PARAM_RAW, 'value of the item as it is stored', VALUE_OPTIONAL),
                'itemid' => new external_value(PARAM_RAW, 'identifier of the updated item', VALUE_OPTIONAL),
                'edithint' => new external_value(PARAM_NOTAGS, 'hint for editing element', VALUE_OPTIONAL),
                'editlabel' => new external_value(PARAM_NOTAGS, 'label for editing element', VALUE_OPTIONAL),
                'type' => new external_value(PARAM_ALPHA, 'type of the element (text, toggle, select)', VALUE_OPTIONAL),
                'options' => new external_value(PARAM_RAW, 'options of the element, format depends on type', VALUE_OPTIONAL),
                'linkeverything' => new external_value(PARAM_INT, 'Should everything be wrapped in the edit link or link displayed separately', VALUE_OPTIONAL),
            )
        );
    }

    
    public static function fetch_notifications_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'Context ID', VALUE_REQUIRED),
            ));
    }

    
    public static function fetch_notifications_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'template'      => new external_value(PARAM_RAW, 'Name of the template'),
                    'variables'     => new external_single_structure(array(
                        'message'       => new external_value(PARAM_RAW, 'HTML content of the Notification'),
                        'extraclasses'  => new external_value(PARAM_RAW, 'Extra classes to provide to the tmeplate'),
                        'announce'      => new external_value(PARAM_RAW, 'Whether to announce'),
                        'closebutton'   => new external_value(PARAM_RAW, 'Whether to close'),
                    )),
                )
            )
        );
    }

    
    public static function fetch_notifications($contextid) {
        global $PAGE;

        self::validate_parameters(self::fetch_notifications_parameters(), [
                'contextid' => $contextid,
            ]);

        $context = \context::instance_by_id($contextid);
        self::validate_context($context);

        return \core\notification::fetch_as_array($PAGE->get_renderer('core'));
    }
}
