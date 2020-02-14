<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/lib.php');  require_once($CFG->libdir . '/gradelib.php');           

function workshopform_numerrors_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
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

    if (!$dimension = $DB->get_record('workshopform_numerrors', array('id' => $itemid ,'workshopid' => $workshop->id))) {
        send_file_not_found();
    }

            $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/workshopform_numerrors/$filearea/$itemid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

        send_stored_file($file, 0, 0, $forcedownload, $options);
}


class workshop_numerrors_strategy implements workshop_strategy {

    
    const MINDIMS = 3;

    
    const ADDDIMS = 2;

    
    protected $workshop;

    
    protected $dimensions = null;

    
    protected $mappings = null;

    
    protected $descriptionopts;

    
    public function __construct(workshop $workshop) {
        $this->workshop         = $workshop;
        $this->dimensions       = $this->load_fields();
        $this->mappings         = $this->load_mappings();
        $this->descriptionopts  = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1);
    }

    
    public function get_edit_strategy_form($actionurl=null) {
        global $CFG;            global $PAGE;

        require_once(dirname(__FILE__) . '/edit_form.php');

        $fields             = $this->prepare_form_fields($this->dimensions, $this->mappings);
        $nodimensions       = count($this->dimensions);
        $norepeatsdefault   = max($nodimensions + self::ADDDIMS, self::MINDIMS);
        $norepeats          = optional_param('norepeats', $norepeatsdefault, PARAM_INT);            $noadddims          = optional_param('noadddims', '', PARAM_ALPHA);                         if ($noadddims) {
            $norepeats += self::ADDDIMS;
        }

                $this->descriptionopts = array_merge(array('context' => $PAGE->context), $this->descriptionopts);

                for ($i = 0; $i < $nodimensions; $i++) {
                        $fields = file_prepare_standard_editor($fields, 'description__idx_'.$i, $this->descriptionopts,
                $PAGE->context, 'workshopform_numerrors', 'description', $fields->{'dimensionid__idx_'.$i});
        }

        $customdata = array();
        $customdata['workshop'] = $this->workshop;
        $customdata['strategy'] = $this;
        $customdata['norepeats'] = $norepeats;
        $customdata['nodimensions'] = $nodimensions;
        $customdata['descriptionopts'] = $this->descriptionopts;
        $customdata['current']  = $fields;
        $attributes = array('class' => 'editstrategyform');

        return new workshop_edit_numerrors_strategy_form($actionurl, $customdata, 'post', '', $attributes);
    }

    
    public function save_edit_strategy_form(stdclass $data) {
        global $DB, $PAGE;

        $workshopid = $data->workshopid;
        $norepeats  = $data->norepeats;

        $data       = $this->prepare_database_fields($data);
        $records    = $data->numerrors;         $mappings   = $data->mappings;          $todelete   = array();                  $maxnonegative = 0;             
        for ($i=0; $i < $norepeats; $i++) {
            $record = $records[$i];
            if (0 == strlen(trim($record->description_editor['text']))) {
                if (!empty($record->id)) {
                                        $todelete[] = $record->id;
                }
                continue;
            }
            if (empty($record->id)) {
                                $record->id = $DB->insert_record('workshopform_numerrors', $record);
            } else {
                                $DB->update_record('workshopform_numerrors', $record);
            }
            $maxnonegative += $record->weight;
                        $record = file_postupdate_standard_editor($record, 'description', $this->descriptionopts, $PAGE->context,
                                                      'workshopform_numerrors', 'description', $record->id);
            $DB->update_record('workshopform_numerrors', $record);
        }
        $this->delete_dimensions($todelete);

                $todelete = array();
        foreach ($data->mappings as $nonegative => $grade) {
            if (is_null($grade)) {
                                $todelete[] = $nonegative;
                continue;
            }
            if (isset($this->mappings[$nonegative])) {
                $DB->set_field('workshopform_numerrors_map', 'grade', $grade,
                            array('workshopid' => $this->workshop->id, 'nonegative' => $nonegative));
            } else {
                $DB->insert_record('workshopform_numerrors_map',
                            (object)array('workshopid' => $this->workshop->id, 'nonegative' => $nonegative, 'grade' => $grade));
            }
        }
                if (!empty($todelete)) {
            list($insql, $params) = $DB->get_in_or_equal($todelete, SQL_PARAMS_NAMED);
            $insql = "nonegative $insql OR ";
        } else {
            $insql = '';
        }
        $sql = "DELETE FROM {workshopform_numerrors_map}
                      WHERE (($insql nonegative > :maxnonegative) AND (workshopid = :workshopid))";
        $params['maxnonegative'] = $maxnonegative;
        $params['workshopid']   = $this->workshop->id;
        $DB->execute($sql, $params);
    }

    
    public function get_assessment_form(moodle_url $actionurl=null, $mode='preview', stdclass $assessment=null, $editable=true, $options=array()) {
        global $CFG;            global $PAGE;
        global $DB;
        require_once(dirname(__FILE__) . '/assessment_form.php');

        $fields         = $this->prepare_form_fields($this->dimensions, $this->mappings);
        $nodimensions   = count($this->dimensions);

                for ($i = 0; $i < $nodimensions; $i++) {
            $fields->{'description__idx_'.$i} = file_rewrite_pluginfile_urls($fields->{'description__idx_'.$i},
                'pluginfile.php', $PAGE->context->id, 'workshopform_numerrors', 'description', $fields->{'dimensionid__idx_'.$i});
        }

        if ('assessment' === $mode and !empty($assessment)) {
                        $grades = $this->get_current_assessment_data($assessment);
            $current = new stdclass();
            for ($i = 0; $i < $nodimensions; $i++) {
                $dimid = $fields->{'dimensionid__idx_'.$i};
                if (isset($grades[$dimid])) {
                    $current->{'gradeid__idx_'.$i}      = $grades[$dimid]->id;
                    $current->{'grade__idx_'.$i}        = ($grades[$dimid]->grade == 0 ? -1 : 1);
                    $current->{'peercomment__idx_'.$i}  = $grades[$dimid]->peercomment;
                }
            }
        }

                $customdata['workshop'] = $this->workshop;
        $customdata['strategy'] = $this;
        $customdata['mode']     = $mode;
        $customdata['options']  = $options;

                $customdata['nodims']   = $nodimensions;
        $customdata['fields']   = $fields;
        $customdata['current']  = isset($current) ? $current : null;
        $attributes = array('class' => 'assessmentform numerrors');

        return new workshop_numerrors_assessment_form($actionurl, $customdata, 'post', '', $attributes, $editable);
    }

    
    public function save_assessment(stdclass $assessment, stdclass $data) {
        global $DB;

        if (!isset($data->nodims)) {
            throw new coding_exception('You did not send me the number of assessment dimensions to process');
        }
        for ($i = 0; $i < $data->nodims; $i++) {
            $grade = new stdclass();
            $grade->id                  = $data->{'gradeid__idx_' . $i};
            $grade->assessmentid        = $assessment->id;
            $grade->strategy            = 'numerrors';
            $grade->dimensionid         = $data->{'dimensionid__idx_' . $i};
            $grade->grade               = ($data->{'grade__idx_' . $i} <= 0 ? 0 : 1);
            $grade->peercomment         = $data->{'peercomment__idx_' . $i};
            $grade->peercommentformat   = FORMAT_HTML;
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

        $params = array('workshopid' => $this->workshop->id);
        $dimrecords = $DB->get_records('workshopform_numerrors', array('workshopid' => $this->workshop->id), 'sort', 'id,weight');
        foreach ($dimrecords as $dimid => $dimrecord) {
            $dimrecords[$dimid]->min = 0;
            $dimrecords[$dimid]->max = 1;
        }
        return $dimrecords;
    }

    
    public static function scale_used($scaleid, $workshopid=null) {
        return false;
    }

    
    public static function delete_instance($workshopid) {
        global $DB;

        $DB->delete_records('workshopform_numerrors', array('workshopid' => $workshopid));
        $DB->delete_records('workshopform_numerrors_map', array('workshopid' => $workshopid));
    }

            
    
    protected function load_fields() {
        global $DB;

        $sql = 'SELECT *
                  FROM {workshopform_numerrors}
                 WHERE workshopid = :workshopid
                 ORDER BY sort';
        $params = array('workshopid' => $this->workshop->id);

        return $DB->get_records_sql($sql, $params);
    }

    
    protected function load_mappings() {
        global $DB;
        return $DB->get_records('workshopform_numerrors_map', array('workshopid' => $this->workshop->id), 'nonegative',
                                'nonegative,grade');     }

    
    protected function prepare_form_fields(array $dims, array $maps) {

        $formdata = new stdclass();
        $key = 0;
        foreach ($dims as $dimension) {
            $formdata->{'dimensionid__idx_' . $key}             = $dimension->id;
            $formdata->{'description__idx_' . $key}             = $dimension->description;
            $formdata->{'description__idx_' . $key.'format'}    = $dimension->descriptionformat;
            $formdata->{'grade0__idx_' . $key}                  = $dimension->grade0;
            $formdata->{'grade1__idx_' . $key}                  = $dimension->grade1;
            $formdata->{'weight__idx_' . $key}                  = $dimension->weight;
            $key++;
        }

        foreach ($maps as $nonegative => $map) {
            $formdata->{'map__idx_' . $nonegative} = $map->grade;
        }

        return $formdata;
    }

    
    protected function delete_dimensions(array $ids) {
        global $DB, $PAGE;

        $fs         = get_file_storage();
        foreach ($ids as $id) {
            $fs->delete_area_files($PAGE->context->id, 'workshopform_numerrors', 'description', $id);
        }
        $DB->delete_records_list('workshopform_numerrors', 'id', $ids);
    }

    
    protected function prepare_database_fields(stdclass $raw) {
        global $PAGE;

        $cook               = new stdclass();           $cook->numerrors    = array();                  $cook->mappings     = array();          
        for ($i = 0; $i < $raw->norepeats; $i++) {
            $cook->numerrors[$i]                        = new stdclass();
            $cook->numerrors[$i]->id                    = $raw->{'dimensionid__idx_'.$i};
            $cook->numerrors[$i]->workshopid            = $this->workshop->id;
            $cook->numerrors[$i]->sort                  = $i + 1;
            $cook->numerrors[$i]->description_editor    = $raw->{'description__idx_'.$i.'_editor'};
            $cook->numerrors[$i]->grade0                = $raw->{'grade0__idx_'.$i};
            $cook->numerrors[$i]->grade1                = $raw->{'grade1__idx_'.$i};
            $cook->numerrors[$i]->weight                = $raw->{'weight__idx_'.$i};
        }

        $i = 1;
        while (isset($raw->{'map__idx_'.$i})) {
            if (is_numeric($raw->{'map__idx_'.$i})) {
                $cook->mappings[$i] = $raw->{'map__idx_'.$i};             } else {
                $cook->mappings[$i] = null;             }
            $i++;
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
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'numerrors');
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
        $sumerrors  = 0;            foreach ($grades as $grade) {
            if (grade_floats_different($grade->grade, 1.00000)) {
                                $sumerrors += $this->dimensions[$grade->dimensionid]->weight;
            }
        }
        return $this->errors_to_grade($sumerrors);
    }

    
    protected function errors_to_grade($numerrors) {
        $grade = 100.00000;
        for ($i = 1; $i <= $numerrors; $i++) {
            if (isset($this->mappings[$i])) {
                $grade = $this->mappings[$i]->grade;
            }
        }
        if ($grade > 100.00000) {
            $grade = 100.00000;
        }
        if ($grade < 0.00000) {
            $grade = 0.00000;
        }
        return grade_floatval($grade);
    }
}
