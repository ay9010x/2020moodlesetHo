<?php


defined('MOODLE_INTERNAL') || die;


class report_loglive_renderer extends plugin_renderer_base {

    
    public function render_report_loglive_renderable(report_loglive_renderable $reportloglive) {
        debugging('Do not call this method. Please call $renderer->render($reportloglive) instead.', DEBUG_DEVELOPER);
        return $this->render($reportloglive);
    }

    
    protected function render_report_loglive(report_loglive_renderable $reportloglive) {
        if (empty($reportloglive->selectedlogreader)) {
            return $this->output->notification(get_string('nologreaderenabled', 'report_loglive'), 'notifyproblem');
        }

        $table = $reportloglive->get_table();
        return $this->render_table($table, $reportloglive->perpage);
    }

    
    public function reader_selector(report_loglive_renderable $reportloglive) {
        $readers = $reportloglive->get_readers(true);
        if (count($readers) <= 1) {
                        return '';
        }
        $select = new single_select($reportloglive->url, 'logreader', $readers, $reportloglive->selectedlogreader, null);
        $select->set_label(get_string('selectlogreader', 'report_loglive'));
        return $this->output->render($select);
    }

    
    public function toggle_liveupdate_button(report_loglive_renderable $reportloglive) {
                if ($reportloglive->page == 0 && $reportloglive->selectedlogreader) {
            echo html_writer::tag('button' , get_string('pause', 'report_loglive'), array('id' => 'livelogs-pause-button'));
            $icon = new pix_icon('i/loading_small', 'loading', 'moodle', array('class' => 'spinner'));
            return $this->output->render($icon);
        }
        return '';
    }

    
    protected function render_table(report_loglive_table_log $table, $perpage) {
        $o = '';
        ob_start();
        $table->out($perpage, true);
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }
}