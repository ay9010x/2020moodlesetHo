<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class mod_wiki_create_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;

        $formats = $this->_customdata['formats'];
        $defaultformat = $this->_customdata['defaultformat'];
        $forceformat = $this->_customdata['forceformat'];

        $mform->addElement('header', 'general', get_string('newpagehdr', 'wiki'));

        $textoptions = array();
        if (!empty($this->_customdata['disable_pagetitle'])) {
            $textoptions = array('readonly'=>'readonly');
        }
        $mform->addElement('text', 'pagetitle', get_string('newpagetitle', 'wiki'), $textoptions);
        $mform->setType('pagetitle', PARAM_TEXT);
        $mform->addRule('pagetitle', get_string('required'), 'required', null, 'client');

        if ($forceformat) {
            $mform->addElement('hidden', 'pageformat', $defaultformat);
        } else {
            $mform->addElement('static', 'format', get_string('format', 'wiki'));
            $mform->addHelpButton('format', 'format', 'wiki');
            foreach ($formats as $format) {
                if ($format == $defaultformat) {
                    $attr = array('checked'=>'checked');
                }else if (!empty($forceformat)){
                    $attr = array('disabled'=>'disabled');
                } else {
                    $attr = array();
                }
                $mform->addElement('radio', 'pageformat', '', get_string('format'.$format, 'wiki'), $format, $attr);
            }
            $mform->addRule('pageformat', get_string('required'), 'required', null, 'client');
        }
        $mform->setType('pageformat', PARAM_ALPHANUMEXT);

        if (!empty($this->_customdata['groups']->availablegroups)) {
            foreach ($this->_customdata['groups']->availablegroups as $groupdata) {
                $groupinfo[$groupdata->id] = $groupdata->name;
            }
            if (count($groupinfo) > 1) {
                $mform->addElement('select', 'groupinfo', get_string('group'), $groupinfo);
                $mform->setDefault('groupinfo', $this->_customdata['groups']->currentgroup);
                $mform->setType('groupinfo', PARAM_INT);
            } else {
                $groupid = key($groupinfo);
                $groupname = $groupinfo[$groupid];
                $mform->addElement('static', 'groupdesciption', get_string('group'), $groupname);
                $mform->addElement('hidden', 'groupinfo', $groupid);
                $mform->setType('groupinfo', PARAM_INT);
            }
        }

                $mform->addElement('hidden', 'action', 'create');
        $mform->setType('action', PARAM_ALPHA);

        $this->add_action_buttons(false, get_string('createpage', 'wiki'));
    }
}
