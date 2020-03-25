<?php



namespace mod_feedback\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use mod_feedback_structure;


class summary implements renderable, templatable {

    
    protected $feedbackstructure;

    
    protected $mygroupid;

    
    protected $extradetails;

    
    public function __construct($feedbackstructure, $mygroupid = false, $extradetails = false) {
        $this->feedbackstructure = $feedbackstructure;
        $this->mygroupid = $mygroupid;
        $this->extradetails = $extradetails;
    }

    
    public function export_for_template(renderer_base $output) {
        $r = new stdClass();
        $r->completedcount = $this->feedbackstructure->count_completed_responses($this->mygroupid);
        $r->itemscount = count($this->feedbackstructure->get_items(true));
        if ($this->extradetails && ($timeopen = $this->feedbackstructure->get_feedback()->timeopen)) {
            $r->timeopen = userdate($timeopen);
        }
        if ($this->extradetails && ($timeclose = $this->feedbackstructure->get_feedback()->timeclose)) {
            $r->timeclose = userdate($timeclose);
        }

        return $r;
    }
}
