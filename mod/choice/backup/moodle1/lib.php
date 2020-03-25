<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_choice_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path(
                'choice', '/MOODLE_BACKUP/COURSE/MODULES/MOD/CHOICE',
                array(
                    'renamefields' => array(
                        'text' => 'intro',
                        'format' => 'introformat',
                    ),
                    'newfields' => array(
                        'completionsubmit' => 0,
                    ),
                    'dropfields' => array(
                        'modtype'
                    ),
                )
            ),
            new convert_path('choice_options', '/MOODLE_BACKUP/COURSE/MODULES/MOD/CHOICE/OPTIONS'),
            new convert_path('choice_option', '/MOODLE_BACKUP/COURSE/MODULES/MOD/CHOICE/OPTIONS/OPTION'),
        );
    }

    
    public function process_choice($data) {

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_choice');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                $this->open_xml_writer("activities/choice_{$this->moduleid}/choice.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'choice', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('choice', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    
    public function on_choice_options_start() {
        $this->xmlwriter->begin_tag('options');
    }

    
    public function process_choice_option($data) {
        $this->write_xml('option', $data, array('/option/id'));
    }

    
    public function on_choice_options_end() {
        $this->xmlwriter->end_tag('options');
    }

    
    public function on_choice_end() {
                $this->xmlwriter->end_tag('choice');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/choice_{$this->moduleid}/inforef.xml");
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
