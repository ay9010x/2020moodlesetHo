<?php



defined('MOODLE_INTERNAL') || die();

use \assignfeedback_editpdf\comments_quick_list;
use \assignfeedback_editpdf\document_services;
use \assignfeedback_editpdf\page_editor;
use \assignfeedback_editpdf\pdf;
use \assignfeedback_editpdf\comment;
use \assignfeedback_editpdf\annotation;

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');


class assignfeedback_editpdf_testcase extends mod_assign_base_testcase {

    protected function setUp() {
                if (!pdf::test_gs_path(false)) {
            $this->markTestSkipped('Ghostscript not setup');
            return;
        }
        parent::setUp();
    }

    protected function create_assign_and_submit_pdf() {
        global $CFG;
        $assign = $this->create_instance(array('assignsubmission_onlinetext_enabled' => 1,
                                               'assignsubmission_file_enabled' => 1,
                                               'assignsubmission_file_maxfiles' => 1,
                                               'assignfeedback_editpdf_enabled' => 1,
                                               'assignsubmission_file_maxsizebytes' => 1000000));

        $user = $this->students[0];
        $this->setUser($user);

                $submission = $assign->get_user_submission($user->id, true);

        $fs = get_file_storage();
        $pdfsubmission = (object) array(
            'contextid' => $assign->get_context()->id,
            'component' => 'assignsubmission_file',
            'filearea' => ASSIGNSUBMISSION_FILE_FILEAREA,
            'itemid' => $submission->id,
            'filepath' => '/',
            'filename' => 'submission.pdf'
        );
        $sourcefile = $CFG->dirroot.'/mod/assign/feedback/editpdf/tests/fixtures/submission.pdf';
        $fi = $fs->create_file_from_pathname($pdfsubmission, $sourcefile);

        $data = new stdClass();
        $plugin = $assign->get_submission_plugin_by_type('file');
        $plugin->save($submission, $data);

        return $assign;
    }

    public function test_comments_quick_list() {

        $this->setUser($this->teachers[0]);

        $comments = comments_quick_list::get_comments();

        $this->assertEmpty($comments);

        $comment = comments_quick_list::add_comment('test', 45, 'red');

        $comments = comments_quick_list::get_comments();

        $this->assertEquals(count($comments), 1);
        $first = reset($comments);
        $this->assertEquals($comment, $first);

        $commentbyid = comments_quick_list::get_comment($comment->id);
        $this->assertEquals($comment, $commentbyid);

        $result = comments_quick_list::remove_comment($comment->id);

        $this->assertTrue($result);

        $comments = comments_quick_list::get_comments();
        $this->assertEmpty($comments);
    }

    public function test_page_editor() {

        $assign = $this->create_assign_and_submit_pdf();
        $this->setUser($this->teachers[0]);

        $grade = $assign->get_user_grade($this->students[0]->id, true);

        $notempty = page_editor::has_annotations_or_comments($grade->id, false);
        $this->assertFalse($notempty);

        $comment = new comment();

        $comment->rawtext = 'Comment text';
        $comment->width = 100;
        $comment->x = 100;
        $comment->y = 100;
        $comment->colour = 'red';

        $comment2 = new comment();

        $comment2->rawtext = 'Comment text 2';
        $comment2->width = 100;
        $comment2->x = 200;
        $comment2->y = 100;
        $comment2->colour = 'clear';

        page_editor::set_comments($grade->id, 0, array($comment, $comment2));

        $annotation = new annotation();

        $annotation->path = '';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 200;
        $annotation->endy = 200;
        $annotation->type = 'line';
        $annotation->colour = 'red';

        $annotation2 = new annotation();

        $annotation2->path = '';
        $annotation2->x = 100;
        $annotation2->y = 100;
        $annotation2->endx = 200;
        $annotation2->endy = 200;
        $annotation2->type = 'rectangle';
        $annotation2->colour = 'yellow';

        page_editor::set_annotations($grade->id, 0, array($annotation, $annotation2));

        $notempty = page_editor::has_annotations_or_comments($grade->id, false);
                $this->assertFalse($notempty);

        $comments = page_editor::get_comments($grade->id, 0, false);

        $this->assertEmpty($comments);

        $comments = page_editor::get_comments($grade->id, 0, true);

        $this->assertEquals(count($comments), 2);

        $annotations = page_editor::get_annotations($grade->id, 0, false);

        $this->assertEmpty($annotations);

        $annotations = page_editor::get_annotations($grade->id, 0, true);

        $this->assertEquals(count($annotations), 2);

        $comment = reset($comments);
        $annotation = reset($annotations);

        page_editor::remove_comment($comment->id);
        page_editor::remove_annotation($annotation->id);

        $comments = page_editor::get_comments($grade->id, 0, true);

        $this->assertEquals(count($comments), 1);

        $annotations = page_editor::get_annotations($grade->id, 0, true);

        $this->assertEquals(count($annotations), 1);

        page_editor::release_drafts($grade->id);

        $notempty = page_editor::has_annotations_or_comments($grade->id, false);

        $this->assertTrue($notempty);

        page_editor::unrelease_drafts($grade->id);

        $notempty = page_editor::has_annotations_or_comments($grade->id, false);

        $this->assertFalse($notempty);
    }

