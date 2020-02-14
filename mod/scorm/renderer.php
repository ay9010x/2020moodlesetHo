<?php



defined('MOODLE_INTERNAL') || die();


class mod_scorm_renderer extends plugin_renderer_base {
    public function view_user_heading($user, $course, $baseurl, $attempt, $attemptids) {
        $output = '';
        $output .= $this->box_start('generalbox boxaligncenter');
        $output .= html_writer::start_tag('div', array('class' => 'mdl-align'));
        $output .= $this->user_picture($user, array('courseid' => $course->id, 'link' => true));
        $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
        $output .= html_writer::link($url, fullname($user));
        $baseurl->param('attempt', '');
        $pb = new mod_scorm_attempt_bar($attemptids, $attempt, $baseurl, 'attempt');
        $output .= $this->render($pb);
        $output .= html_writer::end_tag('div');
        $output .= $this->box_end();
        return $output;
    }
    
    protected function render_mod_scorm_attempt_bar(mod_scorm_attempt_bar $attemptbar) {
        $output = '';
        $attemptbar = clone($attemptbar);
        $attemptbar->prepare($this, $this->page, $this->target);

        if (count($attemptbar->attemptids) > 1) {
            $output .= get_string('attempt', 'scorm') . ':';

            if (!empty($attemptbar->previouslink)) {
                $output .= '&#160;(' . $attemptbar->previouslink . ')&#160;';
            }

            foreach ($attemptbar->attemptlinks as $link) {
                $output .= "&#160;&#160;$link";
            }

            if (!empty($attemptbar->nextlink)) {
                $output .= '&#160;&#160;(' . $attemptbar->nextlink . ')';
            }
        }

        return html_writer::tag('div', $output, array('class' => 'paging'));
    }

}


class mod_scorm_attempt_bar implements renderable {

    
    public $attemptids;

    
    public $attempt;

    
    public $baseurl;

    
    public $pagevar;

    
    public $previouslink = null;

    
    public $nextlink = null;

    
    public $attemptlinks = array();

    
    public function __construct($attemptids, $attempt, $baseurl, $pagevar = 'page') {
        $this->attemptids = $attemptids;
        $this->attempt    = $attempt;
        $this->baseurl    = $baseurl;
        $this->pagevar    = $pagevar;
    }

    
    public function prepare(renderer_base $output, moodle_page $page, $target) {
        if (empty($this->attemptids)) {
            throw new coding_exception('mod_scorm_attempt_bar requires a attemptids value.');
        }
        if (!isset($this->attempt) || is_null($this->attempt)) {
            throw new coding_exception('mod_scorm_attempt_bar requires a attempt value.');
        }
        if (empty($this->baseurl)) {
            throw new coding_exception('mod_scorm_attempt_bar requires a baseurl value.');
        }

        if (count($this->attemptids) > 1) {
            $lastattempt = end($this->attemptids);             $firstattempt = reset($this->attemptids); 
            $nextattempt = 0;
            $prevattempt = null;
            $previous = 0;
            foreach ($this->attemptids as $attemptid) {
                if ($this->attempt == $attemptid) {
                    $this->attemptlinks[] = $attemptid;
                    $prevattempt = $previous;
                } else {
                    $attemptlink = html_writer::link(
                        new moodle_url($this->baseurl, array($this->pagevar => $attemptid)), $attemptid);
                    $this->attemptlinks[] = $attemptlink;
                    if (empty($nextattempt) && $prevattempt !== null) {
                                                $nextattempt = $attemptid;
                    }
                }
                $previous = $attemptid;             }

            if ($this->attempt != $firstattempt) {
                $this->previouslink = html_writer::link(
                    new moodle_url($this->baseurl, array($this->pagevar => $prevattempt)),
                    get_string('previous'), array('class' => 'previous'));
            }

            if ($this->attempt != $lastattempt) {
                $this->nextlink = html_writer::link(
                    new moodle_url($this->baseurl, array($this->pagevar => $nextattempt)),
                    get_string('next'), array('class' => 'next'));
            }
        }
    }
}