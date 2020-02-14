<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/' . $CFG->admin .'/tool/behat/locallib.php');
require_once($CFG->libdir . '/behat/classes/util.php');
require_once($CFG->libdir . '/behat/classes/behat_config_manager.php');


class tool_behat_manager_testcase extends advanced_testcase {

    
    public function test_merge_configs() {

                $array1 = array(
            'the' => 'same',
            'simple' => 'value',
            'array' => array(
                'one' => 'arrayvalue1',
                'two' => 'arrayvalue2'
            )
        );

                $array2 = array(
            'simple' => 'OVERRIDDEN1',
            'array' => array(
                'one' => 'OVERRIDDEN2'
            ),
            'newprofile' => array(
                'anotherlevel' => array(
                    'andanotherone' => array(
                        'list1',
                        'list2'
                    )
                )
            )
        );

        $array = testable_behat_config_manager::merge_config($array1, $array2);

                $this->assertEquals('OVERRIDDEN1', $array['simple']);
        $this->assertEquals('OVERRIDDEN2', $array['array']['one']);

                $this->assertNotEmpty($array['array']['two']);

                $this->assertNotEmpty($array['newprofile']);
        $this->assertNotEmpty($array['newprofile']['anotherlevel']['andanotherone']);
        $this->assertEquals('list1', $array['newprofile']['anotherlevel']['andanotherone'][0]);
        $this->assertEquals('list2', $array['newprofile']['anotherlevel']['andanotherone'][1]);

                $array2 = array(
            'simple' => array(
                'simple' => 'should',
                'be' => 'overridden',
                'by' => 'this-array'
            ),
            'array' => 'one'
        );

        $array = testable_behat_config_manager::merge_config($array1, $array2);

                $this->assertNotEmpty($array['simple']);
        $this->assertNotEmpty($array['array']);
        $this->assertTrue(is_array($array['simple']));
        $this->assertFalse(is_array($array['array']));

                $this->assertEquals('same', $array['the']);
    }

    
    public function test_config_file_contents() {
        global $CFG;

                $vendorpath = $CFG->dirroot . '/vendor';
        if (!file_exists($vendorpath . '/autoload.php') || !is_dir($vendorpath . '/behat')) {
            $this->markTestSkipped('Behat not installed.');
        }

                $CFG->behat_wwwroot = 'http://example.com/behat';

                unset($CFG->behat_config);

                $features = array(
            'feature1',
            'feature2',
            'feature3'
        );

                $stepsdefinitions = array(
            'micarro' => '/me/lo/robaron',
            'anoche' => '/cuando/yo/dormia'
        );

        $contents = testable_behat_config_manager::get_config_file_contents($features, $stepsdefinitions);

                                $this->assertContains($CFG->dirroot, $contents);

                $this->assertContains('micarro: /me/lo/robaron', $contents);

                $this->assertContains("base_url: '" . $CFG->behat_wwwroot . "'", $contents);

                $this->assertContains('- feature1', $contents);
        $this->assertContains('- feature3', $contents);

        unset($CFG->behat_wwwroot);
    }

}


class testable_behat_config_manager extends behat_config_manager {

    
    public static function merge_config($config, $localconfig) {
        return parent::merge_config($config, $localconfig);
    }

    
    public static function get_config_file_contents($features, $stepsdefinitions) {
        return parent::get_config_file_contents($features, $stepsdefinitions);
    }
}
