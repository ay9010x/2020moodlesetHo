<?php



defined('MOODLE_INTERNAL') || die();


class testable_tool_installaddon_installer extends tool_installaddon_installer {

    public function get_site_fullname() {
        return strip_tags('<h1 onmouseover="alert(\'Hello Moodle.org!\');">Nasty site</h1>');
    }

    public function get_site_url() {
        return 'file:///etc/passwd';
    }

    public function get_site_major_version() {
        return "2.5'; DROP TABLE mdl_user; --";
    }

    public function testable_decode_remote_request($request) {
        return parent::decode_remote_request($request);
    }

    protected function should_send_site_info() {
        return true;
    }

    public function testable_detect_plugin_component_from_versionphp($code) {
        return parent::detect_plugin_component_from_versionphp($code);
    }
}
