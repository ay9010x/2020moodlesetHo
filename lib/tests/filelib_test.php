<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/repository/lib.php');

class core_filelib_testcase extends advanced_testcase {
    public function test_format_postdata_for_curlcall() {

                $postdatatoconvert = array( 'userid' => 1, 'roleid' => 22, 'name' => 'john');
        $expectedresult = "userid=1&roleid=22&name=john";
        $postdata = format_postdata_for_curlcall($postdatatoconvert);
        $this->assertEquals($expectedresult, $postdata);

                $postdatatoconvert = array( 'name' => 'john&emilie', 'roleid' => 22);
        $expectedresult = "name=john%26emilie&roleid=22";         $postdata = format_postdata_for_curlcall($postdatatoconvert);
        $this->assertEquals($expectedresult, $postdata);

                $postdatatoconvert = array( 'name' => null, 'roleid' => 22);
        $expectedresult = "name=&roleid=22";
        $postdata = format_postdata_for_curlcall($postdatatoconvert);
        $this->assertEquals($expectedresult, $postdata);

                $postdatatoconvert = array( 'users' => array(
            array(
                'id' => 2,
                'customfields' => array(
                    array
                    (
                        'type' => 'Color',
                        'value' => 'violet'
                    )
                )
            )
        )
        );
        $expectedresult = "users[0][id]=2&users[0][customfields][0][type]=Color&users[0][customfields][0][value]=violet";
        $postdata = format_postdata_for_curlcall($postdatatoconvert);
        $this->assertEquals($expectedresult, $postdata);

                $postdatatoconvert = array ('members' =>
        array(
            array('groupid' => 1, 'userid' => 1)
        , array('groupid' => 1, 'userid' => 2)
        )
        );
        $expectedresult = "members[0][groupid]=1&members[0][userid]=1&members[1][groupid]=1&members[1][userid]=2";
        $postdata = format_postdata_for_curlcall($postdatatoconvert);
        $this->assertEquals($expectedresult, $postdata);
    }

    public function test_download_file_content() {
        global $CFG;

                $testhtml = $this->getExternalTestFileUrl('/test.html');

        $contents = download_file_content($testhtml);
        $this->assertSame('47250a973d1b88d9445f94db4ef2c97a', md5($contents));

        $tofile = "$CFG->tempdir/test.html";
        @unlink($tofile);
        $result = download_file_content($testhtml, null, null, false, 300, 20, false, $tofile);
        $this->assertTrue($result);
        $this->assertFileExists($tofile);
        $this->assertSame(file_get_contents($tofile), $contents);
        @unlink($tofile);

        $result = download_file_content($testhtml, null, null, false, 300, 20, false, null, true);
        $this->assertSame($contents, $result);

        $response = download_file_content($testhtml, null, null, true);
        $this->assertInstanceOf('stdClass', $response);
        $this->assertSame('200', $response->status);
        $this->assertTrue(is_array($response->headers));
        $this->assertRegExp('|^HTTP/1\.[01] 200 OK$|', rtrim($response->response_code));
        $this->assertSame($contents, $response->results);
        $this->assertSame('', $response->error);

                $testhtml = $this->getExternalTestFileUrl('/test.html', true);

        $contents = download_file_content($testhtml, null, null, false, 300, 20, true);
        $this->assertSame('47250a973d1b88d9445f94db4ef2c97a', md5($contents));

        $contents = download_file_content($testhtml);
        $this->assertSame('47250a973d1b88d9445f94db4ef2c97a', md5($contents));

                $testhtml = $this->getExternalTestFileUrl('/test.html_nonexistent');

        $contents = download_file_content($testhtml);
        $this->assertFalse($contents);
        $this->assertDebuggingCalled();

        $response = download_file_content($testhtml, null, null, true);
        $this->assertInstanceOf('stdClass', $response);
        $this->assertSame('404', $response->status);
        $this->assertTrue(is_array($response->headers));
        $this->assertRegExp('|^HTTP/1\.[01] 404 Not Found$|', rtrim($response->response_code));
                $this->assertSame('', $response->error);

                $testhtml = $this->getExternalTestFileUrl('/test.html');
        $testhtml = str_replace('http://', 'ftp://', $testhtml);

        $contents = download_file_content($testhtml);
        $this->assertFalse($contents);

                $testurl = $this->getExternalTestFileUrl('/test_redir.php');

        $contents = download_file_content("$testurl?redir=2");
        $this->assertSame('done', $contents);

        $response = download_file_content("$testurl?redir=2", null, null, true);
        $this->assertInstanceOf('stdClass', $response);
        $this->assertSame('200', $response->status);
        $this->assertTrue(is_array($response->headers));
        $this->assertRegExp('|^HTTP/1\.[01] 200 OK$|', rtrim($response->response_code));
        $this->assertSame('done', $response->results);
        $this->assertSame('', $response->error);

                

                $testurl = $this->getExternalTestFileUrl('/test_relative_redir.php');

        $contents = download_file_content("$testurl");
        $this->assertSame('done', $contents);

        $contents = download_file_content("$testurl?unused=xxx");
        $this->assertSame('done', $contents);
    }

    
    public function test_curl_basics() {
        global $CFG;

                $testhtml = $this->getExternalTestFileUrl('/test.html');

        $curl = new curl();
        $contents = $curl->get($testhtml);
        $this->assertSame('47250a973d1b88d9445f94db4ef2c97a', md5($contents));
        $this->assertSame(0, $curl->get_errno());

        $curl = new curl();
        $tofile = "$CFG->tempdir/test.html";
        @unlink($tofile);
        $fp = fopen($tofile, 'w');
        $result = $curl->get($testhtml, array(), array('CURLOPT_FILE'=>$fp));
        $this->assertTrue($result);
        fclose($fp);
        $this->assertFileExists($tofile);
        $this->assertSame($contents, file_get_contents($tofile));
        @unlink($tofile);

        $curl = new curl();
        $tofile = "$CFG->tempdir/test.html";
        @unlink($tofile);
        $result = $curl->download_one($testhtml, array(), array('filepath'=>$tofile));
        $this->assertTrue($result);
        $this->assertFileExists($tofile);
        $this->assertSame($contents, file_get_contents($tofile));
        @unlink($tofile);

                $curl = new curl();
        $contents = $curl->get($this->getExternalTestFileUrl('/i.do.not.exist'));
        $response = $curl->getResponse();
        $this->assertSame('404 Not Found', reset($response));
        $this->assertSame(0, $curl->get_errno());
    }

