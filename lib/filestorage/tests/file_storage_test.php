<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/filestorage/stored_file.php');

class core_files_file_storage_testcase extends advanced_testcase {

    
    public function test_create_file_from_string() {
        global $DB;

        $this->resetAfterTest(true);

                $installedfiles = $DB->count_records('files', array());

        $content = 'abcd';
        $syscontext = context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/images/',
            'filename'  => 'testfile.txt',
        );
        $pathhash = sha1('/'.$filerecord['contextid'].'/'.$filerecord['component'].'/'.$filerecord['filearea'].'/'.$filerecord['itemid'].$filerecord['filepath'].$filerecord['filename']);

        $fs = get_file_storage();
        $file = $fs->create_file_from_string($filerecord, $content);

        $this->assertInstanceOf('stored_file', $file);
        $this->assertSame(sha1($content), $file->get_contenthash());
        $this->assertSame($pathhash, $file->get_pathnamehash());

        $this->assertTrue($DB->record_exists('files', array('pathnamehash'=>$pathhash)));

        $location = test_stored_file_inspection::get_pretected_pathname($file);

        $this->assertFileExists($location);

                $this->assertEquals($installedfiles + 3, $DB->count_records('files', array()));
        $this->assertTrue($DB->record_exists('files', array('pathnamehash'=>sha1('/'.$filerecord['contextid'].'/'.$filerecord['component'].'/'.$filerecord['filearea'].'/'.$filerecord['itemid'].'/.'))));
        $this->assertTrue($DB->record_exists('files', array('pathnamehash'=>sha1('/'.$filerecord['contextid'].'/'.$filerecord['component'].'/'.$filerecord['filearea'].'/'.$filerecord['itemid'].$filerecord['filepath'].'.'))));

        
        unlink($location);
        $this->assertFileNotExists($location);

        $filerecord['filename'] = 'testfile2.txt';
        $file2 = $fs->create_file_from_string($filerecord, $content);
        $this->assertInstanceOf('stored_file', $file2);
        $this->assertSame($file->get_contenthash(), $file2->get_contenthash());
        $this->assertFileExists($location);

        $this->assertEquals($installedfiles + 4, $DB->count_records('files', array()));

        
        $this->assertSame(2, file_put_contents($location, 'xx'));

        $filerecord['filename'] = 'testfile3.txt';
        $file3 = $fs->create_file_from_string($filerecord, $content);
        $this->assertInstanceOf('stored_file', $file3);
        $this->assertSame($file->get_contenthash(), $file3->get_contenthash());
        $this->assertFileExists($location);

        $this->assertSame($content, file_get_contents($location));
        $this->assertDebuggingCalled();

