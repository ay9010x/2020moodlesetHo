<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once(__DIR__.'/locallib.php');



class database_transfer_form extends moodleform {

    
    protected function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'database', get_string('targetdatabase', 'tool_dbtransfer'));

        $drivers = tool_dbtransfer_get_drivers();
        $drivers = array_reverse($drivers, true);
        $drivers[''] = get_string('choosedots');
        $drivers = array_reverse($drivers, true);

        $mform->addElement('select', 'driver', get_string('dbtype', 'install'), $drivers);
        $mform->setType('driver', PARAM_RAW);

        $mform->addElement('text', 'dbhost', get_string('databasehost', 'install'));
        $mform->setType('dbhost', PARAM_HOST);

        $mform->addElement('text', 'dbname', get_string('databasename', 'install'));
        $mform->setType('dbname', PARAM_ALPHANUMEXT);

        $mform->addElement('text', 'dbuser', get_string('databaseuser', 'install'));
        $mform->setType('dbuser', PARAM_ALPHANUMEXT);

        $mform->addElement('passwordunmask', 'dbpass', get_string('databasepass', 'install'));
        $mform->setType('dbpass', PARAM_RAW);

        $mform->addElement('text', 'prefix', get_string('dbprefix', 'install'));
        $mform->setType('prefix', PARAM_ALPHANUMEXT);

        $mform->addElement('text', 'dbport', get_string('dbport', 'install'));
        $mform->setType('dbport', PARAM_INT);

        if ($CFG->ostype !== 'WINDOWS') {
            $mform->addElement('text', 'dbsocket', get_string('databasesocket', 'install'));
        } else {
            $mform->addElement('hidden', 'dbsocket');
        }
        $mform->setType('dbsocket', PARAM_RAW);

        $mform->addRule('driver', get_string('required'), 'required', null);
        $mform->addRule('dbhost', get_string('required'), 'required', null);
        $mform->addRule('dbname', get_string('required'), 'required', null);
        $mform->addRule('dbuser', get_string('required'), 'required', null);
        $mform->addRule('dbpass', get_string('required'), 'required', null);
        if (!isset($drivers['mysqli/native'])) {
            $mform->addRule('prefix', get_string('required'), 'required', null);
        }

        $mform->addElement('header', 'database', get_string('options', 'tool_dbtransfer'));

        $mform->addElement('advcheckbox', 'enablemaintenance', get_string('enablemaintenance', 'tool_dbtransfer'));
        $mform->setType('enablemaintenance', PARAM_BOOL);
        $mform->addHelpButton('enablemaintenance', 'enablemaintenance', 'tool_dbtransfer');

        $this->add_action_buttons(false, get_string('transferdata', 'tool_dbtransfer'));
    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['driver'] !== 'mysqli/native') {
                        if ($data['prefix'] === '') {
                $errors['prefix'] = get_string('required');
            }
        }
        return $errors;
    }
}
