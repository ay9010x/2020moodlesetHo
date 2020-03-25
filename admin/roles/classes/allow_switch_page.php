<?php



defined('MOODLE_INTERNAL') || die();


class core_role_allow_switch_page extends core_role_allow_role_page {
    protected $allowedtargetroles;

    public function __construct() {
        parent::__construct('role_allow_switch', 'allowswitch');
    }

    protected function load_required_roles() {
        global $DB;
        parent::load_required_roles();
        $this->allowedtargetroles = $DB->get_records_menu('role', null, 'id');
    }

    protected function set_allow($fromroleid, $targetroleid) {
        allow_switch($fromroleid, $targetroleid);
    }

    protected function is_allowed_target($targetroleid) {
        return isset($this->allowedtargetroles[$targetroleid]);
    }

    protected function get_cell_tooltip($fromrole, $targetrole) {
        $a = new stdClass;
        $a->fromrole = $fromrole->localname;
        $a->targetrole = $targetrole->localname;
        return get_string('allowroletoswitch', 'core_role', $a);
    }

    public function get_intro_text() {
        return get_string('configallowswitch', 'core_admin');
    }
}