    public function test_curl_redirects() {
        global $CFG;

                $testurl = $this->getExternalTestFileUrl('/test_redir.php');

        $curl = new curl();
        $contents = $curl->get("$testurl?redir=2", array(), array('CURLOPT_MAXREDIRS'=>2));
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(2, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?redir=2", array(), array('CURLOPT_MAXREDIRS'=>2));
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(2, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

                                reset($response);
        if (key($response) === 'HTTP/1.0') {
            $responsecode302 = '302 Moved Temporarily';
        } else {
            $responsecode302 = '302 Found';
        }

        $curl = new curl();
        $contents = $curl->get("$testurl?redir=3", array(), array('CURLOPT_FOLLOWLOCATION'=>0));
        $response = $curl->getResponse();
        $this->assertSame($responsecode302, reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(302, $curl->info['http_code']);
        $this->assertSame('', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?redir=3", array(), array('CURLOPT_FOLLOWLOCATION'=>0));
        $response = $curl->getResponse();
        $this->assertSame($responsecode302, reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(302, $curl->info['http_code']);
        $this->assertSame('', $contents);

        $curl = new curl();
        $contents = $curl->get("$testurl?redir=2", array(), array('CURLOPT_MAXREDIRS'=>1));
        $this->assertSame(CURLE_TOO_MANY_REDIRECTS, $curl->get_errno());
        $this->assertNotEmpty($contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?redir=2", array(), array('CURLOPT_MAXREDIRS'=>1));
        $this->assertSame(CURLE_TOO_MANY_REDIRECTS, $curl->get_errno());
        $this->assertNotEmpty($contents);

        $curl = new curl();
        $tofile = "$CFG->tempdir/test.html";
        @unlink($tofile);
        $fp = fopen($tofile, 'w');
        $result = $curl->get("$testurl?redir=1", array(), array('CURLOPT_FILE'=>$fp));
        $this->assertTrue($result);
        fclose($fp);
        $this->assertFileExists($tofile);
        $this->assertSame('done', file_get_contents($tofile));
        @unlink($tofile);

        $curl = new curl();
        $curl->emulateredirects = true;
        $tofile = "$CFG->tempdir/test.html";
        @unlink($tofile);
        $fp = fopen($tofile, 'w');
        $result = $curl->get("$testurl?redir=1", array(), array('CURLOPT_FILE'=>$fp));
        $this->assertTrue($result);
        fclose($fp);
        $this->assertFileExists($tofile);
        $this->assertSame('done', file_get_contents($tofile));
        @unlink($tofile);

        $curl = new curl();
        $tofile = "$CFG->tempdir/test.html";
        @unlink($tofile);
        $result = $curl->download_one("$testurl?redir=1", array(), array('filepath'=>$tofile));
        $this->assertTrue($result);
        $this->assertFileExists($tofile);
        $this->assertSame('done', file_get_contents($tofile));
        @unlink($tofile);

        $curl = new curl();
        $curl->emulateredirects = true;
        $tofile = "$CFG->tempdir/test.html";
        @unlink($tofile);
        $result = $curl->download_one("$testurl?redir=1", array(), array('filepath'=>$tofile));
        $this->assertTrue($result);
        $this->assertFileExists($tofile);
        $this->assertSame('done', file_get_contents($tofile));
        @unlink($tofile);
    }

    public function test_curl_relative_redirects() {
                $testurl = $this->getExternalTestFileUrl('/test_relative_redir.php');

        $curl = new curl();
        $contents = $curl->get($testurl);
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get($testurl);
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

                $testurl = $this->getExternalTestFileUrl('/test_relative_redir.php');

        $curl = new curl();
        $contents = $curl->get("$testurl?type=301");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?type=301");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $contents = $curl->get("$testurl?type=302");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?type=302");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $contents = $curl->get("$testurl?type=303");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?type=303");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $contents = $curl->get("$testurl?type=307");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?type=307");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $contents = $curl->get("$testurl?type=308");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

        $curl = new curl();
        $curl->emulateredirects = true;
        $contents = $curl->get("$testurl?type=308");
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame(1, $curl->info['redirect_count']);
        $this->assertSame('done', $contents);

    }

    public function test_curl_proxybypass() {
        global $CFG;
        $testurl = $this->getExternalTestFileUrl('/test.html');

        $oldproxy = $CFG->proxyhost;
        $oldproxybypass = $CFG->proxybypass;

                $CFG->proxyhost = 'i.do.not.exist';
        $CFG->proxybypass = '';
        $curl = new curl();
        $contents = $curl->get($testurl);
        $this->assertNotEquals(0, $curl->get_errno());
        $this->assertNotEquals('47250a973d1b88d9445f94db4ef2c97a', md5($contents));

                $testurlhost = parse_url($testurl, PHP_URL_HOST);
        $CFG->proxybypass = $testurlhost;
        $curl = new curl();
        $contents = $curl->get($testurl);
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame('47250a973d1b88d9445f94db4ef2c97a', md5($contents));

        $CFG->proxyhost = $oldproxy;
        $CFG->proxybypass = $oldproxybypass;
    }

    public function test_curl_post() {
        $testurl = $this->getExternalTestFileUrl('/test_post.php');

                $curl = new curl();
        $contents = $curl->post($testurl, 'data=moodletest');
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame('OK', $contents);

                $curl = new curl();
        $curl->setHeader('Expect: 100-continue');
        $contents = $curl->post($testurl, 'data=moodletest');
        $response = $curl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $curl->get_errno());
        $this->assertSame('OK', $contents);
    }

    public function test_curl_file() {
        $this->resetAfterTest();
        $testurl = $this->getExternalTestFileUrl('/test_file.php');

        $fs = get_file_storage();
        $filerecord = array(
            'contextid' => context_system::instance()->id,
            'component' => 'test',
            'filearea' => 'curl_post',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'test.txt'
        );
        $teststring = 'moodletest';
        $testfile = $fs->create_file_from_string($filerecord, $teststring);

                $data = array('testfile' => $testfile);
        $curl = new curl();
        $contents = $curl->post($testurl, $data);
        $this->assertSame('OK', $contents);
    }

    public function test_curl_protocols() {

                        $curl = new curl();

                $testurl = 'file:///';
        $curl->get($testurl);
        $this->assertNotEmpty($curl->error);
        $this->assertEquals(CURLE_UNSUPPORTED_PROTOCOL, $curl->errno);

        $testurl = 'ftp://nowhere';
        $curl->get($testurl);
        $this->assertNotEmpty($curl->error);
        $this->assertEquals(CURLE_UNSUPPORTED_PROTOCOL, $curl->errno);

        $testurl = 'telnet://somewhere';
        $curl->get($testurl);
        $this->assertNotEmpty($curl->error);
        $this->assertEquals(CURLE_UNSUPPORTED_PROTOCOL, $curl->errno);

                $testurl = $this->getExternalTestFileUrl('/test_redir_proto.php');
        $curl->get($testurl, array('proto' => 'file'));
        $this->assertNotEmpty($curl->error);
        $this->assertEquals(CURLE_UNSUPPORTED_PROTOCOL, $curl->errno);

        $testurl = $this->getExternalTestFileUrl('/test_redir_proto.php');
        $curl->get($testurl, array('proto' => 'ftp'));
        $this->assertNotEmpty($curl->error);
        $this->assertEquals(CURLE_UNSUPPORTED_PROTOCOL, $curl->errno);

        $testurl = $this->getExternalTestFileUrl('/test_redir_proto.php');
        $curl->get($testurl, array('proto' => 'telnet'));
        $this->assertNotEmpty($curl->error);
        $this->assertEquals(CURLE_UNSUPPORTED_PROTOCOL, $curl->errno);
    }

    
    public function test_prepare_draft_area() {
        global $USER, $DB;

        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $usercontext = context_user::instance($user->id);
        $USER = $DB->get_record('user', array('id'=>$user->id));

        $repositorypluginname = 'user';

        $args = array();
        $args['type'] = $repositorypluginname;
        $repos = repository::get_instances($args);
        $userrepository = reset($repos);
        $this->assertInstanceOf('repository', $userrepository);

        $fs = get_file_storage();

        $syscontext = context_system::instance();
        $component = 'core';
        $filearea  = 'unittest';
        $itemid    = 0;
        $filepath  = '/';
        $filename  = 'test.txt';
        $sourcefield = 'Copyright stuff';

        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => $component,
            'filearea'  => $filearea,
            'itemid'    => $itemid,
            'filepath'  => $filepath,
            'filename'  => $filename,
            'source'    => $sourcefield,
        );
        $ref = $fs->pack_reference($filerecord);
        $originalfile = $fs->create_file_from_string($filerecord, 'Test content');
        $fileid = $originalfile->get_id();
        $this->assertInstanceOf('stored_file', $originalfile);

                $userfilerecord = new stdClass;
        $userfilerecord->contextid = $usercontext->id;
        $userfilerecord->component = 'user';
        $userfilerecord->filearea  = 'private';
        $userfilerecord->itemid    = 0;
        $userfilerecord->filepath  = '/';
        $userfilerecord->filename  = 'userfile.txt';
        $userfilerecord->source    = 'test';
        $userfile = $fs->create_file_from_string($userfilerecord, 'User file content');
        $userfileref = $fs->pack_reference($userfilerecord);

        $filerefrecord = clone((object)$filerecord);
        $filerefrecord->filename = 'testref.txt';

                $fileref = $fs->create_file_from_reference($filerefrecord, $userrepository->id, $userfileref);
        $this->assertInstanceOf('stored_file', $fileref);
        $this->assertEquals($userrepository->id, $fileref->get_repository_id());
        $this->assertSame($userfile->get_contenthash(), $fileref->get_contenthash());
        $this->assertEquals($userfile->get_filesize(), $fileref->get_filesize());
        $this->assertRegExp('#' . $userfile->get_filename(). '$#', $fileref->get_reference_details());

        $draftitemid = 0;
        file_prepare_draft_area($draftitemid, $syscontext->id, $component, $filearea, $itemid);

        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid);
        $this->assertCount(3, $draftfiles);

        $draftfile = $fs->get_file($usercontext->id, 'user', 'draft', $draftitemid, $filepath, $filename);
        $source = unserialize($draftfile->get_source());
        $this->assertSame($ref, $source->original);
        $this->assertSame($sourcefield, $source->source);

        $draftfileref = $fs->get_file($usercontext->id, 'user', 'draft', $draftitemid, $filepath, $filerefrecord->filename);
        $this->assertInstanceOf('stored_file', $draftfileref);
        $this->assertTrue($draftfileref->is_external_file());

                $author = 'Dongsheng Cai';
        $draftfile->set_author($author);
        $newsourcefield = 'Get from Flickr';
        $license = 'GPLv3';
        $draftfile->set_license($license);
                $source = unserialize($draftfile->get_source());
        $newsourcefield = 'From flickr';
        $source->source = $newsourcefield;
        $draftfile->set_source(serialize($source));

                file_save_draft_area_files($draftitemid, $syscontext->id, $component, $filearea, $itemid);

        $file = $fs->get_file($syscontext->id, $component, $filearea, $itemid, $filepath, $filename);

                $this->assertEquals($fileid, $file->get_id());
        $this->assertInstanceOf('stored_file', $file);
        $this->assertSame($author, $file->get_author());
        $this->assertSame($license, $file->get_license());
        $this->assertEquals($newsourcefield, $file->get_source());
    }

    
    public function test_delete_original_file_from_draft() {
        global $USER, $DB;

        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $usercontext = context_user::instance($user->id);
        $USER = $DB->get_record('user', array('id'=>$user->id));

        $repositorypluginname = 'user';

        $args = array();
        $args['type'] = $repositorypluginname;
        $repos = repository::get_instances($args);
        $userrepository = reset($repos);
        $this->assertInstanceOf('repository', $userrepository);

        $fs = get_file_storage();
        $syscontext = context_system::instance();

        $filecontent = 'User file content';

                $userfilerecord = new stdClass;
        $userfilerecord->contextid = $usercontext->id;
        $userfilerecord->component = 'user';
        $userfilerecord->filearea  = 'private';
        $userfilerecord->itemid    = 0;
        $userfilerecord->filepath  = '/';
        $userfilerecord->filename  = 'userfile.txt';
        $userfilerecord->source    = 'test';
        $userfile = $fs->create_file_from_string($userfilerecord, $filecontent);
        $userfileref = $fs->pack_reference($userfilerecord);
        $contenthash = $userfile->get_contenthash();

        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'phpunit',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => 'test.txt',
        );
                $fileref = $fs->create_file_from_reference($filerecord, $userrepository->id, $userfileref);
        $this->assertInstanceOf('stored_file', $fileref);
        $this->assertEquals($userrepository->id, $fileref->get_repository_id());
        $this->assertSame($userfile->get_contenthash(), $fileref->get_contenthash());
        $this->assertEquals($userfile->get_filesize(), $fileref->get_filesize());
        $this->assertRegExp('#' . $userfile->get_filename(). '$#', $fileref->get_reference_details());

        $draftitemid = 0;
        file_prepare_draft_area($draftitemid, $usercontext->id, 'user', 'private', 0);
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid);
        $this->assertCount(2, $draftfiles);
        $draftfile = $fs->get_file($usercontext->id, 'user', 'draft', $draftitemid, $userfilerecord->filepath, $userfilerecord->filename);
        $draftfile->delete();
                file_save_draft_area_files($draftitemid, $usercontext->id, 'user', 'private', 0);

