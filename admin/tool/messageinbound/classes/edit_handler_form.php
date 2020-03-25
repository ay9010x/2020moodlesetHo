<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


class tool_messageinbound_edit_handler_form extends moodleform {

    
    public function definition() {
        $mform = $this->_form;

        $handler = $this->_customdata['handler'];

                $formatoptions = new stdClass();
        $formatoptions->trusted = false;
        $formatoptions->noclean = false;
        $formatoptions->smiley = false;
        $formatoptions->filter = false;
        $formatoptions->para = true;
        $formatoptions->newlines = false;
        $formatoptions->overflowdiv = true;

                $mform->addElement('header', 'general', get_string('general'));
        $mform->addElement('static', 'name', get_string('name', 'tool_messageinbound'),
            $handler->name);
        $mform->addElement('static', 'classname', get_string('classname', 'tool_messageinbound'));

        $description = format_text($handler->description, FORMAT_MARKDOWN, $formatoptions);

        $mform->addElement('static', 'description', get_string('description', 'tool_messageinbound'),
            $description);

                $mform->addElement('header', 'configuration', get_string('configuration'));

        if ($handler->can_change_defaultexpiration()) {
                        $options = array(
                HOURSECS => get_string('onehour', 'tool_messageinbound'),
                DAYSECS => get_string('oneday', 'tool_messageinbound'),
                WEEKSECS => get_string('oneweek', 'tool_messageinbound'),
                YEARSECS => get_string('oneyear', 'tool_messageinbound'),
                0 => get_string('noexpiry', 'tool_messageinbound'),
            );
            $mform->addElement('select', 'defaultexpiration', get_string('defaultexpiration', 'tool_messageinbound'), $options);
            $mform->addHelpButton('defaultexpiration', 'defaultexpiration', 'tool_messageinbound');
        } else {
            $text = $this->get_defaultexpiration_text($handler);
            $mform->addElement('static', 'defaultexpiration_fake', get_string('defaultexpiration', 'tool_messageinbound'), $text);
            $mform->addElement('hidden', 'defaultexpiration');
            $mform->addHelpButton('defaultexpiration_fake', 'defaultexpiration', 'tool_messageinbound');
            $mform->setType('defaultexpiration', PARAM_INT);
        }

        if ($handler->can_change_validateaddress()) {
            $mform->addElement('checkbox', 'validateaddress', get_string('requirevalidation', 'tool_messageinbound'));
            $mform->addHelpButton('validateaddress', 'validateaddress', 'tool_messageinbound');
        } else {
            if ($handler->validateaddress) {
                $text = get_string('yes');
            } else {
                $text = get_string('no');
            }
            $mform->addElement('static', 'validateaddress_fake', get_string('requirevalidation', 'tool_messageinbound'), $text);
            $mform->addElement('hidden', 'validateaddress');
            $mform->addHelpButton('validateaddress_fake', 'fixedvalidateaddress', 'tool_messageinbound');
            $mform->setType('validateaddress', PARAM_INT);
        }

        if ($handler->can_change_enabled()) {
            $mform->addElement('checkbox', 'enabled', get_string('enabled', 'tool_messageinbound'));
        } else {
            if ($handler->enabled) {
                $text = get_string('yes');
            } else {
                $text = get_string('no');
            }
            $mform->addElement('static', 'enabled_fake', get_string('enabled', 'tool_messageinbound'), $text);
            $mform->addHelpButton('enabled', 'fixedenabled', 'tool_messageinbound');
            $mform->addElement('hidden', 'enabled');
            $mform->setType('enabled', PARAM_INT);
        }

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    
    protected function get_defaultexpiration_text(\core\message\inbound\handler $handler) {
        switch($handler->defaultexpiration) {
            case HOURSECS :
                    return get_string('onehour', 'tool_messageinbound');
            case DAYSECS :
                    return get_string('oneday', 'tool_messageinbound');
            case WEEKSECS :
                    return get_string('oneweek', 'tool_messageinbound');
            case YEARSECS :
                    return get_string('oneyear', 'tool_messageinbound');
            case 0:
                    return get_string('noexpiry', 'tool_messageinbound');
            default:
                    return '';         }
    }
}
