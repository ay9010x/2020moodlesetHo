<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_assignment_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    private $currentsubpluginname = null;

    
    private $subpluginhandlers = null;

    
    public function get_paths() {
        return array(
            new convert_path(
                'assignment', '/MOODLE_BACKUP/COURSE/MODULES/MOD/ASSIGNMENT',
                array(
                    'renamefields' => array(
                        'description' => 'intro',
                        'format' => 'introformat',
                    )
                )
            )
                                );
    }

    
    public function process_assignment($data) {
        global $CFG;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->currentsubpluginname = $data['assignmenttype'];

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_assignment');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                if ($CFG->texteditors !== 'textarea') {
            $data['intro'] = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->open_xml_writer("activities/assignment_{$this->moduleid}/assignment.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'assignment', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('assignment', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

                $this->handle_assignment_subplugin($data);

        $this->xmlwriter->begin_tag('submissions');

        return $data;
    }

    
    public function process_assignment_submission($data) {
                    }

    
    public function handle_assignment_subplugin($data) {
        $handler = $this->get_subplugin_handler($this->currentsubpluginname);
        $this->log('Instantiated assignment subplugin handler for '.$this->currentsubpluginname.'.', backup::LOG_DEBUG);
        $handler->use_xml_writer($this->xmlwriter);

        $this->log('Processing assignment subplugin handler callback for '.$this->currentsubpluginname.'.', backup::LOG_DEBUG);
        $handler->append_subplugin_data($data);
    }

    
    public function on_assignment_end() {
                $this->xmlwriter->end_tag('submissions');
        $this->xmlwriter->end_tag('assignment');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/assignment_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }

    
    
    protected function get_subplugin_handler($subplugin) {
        global $CFG; 
        if (is_null($this->subpluginhandlers)) {
            $this->subpluginhandlers = array();
            $subplugins = core_component::get_plugin_list('assignment');
            foreach ($subplugins as $name => $dir) {
                $handlerfile  = $dir.'/backup/moodle1/lib.php';
                $handlerclass = "moodle1_mod_assignment_{$name}_subplugin_handler";
                if (!file_exists($handlerfile)) {
                    continue;
                }
                require_once($handlerfile);

                if (!class_exists($handlerclass)) {
                    throw new moodle1_convert_exception('missing_handler_class', $handlerclass);
                }
                $this->log('preparing assignment subplugin handler', backup::LOG_DEBUG, $handlerclass);
                $this->subpluginhandlers[$name] = new $handlerclass($this, $name);
                if (!$this->subpluginhandlers[$name] instanceof moodle1_assignment_subplugin_handler) {
                    throw new moodle1_convert_exception('wrong_handler_class', get_class($this->subpluginhandlers[$name]));
                }
            }
        }

        if (!isset($this->subpluginhandlers[$subplugin])) {
                        $this->subpluginhandlers[$subplugin] = new moodle1_assignment_unsupported_subplugin_handler($this, $subplugin);
        }

        return $this->subpluginhandlers[$subplugin];
    }
}



abstract class moodle1_assignment_subplugin_handler extends moodle1_submod_handler {

    
    public function __construct(moodle1_mod_handler $assignmenthandler, $subpluginname) {
        parent::__construct($assignmenthandler, 'assignment', $subpluginname);
    }

    
    public function use_xml_writer(xml_writer $xmlwriter) {
        $this->xmlwriter = $xmlwriter;
    }

    

    public function append_subplugin_data($data) {
                return false;

            }
}


class moodle1_assignment_unsupported_subplugin_handler extends moodle1_assignment_subplugin_handler {
}
