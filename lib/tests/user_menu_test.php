<?php


class core_user_menu_testcase extends advanced_testcase {

    
    public function custom_user_menu_data() {
        return array(
                        array('###', 0, 1),
            array('#####', 0, 1),

                        array('-----', 0, 0),
            array('_____', 0, 0),
            array('test', 0, 0),
            array('#Garbage#', 0, 0),

                                    array('#my1files,moodle|/user/files.php|download', 1, 0),
            array('#my1files,moodleakjladf|/user/files.php|download', 1, 0),
            array('#my1files,a/b|/user/files.php|download', 1, 0),
            array('#my1files,#b|/user/files.php|download', 1, 0),

                        array('-|-|-|-', 1, 0),
            array('-|-|-', 1, 0),
            array('-|-', 1, 0),
            array('#f234|2', 1, 0),

                        array('messages,message|/message/index.php|message', 1, 0),

                        array('messages,message|/message/index.php|message
privatefiles,moodle|/user/files.php|download
###
badges,badges|/badges/mybadges.php|award
-|-|-
test
-
#####
#f234|2', 5, 2),
        );
    }

    
    public function test_custom_user_menu($data, $entrycount, $dividercount) {
        global $CFG, $OUTPUT, $USER, $PAGE;

                $this->resetAfterTest(true);

                $this->setAdminUser();
        $PAGE->set_url('/');

                set_config('customusermenuitems', $data);

                $dividercount += 2;

                $entrycount += 4;

        $output = $OUTPUT->user_menu($USER);
        preg_match_all('/<a [^>]+role="menuitem"[^>]+>/', $output, $results);
        $this->assertCount($entrycount, $results[0]);

        preg_match_all('/<span class="filler">/', $output, $results);
        $this->assertCount($dividercount, $results[0]);
    }

}
