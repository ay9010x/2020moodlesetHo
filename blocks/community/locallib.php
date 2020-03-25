<?php



class block_community_manager {

    
    public function block_community_add_course($course, $userid) {
        global $DB;

        $community = $this->block_community_get_course($course->url, $userid);

        if (empty($community)) {
            $community = new stdClass();
            $community->userid = $userid;
            $community->coursename = $course->name;
            $community->coursedescription = $course->description;
            $community->courseurl = $course->url;
            $community->imageurl = $course->imageurl;
            return $DB->insert_record('block_community', $community);
        } else {
            return false;
        }
    }

    
    public function block_community_get_courses($userid) {
        global $DB;
        return $DB->get_records('block_community', array('userid' => $userid), 'coursename');
    }

    
    public function block_community_get_course($courseurl, $userid) {
        global $DB;
        return $DB->get_record('block_community',
                array('courseurl' => $courseurl, 'userid' => $userid));
    }

    
    public function block_community_download_course_backup($course) {
        global $CFG, $USER;
        require_once($CFG->libdir . "/filelib.php");
        require_once($CFG->dirroot. "/course/publish/lib.php");

        $params['courseid'] = $course->id;
        $params['filetype'] = HUB_BACKUP_FILE_TYPE;

        make_temp_directory('backup');

        $filename = md5(time() . '-' . $course->id . '-'. $USER->id . '-'. random_string(20));

        $url  = new moodle_url($course->huburl.'/local/hub/webservice/download.php', $params);
        $path = $CFG->tempdir.'/backup/'.$filename.".mbz";
        $fp = fopen($path, 'w');
        $curlurl = $course->huburl.'/local/hub/webservice/download.php?filetype='
                .HUB_BACKUP_FILE_TYPE.'&courseid='.$course->id;

                require_once($CFG->dirroot . '/' . $CFG->admin . '/registration/lib.php');
        $registrationmanager = new registration_manager();
        $registeredhub = $registrationmanager->get_registeredhub($course->huburl);
        if (!empty($registeredhub)) {
            $token = $registeredhub->token;
            $curlurl .= '&token='.$token;
        }

        $ch = curl_init($curlurl);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $fs = get_file_storage();
        $record = new stdClass();
        $record->contextid = context_user::instance($USER->id)->id;
        $record->component = 'user';
        $record->filearea = 'private';
        $record->itemid = 0;
        $record->filename = urlencode($course->fullname)."_".time().".mbz";
        $record->filepath = '/downloaded_backup/';
        if (!$fs->file_exists($record->contextid, $record->component,
                $record->filearea, 0, $record->filepath, $record->filename)) {
            $fs->create_file_from_pathname($record,
                    $CFG->tempdir.'/backup/'.$filename.".mbz");
        }

        $filenames = array();
        $filenames['privatefile'] = $record->filename;
        $filenames['tmpfile'] = $filename;
        return $filenames;
    }

    
    public function block_community_remove_course($communityid, $userid) {
        global $DB, $USER;
        return $DB->delete_records('block_community',
                array('userid' => $userid, 'id' => $communityid));
    }

}
