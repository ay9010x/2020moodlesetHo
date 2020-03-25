<?php



namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();


class template_plans_page implements \renderable {

    
    public function __construct(\core_competency\template $template, \moodle_url $url) {
        $this->template = $template;
        $this->url = $url;
        $this->table = new template_plans_table('tplplans', $template);
        $this->table->define_baseurl($url);
    }

}
