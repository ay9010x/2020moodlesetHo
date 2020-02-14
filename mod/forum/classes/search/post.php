<?php



namespace mod_forum\search;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/forum/lib.php');


class post extends \core_search\area\base_mod {

    
    protected $forumsdata = array();

    
    protected $discussionsdata = array();

    
    protected $postsdata = array();

    
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;

        $sql = 'SELECT fp.*, f.id AS forumid, f.course AS courseid
                  FROM {forum_posts} fp
                  JOIN {forum_discussions} fd ON fd.id = fp.discussion
                  JOIN {forum} f ON f.id = fd.forum
                 WHERE fp.modified >= ? ORDER BY fp.modified ASC';
        return $DB->get_recordset_sql($sql, array($modifiedfrom));
    }

    
    public function get_document($record, $options = array()) {

        try {
            $cm = $this->get_cm('forum', $record->forumid, $record->courseid);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $ex) {
                        debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document, not all required data is available: ' .
                $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
                        debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

                $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('title', content_to_text($record->subject, false));
        $doc->set('content', content_to_text($record->message, $record->messageformat));
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $record->courseid);
        $doc->set('userid', $record->userid);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $record->modified);

                if (isset($options['lastindexedtime']) && ($options['lastindexedtime'] < $record->created)) {
                        $doc->set_is_new(true);
        }

        return $doc;
    }

    
    public function uses_file_indexing() {
        return true;
    }

    
    public function attach_files($document) {
        global $DB;

        $postid = $document->get('itemid');

        try {
            $post = $this->get_post($postid);
        } catch (\dml_missing_record_exception $e) {
            unset($this->postsdata[$postid]);
            debugging('Could not get record to attach files to '.$document->get('id'), DEBUG_DEVELOPER);
            return;
        }

                unset($this->postsdata[$postid]);

        $cm = $this->get_cm('forum', $post->forum, $document->get('courseid'));
        $context = \context_module::instance($cm->id);

                $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_forum', 'attachment', $postid, "filename", false);
        foreach ($files as $file) {
            $document->add_stored_file($file);
        }
    }

    
    public function check_access($id) {
        global $USER;

        try {
            $post = $this->get_post($id);
            $forum = $this->get_forum($post->forum);
            $discussion = $this->get_discussion($post->discussion);
            $cminfo = $this->get_cm('forum', $forum->id, $forum->course);
            $cm = $cminfo->get_course_module_record();
        } catch (\dml_missing_record_exception $ex) {
            return \core_search\manager::ACCESS_DELETED;
        } catch (\dml_exception $ex) {
            return \core_search\manager::ACCESS_DENIED;
        }

                if ($cminfo->uservisible === false) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if (!forum_user_can_see_post($forum, $discussion, $post, $USER, $cm)) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    
    public function get_doc_url(\core_search\document $doc) {
                $post = $this->get_post($doc->get('itemid'));
        return new \moodle_url('/mod/forum/discuss.php', array('d' => $post->discussion));
    }

    
    public function get_context_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        return new \moodle_url('/mod/forum/view.php', array('id' => $contextmodule->instanceid));
    }

    
    protected function get_post($postid) {
        if (empty($this->postsdata[$postid])) {
            $this->postsdata[$postid] = forum_get_post_full($postid);
            if (!$this->postsdata[$postid]) {
                throw new \dml_missing_record_exception('forum_posts');
            }
        }
        return $this->postsdata[$postid];
    }

    
    protected function get_forum($forumid) {
        global $DB;

        if (empty($this->forumsdata[$forumid])) {
            $this->forumsdata[$forumid] = $DB->get_record('forum', array('id' => $forumid), '*', MUST_EXIST);
        }
        return $this->forumsdata[$forumid];
    }

    
    protected function get_discussion($discussionid) {
        global $DB;

        if (empty($this->discussionsdata[$discussionid])) {
            $this->discussionsdata[$discussionid] = $DB->get_record('forum_discussions',
                array('id' => $discussionid), '*', MUST_EXIST);
        }
        return $this->discussionsdata[$discussionid];
    }
}
