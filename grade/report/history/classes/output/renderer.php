<?php



namespace gradereport_history\output;

defined('MOODLE_INTERNAL') || die;


class renderer extends \plugin_renderer_base {

    
    protected function render_user_button(user_button $button) {
        $attributes = array('type'     => 'button',
                            'class'    => 'selectortrigger',
                            'value'    => $button->label,
                            'disabled' => $button->disabled ? 'disabled' : null,
                            'title'    => $button->tooltip);

        if ($button->actions) {
            $id = \html_writer::random_id('single_button');
            $attributes['id'] = $id;
            foreach ($button->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }
                $output = \html_writer::empty_tag('input', $attributes);

                $params = $button->url->params();
        if ($button->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $var => $val) {
            $output .= \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
        }

                $output = \html_writer::tag('div', $output);

                if ($button->method === 'get') {
            $url = $button->url->out_omit_querystring(true);         } else {
            $url = $button->url->out_omit_querystring();             }
        if ($url === '') {
            $url = '#';         }
        $attributes = array('method' => $button->method,
                            'action' => $url,
                            'id'     => $button->formid);
        $output = \html_writer::tag('div', $output, $attributes);

                return \html_writer::tag('div', $output, array('class' => $button->class));
    }

    
    protected function render_tablelog(tablelog $tablelog) {
        $o = '';
        ob_start();
        $tablelog->out($tablelog->pagesize, false);
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

}
