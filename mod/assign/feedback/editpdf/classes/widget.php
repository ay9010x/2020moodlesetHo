<?php



defined('MOODLE_INTERNAL') || die();


class assignfeedback_editpdf_widget implements renderable {

    
    public $assignment = 0;
    
    public $userid = 0;
    
    public $attemptnumber = 0;
    
    public $downloadurl = null;
    
    public $downloadfilename = null;
    
    public $stampfiles = array();
    
    public $readonly = true;
    
    public $pagetotal = 0;

    
    public function __construct($assignment, $userid, $attemptnumber, $downloadurl,
                                $downloadfilename, $stampfiles, $readonly, $pagetotal) {
        $this->assignment = $assignment;
        $this->userid = $userid;
        $this->attemptnumber = $attemptnumber;
        $this->downloadurl = $downloadurl;
        $this->downloadfilename = $downloadfilename;
        $this->stampfiles = $stampfiles;
        $this->readonly = $readonly;
        $this->pagetotal = $pagetotal;
    }
}