    public function test_document_services() {

        $assign = $this->create_assign_and_submit_pdf();
        $this->setUser($this->teachers[0]);

        $grade = $assign->get_user_grade($this->students[0]->id, true);

        $notempty = page_editor::has_annotations_or_comments($grade->id, false);
        $this->assertFalse($notempty);

        $comment = new comment();

        $comment->rawtext = 'Comment text';
        $comment->width = 100;
        $comment->x = 100;
        $comment->y = 100;
        $comment->colour = 'red';

        page_editor::set_comments($grade->id, 0, array($comment));

        $annotations = array();

        $annotation = new annotation();
        $annotation->path = '';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 200;
        $annotation->endy = 200;
        $annotation->type = 'line';
        $annotation->colour = 'red';
        array_push($annotations, $annotation);

        $annotation = new annotation();
        $annotation->path = '';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 200;
        $annotation->endy = 200;
        $annotation->type = 'rectangle';
        $annotation->colour = 'yellow';
        array_push($annotations, $annotation);

        $annotation = new annotation();
        $annotation->path = '';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 200;
        $annotation->endy = 200;
        $annotation->type = 'oval';
        $annotation->colour = 'green';
        array_push($annotations, $annotation);

        $annotation = new annotation();
        $annotation->path = '';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 200;
        $annotation->endy = 116;
        $annotation->type = 'highlight';
        $annotation->colour = 'blue';
        array_push($annotations, $annotation);

        $annotation = new annotation();
        $annotation->path = '100,100:105,105:110,100';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 110;
        $annotation->endy = 105;
        $annotation->type = 'pen';
        $annotation->colour = 'black';
        array_push($annotations, $annotation);
        page_editor::set_annotations($grade->id, 0, $annotations);

        page_editor::release_drafts($grade->id);

        $notempty = page_editor::has_annotations_or_comments($grade->id, false);

        $this->assertTrue($notempty);

        $file = document_services::generate_feedback_document($assign->get_instance()->id, $grade->userid, $grade->attemptnumber);
        $this->assertNotEmpty($file);

        $file2 = document_services::get_feedback_document($assign->get_instance()->id, $grade->userid, $grade->attemptnumber);

        $this->assertEquals($file, $file2);

        document_services::delete_feedback_document($assign->get_instance()->id, $grade->userid, $grade->attemptnumber);
        $file3 = document_services::get_feedback_document($assign->get_instance()->id, $grade->userid, $grade->attemptnumber);

        $this->assertEmpty($file3);
    }

    
    public function test_is_feedback_modified() {
        global $DB;
        $assign = $this->create_assign_and_submit_pdf();
        $this->setUser($this->teachers[0]);

        $grade = $assign->get_user_grade($this->students[0]->id, true);

        $notempty = page_editor::has_annotations_or_comments($grade->id, false);
        $this->assertFalse($notempty);

        $comment = new comment();

        $comment->rawtext = 'Comment text';
        $comment->width = 100;
        $comment->x = 100;
        $comment->y = 100;
        $comment->colour = 'red';

        page_editor::set_comments($grade->id, 0, array($comment));

        $annotations = array();

        $annotation = new annotation();
        $annotation->path = '';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 200;
        $annotation->endy = 200;
        $annotation->type = 'line';
        $annotation->colour = 'red';
        array_push($annotations, $annotation);

        page_editor::set_annotations($grade->id, 0, $annotations);

        $plugin = $assign->get_feedback_plugin_by_type('editpdf');
        $data = new stdClass();
        $data->editpdf_source_userid = $this->students[0]->id;
        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
        $plugin->save($grade, $data);

        $annotation = new annotation();
        $annotation->gradeid = $grade->id;
        $annotation->pageno = 0;
        $annotation->path = '';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 200;
        $annotation->endy = 200;
        $annotation->type = 'rectangle';
        $annotation->colour = 'yellow';

        $yellowannotationid = page_editor::add_annotation($annotation);

                $comment = new comment();
        $comment->gradeid = $grade->id;
        $comment->pageno = 0;
        $comment->rawtext = 'Second Comment text';
        $comment->width = 100;
        $comment->x = 100;
        $comment->y = 100;
        $comment->colour = 'red';
        page_editor::add_comment($comment);

        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
        $plugin->save($grade, $data);

                $this->assertCount(2, page_editor::get_annotations($grade->id, 0, false));
                $this->assertCount(2, page_editor::get_comments($grade->id, 0, false));

                $annotation = new annotation();
        $annotation->gradeid = $grade->id;
        $annotation->pageno = 0;
        $annotation->path = '100,100:105,105:110,100';
        $annotation->x = 100;
        $annotation->y = 100;
        $annotation->endx = 110;
        $annotation->endy = 105;
        $annotation->type = 'pen';
        $annotation->colour = 'black';
        page_editor::add_annotation($annotation);

        $annotations = page_editor::get_annotations($grade->id, 0, true);
        page_editor::remove_annotation($yellowannotationid);
        $this->assertTrue($plugin->is_feedback_modified($grade, $data));
        $plugin->save($grade, $data);

                $this->assertCount(2, page_editor::get_annotations($grade->id, 0, false));
                $this->assertCount(2, page_editor::get_comments($grade->id, 0, false));

                $comment = new comment();
        $comment->gradeid = $grade->id;
        $comment->pageno = 0;
        $comment->rawtext = 'Third Comment text';
        $comment->width = 400;
        $comment->x = 57;
        $comment->y = 205;
        $comment->colour = 'black';
        $comment->id = page_editor::add_comment($comment);

                $this->assertCount(3, page_editor::get_comments($grade->id, 0, true));
                page_editor::remove_comment($comment->id);
                $this->assertCount(2, page_editor::get_comments($grade->id, 0, true));
                $this->assertFalse($plugin->is_feedback_modified($grade, $data));
    }
}
