<?php



defined('MOODLE_INTERNAL') || die();


class core_role_allow_override_page extends core_role_allow_role_page {
    public function __construct() {
        parent::__construct('role_allow_override', 'allowoverride');
    }

    protected function set_allow($fromroleid, $targetroleid) {
        allow_override($fromroleid, $targetroleid);
    }

    protected function get_cell_tooltip($fromrole, $targetrole) {
        $a = new stdClass;
        $a->fromrole = $fromrole->localname;
        $a->targetrole = $targetrole->localname;
        return get_string('allowroletooverride', 'core_role', $a);
    }

    public function get_intro_text() {
        return get_string('configallowoverride2', 'core_admin');
    }
}
