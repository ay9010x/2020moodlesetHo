<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/lib.php');  require_once($CFG->libdir . '/gradelib.php');           

function workshopform_rubric_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
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

    if (!$dimension = $DB->get_record('workshopform_rubric', array('id' => $itemid ,'workshopid' => $workshop->id))) {
        send_file_not_found();
    }

            $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/workshopform_rubric/$filearea/$itemid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

        send_stored_file($file, 0, 0, $forcedownload, $options);
}


class workshop_rubric_strategy implements workshop_strategy {

    
    const MINDIMS = 3;

    
    const ADDDIMS = 2;

    
    protected $workshop;

    
    protected $dimensions = null;

    
    protected $descriptionopts;

    
    protected $definitionopts;

    
    protected $config;

    
    public function __construct(workshop $workshop) {
        $this->workshop         = $workshop;
        $this->dimensions       = $this->load_fields();
        $this->config           = $this->load_config();
        $this->descriptionopts  = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1);
                $this->definitionopts   = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1);
    }

    
    public function get_edit_strategy_form($actionurl=null) {
        global $CFG;    
        require_once(dirname(__FILE__) . '/edit_form.php');

        $fields             = $this->prepare_form_fields($this->dimensions);
        $fields->config_layout = $this->config->layout;

        $nodimensions       = count($this->dimensions);
        $norepeatsdefault   = max($nodimensions + self::ADDDIMS, self::MINDIMS);
        $norepeats          = optional_param('norepeats', $norepeatsdefault, PARAM_INT);            $adddims            = optional_param('adddims', '', PARAM_ALPHA);                           if ($adddims) {
            $norepeats += self::ADDDIMS;
        }

                $this->descriptionopts = array_merge(array('context' => $this->workshop->context), $this->descriptionopts);

                for ($i = 0; $i < $nodimensions; $i++) {
                        $fields = file_prepare_standard_editor($fields, 'description__idx_'.$i, $this->descriptionopts,
                $this->workshop->context, 'workshopform_rubric', 'description', $fields->{'dimensionid__idx_'.$i});
        }

        $customdata = array();
        $customdata['workshop'] = $this->workshop;
        $customdata['strategy'] = $this;
        $customdata['norepeats'] = $norepeats;
        $customdata['descriptionopts'] = $this->descriptionopts;
        $customdata['current']  = $fields;
        $attributes = array('class' => 'editstrategyform');

        return new workshop_edit_rubric_strategy_form($actionurl, $customdata, 'post', '', $attributes);
    }

    
    public function save_edit_strategy_form(stdclass $data) {
        global $DB;

        $norepeats  = $data->norepeats;
        $layout     = $data->config_layout;
        $data       = $this->prepare_database_fields($data);
        $deletedims = array();          $deletelevs = array();  
        if ($DB->record_exists('workshopform_rubric_config', array('workshopid' => $this->workshop->id))) {
            $DB->set_field('workshopform_rubric_config', 'layout', $layout, array('workshopid' => $this->workshop->id));
        } else {
            $record = new stdclass();
            $record->workshopid = $this->workshop->id;
            $record->layout     = $layout;
            $DB->insert_record('workshopform_rubric_config', $record, false);
        }

        foreach ($data as $record) {
            if (0 == strlen(trim($record->description_editor['text']))) {
                if (!empty($record->id)) {
                                        $deletedims[] = $record->id;
                    foreach ($record->levels as $level) {
                        if (!empty($level->id)) {
                            $deletelevs[] = $level->id;
                        }
                    }
                }
                continue;
            }
            if (empty($record->id)) {
                                $record->id = $DB->insert_record('workshopform_rubric', $record);
            } else {
                                $DB->update_record('workshopform_rubric', $record);
            }
                        $record = file_postupdate_standard_editor($record, 'description', $this->descriptionopts,
                                                      $this->workshop->context, 'workshopform_rubric', 'description', $record->id);
            $DB->update_record('workshopform_rubric', $record);

                        foreach ($record->levels as $level) {
                $level->dimensionid = $record->id;
                if (0 == strlen(trim($level->definition))) {
                    if (!empty($level->id)) {
                        $deletelevs[] = $level->id;
                    }
                    continue;
                }
                if (empty($level->id)) {
                                        $level->id = $DB->insert_record('workshopform_rubric_levels', $level);
                } else {
                                        $DB->update_record('workshopform_rubric_levels', $level);
                }
            }
        }
        $DB->delete_records_list('workshopform_rubric_levels', 'id', $deletelevs);
        $this->delete_dimensions($deletedims);
    }

    
    public function get_assessment_form(moodle_url $actionurl=null, $mode='preview', stdclass $assessment=null, $editable=true, $options=array()) {
        global $CFG;            global $DB;
        require_once(dirname(__FILE__) . '/assessment_form.php');

        $fields         = $this->prepare_form_fields($this->dimensions);
        $nodimensions   = count($this->dimensions);

                for ($i = 0; $i < $nodimensions; $i++) {
            $fields->{'description__idx_'.$i} = file_rewrite_pluginfile_urls($fields->{'description__idx_'.$i},
                'pluginfile.php', $this->workshop->context->id, 'workshopform_rubric', 'description',
                $fields->{'dimensionid__idx_'.$i});

        }

        if ('assessment' === $mode and !empty($assessment)) {
                        $grades = $this->get_current_assessment_data($assessment);
            $current = new stdclass();
            for ($i = 0; $i < $nodimensions; $i++) {
                $dimid = $fields->{'dimensionid__idx_'.$i};
                if (isset($grades[$dimid])) {
                    $givengrade = $grades[$dimid]->grade;
                                        $levelid = null;
                    foreach ($this->dimensions[$dimid]->levels as $level) {
                        if (grade_floats_equal($level->grade, $givengrade)) {
                            $levelid = $level->id;
                            break;
                        }
                    }
                    $current->{'gradeid__idx_'.$i}       = $grades[$dimid]->id;
                    $current->{'chosenlevelid__idx_'.$i} = $levelid;
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
        $attributes = array('class' => 'assessmentform rubric ' . $this->config->layout);

        $formclassname = 'workshop_rubric_' . $this->config->layout . '_assessment_form';
        return new $formclassname($actionurl, $customdata, 'post', '', $attributes, $editable);
    }

    
    public function save_assessment(stdclass $assessment, stdclass $data) {
        global $DB;

        for ($i = 0; isset($data->{'dimensionid__idx_' . $i}); $i++) {
            $grade = new stdclass();
            $grade->id = $data->{'gradeid__idx_' . $i};
            $grade->assessmentid = $assessment->id;
            $grade->strategy = 'rubric';
            $grade->dimensionid = $data->{'dimensionid__idx_' . $i};
            $chosenlevel = $data->{'chosenlevelid__idx_'.$i};
            $grade->grade = $this->dimensions[$grade->dimensionid]->levels[$chosenlevel]->grade;

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

        $sql = 'SELECT d.id AS id, MIN(l.grade) AS min, MAX(l.grade) AS max, 1 AS weight
                  FROM {workshopform_rubric} d
            INNER JOIN {workshopform_rubric_levels} l ON (d.id = l.dimensionid)
                 WHERE d.workshopid = :workshopid
              GROUP BY d.id';
        $params = array('workshopid' => $this->workshop->id);

        return $DB->get_records_sql($sql, $params);
    }

    
    public static function scale_used($scaleid, $workshopid=null) {
        return false;
    }

    
    public static function delete_instance($workshopid) {
        global $DB;

        $dimensions = $DB->get_records('workshopform_rubric', array('workshopid' => $workshopid), '', 'id');
        $DB->delete_records_list('workshopform_rubric_levels', 'dimensionid', array_keys($dimensions));
        $DB->delete_records('workshopform_rubric', array('workshopid' => $workshopid));
        $DB->delete_records('workshopform_rubric_config', array('workshopid' => $workshopid));
    }

            
    
    protected function load_fields() {
        global $DB;

        $sql = "SELECT r.id AS rid, r.sort, r.description, r.descriptionformat,
                       l.id AS lid, l.grade, l.definition, l.definitionformat
                  FROM {workshopform_rubric} r
             LEFT JOIN {workshopform_rubric_levels} l ON (l.dimensionid = r.id)
                 WHERE r.workshopid = :workshopid
                 ORDER BY r.sort, l.grade";
        $params = array('workshopid' => $this->workshop->id);

        $rs = $DB->get_recordset_sql($sql, $params);
        $fields = array();
        foreach ($rs as $record) {
            if (!isset($fields[$record->rid])) {
                $fields[$record->rid] = new stdclass();
                $fields[$record->rid]->id = $record->rid;
                $fields[$record->rid]->sort = $record->sort;
                $fields[$record->rid]->description = $record->description;
                $fields[$record->rid]->descriptionformat = $record->descriptionformat;
                $fields[$record->rid]->levels = array();
            }
            if (!empty($record->lid)) {
                $fields[$record->rid]->levels[$record->lid] = new stdclass();
                $fields[$record->rid]->levels[$record->lid]->id = $record->lid;
                $fields[$record->rid]->levels[$record->lid]->grade = $record->grade;
                $fields[$record->rid]->levels[$record->lid]->definition = $record->definition;
                $fields[$record->rid]->levels[$record->lid]->definitionformat = $record->definitionformat;
            }
        }
        $rs->close();

        return $fields;
    }

    
    protected function load_config() {
        global $DB;

        if (!$config = $DB->get_record('workshopform_rubric_config', array('workshopid' => $this->workshop->id), 'layout')) {
            $config = (object)array('layout' => 'list');
        }
        return $config;
    }

    
    protected function prepare_form_fields(array $fields) {

        $formdata = new stdclass();
        $key = 0;
        foreach ($fields as $field) {
            $formdata->{'dimensionid__idx_' . $key}             = $field->id;
            $formdata->{'description__idx_' . $key}             = $field->description;
            $formdata->{'description__idx_' . $key.'format'}    = $field->descriptionformat;
            $formdata->{'numoflevels__idx_' . $key}             = count($field->levels);
            $lev = 0;
            foreach ($field->levels as $level) {
                $formdata->{'levelid__idx_' . $key . '__idy_' . $lev} = $level->id;
                $formdata->{'grade__idx_' . $key . '__idy_' . $lev} = $level->grade;
                $formdata->{'definition__idx_' . $key . '__idy_' . $lev} = $level->definition;
                $formdata->{'definition__idx_' . $key . '__idy_' . $lev . 'format'} = $level->definitionformat;
                $lev++;
            }
            $key++;
        }
        return $formdata;
    }

    
    protected function delete_dimensions(array $ids) {
        global $DB;

        $fs = get_file_storage();
        foreach ($ids as $id) {
            if (!empty($id)) {                   $fs->delete_area_files($this->workshop->context->id, 'workshopform_rubric', 'description', $id);
            }
        }
        $DB->delete_records_list('workshopform_rubric', 'id', $ids);
    }

    
    protected function prepare_database_fields(stdclass $raw) {

        $cook = array();

        for ($i = 0; $i < $raw->norepeats; $i++) {
            $cook[$i]                       = new stdclass();
            $cook[$i]->id                   = $raw->{'dimensionid__idx_'.$i};
            $cook[$i]->workshopid           = $this->workshop->id;
            $cook[$i]->sort                 = $i + 1;
            $cook[$i]->description_editor   = $raw->{'description__idx_'.$i.'_editor'};
            $cook[$i]->levels               = array();

            $j = 0;
            while (isset($raw->{'levelid__idx_' . $i . '__idy_' . $j})) {
                $level                      = new stdclass();
                $level->id                  = $raw->{'levelid__idx_' . $i . '__idy_' . $j};
                $level->grade               = $raw->{'grade__idx_'.$i.'__idy_'.$j};
                $level->definition          = $raw->{'definition__idx_'.$i.'__idy_'.$j};
                $level->definitionformat    = FORMAT_HTML;
                $cook[$i]->levels[]         = $level;
                $j++;
            }
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
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'rubric');
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
        foreach ($grades as $grade) {
            $sumgrades += $grade->grade;
        }

                $mingrade = 0;
        $maxgrade = 0;
        foreach ($this->dimensions as $dimension) {
            $mindimensiongrade = null;
            $maxdimensiongrade = null;
            foreach ($dimension->levels as $level) {
                if (is_null($mindimensiongrade) or $level->grade < $mindimensiongrade) {
                    $mindimensiongrade = $level->grade;
                }
                if (is_null($maxdimensiongrade) or $level->grade > $maxdimensiongrade) {
                    $maxdimensiongrade = $level->grade;
                }
            }
            $mingrade += $mindimensiongrade;
            $maxgrade += $maxdimensiongrade;
        }

        if ($maxgrade - $mingrade > 0) {
            return grade_floatval(100 * ($sumgrades - $mingrade) / ($maxgrade - $mingrade));
        } else {
            return null;
        }
    }
}
