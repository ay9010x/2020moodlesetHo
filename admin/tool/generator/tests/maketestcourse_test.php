<?php

defined('MOODLE_INTERNAL') || die();


class tool_generator_maketestcourse_testcase extends advanced_testcase {
    
    public function test_make_xs_course() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $expectedshortname = 'TOOL_MAKELARGECOURSE_XS';
        $expectedfullname = 'Ridiculous fullname';
        $expectedsummary = 'who even knows what this is about';

                $backend = new tool_generator_course_backend(
            $expectedshortname,
            0,
            false,
            false,
            false,
            $expectedfullname,
            $expectedsummary
        );
        $courseid = $backend->make();

                $course = get_course($courseid);
        $context = context_course::instance($courseid);
        $modinfo = get_fast_modinfo($course);

                $this->assertEquals($expectedshortname, $course->shortname);
        $this->assertEquals($expectedfullname, $course->fullname);

                $this->assertEquals($expectedsummary, $course->summary);

                $this->assertEquals(2, count($modinfo->get_section_info_all()));

                $users = get_enrolled_users($context);
        $this->assertEquals(1, count($users));
        $this->assertEquals('tool_generator_000001', reset($users)->username);

                $pages = $modinfo->get_instances_of('page');
        $this->assertEquals(1, count($pages));

                $resources = $modinfo->get_instances_of('resource');
        $ok = false;
        foreach ($resources as $resource) {
            if ($resource->sectionnum == 0) {
                                $ok = true;
                break;
            }
        }
        $this->assertTrue($ok);

                $fs = get_file_storage();
        $resourcecontext = context_module::instance($resource->id);
        $files = $fs->get_area_files($resourcecontext->id, 'mod_resource', 'content', false, 'filename', false);
        $files = array_values($files);
        $this->assertEquals(2, count($files));
        $this->assertEquals('resource1.txt', $files[0]->get_filename());
        $this->assertEquals('smallfile0.dat', $files[1]->get_filename());

                $ok = false;
        foreach ($resources as $resource) {
            if ($resource->sectionnum == 1) {
                $ok = true;
                break;
            }
        }
        $this->assertTrue($ok);

                $resourcecontext = context_module::instance($resource->id);
        $files = $fs->get_area_files($resourcecontext->id, 'mod_resource', 'content', false, 'filename', false);
        $files = array_values($files);
        $this->assertEquals(2, count($files));
        $this->assertEquals('bigfile0.dat', $files[0]->get_filename());
        $this->assertEquals('resource2.txt', $files[1]->get_filename());

                $forums = $modinfo->get_instances_of('forum');
        $forum = reset($forums);
        $posts = $DB->count_records_sql("
                SELECT
                    COUNT(1)
                FROM
                    {forum_posts} fp
                    JOIN {forum_discussions} fd ON fd.id = fp.discussion
                WHERE
                    fd.forum = ?", array($forum->instance));
        $this->assertEquals(2, $posts);
    }

    
    public function test_fixed_data_set() {

        $this->resetAfterTest();
        $this->setAdminUser();

                $backend = new tool_generator_course_backend('TOOL_S_COURSE_1', 1, true, false, false);
        $courseid = $backend->make();

                $course = get_course($courseid);
        $modinfo = get_fast_modinfo($course);

                $instances = $modinfo->get_instances_of('page');
        foreach ($instances as $instance) {
            $this->assertEquals(1, $instance->sectionnum);
        }

                $forums = $modinfo->get_instances_of('forum');
        $discussions = forum_get_discussions(reset($forums), 'd.timemodified ASC');
        $lastusernumber = 0;
        $discussionstarters = array();
        foreach ($discussions as $discussion) {
            $usernumber = core_user::get_user($discussion->userid, 'id, idnumber')->idnumber;

                        $this->assertEquals(1, $usernumber % 2);

                        $this->assertGreaterThan($lastusernumber, $usernumber);
            $lastusernumber = $usernumber;
            $discussionstarters[$discussion->userid] = $discussion->subject;
        }

    }

    
    public function test_filesize_limit() {

        $this->resetAfterTest();
        $this->setAdminUser();

                $filesizelimit = 100;

                $backend = new tool_generator_course_backend('TOOL_XS_LIMITED', 0, false, $filesizelimit, false);
        $courseid = $backend->make();

        $course = get_course($courseid);
        $modinfo = get_fast_modinfo($course);

                $fs = get_file_storage();
        $resources = $modinfo->get_instances_of('resource');
        foreach ($resources as $resource) {
            $resourcecontext = context_module::instance($resource->id);
            $files = $fs->get_area_files($resourcecontext->id, 'mod_resource', 'content', false, 'filename', false);
            foreach ($files as $file) {
                if ($file->get_mimetype() == 'application/octet-stream') {
                    $this->assertLessThanOrEqual($filesizelimit, $file->get_filesize());
                }
            }
        }

                $backend = new tool_generator_course_backend('TOOL_XS_NOLIMITS', 0, false, false, false);
        $courseid = $backend->make();

        $course = get_course($courseid);
        $modinfo = get_fast_modinfo($course);

                $fs = get_file_storage();
        $resources = $modinfo->get_instances_of('resource');
        foreach ($resources as $resource) {
            $resourcecontext = context_module::instance($resource->id);
            $files = $fs->get_area_files($resourcecontext->id, 'mod_resource', 'content', false, 'filename', false);
            foreach ($files as $file) {
                if ($file->get_mimetype() == 'application/octet-stream') {
                    $this->assertGreaterThan($filesizelimit, (int)$file->get_filesize());
                }
            }
        }

    }
}
