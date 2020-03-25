<?php



defined('MOODLE_INTERNAL') || die();


class block_globalsearch extends block_base {

    
    public function init() {
        $this->title = get_string('pluginname', 'block_globalsearch');
    }

    
    public function get_content() {
        global $OUTPUT;
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (\core_search\manager::is_global_search_enabled() === false) {
            $this->content->text = get_string('globalsearchdisabled', 'search');
            return $this->content;
        }

        $url = new moodle_url('/search/index.php');
        $this->content->footer .= html_writer::link($url, get_string('advancedsearch', 'search'));

        $this->content->text  = html_writer::start_tag('div', array('class' => 'searchform'));
        $this->content->text .= html_writer::start_tag('form', array('action' => $url->out()));
        $this->content->text .= html_writer::start_tag('fieldset', array('action' => 'invisiblefieldset'));

                $this->content->text .= html_writer::tag('label', get_string('search', 'search'),
            array('for' => 'searchform_search', 'class' => 'accesshide'));
        $inputoptions = array('id' => 'searchform_search', 'name' => 'q', 'type' => 'text', 'size' => '15');
        $this->content->text .= html_writer::empty_tag('input', $inputoptions);

                $this->content->text .= html_writer::tag('button', get_string('search', 'search'),
            array('id' => 'searchform_button', 'type' => 'submit', 'title' => 'globalsearch'));
        $this->content->text .= html_writer::end_tag('fieldset');
        $this->content->text .= html_writer::end_tag('form');
        $this->content->text .= html_writer::end_tag('div');

        return $this->content;
    }
}
