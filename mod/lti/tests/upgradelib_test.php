<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/mod/lti/db/upgradelib.php');



class mod_lti_upgradelib_testcase extends advanced_testcase {

    
    public function test_custom_parameter() {
        global $DB, $SITE, $USER;

        $custom1 = 'a=one;b=two;three=3';
        $custom2 = "a=one\nb=two\nthree=3";

        $this->resetAfterTest(true);

        $ltigenerator = $this->getDataGenerator()->get_plugin_generator('mod_lti');

                $toolid1 = $DB->insert_record('lti_types', array('course' => $SITE->id, 'baseurl' => '', 'createdby' => $USER->id,
            'timecreated' => time(), 'timemodified' => time()));
        $configid1 = $DB->insert_record('lti_types_config', array('typeid' => $toolid1, 'name' => 'customparameters',
            'value' => $custom1));
        $toolid2 = $DB->insert_record('lti_types', array('course' => $SITE->id, 'baseurl' => '', 'createdby' => $USER->id,
            'timecreated' => time(), 'timemodified' => time()));
        $configid2 = $DB->insert_record('lti_types_config', array('typeid' => $toolid2, 'name' => 'customparameters',
            'value' => $custom2));

                $activity1 = $ltigenerator->create_instance(array('course' => $SITE->id, 'name' => 'LTI activity 1',
            'typeid' => $toolid1, 'toolurl' => '', 'instructorcustomparameters' => $custom1));
        $activity2 = $ltigenerator->create_instance(array('course' => $SITE->id, 'name' => 'LTI activity 2',
            'typeid' => $toolid2, 'toolurl' => '', 'instructorcustomparameters' => $custom2));

                mod_lti_upgrade_custom_separator();

                $config = $DB->get_record('lti_types_config', array('id' => $configid1));
        $this->assertEquals($config->value, $custom2);

        $config = $DB->get_record('lti_types_config', array('id' => $configid2));
        $this->assertEquals($config->value, $custom2);

        $config = $DB->get_record('lti', array('id' => $activity1->id));
        $this->assertEquals($config->instructorcustomparameters, $custom2);

        $config = $DB->get_record('lti', array('id' => $activity2->id));
        $this->assertEquals($config->instructorcustomparameters, $custom2);
    }

}
