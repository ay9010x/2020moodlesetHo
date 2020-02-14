<?php



defined('MOODLE_INTERNAL') || die();

class core_role_view_role_definition_table extends core_role_define_role_table_advanced {
    public function __construct($context, $roleid) {
        parent::__construct($context, $roleid);
        $this->displaypermissions = array(CAP_ALLOW => $this->allpermissions[CAP_ALLOW]);
        $this->disabled = 'disabled="disabled" ';
    }

    public function save_changes() {
        throw new moodle_exception('invalidaccess');
    }

    protected function get_name_field($id) {
        return role_get_name($this->role);
    }

    protected function get_shortname_field($id) {
        return $this->role->shortname;
    }

    protected function get_description_field($id) {
        return role_get_description($this->role);
    }

    protected function get_archetype_field($id) {
        if (empty($this->role->archetype)) {
            return get_string('none');
        } else {
            return get_string('archetype'.$this->role->archetype, 'core_role');
        }
    }

    protected function get_allow_role_control($type) {
        if ($roles = $this->get_allow_roles_list($type)) {
            $roles = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
            return implode(', ', $roles);
        } else {
            return get_string('none');
        }
    }


    protected function print_show_hide_advanced_button() {
            }

    
    protected function get_role_risks_info() {
        global $OUTPUT;

        if (empty($this->roleid)) {
            return '';
        }

        $risks = array();
        $allrisks = get_all_risks();
        foreach ($this->capabilities as $capability) {
            $perm = $this->permissions[$capability->name];
            if ($perm != CAP_ALLOW) {
                continue;
            }
            foreach ($allrisks as $type => $risk) {
                if ($risk & (int)$capability->riskbitmask) {
                    $risks[$type] = $risk;
                }
            }
        }

        $risksurl = new moodle_url(get_docs_url(s(get_string('risks', 'core_role'))));
        foreach ($risks as $type => $risk) {
            $pixicon = new pix_icon('/i/' . str_replace('risk', 'risk_', $type), get_string($type . 'short', 'admin'));
            $risks[$type] = $OUTPUT->action_icon($risksurl, $pixicon, new popup_action('click', $risksurl));
        }

        return implode(' ', $risks);
    }

    
    protected function skip_row($capability) {
        $perm = $this->permissions[$capability->name];
        if ($perm == CAP_INHERIT) {
                                    return true;
        }
        parent::skip_row($capability);
    }

    protected function add_permission_cells($capability) {
        $perm = $this->permissions[$capability->name];
        $permname = $this->allpermissions[$perm];
        $defaultperm = $this->allpermissions[$this->parentpermissions[$capability->name]];
        if ($permname != $defaultperm) {
            $default = get_string('defaultx', 'core_role', $this->strperms[$defaultperm]);
        } else {
            $default = "&#xa0;";
        }
        return '<td class="' . $permname . '">' . $this->strperms[$permname] . '<span class="note">' .
            $default . '</span></td>';

    }
}
