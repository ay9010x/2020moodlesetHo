<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");



class core_role_preset_form extends moodleform {

    
    protected function definition() {
        $mform = $this->_form;

        $data = $this->_customdata;
        $options = array();

        $group = get_string('other');
        $options[$group] = array();
        $options[$group][0] = get_string('norole', 'core_role');

        $group = get_string('role', 'core');
        $options[$group] = array();
        foreach (role_get_names(null, ROLENAME_BOTH) as $role) {
                        $options[$group][$role->id] = $role->localname;
        }

        $group = get_string('archetype', 'core_role');
        $options[$group] = array();
        foreach (get_role_archetypes() as $type) {
            $options[$group][$type] = get_string('archetype'.$type, 'core_role');
        }

        $mform->addElement('header', 'presetheader', get_string('roleresetdefaults', 'core_role'));

        $mform->addElement('selectgroups', 'resettype', get_string('roleresetrole', 'core_role'), $options);

        $mform->addElement('filepicker', 'rolepreset', get_string('rolerepreset', 'core_role'));

        if ($data['roleid']) {
            $mform->addElement('header', 'resetheader', get_string('resetrole', 'core_role'));

            $mform->addElement('advcheckbox', 'shortname', get_string('roleshortname', 'core_role'));
            $mform->addElement('advcheckbox', 'name', get_string('customrolename', 'core_role'));
            $mform->addElement('advcheckbox', 'description', get_string('customroledescription', 'core_role'));
            $mform->addElement('advcheckbox', 'archetype', get_string('archetype', 'core_role'));
            $mform->addElement('advcheckbox', 'contextlevels', get_string('maybeassignedin', 'core_role'));
            $mform->addElement('advcheckbox', 'allowassign', get_string('allowassign', 'core_role'));
            $mform->addElement('advcheckbox', 'allowoverride', get_string('allowoverride', 'core_role'));
            $mform->addElement('advcheckbox', 'allowswitch', get_string('allowswitch', 'core_role'));
            $mform->addElement('advcheckbox', 'permissions', get_string('permissions', 'core_role'));
        }

        $mform->addElement('hidden', 'roleid');
        $mform->setType('roleid', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('hidden', 'return');
        $mform->setType('return', PARAM_ALPHA);

        $this->add_action_buttons(true, get_string('continue', 'core'));

        $this->set_data($data);
    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($files = $this->get_draft_files('rolepreset')) {
            
            $file = reset($files);
            $xml = $file->get_content();
            if (!core_role_preset::is_valid_preset($xml)) {
                $errors['rolepreset'] = get_string('invalidpresetfile', 'core_role');
            }
        }

        return $errors;
    }
}
