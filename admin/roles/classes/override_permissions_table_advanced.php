<?php



defined('MOODLE_INTERNAL') || die();

class core_role_override_permissions_table_advanced extends core_role_capability_table_with_risks {
    protected $strnotset;
    protected $haslockedcapabilities = false;

    
    public function __construct($context, $roleid, $safeoverridesonly) {
        parent::__construct($context, 'overriderolestable', $roleid);
        $this->displaypermissions = $this->allpermissions;
        $this->strnotset = get_string('notset', 'core_role');

                if ($safeoverridesonly) {
            foreach ($this->capabilities as $capid => $cap) {
                if (!is_safe_capability($cap)) {
                    $this->capabilities[$capid]->locked = true;
                    $this->haslockedcapabilities = true;
                }
            }
        }
    }

    protected function load_parent_permissions() {
                $parentcontext = $this->context->get_parent_context();
        $this->parentpermissions = role_context_capabilities($this->roleid, $parentcontext);
    }

    public function has_locked_capabilities() {
        return $this->haslockedcapabilities;
    }

    protected function add_permission_cells($capability) {
        $disabled = '';
        if ($capability->locked || $this->parentpermissions[$capability->name] == CAP_PROHIBIT) {
            $disabled = ' disabled="disabled"';
        }

                $content = '';
        foreach ($this->displaypermissions as $perm => $permname) {
            $strperm = $this->strperms[$permname];
            $extraclass = '';
            if ($perm != CAP_INHERIT && $perm == $this->parentpermissions[$capability->name]) {
                $extraclass = ' capcurrent';
            }
            $checked = '';
            if ($this->permissions[$capability->name] == $perm) {
                $checked = 'checked="checked" ';
            }
            $content .= '<td class="' . $permname . $extraclass . '">';
            $content .= '<label><input type="radio" name="' . $capability->name .
                '" value="' . $perm . '" ' . $checked . $disabled . '/> ';
            if ($perm == CAP_INHERIT) {
                $inherited = $this->parentpermissions[$capability->name];
                if ($inherited == CAP_INHERIT) {
                    $inherited = $this->strnotset;
                } else {
                    $inherited = $this->strperms[$this->allpermissions[$inherited]];
                }
                $strperm .= ' (' . $inherited . ')';
            }
            $content .= '<span class="note">' . $strperm . '</span>';
            $content .= '</label></td>';
        }
        return $content;
    }
}
