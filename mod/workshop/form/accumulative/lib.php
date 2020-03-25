<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/lib.php');  require_once($CFG->libdir . '/gradelib.php');           

function workshopform_accumulative_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea !== 'description') {
        return false;
    }

    $itemid = (int)array_shift($args);     if (!$workshop = $DB->get_record('workshop', array('id' => $cm->instance))) {
        send_file_not_found();
    }

    if (!$dimension = $DB->get_record('workshopform_accumulative', array('id' => $itemid ,'workshopid' => $workshop->id))) {
        send_file_not_found();
    }

            $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/workshopform_accumulative/$filearea/$itemid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

        send_stored_file($file, 0, 0, $forcedownload, $options);
}


class workshop_accumulative_strategy implements workshop_strategy {

    
    const MINDIMS = 3;

    
    const ADDDIMS = 2;

    
    protected $workshop;

    
    protected $dimensions = null;

    
    protected $descriptionopts;

    
    public function __construct(workshop $workshop) {
        $this->workshop         = $workshop;
        $this->dimensions       = $this->load_fields();
        $this->descriptionopts  = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1);
    }

    
    public function get_edit_strategy_form($actionurl=null) {
        global $CFG;            global $PAGE;

        require_once(dirname(__FILE__) . '/edit_form.php');

        $fields             = $this->prepare_form_fields($this->dimensions);
        $nodimensions       = count($this->dimensions);
        $norepeatsdefault   = max($nodimensions + self::ADDDIMS, self::MINDIMS);
        $norepeats          = optional_param('norepeats', $norepeatsdefault, PARAM_INT);            $noadddims          = optional_param('noadddims', '', PARAM_ALPHA);                         if ($noadddims) {
            $norepeats += self::ADDDIMS;
        }

                $this->descriptionopts = array_merge(array('context' => $PAGE->context), $this->descriptionopts);

                for ($i = 0; $i < $nodimensions; $i++) {
                        $fields = file_prepare_standard_editor($fields, 'description__idx_'.$i, $this->descriptionopts,
                $PAGE->context, 'workshopform_accumulative', 'description', $fields->{'dimensionid__idx_'.$i});
        }

        $customdata = array();
        $customdata['workshop'] = $this->workshop;
        $customdata['strategy'] = $this;
        $customdata['norepeats'] = $norepeats;
        $customdata['descriptionopts'] = $this->descriptionopts;
        $customdata['current']  = $fields;
        $attributes = array('class' => 'editstrategyform');

        return new workshop_edit_accumulative_strategy_form($actionurl, $customdata, 'post', '', $attributes);
    }

    
    public function save_edit_strategy_form(stdclass $data) {
        global $DB, $PAGE;

        $workshopid = $data->workshopid;
        $norepeats  = $data->norepeats;

        $data       = $this->prepare_database_fields($data);
        $records    = $data->accumulative;          $todelete   = array();              
        for ($i=0; $i < $norepeats; $i++) {
            $record = $records[$i];
            if (0 == strlen(trim($record->description_editor['text']))) {
                if (!empty($record->id)) {
                                        $todelete[] = $record->id;
                }
                continue;
            }
            if (empty($record->id)) {
                                $record->id         = $DB->insert_record('workshopform_accumulative', $record);
            } else {
                                $DB->update_record('workshopform_accumulative', $record);
            }
                        $record = file_postupdate_standard_editor($record, 'description', $this->descriptionopts,
                                                      $PAGE->context, 'workshopform_accumulative', 'description', $record->id);
            $DB->update_record('workshopform_accumulative', $record);
        }
        $this->delete_dimensions($todelete);
    }

    
    public function get_assessment_form(moodle_url $actionurl=null, $mode='preview', stdclass $assessment=null, $editable=true, $options=array()) {
        global $CFG;            global $PAGE;
        global $DB;
        require_once(dirname(__FILE__) . '/assessment_form.php');

        $fields         = $this->prepare_form_fields($this->dimensions);
        $nodimensions   = count($this->dimensions);

                for ($i = 0; $i < $nodimensions; $i++) {
            $fields->{'description__idx_'.$i} = file_rewrite_pluginfile_urls($fields->{'description__idx_'.$i},
                'pluginfile.php', $PAGE->context->id, 'workshopform_accumulative', 'description', $fields->{'dimensionid__idx_'.$i});
        }

        if ('assessment' === $mode and !empty($assessment)) {
                        $grades = $this->get_current_assessment_data($assessment);
            $current = new stdclass();
            for ($i = 0; $i < $nodimensions; $i++) {
                $dimid = $fields->{'dimensionid__idx_'.$i};
                if (isset($grades[$dimid])) {
                    $current->{'gradeid__idx_'.$i}      = $grades[$dimid]->id;
                    $current->{'grade__idx_'.$i}        = $grades[$dimid]->grade;
                    $current->{'peercomment__idx_'.$i}  = $grades[$dimid]->peercomment;
                }
            }
        }

                $customdata['strategy'] = $this;
        $customdata['workshop'] = $this->workshop;
        $customdata['mode']     = $mode;
        $customdata['options']  = $options;

                $customdata['nodims']   = $nodimensions;
        $customdata['fields']   = $fields;
        $customdata['current']  = isset($current) ? $current : null;
        $attributes = array('class' => 'assessmentform accumulative');

        return new workshop_accumulative_assessment_form($actionurl, $customdata, 'post', '', $attributes, $editable);
    }

    
    public function save_assessment(stdclass $assessment, stdclass $data) {
        global $DB;

        if (!isset($data->nodims)) {
            throw new coding_exception('You did not send me the number of assessment dimensions to process');
        }
        for ($i = 0; $i < $data->nodims; $i++) {
            $grade = new stdclass();
            $grade->id = $data->{'gradeid__idx_' . $i};
            $grade->assessmentid = $assessment->id;
            $grade->strategy = 'accumulative';
            $grade->dimensionid = $data->{'dimensionid__idx_' . $i};
            $grade->grade = $data->{'grade__idx_' . $i};
            $grade->peercomment = $data->{'peercomment__idx_' . $i};
            $grade->peercommentformat = FORMAT_MOODLE;
            if (empty($grade->id)) {
                                $grade->id = $DB->insert_record('workshop_grades', $grade);
            } else {
                                $DB->update_record('workshop_grades', $grade);
            }
        }
        return $this->update_peer_grade($assessment);
    }

    
    public function form_ready() {
        if (count($this->dimensions) > 0) {
            return true;
        }
        return false;
    }

    
    public function get_assessments_recordset($restrict=null) {
        global $DB;

        $sql = 'SELECT s.id AS submissionid,
                       a.id AS assessmentid, a.weight AS assessmentweight, a.reviewerid, a.gradinggrade,
                       g.dimensionid, g.grade
                  FROM {workshop_submissions} s
                  JOIN {workshop_assessments} a ON (a.submissionid = s.id)
                  JOIN {workshop_grades} g ON (g.assessmentid = a.id AND g.strategy = :strategy)
                 WHERE s.example=0 AND s.workshopid=:workshopid';         $params = array('workshopid' => $this->workshop->id, 'strategy' => $this->workshop->strategy);

        if (is_null($restrict)) {
                    } elseif (!empty($restrict)) {
            list($usql, $uparams) = $DB->get_in_or_equal($restrict, SQL_PARAMS_NAMED);
            $sql .= " AND a.reviewerid $usql";
            $params = array_merge($params, $uparams);
        } else {
            throw new coding_exception('Empty value is not a valid parameter here');
        }

        $sql .= ' ORDER BY s.id'; 
        return $DB->get_recordset_sql($sql, $params);
    }

    
    public function get_dimensions_info() {
        global $DB;

        $sql = 'SELECT d.id, d.grade, d.weight, s.scale
                  FROM {workshopform_accumulative} d
             LEFT JOIN {scale} s ON (d.grade < 0 AND -d.grade = s.id)
                 WHERE d.workshopid = :workshopid';
        $params = array('workshopid' => $this->workshop->id);
        $dimrecords = $DB->get_records_sql($sql, $params);
        $diminfo = array();
        foreach ($dimrecords as $dimid => $dimrecord) {
            $diminfo[$dimid] = new stdclass();
            $diminfo[$dimid]->id = $dimid;
            $diminfo[$dimid]->weight = $dimrecord->weight;
            if ($dimrecord->grade < 0) {
                                $diminfo[$dimid]->min = 1;
                $diminfo[$dimid]->max = count(explode(',', $dimrecord->scale));
            } else {
                                $diminfo[$dimid]->min = 0;
                $diminfo[$dimid]->max = grade_floatval($dimrecord->grade);
            }
        }
        return $diminfo;
    }

    
    public static function scale_used($scaleid, $workshopid=null) {
        global $DB;

        $conditions['grade'] = -$scaleid;
        if (!is_null($workshopid)) {
            $conditions['workshopid'] = $workshopid;
        }
        return $DB->record_exists('workshopform_accumulative', $conditions);
    }

    
    public static function delete_instance($workshopid) {
        global $DB;

        $DB->delete_records('workshopform_accumulative', array('workshopid' => $workshopid));
    }

            
    
    protected function load_fields() {
        global $DB;

        $sql = 'SELECT *
                  FROM {workshopform_accumulative}
                 WHERE workshopid = :workshopid
                 ORDER BY sort';
        $params = array('workshopid' => $this->workshop->id);

        return $DB->get_records_sql($sql, $params);
    }

    
    protected function prepare_form_fields(array $raw) {

        $formdata = new stdclass();
        $key = 0;
        foreach ($raw as $dimension) {
            $formdata->{'dimensionid__idx_' . $key}             = $dimension->id;
            $formdata->{'description__idx_' . $key}             = $dimension->description;
            $formdata->{'description__idx_' . $key.'format'}    = $dimension->descriptionformat;
            $formdata->{'grade__idx_' . $key}                   = $dimension->grade;
            $formdata->{'weight__idx_' . $key}                  = $dimension->weight;
            $key++;
        }
        return $formdata;
    }

    
    protected function delete_dimensions(array $ids) {
        global $DB, $PAGE;

        $fs = get_file_storage();
        foreach ($ids as $id) {
            if (!empty($id)) {                   $fs->delete_area_files($PAGE->context->id, 'workshopform_accumulative', 'description', $id);
            }
        }
        $DB->delete_records_list('workshopform_accumulative', 'id', $ids);
    }

    
    protected function prepare_database_fields(stdclass $raw) {
        global $PAGE;

        $cook               = new stdclass();         $cook->accumulative = array();        
        for ($i = 0; $i < $raw->norepeats; $i++) {
            $cook->accumulative[$i]                     = new stdclass();
            $cook->accumulative[$i]->id                 = $raw->{'dimensionid__idx_'.$i};
            $cook->accumulative[$i]->workshopid         = $this->workshop->id;
            $cook->accumulative[$i]->sort               = $i + 1;
            $cook->accumulative[$i]->description_editor = $raw->{'description__idx_'.$i.'_editor'};
            $cook->accumulative[$i]->grade              = $raw->{'grade__idx_'.$i};
            $cook->accumulative[$i]->weight             = $raw->{'weight__idx_'.$i};
        }
        return $cook;
    }

    
    protected function get_current_assessment_data(stdclass $assessment) {
        global $DB;

        if (empty($this->dimensions)) {
            return array();
        }
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($this->dimensions), SQL_PARAMS_NAMED);
                $sql = "SELECT dimensionid, wg.*
                  FROM {workshop_grades} wg
                 WHERE assessmentid = :assessmentid AND strategy= :strategy AND dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'accumulative');
        $params = array_merge($params, $dimparams);

        return $DB->get_records_sql($sql, $params);
    }

    
    protected function update_peer_grade(stdclass $assessment) {
        $grades     = $this->get_current_assessment_data($assessment);
        $suggested  = $this->calculate_peer_grade($grades);
        if (!is_null($suggested)) {
            $this->workshop->set_peer_grade($assessment->id, $suggested);
        }
        return $suggested;
    }

    
    protected function calculate_peer_grade(array $grades) {

        if (empty($grades)) {
            return null;
        }
        $sumgrades  = 0;
        $sumweights = 0;
        foreach ($grades as $grade) {
            $dimension = $this->dimensions[$grade->dimensionid];
            if ($dimension->weight < 0) {
                throw new coding_exception('Negative weights are not supported any more. Something is wrong with your data');
            }
            if (grade_floats_equal($dimension->weight, 0) or grade_floats_equal($dimension->grade, 0)) {
                                continue;
            }
            if ($dimension->grade < 0) {
                                $scaleid    = -$dimension->grade;
                $sumgrades  += $this->scale_to_grade($scaleid, $grade->grade) * $dimension->weight * 100;
                $sumweights += $dimension->weight;
            } else {
                                $sumgrades  += ($grade->grade / $dimension->grade) * $dimension->weight * 100;
                $sumweights += $dimension->weight;
            }
        }

        if ($sumweights === 0) {
            return 0;
        }
        return grade_floatval($sumgrades / $sumweights);
    }

    
    protected function scale_to_grade($scaleid, $item) {
        global $DB;

        
        static $numofscaleitems = array();

        if (!isset($numofscaleitems[$scaleid])) {
            $scale = $DB->get_field('scale', 'scale', array('id' => $scaleid), MUST_EXIST);
            $items = explode(',', $scale);
            $numofscaleitems[$scaleid] = count($items);
            unset($scale);
            unset($items);
        }

        if ($numofscaleitems[$scaleid] <= 1) {
            throw new coding_exception('Invalid scale definition, no scale items found');
        }

        if ($item <= 0 or $numofscaleitems[$scaleid] < $item) {
            throw new coding_exception('Invalid scale item number');
        }

        return ($item - 1) / ($numofscaleitems[$scaleid] - 1);
    }
}
