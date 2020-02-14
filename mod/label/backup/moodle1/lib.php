<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_label_handler extends moodle1_mod_handler {

    
    public function get_paths() {
        return array(
            new convert_path(
                'label', '/MOODLE_BACKUP/COURSE/MODULES/MOD/LABEL',
                array(
                    'renamefields' => array(
                        'content' => 'intro'
                    ),
                    'newfields' => array(
                        'introformat' => FORMAT_HTML
                    )
                )
            )
        );
    }

    
    public function process_label($data) {
                $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($instanceid);
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

                $fileman = $this->converter->get_file_manager($contextid, 'mod_label');

                $fileman->filearea = 'intro';
        $fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $fileman);

                $this->open_xml_writer("activities/label_{$moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/label_{$moduleid}/label.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'label', 'contextid' => $contextid));
        $this->write_xml('label', $data, array('/label/id'));
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

        return $data;
    }
}
