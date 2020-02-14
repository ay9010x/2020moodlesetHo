<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_folder_handler extends moodle1_resource_successor_handler {

    
    protected $fileman = null;

    
    public function process_legacy_resource(array $data, array $raw = null) {
                $instanceid     = $data['id'];
        $currentcminfo  = $this->get_cminfo($instanceid);
        $moduleid       = $currentcminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

                $folder                 = array();
        $folder['id']           = $data['id'];
        $folder['name']         = $data['name'];
        $folder['intro']        = $data['intro'];
        $folder['introformat']  = $data['introformat'];
        $folder['revision']     = 1;
        $folder['timemodified'] = $data['timemodified'];

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_folder');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $folder['intro'] = moodle1_converter::migrate_referenced_files($folder['intro'], $this->fileman);

                $this->fileman->filearea = 'content';
        $this->fileman->itemid   = 0;
        if (empty($data['reference'])) {
            $this->fileman->migrate_directory('course_files');
        } else {
            $this->fileman->migrate_directory('course_files/'.$data['reference']);
        }

                $this->open_xml_writer("activities/folder_{$moduleid}/folder.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'folder', 'contextid' => $contextid));
        $this->write_xml('folder', $folder, array('/folder/id'));
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/folder_{$moduleid}/inforef.xml");
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
