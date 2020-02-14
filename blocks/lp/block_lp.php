<?php



defined('MOODLE_INTERNAL') || die();


class block_lp extends block_base {

    
    public function applicable_formats() {
        return array('site' => true, 'course' => true, 'my' => true);
    }

    
    public function init() {
        $this->title = get_string('pluginname', 'block_lp');
    }

    
    public function get_content() {
        if (isset($this->content)) {
            return $this->content;
        }
        $this->content = new stdClass();

        if (!get_config('core_competency', 'enabled')) {
            return $this->content;
        }

                if (isloggedin() && !isguestuser()) {
            $summary = new \block_lp\output\summary();
            if (!$summary->has_content()) {
                return $this->content;
            }

            $renderer = $this->page->get_renderer('block_lp');
            $this->content->text = $renderer->render($summary);
            $this->content->footer = '';
        }

        return $this->content;
    }

}
