<?php



defined('MOODLE_INTERNAL') || die();


class moodle1_mod_imscp_handler extends moodle1_resource_successor_handler {

    
    protected $fileman = null;

    
    public function process_legacy_resource(array $data, array $raw = null) {

        $instanceid    = $data['id'];
        $currentcminfo = $this->get_cminfo($instanceid);
        $moduleid      = $currentcminfo['id'];
        $contextid     = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

                $imscp                  = array();
        $imscp['id']            = $data['id'];
        $imscp['name']          = $data['name'];
        $imscp['intro']         = $data['intro'];
        $imscp['introformat']   = $data['introformat'];
        $imscp['revision']      = 1;
        $imscp['keepold']       = 1;
        $imscp['structure']     = null;
        $imscp['timemodified']  = $data['timemodified'];

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_imscp');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $imscp['intro'] = moodle1_converter::migrate_referenced_files($imscp['intro'], $this->fileman);

                if ($data['reference']) {
            $packagename = basename($data['reference']);
            $packagepath = $this->converter->get_tempdir_path().'/moddata/resource/'.$data['id'].'/'.$packagename;
            if (file_exists($packagepath)) {
                $this->fileman->filearea = 'backup';
                $this->fileman->itemid   = 1;
                $this->fileman->migrate_file('moddata/resource/'.$data['id'].'/'.$packagename);
            } else {
                $this->log('missing imscp package', backup::LOG_WARNING, 'moddata/resource/'.$data['id'].'/'.$packagename);
            }
        }

                $this->fileman->filearea = 'content';
        $this->fileman->itemid   = 1;
        $this->fileman->migrate_directory('moddata/resource/'.$data['id']);

                $structure = $this->parse_structure($this->converter->get_tempdir_path().
                    '/moddata/resource/'.$data['id'].'/imsmanifest.xml', $imscp, $contextid);
        $imscp['structure'] = is_array($structure) ? serialize($structure) : null;

                $this->open_xml_writer("activities/imscp_{$moduleid}/imscp.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'imscp', 'contextid' => $contextid));
        $this->write_xml('imscp', $imscp, array('/imscp/id'));
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/imscp_{$moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }

    
    
    protected function parse_structure($manifestfilepath, $imscp, $context) {
        global $CFG;

        if (!file_exists($manifestfilepath)) {
            $this->log('missing imscp manifest file', backup::LOG_WARNING);
            return null;
        }
        $manifestfilecontents = file_get_contents($manifestfilepath);
        if (empty($manifestfilecontents)) {
            $this->log('empty imscp manifest file', backup::LOG_WARNING);
            return null;
        }

        require_once($CFG->dirroot.'/mod/imscp/locallib.php');
        return imscp_parse_manifestfile($manifestfilecontents, $imscp, $context);
    }
}
