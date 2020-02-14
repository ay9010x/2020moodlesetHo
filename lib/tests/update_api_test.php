<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__.'/fixtures/testable_update_api.php');


class core_update_api_testcase extends advanced_testcase {

    
    public function test_convert_branch_numbering_format() {

        $client = \core\update\testable_api::client();

        $this->assertSame('2.9', $client->convert_branch_numbering_format(29));
        $this->assertSame('3.0', $client->convert_branch_numbering_format('30'));
        $this->assertSame('3.1', $client->convert_branch_numbering_format(3.1));
        $this->assertSame('3.1', $client->convert_branch_numbering_format('3.1'));
        $this->assertSame('10.1', $client->convert_branch_numbering_format(101));
        $this->assertSame('10.2', $client->convert_branch_numbering_format('102'));
    }

    
    public function test_get_plugin_info() {

        $client = \core\update\testable_api::client();

                $this->assertFalse($client->get_plugin_info('non_existing', 2015093000));

                $info = $client->get_plugin_info('foo_bar', 2014010100);
        $this->assertInstanceOf('\core\update\remote_info', $info);
        $this->assertFalse($info->version);

                foreach (array(2015093000 => MATURITY_STABLE, 2015100400 => MATURITY_STABLE,
                2015100500 => MATURITY_BETA) as $version => $maturity) {
            $info = $client->get_plugin_info('foo_bar', $version);
            $this->assertInstanceOf('\core\update\remote_info', $info);
            $this->assertNotEmpty($info->version);
            $this->assertEquals($maturity, $info->version->maturity);
        }
    }

    
    public function test_find_plugin() {

        $client = \core\update\testable_api::client();

                $this->assertFalse($client->find_plugin('non_existing'));

                $info = $client->find_plugin('foo_bar', 2016010100);
        $this->assertFalse($info->version);

                        $info = $client->find_plugin('foo_bar', 2015093000);
        $this->assertInstanceOf('\core\update\remote_info', $info);
        $this->assertEquals(2015100400, $info->version->version);

                        $info = $client->find_plugin('foo_bar', ANY_VERSION);
        $this->assertInstanceOf('\core\update\remote_info', $info);
        $this->assertEquals(2015100400, $info->version->version);

                $info = $client->find_plugin('foo_bar', 2015100500);
        $this->assertInstanceOf('\core\update\remote_info', $info);
        $this->assertEquals(2015100500, $info->version->version);
    }

    
    public function test_validate_pluginfo_format() {

        $client = \core\update\testable_api::client();

        $json = '{"id":127,"name":"Course contents","component":"block_course_contents","source":"https:\/\/github.com\/mudrd8mz\/moodle-block_course_contents","doc":"http:\/\/docs.moodle.org\/20\/en\/Course_contents_block","bugs":"https:\/\/github.com\/mudrd8mz\/moodle-block_course_contents\/issues","discussion":null,"version":{"id":8100,"version":"2015030300","release":"3.0","maturity":200,"downloadurl":"https:\/\/moodle.org\/plugins\/download.php\/8100\/block_course_contents_moodle29_2015030300.zip","downloadmd5":"8d8ae64822f38d278420776f8b42eaa5","vcssystem":"git","vcssystemother":null,"vcsrepositoryurl":"https:\/\/github.com\/mudrd8mz\/moodle-block_course_contents","vcsbranch":"master","vcstag":"v3.0","supportedmoodles":[{"version":2014041100,"release":"2.7"},{"version":2014101000,"release":"2.8"},{"version":2015041700,"release":"2.9"}]}}';

        $data = json_decode($json);
        $this->assertInstanceOf('\core\update\remote_info', $client->validate_pluginfo_format($data));
        $this->assertEquals(json_encode($data), json_encode($client->validate_pluginfo_format($data)));

                unset($data->version);
        $this->assertFalse($client->validate_pluginfo_format($data));

        $data->version = false;
        $this->assertEquals(json_encode($data), json_encode($client->validate_pluginfo_format($data)));

                $data = json_decode($json);
        $data->version->release = null;
        $this->assertEquals(json_encode($data), json_encode($client->validate_pluginfo_format($data)));

                $data = json_decode($json);
        $data->version->downloadurl = '';
        $this->assertFalse($client->validate_pluginfo_format($data));

                $data = json_decode($json);
        $data->version->downloadurl = 'ftp://archive.moodle.org/block_course_contents/2014041100.zip';
        $this->assertFalse($client->validate_pluginfo_format($data));
    }
}
