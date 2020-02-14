<?php



defined('MOODLE_INTERNAL') || die();


class block_myprofile extends block_base {
    
    public function init() {
        $this->title   = get_string('pluginname', 'block_myprofile');
    }

    
    public function get_content() {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (!isloggedin() or isguestuser()) {
            return '';              }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $course = $this->page->course;

        if (!isset($this->config->display_picture) || $this->config->display_picture == 1) {
            $this->content->text .= '<div class="myprofileitem picture">';
            $this->content->text .= $OUTPUT->user_picture($USER, array('courseid'=>$course->id, 'size'=>'100', 'class'=>'profilepicture'));              $this->content->text .= '</div>';
        }

        $this->content->text .= '<div class="myprofileitem fullname">'.fullname($USER).'</div>';

        if(!isset($this->config->display_country) || $this->config->display_country == 1) {
            $countries = get_string_manager()->get_list_of_countries();
            if (isset($countries[$USER->country])) {
                $this->content->text .= '<div class="myprofileitem country">';
                $this->content->text .= get_string('country') . ': ' . $countries[$USER->country];
                $this->content->text .= '</div>';
            }
        }

        if(!isset($this->config->display_city) || $this->config->display_city == 1) {
            $this->content->text .= '<div class="myprofileitem city">';
            $this->content->text .= get_string('city') . ': ' . format_string($USER->city);
            $this->content->text .= '</div>';
        }

        if(!isset($this->config->display_email) || $this->config->display_email == 1) {
            $this->content->text .= '<div class="myprofileitem email">';
            $this->content->text .= obfuscate_mailto($USER->email, '');
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_icq) && !empty($USER->icq)) {
            $this->content->text .= '<div class="myprofileitem icq">';
            $this->content->text .= 'ICQ: ' . s($USER->icq);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_skype) && !empty($USER->skype)) {
            $this->content->text .= '<div class="myprofileitem skype">';
            $this->content->text .= 'Skype: ' . s($USER->skype);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_yahoo) && !empty($USER->yahoo)) {
            $this->content->text .= '<div class="myprofileitem yahoo">';
            $this->content->text .= 'Yahoo: ' . s($USER->yahoo);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_aim) && !empty($USER->aim)) {
            $this->content->text .= '<div class="myprofileitem aim">';
            $this->content->text .= 'AIM: ' . s($USER->aim);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_msn) && !empty($USER->msn)) {
            $this->content->text .= '<div class="myprofileitem msn">';
            $this->content->text .= 'MSN: ' . s($USER->msn);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_phone1) && !empty($USER->phone1)) {
            $this->content->text .= '<div class="myprofileitem phone1">';
            $this->content->text .= get_string('phone1').': ' . s($USER->phone1);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_phone2) && !empty($USER->phone2)) {
            $this->content->text .= '<div class="myprofileitem phone2">';
            $this->content->text .= get_string('phone2').': ' . s($USER->phone2);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_institution) && !empty($USER->institution)) {
            $this->content->text .= '<div class="myprofileitem institution">';
            $this->content->text .= format_string($USER->institution);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_address) && !empty($USER->address)) {
            $this->content->text .= '<div class="myprofileitem address">';
            $this->content->text .= format_string($USER->address);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_firstaccess) && !empty($USER->firstaccess)) {
            $this->content->text .= '<div class="myprofileitem firstaccess">';
            $this->content->text .= get_string('firstaccess').': ' . userdate($USER->firstaccess);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_lastaccess) && !empty($USER->lastaccess)) {
            $this->content->text .= '<div class="myprofileitem lastaccess">';
            $this->content->text .= get_string('lastaccess').': ' . userdate($USER->lastaccess);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_currentlogin) && !empty($USER->currentlogin)) {
            $this->content->text .= '<div class="myprofileitem currentlogin">';
            $this->content->text .= get_string('login').': ' . userdate($USER->currentlogin);
            $this->content->text .= '</div>';
        }

        if(!empty($this->config->display_lastip) && !empty($USER->lastip)) {
            $this->content->text .= '<div class="myprofileitem lastip">';
            $this->content->text .= 'IP: ' . $USER->lastip;
            $this->content->text .= '</div>';
        }

        return $this->content;
    }

    
    public function has_config() {
        return false;
    }

    
    public function instance_allow_multiple() {
                return false;
    }

    
    function instance_allow_config() {
                return false;
    }

    
    public function specialization() {
    }

    
    public function applicable_formats() {
        return array('all'=>true);
    }

    
    public function after_install() {
    }

    
    public function before_delete() {
    }

}
