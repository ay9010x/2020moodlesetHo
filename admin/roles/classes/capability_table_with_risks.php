<?php



defined('MOODLE_INTERNAL') || die();


abstract class core_role_capability_table_with_risks extends core_role_capability_table_base {
    protected $allrisks;
    protected $allpermissions;     protected $strperms;     protected $risksurl;     
    protected $parentpermissions;
    protected $displaypermissions;
    protected $permissions;
    protected $changed;
    protected $roleid;

    public function __construct($context, $id, $roleid) {
        parent::__construct($context, $id);

        $this->allrisks = get_all_risks();
        $this->risksurl = get_docs_url(s(get_string('risks', 'core_role')));

        $this->allpermissions = array(
            CAP_INHERIT => 'inherit',
            CAP_ALLOW => 'allow',
            CAP_PREVENT => 'prevent' ,
            CAP_PROHIBIT => 'prohibit',
        );

        $this->strperms = array();
        foreach ($this->allpermissions as $permname) {
            $this->strperms[$permname] =  get_string($permname, 'core_role');
        }

        $this->roleid = $roleid;
        $this->load_current_permissions();

                foreach ($this->capabilities as $capid => $cap) {
            if (!isset($this->permissions[$cap->name])) {
                $this->permissions[$cap->name] = CAP_INHERIT;
            }
            $this->capabilities[$capid]->locked = false;
        }
    }

    protected function load_current_permissions() {
        global $DB;

                if ($this->roleid) {
            $this->permissions = $DB->get_records_menu('role_capabilities', array('roleid' => $this->roleid,
                'contextid' => $this->context->id), '', 'capability,permission');
        } else {
            $this->permissions = array();
        }
    }

    protected abstract function load_parent_permissions();

    
    public function read_submitted_permissions() {
        $this->changed = array();

        foreach ($this->capabilities as $cap) {
            if ($cap->locked || $this->skip_row($cap)) {
                                continue;
            }

            $permission = optional_param($cap->name, null, PARAM_PERMISSION);
            if (is_null($permission)) {
                                continue;
            }

                                    if ($this->permissions[$cap->name] != $permission) {
                $this->permissions[$cap->name] = $permission;
                $this->changed[] = $cap->name;
            }
        }
    }

    
    public function save_changes() {
                foreach ($this->changed as $changedcap) {
            assign_capability($changedcap, $this->permissions[$changedcap],
                $this->roleid, $this->context->id, true);
        }

                $this->context->mark_dirty();
    }

    public function display() {
        $this->load_parent_permissions();
        foreach ($this->capabilities as $cap) {
            if (!isset($this->parentpermissions[$cap->name])) {
                $this->parentpermissions[$cap->name] = CAP_INHERIT;
            }
        }
        parent::display();
    }

    protected function add_header_cells() {
        global $OUTPUT;
        echo '<th colspan="' . count($this->displaypermissions) . '" scope="col">' .
            get_string('permission', 'core_role') . ' ' . $OUTPUT->help_icon('permission', 'core_role') . '</th>';
        echo '<th class="risk" colspan="' . count($this->allrisks) . '" scope="col">' . get_string('risks', 'core_role') . '</th>';
    }

    protected function num_extra_columns() {
        return count($this->displaypermissions) + count($this->allrisks);
    }

    protected function get_row_classes($capability) {
        $rowclasses = array();
        foreach ($this->allrisks as $riskname => $risk) {
            if ($risk & (int)$capability->riskbitmask) {
                $rowclasses[] = $riskname;
            }
        }
        return $rowclasses;
    }

    protected abstract function add_permission_cells($capability);

    protected function add_row_cells($capability) {
        $cells = $this->add_permission_cells($capability);
                foreach ($this->allrisks as $riskname => $risk) {
            $cells .= '<td class="risk ' . str_replace('risk', '', $riskname) . '">';
            if ($risk & (int)$capability->riskbitmask) {
                $cells .= $this->get_risk_icon($riskname);
            }
            $cells .= '</td>';
        }
        return $cells;
    }

    
    public function get_risk_icon($type) {
        global $OUTPUT;

        $iconurl = $OUTPUT->pix_url('i/' . str_replace('risk', 'risk_', $type));
        $text = '<img src="' . $iconurl . '" alt="' . get_string($type . 'short', 'admin') . '" />';
        $action = new popup_action('click', $this->risksurl, 'docspopup');
        $riskicon = $OUTPUT->action_link($this->risksurl, $text, $action, array('title'=>get_string($type, 'admin')));

        return $riskicon;
    }
}
