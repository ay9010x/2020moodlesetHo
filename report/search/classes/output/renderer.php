<?php



namespace report_search\output;

defined('MOODLE_INTERNAL') || die();


class renderer extends \plugin_renderer_base {

    
    public function render_report($form, $searchareas, $areasconfig) {

        $table = new \html_table();
        $table->head = array(get_string('searcharea', 'search'), get_string('newestdocindexed', 'report_search'),
            get_string('lastrun', 'report_search'));

        foreach ($searchareas as $areaid => $searcharea) {
            $cname = new \html_table_cell($searcharea->get_visible_name());
            $clastrun = new \html_table_cell($areasconfig[$areaid]->lastindexrun);
            if ($areasconfig[$areaid]->indexingstart) {
                $timediff = $areasconfig[$areaid]->indexingend - $areasconfig[$areaid]->indexingstart;
                $ctimetaken = new \html_table_cell($timediff . ' , ' .
                                                  $areasconfig[$areaid]->docsprocessed . ' , ' .
                                                  $areasconfig[$areaid]->recordsprocessed . ' , ' .
                                                  $areasconfig[$areaid]->docsignored);
            } else {
                $ctimetaken = '';
            }
            $row = new \html_table_row(array($cname, $clastrun, $ctimetaken));
            $table->data[] = $row;
        }

                $content = \html_writer::table($table);

                $formcontents = $this->output->heading(get_string('indexform', 'report_search'), 3) .
            $this->output->notification(get_string('indexinginfo', 'report_search'), 'notifymessage') . $form->render();
        $content .= \html_writer::tag('div', $formcontents, array('id' => 'searchindexform'));

        return $content;
    }

}
