<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_workshop_handler extends moodle1_mod_handler {

    
    protected $currentworkshop = null;

    
    protected $currentcminfo = null;

    
    protected $newelementids = array();

    
    protected $fileman = null;

    
    protected $inforefman = null;

    
    private $strategyhandlers = null;

    
    private $currentelementid = null;

    
    public function get_paths() {
        return array(
            new convert_path('workshop', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WORKSHOP'),
            new convert_path('workshop_elements', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WORKSHOP/ELEMENTS'),
            new convert_path(
                'workshop_element', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WORKSHOP/ELEMENTS/ELEMENT',
                array(
                    'dropfields' => array(
                        'stddev',
                        'totalassessments',
                    ),
                )
            ),
            new convert_path('workshop_element_rubric', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WORKSHOP/ELEMENTS/ELEMENT/RUBRICS/RUBRIC'),
        );
    }

    
    public function process_workshop($data, $raw) {

                $fakerecord = (object)$data;
        $fakerecord->course = 12345678;
        $this->currentworkshop = (array)workshop_upgrade_transform_instance($fakerecord);
        unset($this->currentworkshop['course']);

                $this->currentworkshop['id']                        = $data['id'];
        $this->currentworkshop['evaluation']                = 'best';
        $this->currentworkshop['examplesmode']              = workshop::EXAMPLES_VOLUNTARY;
        $this->currentworkshop['gradedecimals']             = 0;
        $this->currentworkshop['instructauthors']           = '';
        $this->currentworkshop['instructauthorsformat']     = FORMAT_HTML;
        $this->currentworkshop['instructreviewers']         = '';
        $this->currentworkshop['instructreviewersformat']   = FORMAT_HTML;
        $this->currentworkshop['latesubmissions']           = 0;
        $this->currentworkshop['conclusion']                = '';
        $this->currentworkshop['conclusionformat']          = FORMAT_HTML;

        foreach (array('submissionend', 'submissionstart', 'assessmentend', 'assessmentstart') as $field) {
            if (!array_key_exists($field, $this->currentworkshop)) {
                $this->currentworkshop[$field] = null;
            }
        }

                $instanceid          = $data['id'];
        $this->currentcminfo = $this->get_cminfo($instanceid);
        $moduleid            = $this->currentcminfo['id'];
        $contextid           = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

                $this->inforefman = $this->converter->get_inforef_manager('activity', $moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_workshop');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $this->currentworkshop['intro'] = moodle1_converter::migrate_referenced_files($this->currentworkshop['intro'], $this->fileman);

                $this->open_xml_writer("activities/workshop_{$moduleid}/workshop.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'workshop', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('workshop', array('id' => $instanceid));

        foreach ($this->currentworkshop as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $this->currentworkshop;
    }

    
    public function on_workshop_elements_start() {

        $this->xmlwriter->begin_tag('subplugin_workshopform_'.$this->currentworkshop['strategy'].'_workshop');

                $handler = $this->get_strategy_handler($this->currentworkshop['strategy']);
        $handler->use_xml_writer($this->xmlwriter);
        $handler->on_elements_start();
    }

    
    public function process_workshop_element($data, $raw) {

                $data['id'] = $this->converter->get_nextid();
        $this->currentelementid = $data['id'];
        $this->newelementids[$data['elementno']] = $data['id'];

                $handler = $this->get_strategy_handler($this->currentworkshop['strategy']);
        return $handler->process_legacy_element($data, $raw);
    }

    
    public function process_workshop_element_rubric($data, $raw) {
        if ($this->currentworkshop['strategy'] == 'rubric') {
            $handler = $this->get_strategy_handler('rubric');
            $data['elementid'] = $this->currentelementid;
            $handler->process_legacy_rubric($data, $raw);
        }
    }

    
    public function on_workshop_element_end() {
                $handler = $this->get_strategy_handler($this->currentworkshop['strategy']);
        $handler->on_legacy_element_end();
    }

    
    public function on_workshop_elements_end() {
                $handler = $this->get_strategy_handler($this->currentworkshop['strategy']);
        $handler->on_elements_end();

                $this->xmlwriter->end_tag('subplugin_workshopform_'.$this->currentworkshop['strategy'].'_workshop');

                $this->write_xml('examplesubmissions', array());
        $this->write_xml('submissions', array());
        $this->write_xml('aggregations', array());
    }

    
    public function on_workshop_end() {
                $this->xmlwriter->end_tag('workshop');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->inforefman->add_refs('file', $this->fileman->get_fileids());
        $moduleid = $this->currentcminfo['id'];
        $this->open_xml_writer("activities/workshop_{$moduleid}/inforef.xml");
        $this->inforefman->write_refs($this->xmlwriter);
        $this->close_xml_writer();

                $this->currentworkshop = null;
        $this->currentcminfo   = null;
        $this->newelementids   = array();
    }

    
    public function get_current_workshop() {
        return $this->currentworkshop;
    }

    
    public function get_inforef_manager() {
        return $this->inforefman;
    }

    
    
    protected function get_strategy_handler($strategy) {
        global $CFG; 
        if (is_null($this->strategyhandlers)) {
            $this->strategyhandlers = array();
            $subplugins = core_component::get_plugin_list('workshopform');
            foreach ($subplugins as $name => $dir) {
                $handlerfile  = $dir.'/backup/moodle1/lib.php';
                $handlerclass = "moodle1_workshopform_{$name}_handler";
                if (!file_exists($handlerfile)) {
                    continue;
                }
                require_once($handlerfile);

                if (!class_exists($handlerclass)) {
                    throw new moodle1_convert_exception('missing_handler_class', $handlerclass);
                }
                $this->log('preparing workshop grading strategy handler', backup::LOG_DEBUG, $handlerclass);
                $this->strategyhandlers[$name] = new $handlerclass($this, $name);
                if (!$this->strategyhandlers[$name] instanceof moodle1_workshopform_handler) {
                    throw new moodle1_convert_exception('wrong_handler_class', get_class($this->strategyhandlers[$name]));
                }
            }
        }

        if (!isset($this->strategyhandlers[$strategy])) {
            throw new moodle1_convert_exception('usupported_subplugin', 'workshopform_'.$strategy);
        }

        return $this->strategyhandlers[$strategy];
    }
}



abstract class moodle1_workshopform_handler extends moodle1_submod_handler {

    
    public function __construct(moodle1_mod_handler $workshophandler, $subpluginname) {
        parent::__construct($workshophandler, 'workshopform', $subpluginname);
    }

    
    public function use_xml_writer(xml_writer $xmlwriter) {
        $this->xmlwriter = $xmlwriter;
    }

    
    public function on_elements_start() {
            }

    
    public function process_legacy_element(array $data, array $raw) {
        return $data;
    }

    
    public function on_legacy_element_end() {
            }

    
    public function on_elements_end() {
            }
}


function workshop_upgrade_transform_instance(stdClass $old) {
    global $CFG;
    require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');

    $new                = new stdClass();
    $new->course        = $old->course;
    $new->name          = $old->name;
    $new->intro         = $old->description;
    $new->introformat   = $old->format;
    $new->nattachments  = $old->nattachments;
    $new->maxbytes      = $old->maxbytes;
    $new->grade         = $old->grade;
    $new->gradinggrade  = $old->gradinggrade;
    $new->phase         = workshop::PHASE_CLOSED;
    $new->timemodified  = time();
    if ($old->ntassessments > 0) {
        $new->useexamples = 1;
    } else {
        $new->useexamples = 0;
    }
    $new->usepeerassessment = 1;
    $new->useselfassessment = $old->includeself;
    switch ($old->gradingstrategy) {
    case 0:         $new->strategy = 'comments';
        break;
    case 1:         $new->strategy = 'accumulative';
        break;
    case 2:         $new->strategy = 'numerrors';
        break;
    case 3:         $new->strategy = 'rubric';
        break;
    case 4:         $new->strategy = 'rubric';
        break;
    }
    if ($old->submissionstart < $old->submissionend) {
        $new->submissionstart = $old->submissionstart;
        $new->submissionend   = $old->submissionend;
    }
    if ($old->assessmentstart < $old->assessmentend) {
        $new->assessmentstart = $old->assessmentstart;
        $new->assessmentend   = $old->assessmentend;
    }

    return $new;
}