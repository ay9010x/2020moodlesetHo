<?php



defined('MOODLE_INTERNAL') || die();



class core_filter_manager_testcase extends advanced_testcase {

    
    protected function filter_text($text, $skipfilters) {
        global $PAGE;
        $filtermanager = filter_manager::instance();
        $filtermanager->setup_page_for_filters($PAGE, $PAGE->context);
        $filteroptions = array(
                'originalformat' => FORMAT_HTML,
                'noclean' => false,
        );
        return $filtermanager->filter_text($text, $PAGE->context, $filteroptions, $skipfilters);
    }

    public function test_filter_normal() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertRegExp('~^<p><img class="emoticon" alt="smile" ([^>]+)></p>$~',
                $this->filter_text('<p>:-)</p>', array()));
    }

    public function test_one_filter_disabled() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertEquals('<p>:-)</p>',
                $this->filter_text('<p>:-)</p>', array('emoticon')));
    }

    public function test_disabling_other_filter_does_not_break_it() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        $this->assertRegExp('~^<p><img class="emoticon" alt="smile" ([^>]+)></p>$~',
                $this->filter_text('<p>:-)</p>', array('urltolink')));
    }

    public function test_one_filter_of_two_disabled() {
        $this->resetAfterTest();
        filter_set_global_state('emoticon', TEXTFILTER_ON);
        filter_set_global_state('urltolink', TEXTFILTER_ON);
        $this->assertRegExp('~^<p><img class="emoticon" alt="smile" ([^>]+)> http://google.com/</p>$~',
                $this->filter_text('<p>:-) http://google.com/</p>', array('glossary', 'urltolink')));
    }
}
