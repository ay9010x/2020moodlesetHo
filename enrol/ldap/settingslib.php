<?php



defined('MOODLE_INTERNAL') || die();

class admin_setting_configtext_trim_lower extends admin_setting_configtext {
    
    private $lowercase;

    
    public function __construct($name, $visiblename, $description, $defaultsetting, $lowercase=false, $enabled=true) {
        $this->lowercase = $lowercase;
        $this->enabled = $enabled;
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    
    public function write_setting($data) {
        if ($this->paramtype === PARAM_INT and $data === '') {
                        $data = 0;
        }

                $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }
        if ($this->lowercase) {
            $data = core_text::strtolower($data);
        }
        if (!$this->enabled) {
            return '';
        }
        return ($this->config_write($this->name, trim($data)) ? '' : get_string('errorsetting', 'admin'));
    }

    
    public function output_html($data, $query='') {
        $default = $this->get_defaultsetting();
        $disabled = $this->enabled ? '': ' disabled="disabled"';
        return format_admin_setting($this, $this->visiblename,
        '<div class="form-text defaultsnext"><input type="text" size="'.$this->size.'" id="'.$this->get_id().'" name="'.$this->get_full_name().'" value="'.s($data).'" '.$disabled.' /></div>',
        $this->description, true, '', $default, $query);
    }

}

class admin_setting_ldap_rolemapping extends admin_setting {

    
    public function __construct($name, $visiblename, $description, $defaultsetting) {
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    
    public function get_setting() {
        $roles = role_fix_names(get_all_roles());
        $result = array();
        foreach ($roles as $role) {
            $contexts = $this->config_read('contexts_role'.$role->id);
            $memberattribute = $this->config_read('memberattribute_role'.$role->id);
            $result[] = array('id' => $role->id,
                              'name' => $role->localname,
                              'contexts' => $contexts,
                              'memberattribute' => $memberattribute);
        }
        return $result;
    }

    
    public function write_setting($data) {
        if(!is_array($data)) {
            return '';         }

        $result = '';
        foreach ($data as $roleid => $data) {
            if (!$this->config_write('contexts_role'.$roleid, trim($data['contexts']))) {
                $return = get_string('errorsetting', 'admin');
            }
            if (!$this->config_write('memberattribute_role'.$roleid, core_text::strtolower(trim($data['memberattribute'])))) {
                $return = get_string('errorsetting', 'admin');
            }
        }
        return $result;
    }

    
    public function output_html($data, $query='') {
        $return  = html_writer::start_tag('div', array('style' =>'float:left; width:auto; margin-right: 0.5em;'));
        $return .= html_writer::tag('div', get_string('roles', 'role'), array('style' => 'height: 2em;'));
        foreach ($data as $role) {
            $return .= html_writer::tag('div', s($role['name']), array('style' => 'height: 2em;'));
        }
        $return .= html_writer::end_tag('div');

        $return .= html_writer::start_tag('div', array('style' => 'float:left; width:auto; margin-right: 0.5em;'));
        $return .= html_writer::tag('div', get_string('contexts', 'enrol_ldap'), array('style' => 'height: 2em;'));
        foreach ($data as $role) {
            $contextid = $this->get_id().'['.$role['id'].'][contexts]';
            $contextname = $this->get_full_name().'['.$role['id'].'][contexts]';
            $return .= html_writer::start_tag('div', array('style' => 'height: 2em;'));
            $return .= html_writer::label(get_string('role_mapping_context', 'enrol_ldap', $role['name']), $contextid, false, array('class' => 'accesshide'));
            $attrs = array('type' => 'text', 'size' => '40', 'id' => $contextid, 'name' => $contextname, 'value' => s($role['contexts']));
            $return .= html_writer::empty_tag('input', $attrs);
            $return .= html_writer::end_tag('div');
        }
        $return .= html_writer::end_tag('div');

        $return .= html_writer::start_tag('div', array('style' => 'float:left; width:auto; margin-right: 0.5em;'));
        $return .= html_writer::tag('div', get_string('memberattribute', 'enrol_ldap'), array('style' => 'height: 2em;'));
        foreach ($data as $role) {
            $memberattrid = $this->get_id().'['.$role['id'].'][memberattribute]';
            $memberattrname = $this->get_full_name().'['.$role['id'].'][memberattribute]';
            $return .= html_writer::start_tag('div', array('style' => 'height: 2em;'));
            $return .= html_writer::label(get_string('role_mapping_attribute', 'enrol_ldap', $role['name']), $memberattrid, false, array('class' => 'accesshide'));
            $attrs = array('type' => 'text', 'size' => '15', 'id' => $memberattrid, 'name' => $memberattrname, 'value' => s($role['memberattribute']));
            $return .= html_writer::empty_tag('input', $attrs);
            $return .= html_writer::end_tag('div');
        }
        $return .= html_writer::end_tag('div');
        $return .= html_writer::tag('div', '', array('style' => 'clear:both;'));

        return format_admin_setting($this, $this->visiblename, $return,
                                    $this->description, true, '', '', $query);
    }
}


class enrol_ldap_admin_setting_category extends admin_setting_configselect {
    public function __construct($name, $visiblename, $description) {
        parent::__construct($name, $visiblename, $description, 1, null);
    }

    public function load_choices() {
        if (is_array($this->choices)) {
            return true;
        }

        $this->choices = make_categories_options();
        return true;
    }
}
