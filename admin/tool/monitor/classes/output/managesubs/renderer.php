<?php



namespace tool_monitor\output\managesubs;

defined('MOODLE_INTERNAL') || die;


class renderer extends \plugin_renderer_base {

    
    protected function render_subs(subs $renderable) {
        $o = $this->render_table($renderable);
        return $o;
    }

    
    protected function render_rules(rules $renderable) {
        $o = '';
        if (!empty($renderable->totalcount)) {
            $o .= $this->render_table($renderable);
        }
        return $o;
    }

    
    protected function render_course_select(rules $renderable) {
        if ($select = $renderable->get_user_courses_select()) {
            return $this->render($select);
        }
    }

    
    protected function render_table($renderable) {
        $o = '';
        ob_start();
        $renderable->out($renderable->pagesize, true);
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

    
    public function render_rules_link($ruleurl) {
        echo \html_writer::start_div();
        $a = \html_writer::link($ruleurl, get_string('managerules', 'tool_monitor'));
        $link = \html_writer::tag('span', get_string('manageruleslink', 'tool_monitor', $a));
        echo $link;
        echo \html_writer::end_div();
    }
}