                $fileref = $fs->get_file($syscontext->id, 'core', 'phpunit', 0, '/', 'test.txt');
        $this->assertFalse($fileref->is_external_file());
        $this->assertSame($contenthash, $fileref->get_contenthash());
        $this->assertEquals($filecontent, $fileref->get_content());
    }

    
    public function test_curl_strip_double_headers() {
                $mdl30648example = <<<EOF
HTTP/1.0 407 Proxy Authentication Required
Server: squid/2.7.STABLE9
Date: Thu, 08 Dec 2011 14:44:33 GMT
Content-Type: text/html
Content-Length: 1275
X-Squid-Error: ERR_CACHE_ACCESS_DENIED 0
Proxy-Authenticate: Basic realm="Squid proxy-caching web server"
X-Cache: MISS from homer.lancs.ac.uk
X-Cache-Lookup: NONE from homer.lancs.ac.uk:3128
Via: 1.0 homer.lancs.ac.uk:3128 (squid/2.7.STABLE9)
Connection: close

HTTP/1.0 200 OK
Server: Apache
X-Lb-Nocache: true
Cache-Control: private, max-age=15, no-transform
ETag: "4d69af5d8ba873ea9192c489e151bd7b"
Content-Type: text/html
Date: Thu, 08 Dec 2011 14:44:53 GMT
Set-Cookie: BBC-UID=c4de2e109c8df6a51de627cee11b214bd4fb6054a030222488317afb31b343360MoodleBot/1.0; expires=Mon, 07-Dec-15 14:44:53 GMT; path=/; domain=bbc.co.uk
X-Cache-Action: MISS
X-Cache-Age: 0
Vary: Cookie,X-Country,X-Ip-is-uk-combined,X-Ip-is-advertise-combined,X-Ip_is_uk_combined,X-Ip_is_advertise_combined, X-GeoIP
X-Cache: MISS from ww

<html>...
EOF;
        $mdl30648expected = <<<EOF
HTTP/1.0 200 OK
Server: Apache
X-Lb-Nocache: true
Cache-Control: private, max-age=15, no-transform
ETag: "4d69af5d8ba873ea9192c489e151bd7b"
Content-Type: text/html
Date: Thu, 08 Dec 2011 14:44:53 GMT
Set-Cookie: BBC-UID=c4de2e109c8df6a51de627cee11b214bd4fb6054a030222488317afb31b343360MoodleBot/1.0; expires=Mon, 07-Dec-15 14:44:53 GMT; path=/; domain=bbc.co.uk
X-Cache-Action: MISS
X-Cache-Age: 0
Vary: Cookie,X-Country,X-Ip-is-uk-combined,X-Ip-is-advertise-combined,X-Ip_is_uk_combined,X-Ip_is_advertise_combined, X-GeoIP
X-Cache: MISS from ww

<html>...
EOF;
                $mdl30648example = preg_replace("~(?!<\r)\n~", "\r\n", $mdl30648example);
        $mdl30648expected = preg_replace("~(?!<\r)\n~", "\r\n", $mdl30648expected);

                $this->assertSame($mdl30648expected, curl::strip_double_headers($mdl30648example));
                $this->assertSame($mdl30648expected, curl::strip_double_headers($mdl30648expected));

                $httpsexample = <<<EOF
HTTP/1.0 200 Connection established

HTTP/1.1 200 OK
Date: Fri, 22 Feb 2013 17:14:23 GMT
Server: Apache/2
X-Powered-By: PHP/5.3.3-7+squeeze14
Content-Type: text/xml
Connection: close
Content-Encoding: gzip
Transfer-Encoding: chunked

<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0">...
EOF;
        $httpsexpected = <<<EOF
HTTP/1.1 200 OK
Date: Fri, 22 Feb 2013 17:14:23 GMT
Server: Apache/2
X-Powered-By: PHP/5.3.3-7+squeeze14
Content-Type: text/xml
Connection: close
Content-Encoding: gzip
Transfer-Encoding: chunked

<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0">...
EOF;
                $httpsexample = preg_replace("~(?!<\r)\n~", "\r\n", $httpsexample);
        $httpsexpected = preg_replace("~(?!<\r)\n~", "\r\n", $httpsexpected);

                $this->assertSame($httpsexpected, curl::strip_double_headers($httpsexample));
                $this->assertSame($httpsexpected, curl::strip_double_headers($httpsexpected));
    }

    
    public function test_get_mimetype_description() {
        $this->resetAfterTest();

                $this->assertEquals(get_string('application/msword', 'mimetypes'),
                get_mimetype_description(array('filename' => 'test.doc')));

                $this->assertEquals(get_string('document/unknown', 'mimetypes'),
                get_mimetype_description(array('filename' => 'test.frog')));

                core_filetypes::add_type('frog', 'application/x-frog', 'document');
        $this->assertEquals('application/x-frog',
                get_mimetype_description(array('filename' => 'test.frog')));

                core_filetypes::update_type('frog', 'frog', 'application/x-frog', 'document',
                array(), '', 'Froggy file');
        $this->assertEquals('Froggy file',
                get_mimetype_description(array('filename' => 'test.frog')));

                filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);
        core_filetypes::update_type('frog', 'frog', 'application/x-frog', 'document',
                array(), '', '<span lang="en" class="multilang">Green amphibian</span>' .
                '<span lang="fr" class="multilang">Amphibian vert</span>');
        $this->assertEquals('Green amphibian',
                get_mimetype_description(array('filename' => 'test.frog')));
    }

    
    public function test_get_mimetypes_array() {
        $mimeinfo = get_mimetypes_array();

                $this->assertEquals('application/msword', $mimeinfo['doc']['type']);
        $this->assertEquals('document', $mimeinfo['doc']['icon']);
        $this->assertEquals(array('document'), $mimeinfo['doc']['groups']);
        $this->assertFalse(isset($mimeinfo['doc']['string']));
        $this->assertFalse(isset($mimeinfo['doc']['defaulticon']));
        $this->assertFalse(isset($mimeinfo['doc']['customdescription']));

                $this->assertEquals('image', $mimeinfo['png']['string']);
        $this->assertEquals(true, $mimeinfo['txt']['defaulticon']);
    }

    
    public function test_get_mimetype_for_sending() {
                $this->assertEquals('application/octet-stream', get_mimetype_for_sending());

                $this->assertEquals('application/octet-stream', get_mimetype_for_sending(null));

                $this->assertEquals('application/octet-stream', get_mimetype_for_sending('filenamewithoutextension'));

                $mimetypes = get_mimetypes_array();
        foreach ($mimetypes as $ext => $info) {
            if ($ext === 'xxx') {
                $this->assertEquals('application/octet-stream', get_mimetype_for_sending('SampleFile.' . $ext));
            } else {
                $this->assertEquals($info['type'], get_mimetype_for_sending('SampleFile.' . $ext));
            }
        }
    }

    
    public function test_curl_useragent() {
        $curl = new testable_curl();
        $options = $curl->get_options();
        $this->assertNotEmpty($options);

        $curl->call_apply_opt($options);
        $this->assertTrue(in_array('User-Agent: MoodleBot/1.0', $curl->header));
        $this->assertFalse(in_array('User-Agent: Test/1.0', $curl->header));

        $options['CURLOPT_USERAGENT'] = 'Test/1.0';
        $curl->call_apply_opt($options);
        $this->assertTrue(in_array('User-Agent: Test/1.0', $curl->header));
        $this->assertFalse(in_array('User-Agent: MoodleBot/1.0', $curl->header));

        $curl->set_option('CURLOPT_USERAGENT', 'AnotherUserAgent/1.0');
        $curl->call_apply_opt();
        $this->assertTrue(in_array('User-Agent: AnotherUserAgent/1.0', $curl->header));
        $this->assertFalse(in_array('User-Agent: Test/1.0', $curl->header));

        $curl->set_option('CURLOPT_USERAGENT', 'AnotherUserAgent/1.1');
        $options = $curl->get_options();
        $curl->call_apply_opt($options);
        $this->assertTrue(in_array('User-Agent: AnotherUserAgent/1.1', $curl->header));
        $this->assertFalse(in_array('User-Agent: AnotherUserAgent/1.0', $curl->header));

        $curl->unset_option('CURLOPT_USERAGENT');
        $curl->call_apply_opt();
        $this->assertTrue(in_array('User-Agent: MoodleBot/1.0', $curl->header));

                        $testurl = $this->getExternalTestFileUrl('/test_agent.php');
        $extcurl = new curl();
        $contents = $extcurl->get($testurl, array(), array('CURLOPT_USERAGENT' => 'AnotherUserAgent/1.2'));
        $response = $extcurl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $extcurl->get_errno());
        $this->assertSame('OK', $contents);
                $contents = $extcurl->get($testurl, array(), array('CURLOPT_USERAGENT' => 'NonMatchingUserAgent/1.2'));
        $response = $extcurl->getResponse();
        $this->assertSame('200 OK', reset($response));
        $this->assertSame(0, $extcurl->get_errno());
        $this->assertSame('', $contents);
    }

    
    public function test_file_rewrite_pluginfile_urls() {

        $syscontext = context_system::instance();
        $originaltext = 'Fake test with an image <img src="@@PLUGINFILE@@/image.png">';

                $finaltext = file_rewrite_pluginfile_urls($originaltext, 'pluginfile.php', $syscontext->id, 'user', 'private', 0);
        $this->assertContains("pluginfile.php", $finaltext);

                $options = array('reverse' => true);
        $finaltext = file_rewrite_pluginfile_urls($finaltext, 'pluginfile.php', $syscontext->id, 'user', 'private', 0, $options);

                $this->assertEquals($originaltext, $finaltext);
    }

    
    public static function create_draft_file($filedata = array()) {
        global $USER;

        self::setAdminUser();
        $fs = get_file_storage();

        $filerecord = array(
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => isset($filedata['itemid']) ? $filedata['itemid'] : file_get_unused_draft_itemid(),
            'author'    => isset($filedata['author']) ? $filedata['author'] : fullname($USER),
            'filepath'  => isset($filedata['filepath']) ? $filedata['filepath'] : '/',
            'filename'  => isset($filedata['filename']) ? $filedata['filename'] : 'file.txt',
        );

        if (isset($filedata['contextid'])) {
            $filerecord['contextid'] = $filedata['contextid'];
        } else {
            $usercontext = context_user::instance($USER->id);
            $filerecord['contextid'] = $usercontext->id;
        }
        $source = isset($filedata['source']) ? $filedata['source'] : serialize((object)array('source' => 'From string'));
        $content = isset($filedata['content']) ? $filedata['content'] : 'some content here';

        $file = $fs->create_file_from_string($filerecord, $content);
        $file->set_source($source);

        return $file;
    }

    
    public function test_file_merge_files_from_draft_area_into_filearea() {
        global $USER, $CFG;

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);

                $filename = 'data.txt';
        $filerecord = array(
            'filename'  => $filename,
        );
        $file = self::create_draft_file($filerecord);
        $draftitemid = $file->get_itemid();

        $maxbytes = $CFG->userquota;
        $maxareabytes = $CFG->userquota;
        $options = array('subdirs' => 1,
                         'maxbytes' => $maxbytes,
                         'maxfiles' => -1,
                         'areamaxbytes' => $maxareabytes);

                file_merge_files_from_draft_area_into_filearea($draftitemid, $usercontext->id, 'user', 'private', 0, $options);

        $files = $fs->get_area_files($usercontext->id, 'user', 'private', 0);
                $this->assertCount(2, $files);
        $found = false;
        foreach ($files as $file) {
            if (!$file->is_directory()) {
                $found = true;
                $this->assertEquals($filename, $file->get_filename());
                $this->assertEquals('some content here', $file->get_content());
            }
        }
        $this->assertTrue($found);

                $filerecord = array(
            'itemid'  => $draftitemid,
            'filename'  => 'second.txt',
        );
        self::create_draft_file($filerecord);
        $filerecord = array(
            'itemid'  => $draftitemid,
            'filename'  => 'third.txt',
        );
        $file = self::create_draft_file($filerecord);

        file_merge_files_from_draft_area_into_filearea($file->get_itemid(), $usercontext->id, 'user', 'private', 0, $options);

        $files = $fs->get_area_files($usercontext->id, 'user', 'private', 0);
        $this->assertCount(4, $files);

                $filerecord = array(
            'filename'  => 'second.txt',
            'content'  => 'new content',
        );
        $file = self::create_draft_file($filerecord);
        file_merge_files_from_draft_area_into_filearea($file->get_itemid(), $usercontext->id, 'user', 'private', 0, $options);

        $files = $fs->get_area_files($usercontext->id, 'user', 'private', 0);
        $this->assertCount(4, $files);
        $found = false;
        foreach ($files as $file) {
            if ($file->get_filename() == 'second.txt') {
                $found = true;
                $this->assertEquals('new content', $file->get_content());
            }
        }
        $this->assertTrue($found);

                        foreach ($files as $file) {
            if ($file->get_filename() == 'second.txt') {
                $file->set_author('Nobody');
            }
        }
        $filerecord = array(
            'filename'  => 'second.txt',
        );
        $file = self::create_draft_file($filerecord);

        file_merge_files_from_draft_area_into_filearea($file->get_itemid(), $usercontext->id, 'user', 'private', 0, $options);

        $files = $fs->get_area_files($usercontext->id, 'user', 'private', 0);
        $this->assertCount(4, $files);
        $found = false;
        foreach ($files as $file) {
            if ($file->get_filename() == 'second.txt') {
                $found = true;
                $this->assertEquals(fullname($USER), $file->get_author());
            }
        }
        $this->assertTrue($found);

    }

    
    public function test_file_merge_files_from_draft_area_into_filearea_max_area_bytes() {
        global $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $fs = get_file_storage();

        $file = self::create_draft_file();
        $options = array('subdirs' => 1,
                         'maxbytes' => 5,
                         'maxfiles' => -1,
                         'areamaxbytes' => 10);

                file_merge_files_from_draft_area_into_filearea($file->get_itemid(), $file->get_contextid(), 'user', 'private', 0, $options);
        $usercontext = context_user::instance($USER->id);
        $files = $fs->get_area_files($usercontext->id, 'user', 'private', 0);
        $this->assertCount(0, $files);
    }

    
    public function test_file_merge_files_from_draft_area_into_filearea_max_file_bytes() {
        global $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $fs = get_file_storage();

        $file = self::create_draft_file();
        $options = array('subdirs' => 1,
                         'maxbytes' => 1,
                         'maxfiles' => -1,
                         'areamaxbytes' => 100);

                file_merge_files_from_draft_area_into_filearea($file->get_itemid(), $file->get_contextid(), 'user', 'private', 0, $options);
        $usercontext = context_user::instance($USER->id);
                $files = $fs->get_area_files($usercontext->id, 'user', 'private', 0);
        $this->assertCount(1, $files);
        $file = array_shift($files);
        $this->assertTrue($file->is_directory());
    }

    
    public function test_file_merge_files_from_draft_area_into_filearea_max_files() {
        global $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $fs = get_file_storage();

        $file = self::create_draft_file();
        $options = array('subdirs' => 1,
                         'maxbytes' => 1000,
                         'maxfiles' => 0,
                         'areamaxbytes' => 1000);

                file_merge_files_from_draft_area_into_filearea($file->get_itemid(), $file->get_contextid(), 'user', 'private', 0, $options);
        $usercontext = context_user::instance($USER->id);
                $files = $fs->get_area_files($usercontext->id, 'user', 'private', 0);
        $this->assertCount(1, $files);
        $file = array_shift($files);
        $this->assertTrue($file->is_directory());
    }
}


class testable_curl extends curl {
    
    public function get_options() {
                $rp = new ReflectionProperty('curl', 'options');
        $rp->setAccessible(true);
        return $rp->getValue($this);
    }

    
    public function set_options($options) {
                $rp = new ReflectionProperty('curl', 'options');
        $rp->setAccessible(true);
        $rp->setValue($this, $options);
    }

    
    public function set_option($option, $value) {
        $options = $this->get_options();
        $options[$option] = $value;
        $this->set_options($options);
    }

    
    public function unset_option($option) {
        $options = $this->get_options();
        unset($options[$option]);
        $this->set_options($options);
    }

    
    public function call_apply_opt($options = null) {
                $rm = new ReflectionMethod('curl', 'apply_opt');
        $rm->setAccessible(true);
        $ch = curl_init();
        return $rm->invoke($this, $ch, $options);
    }
}
