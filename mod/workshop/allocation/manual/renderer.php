<?php




defined('MOODLE_INTERNAL') || die();


class workshopallocation_manual_renderer extends mod_workshop_renderer  {

    
    protected $workshop;

            
    
    protected function render_workshopallocation_manual_allocations(workshopallocation_manual_allocations $data) {

        $this->workshop     = $data->workshop;

        $allocations        = $data->allocations;               $userinfo           = $data->userinfo;                  $authors            = $data->authors;                   $reviewers          = $data->reviewers;                 $hlauthorid         = $data->hlauthorid;                $hlreviewerid       = $data->hlreviewerid;              $selfassessment     = $data->selfassessment;    
        if (empty($allocations)) {
            return '';
        }

                $authors    = array_map('fullname', $authors);
        $reviewers  =  array_map('fullname', $reviewers);

        $table              = new html_table();
        $table->attributes['class'] = 'allocations';
        $table->head        = array(get_string('participantreviewedby', 'workshop'),
                                    get_string('participant', 'workshop'),
                                    get_string('participantrevierof', 'workshop'));
        $table->rowclasses  = array();
        $table->colclasses  = array('reviewedby', 'peer', 'reviewerof');
        $table->data        = array();
        foreach ($allocations as $allocation) {
            $row = array();
            $row[] = $this->helper_reviewers_of_participant($allocation, $userinfo, $reviewers, $selfassessment);
            $row[] = $this->helper_participant($allocation, $userinfo);
            $row[] = $this->helper_reviewees_of_participant($allocation, $userinfo, $authors, $selfassessment);
            $thisrowclasses = array();
            if ($allocation->userid == $hlauthorid) {
                $thisrowclasses[] = 'highlightreviewedby';
            }
            if ($allocation->userid == $hlreviewerid) {
                $thisrowclasses[] = 'highlightreviewerof';
            }
            $table->rowclasses[] = implode(' ', $thisrowclasses);
            $table->data[] = $row;
        }

        return $this->output->container(html_writer::table($table), 'manual-allocator');
    }

            
    
    protected function helper_participant(stdclass $allocation, array $userinfo) {
        $o  = $this->output->user_picture($userinfo[$allocation->userid], array('courseid' => $this->page->course->id));
        $o .= fullname($userinfo[$allocation->userid]);
        $o .= $this->output->container_start(array('submission'));
        if (is_null($allocation->submissionid)) {
            $o .= $this->output->container(get_string('nosubmissionfound', 'workshop'), 'info');
        } else {
            $link = $this->workshop->submission_url($allocation->submissionid);
            $o .= $this->output->container(html_writer::link($link, format_string($allocation->submissiontitle)), 'title');
            if (is_null($allocation->submissiongrade)) {
                $o .= $this->output->container(get_string('nogradeyet', 'workshop'), array('grade', 'missing'));
            } else {
                $o .= $this->output->container(get_string('alreadygraded', 'workshop'), array('grade', 'missing'));
            }
        }
        $o .= $this->output->container_end();
        return $o;
    }

    
    protected function helper_reviewers_of_participant(stdclass $allocation, array $userinfo, array $reviewers, $selfassessment) {
        $o = '';
        if (is_null($allocation->submissionid)) {
            $o .= $this->output->container(get_string('nothingtoreview', 'workshop'), 'info');
        } else {
            $exclude = array();
            if (! $selfassessment) {
                $exclude[$allocation->userid] = true;
            }
                        $options = array_diff_key($reviewers, $exclude);
            if ($options) {
                $handler = new moodle_url($this->page->url, array('mode' => 'new', 'of' => $allocation->userid, 'sesskey' => sesskey()));
                $select = new single_select($handler, 'by', $options, '', array(''=>get_string('chooseuser', 'workshop')), 'addreviewof' . $allocation->userid);
                $select->set_label(get_string('addreviewer', 'workshopallocation_manual'));
                $o .= $this->output->render($select);
            }
        }
        $o .= html_writer::start_tag('ul', array());
        foreach ($allocation->reviewedby as $reviewerid => $assessmentid) {
            $o .= html_writer::start_tag('li', array());
            $o .= $this->output->user_picture($userinfo[$reviewerid], array('courseid' => $this->page->course->id, 'size' => 16));
            $o .= fullname($userinfo[$reviewerid]);

                        $handler = new moodle_url($this->page->url, array('mode' => 'del', 'what' => $assessmentid, 'sesskey' => sesskey()));
            $o .= $this->helper_remove_allocation_icon($handler);

            $o .= html_writer::end_tag('li');
        }
        $o .= html_writer::end_tag('ul');
        return $o;
    }

    
    protected function helper_reviewees_of_participant(stdclass $allocation, array $userinfo, array $authors, $selfassessment) {
        $o = '';
        if (is_null($allocation->submissionid)) {
            $o .= $this->output->container(get_string('withoutsubmission', 'workshop'), 'info');
        }
        $exclude = array();
        if (! $selfassessment) {
            $exclude[$allocation->userid] = true;
            $o .= $this->output->container(get_string('selfassessmentdisabled', 'workshop'), 'info');
        }
                $options = array_diff_key($authors, $exclude);
        if ($options) {
            $handler = new moodle_url($this->page->url, array('mode' => 'new', 'by' => $allocation->userid, 'sesskey' => sesskey()));
            $select = new single_select($handler, 'of', $options, '', array(''=>get_string('chooseuser', 'workshop')), 'addreviewby' . $allocation->userid);
            $select->set_label(get_string('addreviewee', 'workshopallocation_manual'));
            $o .= $this->output->render($select);
        } else {
            $o .= $this->output->container(get_string('nothingtoreview', 'workshop'), 'info');
        }
        $o .= html_writer::start_tag('ul', array());
        foreach ($allocation->reviewerof as $authorid => $assessmentid) {
            $o .= html_writer::start_tag('li', array());
            $o .= $this->output->user_picture($userinfo[$authorid], array('courseid' => $this->page->course->id, 'size' => 16));
            $o .= fullname($userinfo[$authorid]);

                        $handler = new moodle_url($this->page->url, array('mode' => 'del', 'what' => $assessmentid, 'sesskey' => sesskey()));
            $o .= $this->helper_remove_allocation_icon($handler);

            $o .= html_writer::end_tag('li');
        }
        $o .= html_writer::end_tag('ul');
        return $o;
    }

    
    protected function helper_remove_allocation_icon($link) {
        return $this->output->action_icon($link, new pix_icon('t/delete', 'X'));
    }
}
