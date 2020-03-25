<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/chat/lib.php');


class mod_chat_format_message_testcase extends advanced_testcase {

    const USER_CURRENT = 1;
    const USER_OTHER = 2;

    public function chat_format_message_manually_provider() {
        $dateregexp = '\d{2}:\d{2}';
        return [
            'Beep everyone' => [
                'message'       => 'beep all',
                'system'        => false,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp}: " . get_string('messagebeepseveryone', 'chat', '__CURRENTUSER__') . ': /',
                'refreshusers'  => false,
                'beep'          => true,
            ],
            'Beep the current user' => [
                'message'       => 'beep __CURRENTUSER__',
                'system'        => false,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp}: " . get_string('messagebeepsyou', 'chat', '__CURRENTUSER__') . ': /',
                'refreshusers'  => false,
                'beep'          => true,
            ],
            'Beep another user' => [
                'message'       => 'beep __OTHERUSER__',
                'system'        => false,
                'willreturn'    => false,
                'expecttext'    => null,
                'refreshusers'  => null,
                'beep'          => null,
            ],
            'Malformed beep' => [
                'message'       => 'beep',
                'system'        => false,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp} __CURRENTUSER_FIRST__: beep$/",
                'refreshusers'  => false,
                'beep'          => false,
            ],
            '/me says' => [
                'message'       => '/me writes a test',
                'system'        => false,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp}: \*\*\* __CURRENTUSER_FIRST__ writes a test$/",
                'refreshusers'  => false,
                'beep'          => false,
            ],
            'Invalid command' => [
                'message'       => '/help',
                'system'        => false,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp} __CURRENTUSER_FIRST__: \/help$/",
                'refreshusers'  => false,
                'beep'          => false,
            ],
            'To user' => [
                'message'       => 'To Bernard:I love tests',
                'system'        => false,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp}: __CURRENTUSER_FIRST__ " . get_string('saidto', 'chat') . " Bernard: I love tests$/",
                'refreshusers'  => false,
                'beep'          => false,
            ],
            'To user trimmed' => [
                'message'       => 'To Bernard: I love tests',
                'system'        => false,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp}: __CURRENTUSER_FIRST__ " . get_string('saidto', 'chat') . " Bernard: I love tests$/",
                'refreshusers'  => false,
                'beep'          => false,
            ],
            'System: enter' => [
                'message'       => 'enter',
                'system'        => true,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp}: " . get_string('messageenter', 'chat', '__CURRENTUSER__') . "$/",
                'refreshusers'  => true,
                'beep'          => false,
            ],
            'System: exit' => [
                'message'       => 'exit',
                'system'        => true,
                'willreturn'    => true,
                'expecttext'    => "/^{$dateregexp}: " . get_string('messageexit', 'chat', '__CURRENTUSER__') . "$/",
                'refreshusers'  => true,
                'beep'          => false,
            ],
        ];
    }

    
    public function test_chat_format_message_manually($messagetext, $system, $willreturn,
            $expecttext, $refreshusers, $expectbeep) {

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $currentuser = $this->getDataGenerator()->create_user();
        $this->setUser($currentuser);
        $otheruser = $this->getDataGenerator()->create_user();

                                $messagetext = str_replace('__CURRENTUSER__', $currentuser->id, $messagetext);
        $messagetext = str_replace('__OTHERUSER__', $otheruser->id, $messagetext);

        $message = (object) [
            'message'   => $messagetext,
            'timestamp' => time(),
            'system'    => $system,
        ];

        $result = chat_format_message_manually($message, $course->id, $currentuser, $currentuser);

        if (!$willreturn) {
            $this->assertFalse($result);
        } else {
            $this->assertNotFalse($result);
            if (!empty($expecttext)) {
                $expecttext = str_replace('__CURRENTUSER__', fullname($currentuser), $expecttext);
                $expecttext = str_replace('__CURRENTUSER_FIRST__', $currentuser->firstname, $expecttext);
                $this->assertRegexp($expecttext, $result->text);
            }

            $this->assertEquals($refreshusers, $result->refreshusers);
            $this->assertEquals($expectbeep, $result->beep);
        }
    }
}
