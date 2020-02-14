<?php



defined('MOODLE_INTERNAL') || die();


class assignfeedback_file_import_summary implements renderable {
    
    public $cmid = 0;
    
    public $userswithnewfeedback = 0;
    
    public $feedbackfilesadded = 0;
    
    public $feedbackfilesupdated = 0;

    
    public function __construct($cmid, $userswithnewfeedback, $feedbackfilesadded, $feedbackfilesupdated) {
        $this->cmid = $cmid;
        $this->userswithnewfeedback = $userswithnewfeedback;
        $this->feedbackfilesadded = $feedbackfilesadded;
        $this->feedbackfilesupdated = $feedbackfilesupdated;
    }
}
