<?php



defined('MOODLE_INTERNAL') || die();


class moodle1_mod_feedback_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path(
                'feedback', '/MOODLE_BACKUP/COURSE/MODULES/MOD/FEEDBACK',
                array(
                    'renamefields' => array(
                        'summary' => 'intro',
                        'pageaftersub' => 'page_after_submit',
                    ),
                    'newfields' => array(
                        'autonumbering' => 1,
                        'site_after_submit' => '',
                        'introformat' => 0,
                        'page_after_submitformat' => 0,
                        'completionsubmit' => 0,
                    ),
                )
            ),
            new convert_path(
                'feedback_item', '/MOODLE_BACKUP/COURSE/MODULES/MOD/FEEDBACK/ITEMS/ITEM',
                array (
                    'newfields' => array(
                        'label' => '',
                        'options' => '',
                        'dependitem' => 0,
                        'dependvalue' => '',
                    ),
                )
            ),
        );
    }

    
    public function process_feedback($data) {
        global $CFG;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_feedback');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                if ($CFG->texteditors !== 'textarea') {
            $data['intro'] = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->open_xml_writer("activities/feedback_{$this->moduleid}/feedback.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'feedback', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('feedback', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        $this->xmlwriter->begin_tag('items');

        return $data;
    }

    
    public function process_feedback_item($data) {
        $this->write_xml('item', $data, array('/item/id'));
    }

    
    public function on_feedback_end() {
                $this->xmlwriter->end_tag('items');
        $this->xmlwriter->end_tag('feedback');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/feedback_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }
}
