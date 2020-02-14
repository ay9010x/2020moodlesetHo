<?php



defined('MOODLE_INTERNAL') || die();


class moodle1_mod_scorm_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path('scorm', '/MOODLE_BACKUP/COURSE/MODULES/MOD/SCORM',
                array(
                    'newfields' => array(
                        'whatgrade' => 0,
                        'scormtype' => 'local',
                        'sha1hash' => null,
                        'revision' => '0',
                        'forcecompleted' => 0,
                        'forcenewattempt' => 0,
                        'lastattemptlock' => 0,
                        'masteryoverride' => 1,
                        'displayattemptstatus' => 1,
                        'displaycoursestructure' => 0,
                        'timeopen' => '0',
                        'timeclose' => '0',
                        'introformat' => '0',
                    ),
                    'renamefields' => array(
                        'summary' => 'intro'
                    )
                )
            ),
            new convert_path('scorm_sco', '/MOODLE_BACKUP/COURSE/MODULES/MOD/SCORM/SCOES/SCO')
        );
    }

    
    public function process_scorm($data) {
        global $CFG;

                $instanceid     = $data['id'];
        $currentcminfo  = $this->get_cminfo($instanceid);
        $this->moduleid = $currentcminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                if ($CFG->texteditors !== 'textarea') {
            $data['intro']       = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_scorm');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                $backupinfo = $this->converter->get_stash('backup_info');
        if ($backupinfo['moodle_version'] < 2007110503) {
                                    $data['grademethod'] = $data['grademethod']%10;
        }

                $ismanifest = preg_match('/imsmanifest\.xml$/', $data['reference']);
        $iszippif = preg_match('/.(zip|pif)$/', $data['reference']);
        $isurl = preg_match('/^((http|https):\/\/|www\.)/', $data['reference']);
        if ($isurl) {
            if ($ismanifest) {
                $data['scormtype'] = 'external';
            } else if ($iszippif) {
                $data['scormtype'] = 'localtype';
            }
        }

                $this->fileman->filearea = 'package';
        $this->fileman->itemid   = 0;
        $this->fileman->migrate_file('course_files/'.$data['reference']);

                $this->open_xml_writer("activities/scorm_{$this->moduleid}/scorm.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'scorm', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('scorm', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        $this->xmlwriter->begin_tag('scoes');

        return $data;
    }

    
    public function process_scorm_sco($data) {
        $this->write_xml('sco', $data, array('/sco/id'));
    }

    
    public function on_scorm_end() {
                $this->xmlwriter->end_tag('scoes');
        $this->xmlwriter->end_tag('scorm');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/scorm_{$this->moduleid}/inforef.xml");
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
