<?php



defined('MOODLE_INTERNAL') || die();

use core\output\mustache_template_finder;


class core_output_mustache_template_finder_testcase extends advanced_testcase {

    public function test_get_template_directories_for_component() {
        global $CFG;

                $dirs = mustache_template_finder::get_template_directories_for_component('mod_assign', 'clean');

        $correct = array(
            'theme/clean/templates/mod_assign/',
            'theme/bootstrapbase/templates/mod_assign/',
            'mod/assign/templates/'
        );
        foreach ($dirs as $index => $dir) {
            $this->assertSame($dir, $CFG->dirroot . '/' . $correct[$index]);
        }
                $dirs = mustache_template_finder::get_template_directories_for_component('core_user', 'clean');

        $correct = array(
            'theme/clean/templates/core_user/',
            'theme/bootstrapbase/templates/core_user/',
            'user/templates/'
        );
        foreach ($dirs as $index => $dir) {
            $this->assertSame($dir, $CFG->dirroot . '/' . $correct[$index]);
        }
                $dirs = mustache_template_finder::get_template_directories_for_component('core', 'clean');

        $correct = array(
            'theme/clean/templates/core/',
            'theme/bootstrapbase/templates/core/',
            'lib/templates/'
        );
        foreach ($dirs as $index => $dir) {
            $this->assertSame($dir, $CFG->dirroot . '/' . $correct[$index]);
        }
        return;
    }

    
    public function test_invalid_get_template_directories_for_component() {
                $dirs = mustache_template_finder::get_template_directories_for_component('octopus', 'clean');
    }

    public function test_get_template_filepath() {
        global $CFG;

        $filename = mustache_template_finder::get_template_filepath('core/pix_icon', 'clean');
        $correct = $CFG->dirroot . '/lib/templates/pix_icon.mustache';
        $this->assertSame($correct, $filename);
    }

    
    public function test_invalid_get_template_filepath() {
                $dirs = mustache_template_finder::get_template_filepath('core/octopus', 'clean');
    }
}
