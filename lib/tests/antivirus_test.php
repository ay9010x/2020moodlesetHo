<?php



defined('MOODLE_INTERNAL') || die();

class core_antivirus_testcase extends advanced_testcase {

    public function test_manager_get_antivirus() {
                        $antivirusviaget = \core\antivirus\manager::get_antivirus('clamav');
        $antivirusdirect = new \antivirus_clamav\scanner();
        $this->assertEquals($antivirusdirect, $antivirusviaget);
    }
}
