<?php



require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/formslib.php');

defined('BEHAT_SITE_RUNNING') || die('Only available on Behat test server');


class core_max_input_vars_form extends moodleform {
    
    public function definition() {
        global $CFG, $PAGE;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', '');
        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHA);

                        $select = html_writer::select(array(13 => 'ArrayOpt13', 42 => 'ArrayOpt4', 666 => 'ArrayOpt666'),
                'arraytest[]', array(13, 42), false, array('multiple' => 'multiple', 'size' => 10));
        $mform->addElement('static', 'arraybit', $select);

        switch ($this->_customdata['control']) {
            case 'c' :
                                for ($i = 0; $i < $this->_customdata['fieldcount']; $i++) {
                    $mform->addElement('advcheckbox', 'test_c' . $i, 'Checkbox ' . $i);
                }
                break;

            case 'a' :
                                $options = array();
                $values = array();
                for ($i = 0; $i < $this->_customdata['fieldcount']; $i++) {
                    $options[$i] = 'BigArray ' . $i;
                    if ($i !== 3) {
                        $values[] = $i;
                    }
                }
                $select = html_writer::select($options,
                        'test_a[]', $values, false, array('multiple' => 'multiple', 'size' => 50));
                $mform->addElement('static', 'bigarraybit', $select);
                break;
        }

                $select = html_writer::select(array(13 => 'Array2Opt13', 42 => 'Array2Opt4', 666 => 'Array2Opt666'),
                'array2test[]', array(13, 42), false, array('multiple' => 'multiple', 'size' => 10));
        $mform->addElement('static', 'array2bit', $select);

        $mform->addElement('submit', 'submitbutton', 'Submit here!');
    }
}

require_login();

$context = context_system::instance();

$type = optional_param('type', '', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/lib/tests/fixtures/max_input_vars.php'));
$PAGE->set_context($context);

if ($type) {
        if ($type[1] === 's') {
                $fieldcount = 10;
    } else if ($type[1] === 'm') {
                $fieldcount = (int)ini_get('max_input_vars') - 100;
    } else if ($type[1] === 'e') {
                        if ($type[0] === 'c') {
            $fieldcount = (int)ini_get('max_input_vars') / 2 - 2;
        } else {
            $fieldcount = (int)ini_get('max_input_vars') - 11;
        }
    } else if ($type[1] === 'l') {
                $fieldcount = (int)ini_get('max_input_vars') + 100;
    }

    $mform = new core_max_input_vars_form('max_input_vars.php',
            array('type' => $type, 'fieldcount' => $fieldcount, 'control' => $type[0]));
    if ($type[0] === 'c') {
        $data = array();
        for ($i = 0; $i < $fieldcount; $i++) {
            if ($i === 3) {
                                continue;
            }
            $data['test_c' . $i] = 1;
        }
        $mform->set_data($data);
    }
}

echo $OUTPUT->header();

if ($type && ($result = $mform->get_data())) {
    $testc = array();
    $testa = array();
    foreach ($_POST as $key => $value) {
        $matches = array();
                                if (preg_match('~^test_c([0-9]+)$~', $key, $matches)) {
            $testc[(int)$matches[1]] = $value;
        } else if ($key === 'test_a') {
            $testa = $value;
        } else {
                        if (is_array($value)) {
                echo html_writer::div(s($key) . '=[' . s(implode(',', $value)) . ']');
            } else {
                echo html_writer::div(s($key) . '=' . s($value));
            }
        }
    }

        switch ($type[0]) {
        case 'c' :
            $success = true;
            for ($i = 0; $i < $fieldcount; $i++) {
                if (!array_key_exists($i, $testc)) {
                    $success = false;
                    break;
                }
                if ($testc[$i] != ($i == 3 ? 0 : 1)) {
                    $success = false;
                    break;
                }
            }
            if (array_key_exists($fieldcount, $testc)) {
                $success = false;
            }
                        $key = 'test_c' . ($fieldcount - 1);
            if (empty($result->{$key})) {
                $success = false;
            }
            if (optional_param($key, 0, PARAM_INT) !== 1) {
                $success = false;
            }
            echo html_writer::div('Bulk checkbox success: ' . ($success ? 'true' : 'false'));
            break;

        case 'a' :
            $success = true;
            for ($i = 0; $i < $fieldcount; $i++) {
                if ($i === 3) {
                    if (in_array($i, $testa)) {
                        $success = false;
                        break;
                    }
                } else {
                    if (!in_array($i, $testa)) {
                        $success = false;
                        break;
                    }
                }
            }
            if (in_array($fieldcount, $testa)) {
                $success = false;
            }
                                    $array = optional_param_array('test_a', array(), PARAM_INT);
            if ($array != $testa) {
                $success = false;
            }
            echo html_writer::div('Bulk array success: ' . ($success ? 'true' : 'false'));
            break;
    }

} else if ($type) {
    $mform->display();
}

echo html_writer::start_tag('ul');
foreach (array('c' => 'Advanced checkboxes',
        'a' => 'Select options') as $control => $controlname) {
    foreach (array('s' => 'Small', 'm' => 'Below limit', 'e' => 'Exact PHP limit',
            'l' => 'Above limit') as $size => $sizename) {
        echo html_writer::tag('li', html_writer::link('max_input_vars.php?type=' .
                $control . $size, $controlname . ' / ' . $sizename));
    }
}
echo html_writer::end_tag('ul');

echo $OUTPUT->footer();
