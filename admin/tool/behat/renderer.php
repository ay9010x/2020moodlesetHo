<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/behat/classes/behat_selectors.php');


class tool_behat_renderer extends plugin_renderer_base {

    
    public function render_stepsdefinitions($stepsdefinitions, $form) {

        $html = $this->generic_info();

                ob_start();
        $form->display();
        $html .= ob_get_contents();
        ob_end_clean();

        if (empty($stepsdefinitions)) {
            $stepsdefinitions = get_string('nostepsdefinitions', 'tool_behat');
        } else {

            $stepsdefinitions = implode('', $stepsdefinitions);

                        $stepsdefinitions = preg_replace_callback('/(TEXT_SELECTOR\d?_STRING)/',
                function ($matches) {
                    return html_writer::select(behat_selectors::get_allowed_text_selectors(), uniqid());
                },
                $stepsdefinitions
            );

                        $stepsdefinitions = preg_replace_callback('/(SELECTOR\d?_STRING)/',
                function ($matches) {
                    return html_writer::select(behat_selectors::get_allowed_selectors(), uniqid());
                },
                $stepsdefinitions
            );

                        $regex = '#\(\?P<[^>]+>([^\)|]+\|[^\)]+)\)#';
            $stepsdefinitions = preg_replace_callback($regex,
                function($matches){
                    return html_writer::select(explode('|', $matches[1]), uniqid());
                },
                $stepsdefinitions
            );

            $stepsdefinitions = preg_replace_callback('/(FIELD_VALUE_STRING)/',
                function ($matches) {
                    global $CFG;

                                        $url = new moodle_url(
                        '/help.php',
                        array(
                            'component' => 'tool_behat',
                            'identifier' => 'fieldvalueargument',
                            'lang' => current_language()
                        )
                    );

                                                            $title = get_string('fieldvalueargument', 'tool_behat');
                    $title = get_string('helpprefix2', '', trim($title, ". \t"));

                    $attributes = array('href' => $url, 'title' => $title,
                        'aria-haspopup' => 'true', 'target' => '_blank');

                    $output = html_writer::tag('a', 'FIELD_VALUE_STRING', $attributes);
                    return html_writer::tag('span', $output, array('class' => 'helptooltip'));
                },
                $stepsdefinitions
            );
        }

                $html .= html_writer::tag('div', $stepsdefinitions, array('class' => 'steps-definitions'));

        $html .= $this->output->footer();

        return $html;
    }

    
    public function render_error($msg) {

        $html = $this->generic_info();

        $a = new stdClass();
        $a->errormsg = $msg;
        $a->behatcommand = behat_command::get_behat_command();
        $a->behatinit = 'php admin' . DIRECTORY_SEPARATOR . 'tool' . DIRECTORY_SEPARATOR .
            'behat' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'init.php';

        $msg = get_string('wrongbehatsetup', 'tool_behat', $a);

                $html .= $this->output->box_start('box errorbox');
        $html .= html_writer::tag('div', $msg);
        $html .= $this->output->box_end();

        $html .= $this->output->footer();

        return $html;
    }

    
    protected function generic_info() {

        $title = get_string('pluginname', 'tool_behat');

                $html = $this->output->header();
        $html .= $this->output->heading($title);

                $installurl = behat_command::DOCS_URL . '#Installation';
        $installlink = html_writer::tag('a', $installurl, array('href' => $installurl, 'target' => '_blank'));
        $writetestsurl = behat_command::DOCS_URL . '#Writing_features';
        $writetestslink = html_writer::tag('a', $writetestsurl, array('href' => $writetestsurl, 'target' => '_blank'));
        $writestepsurl = behat_command::DOCS_URL . '#Adding_steps_definitions';
        $writestepslink = html_writer::tag('a', $writestepsurl, array('href' => $writestepsurl, 'target' => '_blank'));
        $infos = array(
            get_string('installinfo', 'tool_behat', $installlink),
            get_string('newtestsinfo', 'tool_behat', $writetestslink),
            get_string('newstepsinfo', 'tool_behat', $writestepslink)
        );

                $html .= $this->output->box_start();
        $html .= html_writer::tag('h3', get_string('infoheading', 'tool_behat'));
        $html .= html_writer::tag('div', get_string('aim', 'tool_behat'));
        $html .= html_writer::start_tag('div');
        $html .= html_writer::start_tag('ul');
        $html .= html_writer::start_tag('li');
        $html .= implode(html_writer::end_tag('li') . html_writer::start_tag('li'), $infos);
        $html .= html_writer::end_tag('li');
        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('div');
        $html .= $this->output->box_end();

        return $html;
    }

}
