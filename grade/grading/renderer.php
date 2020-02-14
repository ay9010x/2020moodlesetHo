<?php



defined('MOODLE_INTERNAL') || die();


class core_grading_renderer extends plugin_renderer_base {

    
    public function management_method_selector(grading_manager $manager, moodle_url $targeturl) {

        $method = $manager->get_active_method();
        $methods = $manager->get_available_methods(false);
        $methods['none'] = get_string('gradingmethodnone', 'core_grading');
        $selector = new single_select(new moodle_url($targeturl, array('sesskey' => sesskey())),
            'setmethod', $methods, empty($method) ? 'none' : $method, null, 'activemethodselector');
        $selector->set_label(get_string('changeactivemethod', 'core_grading'));
        $selector->set_help_icon('gradingmethod', 'core_grading');

        return $this->output->render($selector);
    }

    
    public function management_action_icon(moodle_url $url, $text, $icon) {

        $img = html_writer::empty_tag('img', array('src' => $this->output->pix_url($icon), 'class' => 'action-icon'));
        $txt = html_writer::tag('div', $text, array('class' => 'action-text'));
        return html_writer::link($url, $img . $txt, array('class' => 'action'));
    }

    
    public function management_message($message) {
        $this->page->requires->strings_for_js(array('clicktoclose'), 'core_grading');
        $this->page->requires->yui_module('moodle-core_grading-manage', 'M.core_grading.init_manage');
        return $this->output->box(format_string($message) . ' - ' . html_writer::tag('span', ''), 'message',
                'actionresultmessagebox');
    }

    
    public function pick_action_icon(moodle_url $url, $text, $icon = '', $class = '') {

        $img = html_writer::empty_tag('img', array('src' => $this->output->pix_url($icon), 'class' => 'action-icon'));
        $txt = html_writer::tag('div', $text, array('class' => 'action-text'));
        return html_writer::link($url, $img . $txt, array('class' => 'action '.$class));
    }
}
