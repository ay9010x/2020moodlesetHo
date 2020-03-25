<?php



defined('MOODLE_INTERNAL') || die();

class moodle1_mod_lti_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {

        return array(
            new convert_path(
                'basiclti', '/MOODLE_BACKUP/COURSE/MODULES/MOD/LTI'
            )
        );

    }

    
    public function process_basiclti($data) {
        global $DB;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_lti');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                $this->open_xml_writer("activities/lti_{$this->moduleid}/lti.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
                'modulename' => 'lti', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('lti', array('id' => $instanceid));

        $ignorefields = array('id', 'modtype');
        if (!$DB->record_exists('lti_types', array('id' => $data['typeid']))) {
            $ntypeid = $DB->get_field('lti_types_config',
                                      'typeid',
                                      array('name' => 'toolurl', 'value' => $data['toolurl']),
                                      IGNORE_MULTIPLE);
            if ($ntypeid === false) {
                $ntypeid = $DB->get_field('lti_types_config',
                                          'typeid',
                                          array(),
                                          IGNORE_MULTIPLE);

            }
            if ($ntypeid === false) {
                $ntypeid = 0;
            }
            $data['typeid'] = $ntypeid;
        }
        if (empty($data['servicesalt'])) {
            $data['servicesalt'] = uniqid('', true);
        }
        foreach ($data as $field => $value) {
            if (!in_array($field, $ignorefields)) {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    
    public function on_basiclti_end() {
                $this->xmlwriter->end_tag('lti');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/lti_{$this->moduleid}/inforef.xml");
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

