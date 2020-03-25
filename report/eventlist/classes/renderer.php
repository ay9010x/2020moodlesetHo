<?php



defined('MOODLE_INTERNAL') || die();


class report_eventlist_renderer extends plugin_renderer_base {

    
    public function render_event_list($form, $tabledata) {
        global $PAGE;

        $title = get_string('pluginname', 'report_eventlist');

                $html = $this->output->header();
        $html .= $this->output->heading($title);

                ob_start();
        $form->display();
        $html .= ob_get_contents();
        ob_end_clean();

        $PAGE->requires->yui_module('moodle-report_eventlist-eventfilter', 'Y.M.report_eventlist.EventFilter.init',
                array(array('tabledata' => $tabledata)));
        $PAGE->requires->strings_for_js(array(
            'eventname',
            'component',
            'action',
            'crud',
            'edulevel',
            'affectedtable',
            'dname',
            'legacyevent',
            'since'
            ), 'report_eventlist');
        $html .= html_writer::start_div('report-eventlist-data-table', array('id' => 'report-eventlist-table'));
        $html .= html_writer::end_div();

        $html .= $this->output->footer();
        return $html;
    }

    
    public function render_event_detail($observerlist, $eventinformation) {
        global $PAGE;

        $titlehtml = $this->output->header();
        $titlehtml .= $this->output->heading($eventinformation['title']);

        $html = html_writer::start_tag('dl', array('class' => 'list'));

        $explanation = nl2br($eventinformation['explanation']);
        $html .= html_writer::tag('dt', get_string('eventexplanation', 'report_eventlist'));
        $html .= html_writer::tag('dd', $explanation);

        if (isset($eventinformation['crud'])) {
            $html .= html_writer::tag('dt', get_string('crud', 'report_eventlist'));
            $html .= html_writer::tag('dd', $eventinformation['crud']);
        }

        if (isset($eventinformation['edulevel'])) {
            $html .= html_writer::tag('dt', get_string('edulevel', 'report_eventlist'));
            $html .= html_writer::tag('dd', $eventinformation['edulevel']);
        }

        if (isset($eventinformation['objecttable'])) {
            $html .= html_writer::tag('dt', get_string('affectedtable', 'report_eventlist'));
            $html .= html_writer::tag('dd', $eventinformation['objecttable']);
        }

        if (isset($eventinformation['legacyevent'])) {
            $html .= html_writer::tag('dt', get_string('legacyevent', 'report_eventlist'));
            $html .= html_writer::tag('dd', $eventinformation['legacyevent']);
        }

        if (isset($eventinformation['parentclass'])) {
            $url = new moodle_url('eventdetail.php', array('eventname' => $eventinformation['parentclass']));
            $html .= html_writer::tag('dt', get_string('parentevent', 'report_eventlist'));
            $html .= html_writer::tag('dd', html_writer::link($url, $eventinformation['parentclass']));
        }

        if (isset($eventinformation['abstract'])) {
            $html .= html_writer::tag('dt', get_string('abstractclass', 'report_eventlist'));
            $html .= html_writer::tag('dd', get_string('yes', 'report_eventlist'));
        }

        if (isset($eventinformation['typeparameter'])) {
            $html .= html_writer::tag('dt', get_string('typedeclaration', 'report_eventlist'));
            foreach ($eventinformation['typeparameter'] as $typeparameter) {
                $html .= html_writer::tag('dd', $typeparameter);
            }
        }

        if (isset($eventinformation['otherparameter'])) {
            $html .= html_writer::tag('dt', get_string('othereventparameters', 'report_eventlist'));
            foreach ($eventinformation['otherparameter'] as $otherparameter) {
                $html .= html_writer::tag('dd', $otherparameter);
            }
        }

                if (!empty($observerlist)) {
            $html .= html_writer::tag('dt', get_string('relatedobservers', 'report_eventlist'));
            foreach ($observerlist as $observer) {
                if ($observer->plugin == 'core') {
                    $html .= html_writer::tag('dd', $observer->plugin);
                } else {
                    $manager = get_string_manager();
                    $pluginstring = $observer->plugintype . '_' . $observer->plugin;
                    if ($manager->string_exists('pluginname', $pluginstring)) {
                        if (!empty($observer->parentplugin)) {
                            $string = get_string('pluginname', $pluginstring) . ' (' . $observer->parentplugin
                                    . ' ' . $pluginstring . ')';
                        } else {
                            $string = get_string('pluginname', $pluginstring) . ' (' . $pluginstring . ')';
                        }
                    } else {
                        $string = $observer->plugintype . ' ' . $observer->plugin;
                    }
                    $html .= html_writer::tag('dd', $string);
                }
            }
        }
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('dl');

        $pagecontent = new html_table();
        $pagecontent->data = array(array($html));
        $pagehtml = $titlehtml . html_writer::table($pagecontent);
        $pagehtml .= $this->output->footer();

        return $pagehtml;
    }
}
