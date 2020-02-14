<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_data_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path('data', '/MOODLE_BACKUP/COURSE/MODULES/MOD/DATA',
                        array(
                            'newfields' => array(
                                'introformat' => 0,
                                'assesstimestart' => 0,
                                'assesstimefinish' => 0,
                            )
                        )
                    ),
            new convert_path('data_field', '/MOODLE_BACKUP/COURSE/MODULES/MOD/DATA/FIELDS/FIELD')
        );
    }

    
    public function process_data($data) {
        global $CFG;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                if (!array_key_exists('asearchtemplate', $data)) {
            $data['asearchtemplate'] = null;
        }

                if (is_null($data['notification'])) {
            $data['notification'] = 0;
        }

                if ($CFG->texteditors !== 'textarea') {
            $data['intro'] = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_data');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

        
                $pattern = '/\#\#delete\#\#(\s+)\#\#approve\#\#/';
        $replacement = '##delete##$1##approve##$1##export##';
        $data['listtemplate'] = preg_replace($pattern, $replacement, $data['listtemplate']);
        $data['singletemplate'] = preg_replace($pattern, $replacement, $data['singletemplate']);

                
                $this->open_xml_writer("activities/data_{$this->moduleid}/data.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'data', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('data', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        $this->xmlwriter->begin_tag('fields');

        return $data;
    }

    
    public function process_data_field($data) {
                $this->write_xml('field', $data, array('/field/id'));
    }

    
    public function process_data_record($data) {
                    }

    
    public function on_data_end() {
                $this->xmlwriter->end_tag('fields');
        $this->xmlwriter->end_tag('data');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/data_{$this->moduleid}/inforef.xml");
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
