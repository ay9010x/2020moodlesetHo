<?php



defined('MOODLE_INTERNAL') || die();


class testable_tool_generator_site_backend extends tool_generator_site_backend {

    
    public static function get_last_testcourse_id() {
        return parent::get_last_testcourse_id();
    }
}


class tool_generator_maketestsite_testcase extends advanced_testcase {

    
    public function test_shortnames_generation() {

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();

                $prefix = tool_generator_site_backend::SHORTNAMEPREFIX;

        $record = array();

                $lastshortname = testable_tool_generator_site_backend::get_last_testcourse_id();
        $this->assertEquals(0, $lastshortname);

                $record['shortname'] = $prefix . 'AA';
        $generator->create_course($record);
        $record['shortname'] = $prefix . '__';
        $generator->create_course($record);
        $record['shortname'] = $prefix . '12.2';
        $generator->create_course($record);

        $lastshortname = testable_tool_generator_site_backend::get_last_testcourse_id();
        $this->assertEquals(0, $lastshortname);

                $record['shortname'] = $prefix . '2';
        $generator->create_course($record);
        $record['shortname'] = $prefix . '20';
        $generator->create_course($record);
        $record['shortname'] = $prefix . '8';
        $generator->create_course($record);

        $lastshortname = testable_tool_generator_site_backend::get_last_testcourse_id();
        $this->assertEquals(20, $lastshortname);

                for ($i = 9; $i < 14; $i++) {
            $record['shortname'] = $prefix . $i;
            $generator->create_course($record);
        }

        $lastshortname = testable_tool_generator_site_backend::get_last_testcourse_id();
        $this->assertEquals(20, $lastshortname);
    }

}