        $this->assertEquals($installedfiles + 5, $DB->count_records('files', array()));
    }

    
    public function test_create_file_from_pathname() {
        global $CFG, $DB;

        $this->resetAfterTest(true);

                $installedfiles = $DB->count_records('files', array());

        $filepath = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';
        $syscontext = context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/images/',
            'filename'  => 'testimage.jpg',
        );
        $pathhash = sha1('/'.$filerecord['contextid'].'/'.$filerecord['component'].'/'.$filerecord['filearea'].'/'.$filerecord['itemid'].$filerecord['filepath'].$filerecord['filename']);

        $fs = get_file_storage();
        $file = $fs->create_file_from_pathname($filerecord, $filepath);

        $this->assertInstanceOf('stored_file', $file);
        $this->assertSame(sha1_file($filepath), $file->get_contenthash());

        $this->assertTrue($DB->record_exists('files', array('pathnamehash'=>$pathhash)));

        $location = test_stored_file_inspection::get_pretected_pathname($file);

        $this->assertFileExists($location);

                $this->assertEquals($installedfiles + 3, $DB->count_records('files', array()));
        $this->assertTrue($DB->record_exists('files', array('pathnamehash'=>sha1('/'.$filerecord['contextid'].'/'.$filerecord['component'].'/'.$filerecord['filearea'].'/'.$filerecord['itemid'].'/.'))));
        $this->assertTrue($DB->record_exists('files', array('pathnamehash'=>sha1('/'.$filerecord['contextid'].'/'.$filerecord['component'].'/'.$filerecord['filearea'].'/'.$filerecord['itemid'].$filerecord['filepath'].'.'))));

        
        unlink($location);
        $this->assertFileNotExists($location);

        $filerecord['filename'] = 'testfile2.jpg';
        $file2 = $fs->create_file_from_pathname($filerecord, $filepath);
        $this->assertInstanceOf('stored_file', $file2);
        $this->assertSame($file->get_contenthash(), $file2->get_contenthash());
        $this->assertFileExists($location);

        $this->assertEquals($installedfiles + 4, $DB->count_records('files', array()));

        
        $this->assertSame(2, file_put_contents($location, 'xx'));

        $filerecord['filename'] = 'testfile3.jpg';
        $file3 = $fs->create_file_from_pathname($filerecord, $filepath);
        $this->assertInstanceOf('stored_file', $file3);
        $this->assertSame($file->get_contenthash(), $file3->get_contenthash());
        $this->assertFileExists($location);

        $this->assertSame(file_get_contents($filepath), file_get_contents($location));
        $this->assertDebuggingCalled();

        $this->assertEquals($installedfiles + 5, $DB->count_records('files', array()));

        
        $filerecord['filename'] = 'testfile4.jpg';
        try {
            $fs->create_file_from_pathname($filerecord, $filepath.'nonexistent');
            $this->fail('Exception expected when trying to add non-existent stored file.');
        } catch (Exception $e) {
            $this->assertInstanceOf('file_exception', $e);
        }
    }

    
    public function test_get_file() {
        global $CFG;

        $this->resetAfterTest(false);

        $filepath = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';
        $syscontext = context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/images/',
            'filename'  => 'testimage.jpg',
        );
        $pathhash = sha1('/'.$filerecord['contextid'].'/'.$filerecord['component'].'/'.$filerecord['filearea'].'/'.$filerecord['itemid'].$filerecord['filepath'].$filerecord['filename']);

        $fs = get_file_storage();
        $file = $fs->create_file_from_pathname($filerecord, $filepath);

        $this->assertInstanceOf('stored_file', $file);
        $this->assertEquals($syscontext->id, $file->get_contextid());
        $this->assertEquals('core', $file->get_component());
        $this->assertEquals('unittest', $file->get_filearea());
        $this->assertEquals(0, $file->get_itemid());
        $this->assertEquals('/images/', $file->get_filepath());
        $this->assertEquals('testimage.jpg', $file->get_filename());
        $this->assertEquals(filesize($filepath), $file->get_filesize());
        $this->assertEquals($pathhash, $file->get_pathnamehash());

        return $file;
    }

    
    public function test_get_file_preview(stored_file $file) {
        global $CFG;

        $this->resetAfterTest();
        $fs = get_file_storage();

        $previewtinyicon = $fs->get_file_preview($file, 'tinyicon');
        $this->assertInstanceOf('stored_file', $previewtinyicon);
        $this->assertEquals('6b9864ae1536a8eeef54e097319175a8be12f07c', $previewtinyicon->get_filename());

        $previewtinyicon = $fs->get_file_preview($file, 'thumb');
        $this->assertInstanceOf('stored_file', $previewtinyicon);
        $this->assertEquals('6b9864ae1536a8eeef54e097319175a8be12f07c', $previewtinyicon->get_filename());

        $this->setExpectedException('file_exception');
        $fs->get_file_preview($file, 'amodewhichdoesntexist');
    }

    public function test_get_file_preview_nonimage() {
        $this->resetAfterTest(true);
        $syscontext = context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/textfiles/',
            'filename'  => 'testtext.txt',
        );

        $fs = get_file_storage();
        $fs->create_file_from_string($filerecord, 'text contents');
        $textfile = $fs->get_file($syscontext->id, $filerecord['component'], $filerecord['filearea'],
            $filerecord['itemid'], $filerecord['filepath'], $filerecord['filename']);

        $preview = $fs->get_file_preview($textfile, 'thumb');
        $this->assertFalse($preview);
    }

    
    public function test_file_renaming() {
        global $CFG;

        $this->resetAfterTest();
        $fs = get_file_storage();
        $syscontext = context_system::instance();
        $component = 'core';
        $filearea  = 'unittest';
        $itemid    = 0;
        $filepath  = '/';
        $filename  = 'test.txt';

        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => $component,
            'filearea'  => $filearea,
            'itemid'    => $itemid,
            'filepath'  => $filepath,
            'filename'  => $filename,
        );

        $originalfile = $fs->create_file_from_string($filerecord, 'Test content');
        $this->assertInstanceOf('stored_file', $originalfile);
        $contenthash = $originalfile->get_contenthash();
        $newpath = '/test/';
        $newname = 'newtest.txt';

                $originalfile->rename($newpath, $newname);
        $file = $fs->get_file($syscontext->id, $component, $filearea, $itemid, $newpath, $newname);
        $this->assertInstanceOf('stored_file', $file);
        $this->assertEquals($contenthash, $file->get_contenthash());

                $this->setExpectedException('file_exception',
                'Can not create file "1/core/unittest/0/test/newtest.txt" (file exists, cannot rename)');
                $originalfile->rename($newpath, $newname);
    }

    
    public function test_create_file_from_reference() {
        global $CFG, $DB;

        $this->resetAfterTest();
                $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);
        $usercontext = context_user::instance($user->id);
        $syscontext = context_system::instance();

        $fs = get_file_storage();

        $repositorypluginname = 'user';
                $capability = 'repository/' . $repositorypluginname . ':view';
        $guestroleid = $DB->get_field('role', 'id', array('shortname' => 'guest'));
        assign_capability($capability, CAP_ALLOW, $guestroleid, $syscontext->id, true);

        $args = array();
        $args['type'] = $repositorypluginname;
        $repos = repository::get_instances($args);
        $userrepository = reset($repos);
        $this->assertInstanceOf('repository', $userrepository);

        $component = 'user';
        $filearea  = 'private';
        $itemid    = 0;
        $filepath  = '/';
        $filename  = 'userfile.txt';

        $filerecord = array(
            'contextid' => $usercontext->id,
            'component' => $component,
            'filearea'  => $filearea,
            'itemid'    => $itemid,
            'filepath'  => $filepath,
            'filename'  => $filename,
        );

        $content = 'Test content';
        $originalfile = $fs->create_file_from_string($filerecord, $content);
        $this->assertInstanceOf('stored_file', $originalfile);

        $newfilerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'phpunit',
            'itemid'    => 0,
            'filepath'  => $filepath,
            'filename'  => $filename,
        );
        $ref = $fs->pack_reference($filerecord);
        $newstoredfile = $fs->create_file_from_reference($newfilerecord, $userrepository->id, $ref);
        $this->assertInstanceOf('stored_file', $newstoredfile);
        $this->assertEquals($userrepository->id, $newstoredfile->get_repository_id());
        $this->assertEquals($originalfile->get_contenthash(), $newstoredfile->get_contenthash());
        $this->assertEquals($originalfile->get_filesize(), $newstoredfile->get_filesize());
        $this->assertRegExp('#' . $filename. '$#', $newstoredfile->get_reference_details());

                $count = $fs->get_references_count_by_storedfile($originalfile);
        $this->assertEquals(1, $count);
        $files = $fs->get_references_by_storedfile($originalfile);
        $file = reset($files);
        $this->assertEquals($file, $newstoredfile);

                $files = $fs->get_external_files($userrepository->id);
        $file = reset($files);
        $this->assertEquals($file, $newstoredfile);

                $importedfile = $fs->import_external_file($newstoredfile);
        $this->assertFalse($importedfile->is_external_file());
        $this->assertInstanceOf('stored_file', $importedfile);
                $this->assertEquals($content, $importedfile->get_content());
    }

    private function setup_three_private_files() {

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user->id);
        $usercontext = context_user::instance($user->id);
                $file1 = new stdClass;
        $file1->contextid = $usercontext->id;
        $file1->component = 'user';
        $file1->filearea  = 'private';
        $file1->itemid    = 0;
        $file1->filepath  = '/';
        $file1->filename  = '1.txt';
        $file1->source    = 'test';

        $fs = get_file_storage();
        $userfile1 = $fs->create_file_from_string($file1, 'file1 content');
        $this->assertInstanceOf('stored_file', $userfile1);

        $file2 = clone($file1);
        $file2->filename = '2.txt';
        $userfile2 = $fs->create_file_from_string($file2, 'file2 content longer');
        $this->assertInstanceOf('stored_file', $userfile2);

        $file3 = clone($file1);
        $file3->filename = '3.txt';
        $userfile3 = $fs->create_file_from_storedfile($file3, $userfile2);
        $this->assertInstanceOf('stored_file', $userfile3);

        $user->ctxid = $usercontext->id;

        return $user;
    }

    public function test_get_area_files() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

                $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');

                $this->assertEquals(4, count($areafiles));

                foreach ($areafiles as $key => $file) {
            $this->assertInstanceOf('stored_file', $file);
            $this->assertEquals($key, $file->get_pathnamehash());
        }

                $folderlessfiles = $fs->get_area_files($user->ctxid, 'user', 'private', false, 'sortorder', false);
                $this->assertEquals(3, count($folderlessfiles));

                foreach ($folderlessfiles as $key => $file) {
            $this->assertInstanceOf('stored_file', $file);
            $this->assertEquals($key, $file->get_pathnamehash());
        }

                $filesbyid  = $fs->get_area_files($user->ctxid, 'user', 'private', false, 'id', false);
                $this->assertEquals(3, count($filesbyid));

                foreach ($filesbyid as $key => $file) {
            $this->assertInstanceOf('stored_file', $file);
            $this->assertEquals($key, $file->get_pathnamehash());
        }

                $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private', 666, 'sortorder', false);
                $this->assertEmpty($areafiles);
    }

    public function test_get_area_tree() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

                $areatree = $fs->get_area_tree($user->ctxid, 'user', 'private', 0);
        $this->assertEmpty($areatree['subdirs']);
        $this->assertNotEmpty($areatree['files']);
        $this->assertCount(3, $areatree['files']);

                $emptytree = $fs->get_area_tree($user->ctxid, 'user', 'private', 666);
        $this->assertEmpty($emptytree['subdirs']);
        $this->assertEmpty($emptytree['files']);

                $dir = $fs->create_directory($user->ctxid, 'user', 'private', 0, '/testsubdir/');
        $this->assertInstanceOf('stored_file', $dir);

                $filerecord = array(
            'contextid' => $user->ctxid,
            'component' => 'user',
            'filearea'  => 'private',
            'itemid'    => 0,
            'filepath'  => '/testsubdir/',
            'filename'  => 'test-get-area-tree.txt',
        );

        $directoryfile = $fs->create_file_from_string($filerecord, 'Test content');
        $this->assertInstanceOf('stored_file', $directoryfile);

        $areatree = $fs->get_area_tree($user->ctxid, 'user', 'private', 0);

                $this->assertCount(3, $areatree['files']);

                $this->assertCount(1, $areatree['subdirs']);

                $subdir = $areatree['subdirs']['testsubdir'];
        $this->assertNotEmpty($subdir);
                $this->assertCount(1, $subdir['files']);
                $this->assertCount(0, $subdir['subdirs']);

                $subdirfile = reset($subdir['files']);
        $this->assertInstanceOf('stored_file', $subdirfile);
        $this->assertEquals($filerecord['filename'], $subdirfile->get_filename());
    }

    public function test_get_file_by_id() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

        $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');

                $filebyid = reset($areafiles);
        $shouldbesame = $fs->get_file_by_id($filebyid->get_id());
        $this->assertEquals($filebyid->get_contenthash(), $shouldbesame->get_contenthash());

                $doesntexist = $fs->get_file_by_id(99999);
        $this->assertFalse($doesntexist);
    }

    public function test_get_file_by_hash() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

        $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
                $filebyhash = reset($areafiles);
        $shouldbesame = $fs->get_file_by_hash($filebyhash->get_pathnamehash());
        $this->assertEquals($filebyhash->get_id(), $shouldbesame->get_id());

                $doesntexist = $fs->get_file_by_hash('DOESNTEXIST');
        $this->assertFalse($doesntexist);
    }

    public function test_get_external_files() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

        $repos = repository::get_instances(array('type'=>'user'));
        $userrepository = reset($repos);
        $this->assertInstanceOf('repository', $userrepository);

                $exfiles = $fs->get_external_files($userrepository->id, 'id');
        $this->assertEquals(array(), $exfiles);

                        $originalfile = null;
        foreach ($fs->get_area_files($user->ctxid, 'user', 'private') as $areafile) {
            if (!$areafile->is_directory()) {
                $originalfile = $areafile;
                break;
            }
        }
        $this->assertInstanceOf('stored_file', $originalfile);
        $originalrecord = array(
            'contextid' => $originalfile->get_contextid(),
            'component' => $originalfile->get_component(),
            'filearea'  => $originalfile->get_filearea(),
            'itemid'    => $originalfile->get_itemid(),
            'filepath'  => $originalfile->get_filepath(),
            'filename'  => $originalfile->get_filename(),
        );

        $aliasrecord = $this->generate_file_record();
        $aliasrecord->filepath = '/foo/';
        $aliasrecord->filename = 'one.txt';

        $ref = $fs->pack_reference($originalrecord);
        $aliasfile1 = $fs->create_file_from_reference($aliasrecord, $userrepository->id, $ref);

        $aliasrecord->filepath = '/bar/';
        $aliasrecord->filename = 'uno.txt';
                ksort($originalrecord);
        $ref = $fs->pack_reference($originalrecord);
        $aliasfile2 = $fs->create_file_from_reference($aliasrecord, $userrepository->id, $ref);

        $aliasrecord->filepath = '/bar/';
        $aliasrecord->filename = 'jedna.txt';
        $aliasfile3 = $fs->create_file_from_storedfile($aliasrecord, $aliasfile2);

                $exfiles = $fs->get_external_files($userrepository->id, 'id');
        $this->assertEquals(3, count($exfiles));
        foreach ($exfiles as $exfile) {
            $this->assertTrue($exfile->is_external_file());
        }
                        $this->assertEquals($aliasfile1->get_referencefileid(), $aliasfile2->get_referencefileid());
        $this->assertEquals($aliasfile3->get_referencefileid(), $aliasfile2->get_referencefileid());
    }

    public function test_create_directory_contextid_negative() {
        $fs = get_file_storage();

        $this->setExpectedException('file_exception');
        $fs->create_directory(-1, 'core', 'unittest', 0, '/');
    }

    public function test_create_directory_contextid_invalid() {
        $fs = get_file_storage();

        $this->setExpectedException('file_exception');
        $fs->create_directory('not an int', 'core', 'unittest', 0, '/');
    }

    public function test_create_directory_component_invalid() {
        $fs = get_file_storage();
        $syscontext = context_system::instance();

        $this->setExpectedException('file_exception');
        $fs->create_directory($syscontext->id, 'bad/component', 'unittest', 0, '/');
    }

    public function test_create_directory_filearea_invalid() {
        $fs = get_file_storage();
        $syscontext = context_system::instance();

        $this->setExpectedException('file_exception');
        $fs->create_directory($syscontext->id, 'core', 'bad-filearea', 0, '/');
    }

    public function test_create_directory_itemid_negative() {
        $fs = get_file_storage();
        $syscontext = context_system::instance();

        $this->setExpectedException('file_exception');
        $fs->create_directory($syscontext->id, 'core', 'unittest', -1, '/');
    }

    public function test_create_directory_itemid_invalid() {
        $fs = get_file_storage();
        $syscontext = context_system::instance();

        $this->setExpectedException('file_exception');
        $fs->create_directory($syscontext->id, 'core', 'unittest', 'notanint', '/');
    }

    public function test_create_directory_filepath_invalid() {
        $fs = get_file_storage();
        $syscontext = context_system::instance();

        $this->setExpectedException('file_exception');
        $fs->create_directory($syscontext->id, 'core', 'unittest', 0, '/not-with-trailing/or-leading-slash');
    }

    public function test_get_directory_files() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

        $dir = $fs->create_directory($user->ctxid, 'user', 'private', 0, '/testsubdir/');
        $this->assertInstanceOf('stored_file', $dir);

                $filerecord = array(
            'contextid' => $user->ctxid,
            'component' => 'user',
            'filearea'  => 'private',
            'itemid'    => 0,
            'filepath'  => '/testsubdir/',
            'filename'  => 'test-get-area-tree.txt',
        );

        $directoryfile = $fs->create_file_from_string($filerecord, 'Test content');
        $this->assertInstanceOf('stored_file', $directoryfile);

                $files = $fs->get_directory_files($user->ctxid, 'user', 'private', 0, '/', false, false, 'id');
                $this->assertCount(3, $files);
        foreach ($files as $key => $file) {
            $this->assertInstanceOf('stored_file', $file);
            $this->assertEquals($key, $file->get_pathnamehash());
        }

                $files = $fs->get_directory_files($user->ctxid, 'user', 'private', 0, '/', false, true, 'id');
                $this->assertCount(4, $files);
        foreach ($files as $key => $file) {
            $this->assertInstanceOf('stored_file', $file);
            $this->assertEquals($key, $file->get_pathnamehash());
        }

                $files = $fs->get_directory_files($user->ctxid, 'user', 'private', 0, '/', true, true, 'id');
                $this->assertCount(5, $files);
        foreach ($files as $key => $file) {
            $this->assertInstanceOf('stored_file', $file);
            $this->assertEquals($key, $file->get_pathnamehash());
        }

                $files = $fs->get_directory_files($user->ctxid, 'user', 'private', 0, '/', true, false, 'id');
                $this->assertCount(4, $files);
        foreach ($files as $key => $file) {
            $this->assertInstanceOf('stored_file', $file);
            $this->assertEquals($key, $file->get_pathnamehash());
        }
    }

    public function test_search_references() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();
        $repos = repository::get_instances(array('type'=>'user'));
        $repo = reset($repos);

        $alias1 = array(
            'contextid' => $user->ctxid,
            'component' => 'user',
            'filearea'  => 'private',
            'itemid'    => 0,
            'filepath'  => '/aliases/',
            'filename'  => 'alias-to-1.txt'
        );

        $alias2 = array(
            'contextid' => $user->ctxid,
            'component' => 'user',
            'filearea'  => 'private',
            'itemid'    => 0,
            'filepath'  => '/aliases/',
            'filename'  => 'another-alias-to-1.txt'
        );

        $reference = file_storage::pack_reference(array(
            'contextid' => $user->ctxid,
            'component' => 'user',
            'filearea'  => 'private',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => '1.txt'
        ));

                $result = $fs->search_references($reference);
        $this->assertEquals(array(), $result);

        $result = $fs->search_references_count($reference);
        $this->assertSame($result, 0);

                $fs->create_file_from_reference($alias1, $repo->id, $reference);
        $fs->create_file_from_reference($alias2, $repo->id, $reference);

        $result = $fs->search_references($reference);
        $this->assertTrue(is_array($result));
        $this->assertEquals(count($result), 2);
        foreach ($result as $alias) {
            $this->assertTrue($alias instanceof stored_file);
        }

        $result = $fs->search_references_count($reference);
        $this->assertSame($result, 2);

                $exceptionthrown = false;
        try {
            $fs->search_references('http://dl.dropbox.com/download/1234567/naked-dougiamas.jpg');
        } catch (file_reference_exception $e) {
            $exceptionthrown = true;
        }
        $this->assertTrue($exceptionthrown);

        $exceptionthrown = false;
        try {
            $fs->search_references_count('http://dl.dropbox.com/download/1234567/naked-dougiamas.jpg');
        } catch (file_reference_exception $e) {
            $exceptionthrown = true;
        }
        $this->assertTrue($exceptionthrown);
    }

    public function test_delete_area_files() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

                $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
                $this->assertEquals(4, count($areafiles));
        $fs->delete_area_files($user->ctxid, 'user', 'private');

        $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
                $this->assertEquals(0, count($areafiles));
    }

    public function test_delete_area_files_itemid() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

                $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
                $this->assertEquals(4, count($areafiles));
        $fs->delete_area_files($user->ctxid, 'user', 'private', 9999);

        $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
        $this->assertEquals(4, count($areafiles));
    }

    public function test_delete_area_files_select() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

                $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
                $this->assertEquals(4, count($areafiles));
        $fs->delete_area_files_select($user->ctxid, 'user', 'private', '!= :notitemid', array('notitemid'=>9999));

        $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
                $this->assertEquals(0, count($areafiles));
    }

    public function test_delete_component_files() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

        $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
        $this->assertEquals(4, count($areafiles));
        $fs->delete_component_files('user');
        $areafiles = $fs->get_area_files($user->ctxid, 'user', 'private');
        $this->assertEquals(0, count($areafiles));
    }

    public function test_create_file_from_url() {
        $this->resetAfterTest(true);

        $syscontext = context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/downloadtest/',
        );
        $url = $this->getExternalTestFileUrl('/test.html');

        $fs = get_file_storage();

                $file1 = $fs->create_file_from_url($filerecord, $url);
        $this->assertInstanceOf('stored_file', $file1);

                $filerecord['filename'] = 'unit-test-filename.html';
        $file2 = $fs->create_file_from_url($filerecord, $url);
        $this->assertInstanceOf('stored_file', $file2);

                $filerecord['filename'] = 'unit-test-with-temp-file.html';
        $file3 = $fs->create_file_from_url($filerecord, $url, null, true);
        $file3 = $this->assertInstanceOf('stored_file', $file3);
    }

    public function test_cron() {
        $this->resetAfterTest(true);

                        $fs = get_file_storage();

        $this->expectOutputRegex('/Cleaning up/');
        $fs->cron();
    }

    public function test_is_area_empty() {
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();

        $this->assertFalse($fs->is_area_empty($user->ctxid, 'user', 'private'));

                $this->assertTrue($fs->is_area_empty($user->ctxid, 'user', 'private', 9999));
                $this->assertTrue($fs->is_area_empty($user->ctxid, 'user', 'private', 9999, false));
    }

    public function test_move_area_files_to_new_context() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();
        $page1 = $this->getDataGenerator()->create_module('page', array('course'=>$course->id));
        $page1context = context_module::instance($page1->cmid);

                $fs = get_file_storage();
        $filerecord = array(
            'contextid' => $page1context->id,
            'component' => 'mod_page',
            'filearea'  => 'content',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => 'unit-test-file.txt',
        );

        $originalfile = $fs->create_file_from_string($filerecord, 'Test content');
        $this->assertInstanceOf('stored_file', $originalfile);

        $pagefiles = $fs->get_area_files($page1context->id, 'mod_page', 'content', 0, 'sortorder', false);
                $this->assertFalse($fs->is_area_empty($page1context->id, 'mod_page', 'content'));

                $page2 = $this->getDataGenerator()->create_module('page', array('course'=>$course->id));
        $page2context = context_module::instance($page2->cmid);

                $this->assertTrue($fs->is_area_empty($page2context->id, 'mod_page', 'content'));

                $fs->move_area_files_to_new_context($page1context->id, $page2context->id, 'mod_page', 'content');

                $this->assertFalse($fs->is_area_empty($page2context->id, 'mod_page', 'content'));

                $this->assertTrue($fs->is_area_empty($page1context->id, 'mod_page', 'content'));

        $page2files = $fs->get_area_files($page2context->id, 'mod_page', 'content', 0, 'sortorder', false);
        $movedfile = reset($page2files);

                $this->assertEquals($movedfile->get_contenthash(), $originalfile->get_contenthash());
    }

    public function test_convert_image() {
        global $CFG;

        $this->resetAfterTest(false);

        $filepath = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';
        $syscontext = context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/images/',
            'filename'  => 'testimage.jpg',
        );

        $fs = get_file_storage();
        $original = $fs->create_file_from_pathname($filerecord, $filepath);

        $filerecord['filename'] = 'testimage-converted-10x10.jpg';
        $converted = $fs->convert_image($filerecord, $original, 10, 10, true, 100);
        $this->assertInstanceOf('stored_file', $converted);

        $filerecord['filename'] = 'testimage-convereted-nosize.jpg';
        $converted = $fs->convert_image($filerecord, $original);
        $this->assertInstanceOf('stored_file', $converted);
    }

    public function test_convert_image_png() {
        global $CFG;

        $this->resetAfterTest(false);

        $filepath = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.png';
        $syscontext = context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'core',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/images/',
            'filename'  => 'testimage.png',
        );

        $fs = get_file_storage();
        $original = $fs->create_file_from_pathname($filerecord, $filepath);

                $filerecord['filename'] = 'testimage-converted-nosize.png';
        $vanilla = $fs->convert_image($filerecord, $original);
        $this->assertInstanceOf('stored_file', $vanilla);
                $this->assertTrue(ord(substr($vanilla->get_content(), 25, 1)) == 6);

                        $filerecord['filename'] = 'testimage-converted-10x10.png';
        $converted = $fs->convert_image($filerecord, $original, 10, 10, true, 100);
        $this->assertInstanceOf('stored_file', $converted);
                $this->assertTrue(ord(substr($converted->get_content(), 25, 1)) == 6);

                $filerecord['filename'] = 'testimage-converted-102x31.png';
        $converted = $fs->convert_image($filerecord, $original, 102, 31, true, 9);
        $this->assertInstanceOf('stored_file', $converted);
                $this->assertTrue(ord(substr($converted->get_content(), 25, 1)) == 6);

        $originalfile = imagecreatefromstring($original->get_content());
        $convertedfile = imagecreatefromstring($converted->get_content());
        $vanillafile = imagecreatefromstring($vanilla->get_content());

        $originalcolors = imagecolorsforindex($originalfile, imagecolorat($originalfile, 0, 0));
        $convertedcolors = imagecolorsforindex($convertedfile, imagecolorat($convertedfile, 0, 0));
        $vanillacolors = imagecolorsforindex($vanillafile, imagecolorat($vanillafile, 0, 0));
        $this->assertEquals(count($originalcolors), 4);
        $this->assertEquals(count($convertedcolors), 4);
        $this->assertEquals(count($vanillacolors), 4);
        $this->assertEquals($originalcolors['red'], $convertedcolors['red']);
        $this->assertEquals($originalcolors['green'], $convertedcolors['green']);
        $this->assertEquals($originalcolors['blue'], $convertedcolors['blue']);
        $this->assertEquals($originalcolors['alpha'], $convertedcolors['alpha']);
        $this->assertEquals($originalcolors['red'], $vanillacolors['red']);
        $this->assertEquals($originalcolors['green'], $vanillacolors['green']);
        $this->assertEquals($originalcolors['blue'], $vanillacolors['blue']);
        $this->assertEquals($originalcolors['alpha'], $vanillacolors['alpha']);
        $this->assertEquals($originalcolors['alpha'], 127);

    }

    private function generate_file_record() {
        $syscontext = context_system::instance();
        $filerecord = new stdClass();
        $filerecord->contextid = $syscontext->id;
        $filerecord->component = 'core';
        $filerecord->filearea = 'phpunit';
        $filerecord->filepath = '/';
        $filerecord->filename = 'testfile.txt';
        $filerecord->itemid = 0;

        return $filerecord;
    }

    public function test_create_file_from_storedfile_file_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $this->setExpectedException('file_exception');
                $fs->create_file_from_storedfile($filerecord,  9999);
    }

    public function test_create_file_from_storedfile_contextid_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'invalid.txt';
        $filerecord->contextid = 'invalid';

        $this->setExpectedException('file_exception', 'Invalid contextid');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_component_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'invalid.txt';
        $filerecord->component = 'bad/component';

        $this->setExpectedException('file_exception', 'Invalid component');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_filearea_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'invalid.txt';
        $filerecord->filearea = 'bad-filearea';

        $this->setExpectedException('file_exception', 'Invalid filearea');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_itemid_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'invalid.txt';
        $filerecord->itemid = 'bad-itemid';

        $this->setExpectedException('file_exception', 'Invalid itemid');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_filepath_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'invalid.txt';
        $filerecord->filepath = 'a-/bad/-filepath';

        $this->setExpectedException('file_exception', 'Invalid file path');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_filename_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = '';

        $this->setExpectedException('file_exception', 'Invalid file name');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_timecreated_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'invalid.txt';
        $filerecord->timecreated = 'today';

        $this->setExpectedException('file_exception', 'Invalid file timecreated');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_timemodified_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'invalid.txt';
        $filerecord->timemodified  = 'today';

        $this->setExpectedException('file_exception', 'Invalid file timemodified');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile_duplicate() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();

        $fs = get_file_storage();
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

                $this->setExpectedException('stored_file_creation_exception', 'Can not create file "1/core/phpunit/0/testfile.txt"');
        $fs->create_file_from_storedfile($filerecord, $file1->get_id());
    }

    public function test_create_file_from_storedfile() {
        $this->resetAfterTest(true);

        $syscontext = context_system::instance();

        $filerecord = new stdClass();
        $filerecord->contextid = $syscontext->id;
        $filerecord->component = 'core';
        $filerecord->filearea = 'phpunit';
        $filerecord->filepath = '/';
        $filerecord->filename = 'testfile.txt';
        $filerecord->itemid = 0;

        $fs = get_file_storage();

        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
        $this->assertInstanceOf('stored_file', $file1);

        $filerecord->filename = 'test-create-file-from-storedfile.txt';
        $file2 = $fs->create_file_from_storedfile($filerecord, $file1->get_id());
        $this->assertInstanceOf('stored_file', $file2);

                $filerecord->timecreated = -100;
        $filerecord->timemodified= -100;
        $filerecord->filename = 'test-create-file-from-storedfile-bad-dates.txt';

        $file3 = $fs->create_file_from_storedfile($filerecord, $file1->get_id());
        $this->assertInstanceOf('stored_file', $file3);

        $this->assertNotEquals($file3->get_timemodified(), $filerecord->timemodified);
        $this->assertNotEquals($file3->get_timecreated(), $filerecord->timecreated);
    }

    public function test_create_file_from_string_contextid_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->contextid = 'invalid';

        $this->setExpectedException('file_exception', 'Invalid contextid');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_component_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->component = 'bad/component';

        $this->setExpectedException('file_exception', 'Invalid component');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_filearea_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->filearea = 'bad-filearea';

        $this->setExpectedException('file_exception', 'Invalid filearea');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_itemid_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->itemid = 'bad-itemid';

        $this->setExpectedException('file_exception', 'Invalid itemid');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_filepath_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->filepath = 'a-/bad/-filepath';

        $this->setExpectedException('file_exception', 'Invalid file path');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_filename_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->filename = '';

        $this->setExpectedException('file_exception', 'Invalid file name');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_timecreated_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->timecreated = 'today';

        $this->setExpectedException('file_exception', 'Invalid file timecreated');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_timemodified_invalid() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->timemodified  = 'today';

        $this->setExpectedException('file_exception', 'Invalid file timemodified');
        $file1 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_string_duplicate() {
        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $file1 = $fs->create_file_from_string($filerecord, 'text contents');

                $this->setExpectedException('stored_file_creation_exception');
        $file2 = $fs->create_file_from_string($filerecord, 'text contents');
    }

    public function test_create_file_from_pathname_contextid_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->contextid = 'invalid';

        $this->setExpectedException('file_exception', 'Invalid contextid');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_component_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->component = 'bad/component';

        $this->setExpectedException('file_exception', 'Invalid component');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_filearea_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->filearea = 'bad-filearea';

        $this->setExpectedException('file_exception', 'Invalid filearea');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_itemid_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->itemid = 'bad-itemid';

        $this->setExpectedException('file_exception', 'Invalid itemid');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_filepath_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->filepath = 'a-/bad/-filepath';

        $this->setExpectedException('file_exception', 'Invalid file path');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_filename_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->filename = '';

        $this->setExpectedException('file_exception', 'Invalid file name');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_timecreated_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->timecreated = 'today';

        $this->setExpectedException('file_exception', 'Invalid file timecreated');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_timemodified_invalid() {
        global $CFG;
        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $this->resetAfterTest(true);

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $filerecord->timemodified  = 'today';

        $this->setExpectedException('file_exception', 'Invalid file timemodified');
        $file1 = $fs->create_file_from_pathname($filerecord, $path);
    }

    public function test_create_file_from_pathname_duplicate_file() {
        global $CFG;
        $this->resetAfterTest(true);

        $path = $CFG->dirroot.'/lib/filestorage/tests/fixtures/testimage.jpg';

        $filerecord = $this->generate_file_record();
        $fs = get_file_storage();

        $file1 = $fs->create_file_from_pathname($filerecord, $path);
        $this->assertInstanceOf('stored_file', $file1);

                $this->setExpectedException('stored_file_creation_exception', 'Can not create file "1/core/phpunit/0/testfile.txt"');
        $file2 = $fs->create_file_from_pathname($filerecord, $path);
    }

    
    public function test_delete_reference_on_nonreference() {

        $this->resetAfterTest(true);
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();
        $repos = repository::get_instances(array('type'=>'user'));
        $repo = reset($repos);

        $file = null;
        foreach ($fs->get_area_files($user->ctxid, 'user', 'private') as $areafile) {
            if (!$areafile->is_directory()) {
                $file = $areafile;
                break;
            }
        }
        $this->assertInstanceOf('stored_file', $file);
        $this->assertFalse($file->is_external_file());

        $this->setExpectedException('coding_exception');
        $file->delete_reference();
    }

    
    public function test_delete_reference_one_symlink_does_not_rule_them_all() {

        $this->resetAfterTest(true);
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();
        $repos = repository::get_instances(array('type'=>'user'));
        $repo = reset($repos);

        
        $originalfile = null;
        foreach ($fs->get_area_files($user->ctxid, 'user', 'private') as $areafile) {
            if (!$areafile->is_directory()) {
                $originalfile = $areafile;
                break;
            }
        }
        $this->assertInstanceOf('stored_file', $originalfile);

        
        $originalrecord = array(
            'contextid' => $originalfile->get_contextid(),
            'component' => $originalfile->get_component(),
            'filearea'  => $originalfile->get_filearea(),
            'itemid'    => $originalfile->get_itemid(),
            'filepath'  => $originalfile->get_filepath(),
            'filename'  => $originalfile->get_filename(),
        );

        $aliasrecord = $this->generate_file_record();
        $aliasrecord->filepath = '/A/';
        $aliasrecord->filename = 'symlink.txt';

        $ref = $fs->pack_reference($originalrecord);
        $aliasfile1 = $fs->create_file_from_reference($aliasrecord, $repo->id, $ref);

        $aliasrecord->filepath = '/B/';
        $aliasrecord->filename = 'symlink.txt';
        $ref = $fs->pack_reference($originalrecord);
        $aliasfile2 = $fs->create_file_from_reference($aliasrecord, $repo->id, $ref);

                $symlink1 = $fs->get_file($aliasrecord->contextid, $aliasrecord->component,
            $aliasrecord->filearea, $aliasrecord->itemid, '/A/', 'symlink.txt');
        $this->assertTrue($symlink1->is_external_file());

                $symlink1->delete_reference();
        $this->assertFalse($symlink1->is_external_file());

                $symlink2 = $fs->get_file($aliasrecord->contextid, $aliasrecord->component,
            $aliasrecord->filearea, $aliasrecord->itemid, '/B/', 'symlink.txt');
        $this->assertTrue($symlink2->is_external_file());
    }

    
    public function test_update_reference_internal() {
        purge_all_caches();
        $this->resetAfterTest(true);
        $user = $this->setup_three_private_files();
        $fs = get_file_storage();
        $repos = repository::get_instances(array('type' => 'user'));
        $repo = reset($repos);

        
        $areafiles = array_values($fs->get_area_files($user->ctxid, 'user', 'private', false, 'filename', false));

        $originalfile = $areafiles[0];
        $this->assertInstanceOf('stored_file', $originalfile);
        $contenthash = $originalfile->get_contenthash();
        $filesize = $originalfile->get_filesize();

        $substitutefile = $areafiles[1];
        $this->assertInstanceOf('stored_file', $substitutefile);
        $newcontenthash = $substitutefile->get_contenthash();
        $newfilesize = $substitutefile->get_filesize();

        $originalrecord = array(
            'contextid' => $originalfile->get_contextid(),
            'component' => $originalfile->get_component(),
            'filearea'  => $originalfile->get_filearea(),
            'itemid'    => $originalfile->get_itemid(),
            'filepath'  => $originalfile->get_filepath(),
            'filename'  => $originalfile->get_filename(),
        );

        $aliasrecord = $this->generate_file_record();
        $aliasrecord->filepath = '/A/';
        $aliasrecord->filename = 'symlink.txt';

        $ref = $fs->pack_reference($originalrecord);
        $symlink1 = $fs->create_file_from_reference($aliasrecord, $repo->id, $ref);
                $this->assertEquals($contenthash, $symlink1->get_contenthash());
        $this->assertEquals($filesize, $symlink1->get_filesize());
        $this->assertEquals($repo->id, $symlink1->get_repository_id());
        $this->assertNotEmpty($symlink1->get_referencefileid());
        $referenceid = $symlink1->get_referencefileid();

        $aliasrecord->filepath = '/B/';
        $aliasrecord->filename = 'symlink.txt';
        $ref = $fs->pack_reference($originalrecord);
        $symlink2 = $fs->create_file_from_reference($aliasrecord, $repo->id, $ref);
                $this->assertEquals($contenthash, $symlink2->get_contenthash());
        $this->assertEquals($filesize, $symlink2->get_filesize());
        $this->assertEquals($repo->id, $symlink2->get_repository_id());
                $this->assertEquals($referenceid, $symlink2->get_referencefileid());

                $originalfile->replace_file_with($substitutefile);
        $this->assertEquals($newcontenthash, $originalfile->get_contenthash());
        $this->assertEquals($newfilesize, $originalfile->get_filesize());

                        $symlink1 = $fs->get_file($aliasrecord->contextid, $aliasrecord->component,
            $aliasrecord->filearea, $aliasrecord->itemid, '/A/', 'symlink.txt');
        $this->assertTrue($symlink1->is_external_file());
        $this->assertEquals($newcontenthash, $symlink1->get_contenthash());
        $this->assertEquals($newfilesize, $symlink1->get_filesize());
        $this->assertEquals($repo->id, $symlink1->get_repository_id());
        $this->assertEquals($referenceid, $symlink1->get_referencefileid());

                $symlink2 = $fs->get_file($aliasrecord->contextid, $aliasrecord->component,
            $aliasrecord->filearea, $aliasrecord->itemid, '/B/', 'symlink.txt');
        $this->assertTrue($symlink2->is_external_file());
        $this->assertEquals($newcontenthash, $symlink2->get_contenthash());
        $this->assertEquals($newfilesize, $symlink2->get_filesize());
        $this->assertEquals($repo->id, $symlink2->get_repository_id());
        $this->assertEquals($referenceid, $symlink2->get_referencefileid());

                $originalfile->delete();

                        $symlink1 = $fs->get_file($aliasrecord->contextid, $aliasrecord->component,
            $aliasrecord->filearea, $aliasrecord->itemid, '/A/', 'symlink.txt');
        $this->assertFalse($symlink1->is_external_file());
        $this->assertEquals($newcontenthash, $symlink1->get_contenthash());
        $this->assertEquals($newfilesize, $symlink1->get_filesize());
        $this->assertNull($symlink1->get_repository_id());
        $this->assertNull($symlink1->get_referencefileid());

                $symlink2 = $fs->get_file($aliasrecord->contextid, $aliasrecord->component,
            $aliasrecord->filearea, $aliasrecord->itemid, '/B/', 'symlink.txt');
        $this->assertFalse($symlink2->is_external_file());
        $this->assertEquals($newcontenthash, $symlink2->get_contenthash());
        $this->assertEquals($newfilesize, $symlink2->get_filesize());
        $this->assertNull($symlink2->get_repository_id());
        $this->assertNull($symlink2->get_referencefileid());
    }

    public function test_get_unused_filename() {
        global $USER;
        $this->resetAfterTest(true);

        $fs = get_file_storage();
        $this->setAdminUser();
        $contextid = context_user::instance($USER->id)->id;
        $component = 'user';
        $filearea = 'private';
        $itemid = 0;
        $filepath = '/';

                $file = new stdClass;
        $file->contextid = $contextid;
        $file->component = 'user';
        $file->filearea  = 'private';
        $file->itemid    = 0;
        $file->filepath  = '/';
        $file->source    = 'test';
        $filenames = array('foo.txt', 'foo (1).txt', 'foo (20).txt', 'foo (999)', 'bar.jpg', 'What (a cool file).jpg',
                'Hurray! (1).php', 'Hurray! (2).php', 'Hurray! (9a).php', 'Hurray! (abc).php');
        foreach ($filenames as $key => $filename) {
            $file->filename = $filename;
            $userfile = $fs->create_file_from_string($file, "file $key $filename content");
            $this->assertInstanceOf('stored_file', $userfile);
        }

                $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'unused.txt');
        $this->assertEquals('unused.txt', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'foo.txt');
        $this->assertEquals('foo (21).txt', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'foo (1).txt');
        $this->assertEquals('foo (21).txt', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'foo (2).txt');
        $this->assertEquals('foo (2).txt', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'foo (20).txt');
        $this->assertEquals('foo (21).txt', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'foo');
        $this->assertEquals('foo', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'foo (123)');
        $this->assertEquals('foo (123)', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'foo (999)');
        $this->assertEquals('foo (1000)', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'bar.png');
        $this->assertEquals('bar.png', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'bar (12).png');
        $this->assertEquals('bar (12).png', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'bar.jpg');
        $this->assertEquals('bar (1).jpg', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'bar (1).jpg');
        $this->assertEquals('bar (1).jpg', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'What (a cool file).jpg');
        $this->assertEquals('What (a cool file) (1).jpg', $newfilename);
        $newfilename = $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, 'Hurray! (1).php');
        $this->assertEquals('Hurray! (3).php', $newfilename);

        $this->setExpectedException('coding_exception');
        $fs->get_unused_filename($contextid, $component, $filearea, $itemid, $filepath, '');
    }
}

class test_stored_file_inspection extends stored_file {
    public static function get_pretected_pathname(stored_file $file) {
        return $file->get_pathname_by_contenthash();
    }
}
