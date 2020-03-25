<?php



defined('MOODLE_INTERNAL') || die();


class core_role_allow_assign_page extends core_role_allow_role_page {
    public function __construct() {
        parent::__construct('role_allow_assign', 'allowassign');
    }

    protected function set_allow($fromroleid, $targetroleid) {
        allow_assign($fromroleid, $targetroleid);
    }

    protected function get_cell_tooltip($fromrole, $targetrole) {
        $a = new stdClass;
        $a->fromrole = $fromrole->localname;
        $a->targetrole = $targetrole->localname;
        return get_string('allowroletoassign', 'core_role', $a);
    }

    public function get_intro_text() {
        return get_string('configallowassign', 'core_admin');
    }
}
