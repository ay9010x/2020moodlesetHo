<?php


defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(__DIR__)) . '/moodleblock.class.php');
require_once(dirname(__DIR__) . '/block_rss_client.php');


class block_rss_client_cron_testcase extends advanced_testcase {
    
    public function test_skip() {
        global $DB, $CFG;
        $this->resetAfterTest();
                $record = (object) array(
            'userid' => 1,
            'title' => 'Skip test feed',
            'preferredtitle' => '',
            'description' => 'A feed to test the skip time.',
            'shared' => 0,
            'url' => 'http://example.com/rss',
            'skiptime' => 330,
            'skipuntil' => time() + 300,
        );
        $DB->insert_record('block_rss_client', $record);

        $block = new block_rss_client();
        ob_start();

                $errorlevel = error_reporting($CFG->debug & ~E_USER_NOTICE);
        $block->cron();
        error_reporting($errorlevel);

        $cronoutput = ob_get_clean();
        $this->assertContains('skipping until ' . userdate($record->skipuntil), $cronoutput);
        $this->assertContains('0 feeds refreshed (took ', $cronoutput);
    }

    
    public function test_error() {
        global $DB, $CFG;
        $this->resetAfterTest();
        $time = time();
                $record = (object) array(
            'userid' => 1,
            'title' => 'Skip test feed',
            'preferredtitle' => '',
            'description' => 'A feed to test the skip time.',
            'shared' => 0,
            'url' => 'http://example.com/rss',
            'skiptime' => 330,
            'skipuntil' => $time - 300,
        );
        $record->id = $DB->insert_record('block_rss_client', $record);

                $record2 = (object) array(
            'userid' => 1,
            'title' => 'Skip test feed',
            'preferredtitle' => '',
            'description' => 'A feed to test the skip time.',
            'shared' => 0,
            'url' => 'http://example.com/rss2',
            'skiptime' => 0,
            'skipuntil' => 0,
        );
        $record2->id = $DB->insert_record('block_rss_client', $record2);

                $record3 = (object) array(
            'userid' => 1,
            'title' => 'Skip test feed',
            'preferredtitle' => '',
            'description' => 'A feed to test the skip time.',
            'shared' => 0,
            'url' => 'http://example.com/rss3',
            'skiptime' => block_rss_client::CLIENT_MAX_SKIPTIME - 5,
            'skipuntil' => $time - 1,
        );
        $record3->id = $DB->insert_record('block_rss_client', $record3);

                $block = new block_rss_client();
        ob_start();

                $errorlevel = error_reporting($CFG->debug & ~E_USER_NOTICE);
        $block->cron();
        error_reporting($errorlevel);

        $cronoutput = ob_get_clean();
        $skiptime1 = $record->skiptime * 2;
        $message1 = 'http://example.com/rss Error: could not load/find the RSS feed - skipping for ' . $skiptime1 . ' seconds.';
        $this->assertContains($message1, $cronoutput);
        $skiptime2 = 330;         $message2 = 'http://example.com/rss2 Error: could not load/find the RSS feed - skipping for ' . $skiptime2 . ' seconds.';
        $this->assertContains($message2, $cronoutput);
        $skiptime3 = block_rss_client::CLIENT_MAX_SKIPTIME;
        $message3 = 'http://example.com/rss3 Error: could not load/find the RSS feed - skipping for ' . $skiptime3 . ' seconds.';
        $this->assertContains($message3, $cronoutput);
        $this->assertContains('0 feeds refreshed (took ', $cronoutput);

                $newrecord = $DB->get_record('block_rss_client', array('id' => $record->id));
        $this->assertAttributeEquals($skiptime1, 'skiptime', $newrecord);
        $this->assertAttributeGreaterThanOrEqual($time + $skiptime1, 'skipuntil', $newrecord);
        $newrecord2 = $DB->get_record('block_rss_client', array('id' => $record2->id));
        $this->assertAttributeEquals($skiptime2, 'skiptime', $newrecord2);
        $this->assertAttributeGreaterThanOrEqual($time + $skiptime2, 'skipuntil', $newrecord2);
        $newrecord3 = $DB->get_record('block_rss_client', array('id' => $record3->id));
        $this->assertAttributeEquals($skiptime3, 'skiptime', $newrecord3);
        $this->assertAttributeGreaterThanOrEqual($time + $skiptime3, 'skipuntil', $newrecord3);
    }
}
