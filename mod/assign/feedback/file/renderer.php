<?php



defined('MOODLE_INTERNAL') || die();


class assignfeedback_file_renderer extends plugin_renderer_base {

    
    public function render_assignfeedback_file_import_summary($summary) {
        $o = '';
        $o .= $this->container(get_string('userswithnewfeedback', 'assignfeedback_file', $summary->userswithnewfeedback));
        $o .= $this->container(get_string('filesupdated', 'assignfeedback_file', $summary->feedbackfilesupdated));
        $o .= $this->container(get_string('filesadded', 'assignfeedback_file', $summary->feedbackfilesadded));

        $url = new moodle_url('view.php',
                              array('id'=>$summary->cmid,
                                    'action'=>'grading'));
        $o .= $this->continue_button($url);
        return $o;
    }
}

