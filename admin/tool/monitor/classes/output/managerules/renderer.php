<?php



namespace tool_monitor\output\managerules;

defined('MOODLE_INTERNAL') || die;


class renderer extends \plugin_renderer_base {

    
    protected function render_renderable(renderable $renderable) {
        $o = $this->render_table($renderable);
        $o .= $this->render_add_button($renderable->courseid);

        return $o;
    }

    
    protected function render_table(renderable $renderable) {
        $o = '';
        ob_start();
        $renderable->out($renderable->pagesize, true);
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

    
    protected function render_add_button($courseid) {
        global $CFG;

        $button = \html_writer::tag('button', get_string('addrule', 'tool_monitor'));
        $addurl = new \moodle_url($CFG->wwwroot. '/admin/tool/monitor/edit.php', array('courseid' => $courseid));
        return \html_writer::link($addurl, $button);
    }

    
    public function render_subscriptions_link($manageurl) {
        echo \html_writer::start_div();
        $a = \html_writer::link($manageurl, get_string('managesubscriptions', 'tool_monitor'));
        $link = \html_writer::tag('span', get_string('managesubscriptionslink', 'tool_monitor', $a));
        echo $link;
        echo \html_writer::end_div();
    }
}
