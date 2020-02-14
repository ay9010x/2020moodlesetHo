<?php



defined('MOODLE_INTERNAL') || die();


class core_editorslib_testcase extends advanced_testcase {

    
    public function test_get_preferred_editor() {

                $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.21     5 Safari/534.10';

        $enabled = editors_get_enabled();
                $editors = $enabled;

        $first = array_shift($enabled);

                set_user_preference('htmleditor', '');
        $preferred = editors_get_preferred_editor();
        $this->assertEquals($first, $preferred);

        foreach ($editors as $key => $editor) {
                        set_user_preference('htmleditor', $key);
            $preferred = editors_get_preferred_editor();
            $this->assertEquals($editor, $preferred);
        }
    }

}

