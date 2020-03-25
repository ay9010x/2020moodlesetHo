<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/lesson/locallib.php');
require_once($CFG->dirroot . '/mod/lesson/pagetypes/essay.php');



class mod_lesson_essay_page_type_test extends advanced_testcase {
    public function test_lesson_essay_extract_useranswer() {
                $answer = 'O:8:"stdClass":6:{s:4:"sent";i:1;s:6:"graded";i:1;s:5:"score";s:1:"1";'
                . 's:6:"answer";s:64:"<p>This is my answer <b>with bold</b> and <i>italics</i><br></p>";'
                . 's:12:"answerformat";s:1:"1";s:8:"response";s:10:"Well done!";}';
        $userresponse = new stdClass;
        $userresponse->sent = 1;
        $userresponse->graded = 1;
        $userresponse->score = 1;
        $userresponse->answer = "<p>This is my answer <b>with bold</b> and <i>italics</i><br></p>";
        $userresponse->answerformat = FORMAT_HTML;
        $userresponse->response = "Well done!";
        $userresponse->responseformat = FORMAT_HTML;
        $this->assertEquals($userresponse, lesson_page_type_essay::extract_useranswer($answer));

                $answer = 'O:8:"stdClass":7:{s:4:"sent";i:0;s:6:"graded";i:1;s:5:"score";s:1:"0";'
                . 's:6:"answer";s:64:"<p>This is my answer <b>with bold</b> and <i>italics</i><br></p>";'
                . 's:12:"answerformat";s:1:"1";s:8:"response";s:10:"Well done!";s:14:"responseformat";s:1:"2";}';
        $userresponse = new stdClass;
        $userresponse->sent = 0;
        $userresponse->graded = 1;
        $userresponse->score = 0;
        $userresponse->answer = "<p>This is my answer <b>with bold</b> and <i>italics</i><br></p>";
        $userresponse->answerformat = FORMAT_HTML;
        $userresponse->response = "Well done!";
        $userresponse->responseformat = FORMAT_PLAIN;
        $this->assertEquals($userresponse, lesson_page_type_essay::extract_useranswer($answer));
    }
}
