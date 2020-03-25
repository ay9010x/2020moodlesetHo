<?php



defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/lib.php');     require_once($CFG->libdir . '/gradelib.php');   require_once($CFG->libdir . '/filelib.php');


class workshop {

    
    const ALLOCATION_EXISTS             = -9999;

    
    const PHASE_SETUP                   = 10;
    const PHASE_SUBMISSION              = 20;
    const PHASE_ASSESSMENT              = 30;
    const PHASE_EVALUATION              = 40;
    const PHASE_CLOSED                  = 50;

    
    const EXAMPLES_VOLUNTARY            = 0;
    const EXAMPLES_BEFORE_SUBMISSION    = 1;
    const EXAMPLES_BEFORE_ASSESSMENT    = 2;

    
    public $cm;

    
    public $course;

    
    public $context;

    
    public $id;

    
    public $name;

    
    public $intro;

    
    public $introformat;

    
    public $instructauthors;

    
    public $instructauthorsformat;

    
    public $instructreviewers;

    
    public $instructreviewersformat;

    
    public $timemodified;

    
    public $phase;

    
    public $useexamples;

    
    public $usepeerassessment;

    
    public $useselfassessment;

    
    public $grade;

    
    public $gradinggrade;

    
    public $strategy;

    
    public $evaluation;

    
    public $gradedecimals;

    
    public $nattachments;

     
    public $submissionfiletypes = null;

    
    public $latesubmissions;

    
    public $maxbytes;

    
    public $examplesmode;

    
    public $submissionstart;

    
    public $submissionend;

    
    public $assessmentstart;

    
    public $assessmentend;

    
    public $phaseswitchassessment;

    
    public $conclusion;

    
    public $conclusionformat;

    
    public $overallfeedbackmode;

    
    public $overallfeedbackfiles;

    
    public $overallfeedbackfiletypes = null;

    
    public $overallfeedbackmaxbytes;

    
    protected $strategyinstance = null;

    
    protected $evaluationinstance = null;

    
    public function __construct(stdclass $dbrecord, $cm, $course, stdclass $context=null) {
        foreach ($dbrecord as $field => $value) {
            if (property_exists('workshop', $field)) {
                $this->{$field} = $value;
            }
        }
        if (is_null($cm) || is_null($course)) {
            throw new coding_exception('Must specify $cm and $course');
        }
        $this->course = $course;
        if ($cm instanceof cm_info) {
            $this->cm = $cm;
        } else {
            $modinfo = get_fast_modinfo($course);
            $this->cm = $modinfo->get_cm($cm->id);
        }
        if (is_null($context)) {
            $this->context = context_module::instance($this->cm->id);
        } else {
            $this->context = $context;
        }
    }

            
    
    public static function installed_allocators() {
        $installed = core_component::get_plugin_list('workshopallocation');
        $forms = array();
        foreach ($installed as $allocation => $allocationpath) {
            if (file_exists($allocationpath . '/lib.php')) {
                $forms[$allocation] = get_string('pluginname', 'workshopallocation_' . $allocation);
            }
        }
                if (isset($forms['manual'])) {
            $m = array('manual' => $forms['manual']);
            unset($forms['manual']);
            $forms = array_merge($m, $forms);
        }
        return $forms;
    }

    
    public static function instruction_editors_options(stdclass $context) {
        return array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1,
                     'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 0);
    }

    
    public static function percent_to_value($percent, $total) {
        if ($percent < 0 or $percent > 100) {
            throw new coding_exception('The percent can not be less than 0 or higher than 100');
        }

        return $total * $percent / 100;
    }

    
    public static function available_maxgrades_list() {
        $grades = array();
        for ($i=100; $i>=0; $i--) {
            $grades[$i] = $i;
        }
        return $grades;
    }

    
    public static function available_example_modes_list() {
        $options = array();
        $options[self::EXAMPLES_VOLUNTARY]         = get_string('examplesvoluntary', 'workshop');
        $options[self::EXAMPLES_BEFORE_SUBMISSION] = get_string('examplesbeforesubmission', 'workshop');
        $options[self::EXAMPLES_BEFORE_ASSESSMENT] = get_string('examplesbeforeassessment', 'workshop');
        return $options;
    }

    
    public static function available_strategies_list() {
        $installed = core_component::get_plugin_list('workshopform');
        $forms = array();
        foreach ($installed as $strategy => $strategypath) {
            if (file_exists($strategypath . '/lib.php')) {
                $forms[$strategy] = get_string('pluginname', 'workshopform_' . $strategy);
            }
        }
        return $forms;
    }

    
    public static function available_evaluators_list() {
        $evals = array();
        foreach (core_component::get_plugin_list_with_file('workshopeval', 'lib.php', false) as $eval => $evalpath) {
            $evals[$eval] = get_string('pluginname', 'workshopeval_' . $eval);
        }
        return $evals;
    }

    
    public static function available_dimension_weights_list() {
        $weights = array();
        for ($i=16; $i>=0; $i--) {
            $weights[$i] = $i;
        }
        return $weights;
    }

    
    public static function available_assessment_weights_list() {
        $weights = array();
        for ($i=16; $i>=0; $i--) {
            $weights[$i] = $i;
        }
        return $weights;
    }

    
    public static function gcd($a, $b) {
        return ($b == 0) ? ($a):(self::gcd($b, $a % $b));
    }

    
    public static function lcm($a, $b) {
        return ($a / self::gcd($a,$b)) * $b;
    }

    
    public static function timestamp_formats($timestamp) {
        $formats = array('date', 'datefullshort', 'dateshort', 'datetime',
                'datetimeshort', 'daydate', 'daydatetime', 'dayshort', 'daytime',
                'monthyear', 'recent', 'recentfull', 'time');
        $a = new stdclass();
        foreach ($formats as $format) {
            $a->{$format} = userdate($timestamp, get_string('strftime'.$format, 'langconfig'));
        }
        $day = userdate($timestamp, '%Y%m%d', 99, false);
        $today = userdate(time(), '%Y%m%d', 99, false);
        $tomorrow = userdate(time() + DAYSECS, '%Y%m%d', 99, false);
        $yesterday = userdate(time() - DAYSECS, '%Y%m%d', 99, false);
        $distance = (int)round(abs(time() - $timestamp) / DAYSECS);
        if ($day == $today) {
            $a->distanceday = get_string('daystoday', 'workshop');
        } elseif ($day == $yesterday) {
            $a->distanceday = get_string('daysyesterday', 'workshop');
        } elseif ($day < $today) {
            $a->distanceday = get_string('daysago', 'workshop', $distance);
        } elseif ($day == $tomorrow) {
            $a->distanceday = get_string('daystomorrow', 'workshop');
        } elseif ($day > $today) {
            $a->distanceday = get_string('daysleft', 'workshop', $distance);
        }
        return $a;
    }

    
    public static function normalize_file_extensions($extensions) {

        if ($extensions === '') {
            return array();
        }

        if (!is_array($extensions)) {
            $extensions = preg_split('/[\s,;:"\']+/', $extensions, null, PREG_SPLIT_NO_EMPTY);
        }

        foreach ($extensions as $i => $extension) {
            $extension = str_replace('*.', '', $extension);
            $extension = strtolower($extension);
            $extension = ltrim($extension, '.');
            $extension = trim($extension);
            $extensions[$i] = $extension;
        }

        foreach ($extensions as $i => $extension) {
            if (strpos($extension, '*') !== false or strpos($extension, '?') !== false) {
                unset($extensions[$i]);
            }
        }

        $extensions = array_filter($extensions, 'strlen');
        $extensions = array_keys(array_flip($extensions));

        foreach ($extensions as $i => $extension) {
            $extensions[$i] = '.'.$extension;
        }

        return $extensions;
    }

    
    public static function clean_file_extensions($extensions) {

        $extensions = self::normalize_file_extensions($extensions);

        foreach ($extensions as $i => $extension) {
            $extensions[$i] = ltrim($extension, '.');
        }

        return implode(', ', $extensions);
    }

    
    public static function invalid_file_extensions($extensions, $whitelist) {

        $extensions = self::normalize_file_extensions($extensions);
        $whitelist = self::normalize_file_extensions($whitelist);

        if (empty($extensions) or empty($whitelist)) {
            return array();
        }

                return array_keys(array_diff_key(array_flip($extensions), array_flip($whitelist)));
    }

    
    public static function is_allowed_file_type($filename, $whitelist) {

        $whitelist = self::normalize_file_extensions($whitelist);

        if (empty($whitelist)) {
            return true;
        }

        $haystack = strrev(trim(strtolower($filename)));

        foreach ($whitelist as $extension) {
            if (strpos($haystack, strrev($extension)) === 0) {
                                return true;
            }
        }

        return false;
    }

            
    
    public function get_potential_authors($musthavesubmission=true, $groupid=0, $limitfrom=0, $limitnum=0) {
        global $DB;

        list($sql, $params) = $this->get_users_with_capability_sql('mod/workshop:submit', $musthavesubmission, $groupid);

        if (empty($sql)) {
            return array();
        }

        list($sort, $sortparams) = users_order_by_sql('tmp');
        $sql = "SELECT *
                  FROM ($sql) tmp
              ORDER BY $sort";

        return $DB->get_records_sql($sql, array_merge($params, $sortparams), $limitfrom, $limitnum);
    }

    
    public function count_potential_authors($musthavesubmission=true, $groupid=0) {
        global $DB;

        list($sql, $params) = $this->get_users_with_capability_sql('mod/workshop:submit', $musthavesubmission, $groupid);

        if (empty($sql)) {
            return 0;
        }

        $sql = "SELECT COUNT(*)
                  FROM ($sql) tmp";

        return $DB->count_records_sql($sql, $params);
    }

    
    public function get_potential_reviewers($musthavesubmission=false, $groupid=0, $limitfrom=0, $limitnum=0) {
        global $DB;

        list($sql, $params) = $this->get_users_with_capability_sql('mod/workshop:peerassess', $musthavesubmission, $groupid);

        if (empty($sql)) {
            return array();
        }

        list($sort, $sortparams) = users_order_by_sql('tmp');
        $sql = "SELECT *
                  FROM ($sql) tmp
              ORDER BY $sort";

        return $DB->get_records_sql($sql, array_merge($params, $sortparams), $limitfrom, $limitnum);
    }

    
    public function count_potential_reviewers($musthavesubmission=false, $groupid=0) {
        global $DB;

        list($sql, $params) = $this->get_users_with_capability_sql('mod/workshop:peerassess', $musthavesubmission, $groupid);

        if (empty($sql)) {
            return 0;
        }

        $sql = "SELECT COUNT(*)
                  FROM ($sql) tmp";

        return $DB->count_records_sql($sql, $params);
    }

    
    public function get_participants($musthavesubmission=false, $groupid=0, $limitfrom=0, $limitnum=0) {
        global $DB;

        list($sql, $params) = $this->get_participants_sql($musthavesubmission, $groupid);

        if (empty($sql)) {
            return array();
        }

        list($sort, $sortparams) = users_order_by_sql('tmp');
        $sql = "SELECT *
                  FROM ($sql) tmp
              ORDER BY $sort";

        return $DB->get_records_sql($sql, array_merge($params, $sortparams), $limitfrom, $limitnum);
    }

    
    public function count_participants($musthavesubmission=false, $groupid=0) {
        global $DB;

        list($sql, $params) = $this->get_participants_sql($musthavesubmission, $groupid);

        if (empty($sql)) {
            return 0;
        }

        $sql = "SELECT COUNT(*)
                  FROM ($sql) tmp";

        return $DB->count_records_sql($sql, $params);
    }

    
    public function is_participant($userid=null) {
        global $USER, $DB;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        list($sql, $params) = $this->get_participants_sql();

        if (empty($sql)) {
            return false;
        }

        $sql = "SELECT COUNT(*)
                  FROM {user} uxx
                  JOIN ({$sql}) pxx ON uxx.id = pxx.id
                 WHERE uxx.id = :uxxid";
        $params['uxxid'] = $userid;

        if ($DB->count_records_sql($sql, $params)) {
            return true;
        }

        return false;
    }

    
    public function get_grouped($users) {
        global $DB;
        global $CFG;

        $grouped = array();          if (empty($users)) {
            return $grouped;
        }
        if ($this->cm->groupingid) {
                                                $groupingid = $this->cm->groupingid;
                                    $grouped[0] = array();
        } else {
            $groupingid = 0;
                                    $grouped[0] = $users;
        }
        $gmemberships = groups_get_all_groups($this->cm->course, array_keys($users), $groupingid,
                            'gm.id,gm.groupid,gm.userid');
        foreach ($gmemberships as $gmembership) {
            if (!isset($grouped[$gmembership->groupid])) {
                $grouped[$gmembership->groupid] = array();
            }
            $grouped[$gmembership->groupid][$gmembership->userid] = $users[$gmembership->userid];
            $grouped[0][$gmembership->userid] = $users[$gmembership->userid];
        }
        return $grouped;
    }

    
    public function get_allocations() {
        global $DB;

        $sql = 'SELECT a.id, a.submissionid, a.reviewerid, s.authorid
                  FROM {workshop_assessments} a
            INNER JOIN {workshop_submissions} s ON (a.submissionid = s.id)
                 WHERE s.example = 0 AND s.workshopid = :workshopid';
        $params = array('workshopid' => $this->id);

        return $DB->get_records_sql($sql, $params);
    }

    
    public function count_submissions($authorid='all', $groupid=0) {
        global $DB;

        $params = array('workshopid' => $this->id);
        $sql = "SELECT COUNT(s.id)
                  FROM {workshop_submissions} s
                  JOIN {user} u ON (s.authorid = u.id)";
        if ($groupid) {
            $sql .= " JOIN {groups_members} gm ON (gm.userid = u.id AND gm.groupid = :groupid)";
            $params['groupid'] = $groupid;
        }
        $sql .= " WHERE s.example = 0 AND s.workshopid = :workshopid";

        if ('all' === $authorid) {
                    } elseif (!empty($authorid)) {
            list($usql, $uparams) = $DB->get_in_or_equal($authorid, SQL_PARAMS_NAMED);
            $sql .= " AND authorid $usql";
            $params = array_merge($params, $uparams);
        } else {
                        return 0;
        }

        return $DB->count_records_sql($sql, $params);
    }


    
    public function get_submissions($authorid='all', $groupid=0, $limitfrom=0, $limitnum=0) {
        global $DB;

        $authorfields      = user_picture::fields('u', null, 'authoridx', 'author');
        $gradeoverbyfields = user_picture::fields('t', null, 'gradeoverbyx', 'over');
        $params            = array('workshopid' => $this->id);
        $sql = "SELECT s.id, s.workshopid, s.example, s.authorid, s.timecreated, s.timemodified,
                       s.title, s.grade, s.gradeover, s.gradeoverby, s.published,
                       $authorfields, $gradeoverbyfields
                  FROM {workshop_submissions} s
                  JOIN {user} u ON (s.authorid = u.id)";
        if ($groupid) {
            $sql .= " JOIN {groups_members} gm ON (gm.userid = u.id AND gm.groupid = :groupid)";
            $params['groupid'] = $groupid;
        }
        $sql .= " LEFT JOIN {user} t ON (s.gradeoverby = t.id)
                 WHERE s.example = 0 AND s.workshopid = :workshopid";

        if ('all' === $authorid) {
                    } elseif (!empty($authorid)) {
            list($usql, $uparams) = $DB->get_in_or_equal($authorid, SQL_PARAMS_NAMED);
            $sql .= " AND authorid $usql";
            $params = array_merge($params, $uparams);
        } else {
                        return array();
        }
        list($sort, $sortparams) = users_order_by_sql('u');
        $sql .= " ORDER BY $sort";

        return $DB->get_records_sql($sql, array_merge($params, $sortparams), $limitfrom, $limitnum);
    }

    
    public function get_submission_by_id($id) {
        global $DB;

                        $authorfields      = user_picture::fields('u', null, 'authoridx', 'author');
        $gradeoverbyfields = user_picture::fields('g', null, 'gradeoverbyx', 'gradeoverby');
        $sql = "SELECT s.*, $authorfields, $gradeoverbyfields
                  FROM {workshop_submissions} s
            INNER JOIN {user} u ON (s.authorid = u.id)
             LEFT JOIN {user} g ON (s.gradeoverby = g.id)
                 WHERE s.example = 0 AND s.workshopid = :workshopid AND s.id = :id";
        $params = array('workshopid' => $this->id, 'id' => $id);
        return $DB->get_record_sql($sql, $params, MUST_EXIST);
    }

    
    public function get_submission_by_author($authorid) {
        global $DB;

        if (empty($authorid)) {
            return false;
        }
        $authorfields      = user_picture::fields('u', null, 'authoridx', 'author');
        $gradeoverbyfields = user_picture::fields('g', null, 'gradeoverbyx', 'gradeoverby');
        $sql = "SELECT s.*, $authorfields, $gradeoverbyfields
                  FROM {workshop_submissions} s
            INNER JOIN {user} u ON (s.authorid = u.id)
             LEFT JOIN {user} g ON (s.gradeoverby = g.id)
                 WHERE s.example = 0 AND s.workshopid = :workshopid AND s.authorid = :authorid";
        $params = array('workshopid' => $this->id, 'authorid' => $authorid);
        return $DB->get_record_sql($sql, $params);
    }

    
    public function get_published_submissions($orderby='finalgrade DESC') {
        global $DB;

        $authorfields = user_picture::fields('u', null, 'authoridx', 'author');
        $sql = "SELECT s.id, s.authorid, s.timecreated, s.timemodified,
                       s.title, s.grade, s.gradeover, COALESCE(s.gradeover,s.grade) AS finalgrade,
                       $authorfields
                  FROM {workshop_submissions} s
            INNER JOIN {user} u ON (s.authorid = u.id)
                 WHERE s.example = 0 AND s.workshopid = :workshopid AND s.published = 1
              ORDER BY $orderby";
        $params = array('workshopid' => $this->id);
        return $DB->get_records_sql($sql, $params);
    }

    
    public function get_example_by_id($id) {
        global $DB;
        return $DB->get_record('workshop_submissions',
                array('id' => $id, 'workshopid' => $this->id, 'example' => 1), '*', MUST_EXIST);
    }

    
    public function get_examples_for_manager() {
        global $DB;

        $sql = 'SELECT s.id, s.title,
                       a.id AS assessmentid, a.grade, a.gradinggrade
                  FROM {workshop_submissions} s
             LEFT JOIN {workshop_assessments} a ON (a.submissionid = s.id AND a.weight = 1)
                 WHERE s.example = 1 AND s.workshopid = :workshopid
              ORDER BY s.title';
        return $DB->get_records_sql($sql, array('workshopid' => $this->id));
    }

    
    public function get_examples_for_reviewer($reviewerid) {
        global $DB;

        if (empty($reviewerid)) {
            return false;
        }
        $sql = 'SELECT s.id, s.title,
                       a.id AS assessmentid, a.grade, a.gradinggrade
                  FROM {workshop_submissions} s
             LEFT JOIN {workshop_assessments} a ON (a.submissionid = s.id AND a.reviewerid = :reviewerid AND a.weight = 0)
                 WHERE s.example = 1 AND s.workshopid = :workshopid
              ORDER BY s.title';
        return $DB->get_records_sql($sql, array('workshopid' => $this->id, 'reviewerid' => $reviewerid));
    }

    
    public function prepare_submission(stdClass $record, $showauthor = false) {

        $submission         = new workshop_submission($this, $record, $showauthor);
        $submission->url    = $this->submission_url($record->id);

        return $submission;
    }

    
    public function prepare_submission_summary(stdClass $record, $showauthor = false) {

        $summary        = new workshop_submission_summary($this, $record, $showauthor);
        $summary->url   = $this->submission_url($record->id);

        return $summary;
    }

    
    public function prepare_example_submission(stdClass $record) {

        $example = new workshop_example_submission($this, $record);

        return $example;
    }

    
    public function prepare_example_summary(stdClass $example) {

        $summary = new workshop_example_submission_summary($this, $example);

        if (is_null($example->grade)) {
            $summary->status = 'notgraded';
            $summary->assesslabel = get_string('assess', 'workshop');
        } else {
            $summary->status = 'graded';
            $summary->assesslabel = get_string('reassess', 'workshop');
        }

        $summary->gradeinfo           = new stdclass();
        $summary->gradeinfo->received = $this->real_grade($example->grade);
        $summary->gradeinfo->max      = $this->real_grade(100);

        $summary->url       = new moodle_url($this->exsubmission_url($example->id));
        $summary->editurl   = new moodle_url($this->exsubmission_url($example->id), array('edit' => 'on'));
        $summary->assessurl = new moodle_url($this->exsubmission_url($example->id), array('assess' => 'on', 'sesskey' => sesskey()));

        return $summary;
    }

    
    public function prepare_assessment(stdClass $record, $form, array $options = array()) {

        $assessment             = new workshop_assessment($this, $record, $options);
        $assessment->url        = $this->assess_url($record->id);
        $assessment->maxgrade   = $this->real_grade(100);

        if (!empty($options['showform']) and !($form instanceof workshop_assessment_form)) {
            debugging('Not a valid instance of workshop_assessment_form supplied', DEBUG_DEVELOPER);
        }

        if (!empty($options['showform']) and ($form instanceof workshop_assessment_form)) {
            $assessment->form = $form;
        }

        if (empty($options['showweight'])) {
            $assessment->weight = null;
        }

        if (!is_null($record->grade)) {
            $assessment->realgrade = $this->real_grade($record->grade);
        }

        return $assessment;
    }

    
    public function prepare_example_assessment(stdClass $record, $form = null, array $options = array()) {

        $assessment             = new workshop_example_assessment($this, $record, $options);
        $assessment->url        = $this->exassess_url($record->id);
        $assessment->maxgrade   = $this->real_grade(100);

        if (!empty($options['showform']) and !($form instanceof workshop_assessment_form)) {
            debugging('Not a valid instance of workshop_assessment_form supplied', DEBUG_DEVELOPER);
        }

        if (!empty($options['showform']) and ($form instanceof workshop_assessment_form)) {
            $assessment->form = $form;
        }

        if (!is_null($record->grade)) {
            $assessment->realgrade = $this->real_grade($record->grade);
        }

        $assessment->weight = null;

        return $assessment;
    }

    
    public function prepare_example_reference_assessment(stdClass $record, $form = null, array $options = array()) {

        $assessment             = new workshop_example_reference_assessment($this, $record, $options);
        $assessment->maxgrade   = $this->real_grade(100);

        if (!empty($options['showform']) and !($form instanceof workshop_assessment_form)) {
            debugging('Not a valid instance of workshop_assessment_form supplied', DEBUG_DEVELOPER);
        }

        if (!empty($options['showform']) and ($form instanceof workshop_assessment_form)) {
            $assessment->form = $form;
        }

        if (!is_null($record->grade)) {
            $assessment->realgrade = $this->real_grade($record->grade);
        }

        $assessment->weight = null;

        return $assessment;
    }

    
    public function delete_submission(stdclass $submission) {
        global $DB;

        $assessments = $DB->get_records('workshop_assessments', array('submissionid' => $submission->id), '', 'id');
        $this->delete_assessment(array_keys($assessments));

        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'mod_workshop', 'submission_content', $submission->id);
        $fs->delete_area_files($this->context->id, 'mod_workshop', 'submission_attachment', $submission->id);

        $DB->delete_records('workshop_submissions', array('id' => $submission->id));
    }

    
    public function get_all_assessments() {
        global $DB;

        $reviewerfields = user_picture::fields('reviewer', null, 'revieweridx', 'reviewer');
        $authorfields   = user_picture::fields('author', null, 'authorid', 'author');
        $overbyfields   = user_picture::fields('overby', null, 'gradinggradeoverbyx', 'overby');
        list($sort, $params) = users_order_by_sql('reviewer');
        $sql = "SELECT a.id, a.submissionid, a.reviewerid, a.timecreated, a.timemodified,
                       a.grade, a.gradinggrade, a.gradinggradeover, a.gradinggradeoverby,
                       $reviewerfields, $authorfields, $overbyfields,
                       s.title
                  FROM {workshop_assessments} a
            INNER JOIN {user} reviewer ON (a.reviewerid = reviewer.id)
            INNER JOIN {workshop_submissions} s ON (a.submissionid = s.id)
            INNER JOIN {user} author ON (s.authorid = author.id)
             LEFT JOIN {user} overby ON (a.gradinggradeoverby = overby.id)
                 WHERE s.workshopid = :workshopid AND s.example = 0
              ORDER BY $sort";
        $params['workshopid'] = $this->id;

        return $DB->get_records_sql($sql, $params);
    }

    
    public function get_assessment_by_id($id) {
        global $DB;

        $reviewerfields = user_picture::fields('reviewer', null, 'revieweridx', 'reviewer');
        $authorfields   = user_picture::fields('author', null, 'authorid', 'author');
        $overbyfields   = user_picture::fields('overby', null, 'gradinggradeoverbyx', 'overby');
        $sql = "SELECT a.*, s.title, $reviewerfields, $authorfields, $overbyfields
                  FROM {workshop_assessments} a
            INNER JOIN {user} reviewer ON (a.reviewerid = reviewer.id)
            INNER JOIN {workshop_submissions} s ON (a.submissionid = s.id)
            INNER JOIN {user} author ON (s.authorid = author.id)
             LEFT JOIN {user} overby ON (a.gradinggradeoverby = overby.id)
                 WHERE a.id = :id AND s.workshopid = :workshopid";
        $params = array('id' => $id, 'workshopid' => $this->id);

        return $DB->get_record_sql($sql, $params, MUST_EXIST);
    }

    
    public function get_assessment_of_submission_by_user($submissionid, $reviewerid) {
        global $DB;

        $reviewerfields = user_picture::fields('reviewer', null, 'revieweridx', 'reviewer');
        $authorfields   = user_picture::fields('author', null, 'authorid', 'author');
        $overbyfields   = user_picture::fields('overby', null, 'gradinggradeoverbyx', 'overby');
        $sql = "SELECT a.*, s.title, $reviewerfields, $authorfields, $overbyfields
                  FROM {workshop_assessments} a
            INNER JOIN {user} reviewer ON (a.reviewerid = reviewer.id)
            INNER JOIN {workshop_submissions} s ON (a.submissionid = s.id AND s.example = 0)
            INNER JOIN {user} author ON (s.authorid = author.id)
             LEFT JOIN {user} overby ON (a.gradinggradeoverby = overby.id)
                 WHERE s.id = :sid AND reviewer.id = :rid AND s.workshopid = :workshopid";
        $params = array('sid' => $submissionid, 'rid' => $reviewerid, 'workshopid' => $this->id);

        return $DB->get_record_sql($sql, $params, IGNORE_MISSING);
    }

    
    public function get_assessments_of_submission($submissionid) {
        global $DB;

        $reviewerfields = user_picture::fields('reviewer', null, 'revieweridx', 'reviewer');
        $overbyfields   = user_picture::fields('overby', null, 'gradinggradeoverbyx', 'overby');
        list($sort, $params) = users_order_by_sql('reviewer');
        $sql = "SELECT a.*, s.title, $reviewerfields, $overbyfields
                  FROM {workshop_assessments} a
            INNER JOIN {user} reviewer ON (a.reviewerid = reviewer.id)
            INNER JOIN {workshop_submissions} s ON (a.submissionid = s.id)
             LEFT JOIN {user} overby ON (a.gradinggradeoverby = overby.id)
                 WHERE s.example = 0 AND s.id = :submissionid AND s.workshopid = :workshopid
              ORDER BY $sort";
        $params['submissionid'] = $submissionid;
        $params['workshopid']   = $this->id;

        return $DB->get_records_sql($sql, $params);
    }

    
    public function get_assessments_by_reviewer($reviewerid) {
        global $DB;

        $reviewerfields = user_picture::fields('reviewer', null, 'revieweridx', 'reviewer');
        $authorfields   = user_picture::fields('author', null, 'authorid', 'author');
        $overbyfields   = user_picture::fields('overby', null, 'gradinggradeoverbyx', 'overby');
        $sql = "SELECT a.*, $reviewerfields, $authorfields, $overbyfields,
                       s.id AS submissionid, s.title AS submissiontitle, s.timecreated AS submissioncreated,
                       s.timemodified AS submissionmodified
                  FROM {workshop_assessments} a
            INNER JOIN {user} reviewer ON (a.reviewerid = reviewer.id)
            INNER JOIN {workshop_submissions} s ON (a.submissionid = s.id)
            INNER JOIN {user} author ON (s.authorid = author.id)
             LEFT JOIN {user} overby ON (a.gradinggradeoverby = overby.id)
                 WHERE s.example = 0 AND reviewer.id = :reviewerid AND s.workshopid = :workshopid";
        $params = array('reviewerid' => $reviewerid, 'workshopid' => $this->id);

        return $DB->get_records_sql($sql, $params);
    }

    
    public function get_pending_assessments_by_reviewer($reviewerid, $exclude = null) {

        $assessments = $this->get_assessments_by_reviewer($reviewerid);

        foreach ($assessments as $id => $assessment) {
            if (!is_null($assessment->grade)) {
                unset($assessments[$id]);
                continue;
            }
            if (!empty($exclude)) {
                if (is_array($exclude) and in_array($id, $exclude)) {
                    unset($assessments[$id]);
                    continue;
                } else if ($id == $exclude) {
                    unset($assessments[$id]);
                    continue;
                }
            }
        }

        return $assessments;
    }

    
    public function add_allocation(stdclass $submission, $reviewerid, $weight=1, $bulk=false) {
        global $DB;

        if ($DB->record_exists('workshop_assessments', array('submissionid' => $submission->id, 'reviewerid' => $reviewerid))) {
            return self::ALLOCATION_EXISTS;
        }

        $weight = (int)$weight;
        if ($weight < 0) {
            $weight = 0;
        }
        if ($weight > 16) {
            $weight = 16;
        }

        $now = time();
        $assessment = new stdclass();
        $assessment->submissionid           = $submission->id;
        $assessment->reviewerid             = $reviewerid;
        $assessment->timecreated            = $now;                 $assessment->weight                 = $weight;
        $assessment->feedbackauthorformat   = editors_get_preferred_format();
        $assessment->feedbackreviewerformat = editors_get_preferred_format();

        return $DB->insert_record('workshop_assessments', $assessment, true, $bulk);
    }

    
    public function delete_assessment($id) {
        global $DB;

        if (empty($id)) {
            return true;
        }

        $fs = get_file_storage();

        if (is_array($id)) {
            $DB->delete_records_list('workshop_grades', 'assessmentid', $id);
            foreach ($id as $itemid) {
                $fs->delete_area_files($this->context->id, 'mod_workshop', 'overallfeedback_content', $itemid);
                $fs->delete_area_files($this->context->id, 'mod_workshop', 'overallfeedback_attachment', $itemid);
            }
            $DB->delete_records_list('workshop_assessments', 'id', $id);

        } else {
            $DB->delete_records('workshop_grades', array('assessmentid' => $id));
            $fs->delete_area_files($this->context->id, 'mod_workshop', 'overallfeedback_content', $id);
            $fs->delete_area_files($this->context->id, 'mod_workshop', 'overallfeedback_attachment', $id);
            $DB->delete_records('workshop_assessments', array('id' => $id));
        }

        return true;
    }

    
    public function grading_strategy_instance() {
        global $CFG;    
        if (is_null($this->strategyinstance)) {
            $strategylib = dirname(__FILE__) . '/form/' . $this->strategy . '/lib.php';
            if (is_readable($strategylib)) {
                require_once($strategylib);
            } else {
                throw new coding_exception('the grading forms subplugin must contain library ' . $strategylib);
            }
            $classname = 'workshop_' . $this->strategy . '_strategy';
            $this->strategyinstance = new $classname($this);
            if (!in_array('workshop_strategy', class_implements($this->strategyinstance))) {
                throw new coding_exception($classname . ' does not implement workshop_strategy interface');
            }
        }
        return $this->strategyinstance;
    }

    
    public function set_grading_evaluation_method($method) {
        global $DB;

        $evaluationlib = dirname(__FILE__) . '/eval/' . $method . '/lib.php';

        if (is_readable($evaluationlib)) {
            $this->evaluationinstance = null;
            $this->evaluation = $method;
            $DB->set_field('workshop', 'evaluation', $method, array('id' => $this->id));
            return true;
        }

        throw new coding_exception('Attempt to set a non-existing evaluation method.');
    }

    
    public function grading_evaluation_instance() {
        global $CFG;    
        if (is_null($this->evaluationinstance)) {
            if (empty($this->evaluation)) {
                $this->evaluation = 'best';
            }
            $evaluationlib = dirname(__FILE__) . '/eval/' . $this->evaluation . '/lib.php';
            if (is_readable($evaluationlib)) {
                require_once($evaluationlib);
            } else {
                                $this->evaluation = 'best';
                $evaluationlib = dirname(__FILE__) . '/eval/' . $this->evaluation . '/lib.php';
                if (is_readable($evaluationlib)) {
                    require_once($evaluationlib);
                } else {
                                        throw new coding_exception('Missing default grading evaluation library ' . $evaluationlib);
                }
            }
            $classname = 'workshop_' . $this->evaluation . '_evaluation';
            $this->evaluationinstance = new $classname($this);
            if (!in_array('workshop_evaluation', class_parents($this->evaluationinstance))) {
                throw new coding_exception($classname . ' does not extend workshop_evaluation class');
            }
        }
        return $this->evaluationinstance;
    }

    
    public function allocator_instance($method) {
        global $CFG;    
        $allocationlib = dirname(__FILE__) . '/allocation/' . $method . '/lib.php';
        if (is_readable($allocationlib)) {
            require_once($allocationlib);
        } else {
            throw new coding_exception('Unable to find the allocation library ' . $allocationlib);
        }
        $classname = 'workshop_' . $method . '_allocator';
        return new $classname($this);
    }

    
    public function view_url() {
        global $CFG;
        return new moodle_url('/mod/workshop/view.php', array('id' => $this->cm->id));
    }

    
    public function editform_url() {
        global $CFG;
        return new moodle_url('/mod/workshop/editform.php', array('cmid' => $this->cm->id));
    }

    
    public function previewform_url() {
        global $CFG;
        return new moodle_url('/mod/workshop/editformpreview.php', array('cmid' => $this->cm->id));
    }

    
    public function assess_url($assessmentid) {
        global $CFG;
        $assessmentid = clean_param($assessmentid, PARAM_INT);
        return new moodle_url('/mod/workshop/assessment.php', array('asid' => $assessmentid));
    }

    
    public function exassess_url($assessmentid) {
        global $CFG;
        $assessmentid = clean_param($assessmentid, PARAM_INT);
        return new moodle_url('/mod/workshop/exassessment.php', array('asid' => $assessmentid));
    }

    
    public function submission_url($id=null) {
        global $CFG;
        return new moodle_url('/mod/workshop/submission.php', array('cmid' => $this->cm->id, 'id' => $id));
    }

    
    public function exsubmission_url($id) {
        global $CFG;
        return new moodle_url('/mod/workshop/exsubmission.php', array('cmid' => $this->cm->id, 'id' => $id));
    }

    
    public function compare_url($sid, array $aids) {
        global $CFG;

        $url = new moodle_url('/mod/workshop/compare.php', array('cmid' => $this->cm->id, 'sid' => $sid));
        $i = 0;
        foreach ($aids as $aid) {
            $url->param("aid{$i}", $aid);
            $i++;
        }
        return $url;
    }

    
    public function excompare_url($sid, $aid) {
        global $CFG;
        return new moodle_url('/mod/workshop/excompare.php', array('cmid' => $this->cm->id, 'sid' => $sid, 'aid' => $aid));
    }

    
    public function updatemod_url() {
        global $CFG;
        return new moodle_url('/course/modedit.php', array('update' => $this->cm->id, 'return' => 1));
    }

    
    public function allocation_url($method=null) {
        global $CFG;
        $params = array('cmid' => $this->cm->id);
        if (!empty($method)) {
            $params['method'] = $method;
        }
        return new moodle_url('/mod/workshop/allocation.php', $params);
    }

    
    public function switchphase_url($phasecode) {
        global $CFG;
        $phasecode = clean_param($phasecode, PARAM_INT);
        return new moodle_url('/mod/workshop/switchphase.php', array('cmid' => $this->cm->id, 'phase' => $phasecode));
    }

    
    public function aggregate_url() {
        global $CFG;
        return new moodle_url('/mod/workshop/aggregate.php', array('cmid' => $this->cm->id));
    }

    
    public function toolbox_url($tool) {
        global $CFG;
        return new moodle_url('/mod/workshop/toolbox.php', array('id' => $this->cm->id, 'tool' => $tool));
    }

    
    public function log($action, moodle_url $url = null, $info = null, $return = false) {
        debugging('The log method is now deprecated, please use event classes instead', DEBUG_DEVELOPER);

        if (is_null($url)) {
            $url = $this->view_url();
        }

        if (is_null($info)) {
            $info = $this->id;
        }

        $logurl = $this->log_convert_url($url);
        $args = array($this->course->id, 'workshop', $action, $logurl, $info, $this->cm->id);
        if ($return) {
            return $args;
        }
        call_user_func_array('add_to_log', $args);
    }

    
    public function creating_submission_allowed($userid) {

        $now = time();
        $ignoredeadlines = has_capability('mod/workshop:ignoredeadlines', $this->context, $userid);

        if ($this->latesubmissions) {
            if ($this->phase != self::PHASE_SUBMISSION and $this->phase != self::PHASE_ASSESSMENT) {
                                return false;
            }
            if (!$ignoredeadlines and !empty($this->submissionstart) and $this->submissionstart > $now) {
                                return false;
            }
            return true;

        } else {
            if ($this->phase != self::PHASE_SUBMISSION) {
                                return false;
            }
            if (!$ignoredeadlines and !empty($this->submissionstart) and $this->submissionstart > $now) {
                                return false;
            }
            if (!$ignoredeadlines and !empty($this->submissionend) and $now > $this->submissionend ) {
                                return false;
            }
            return true;
        }
    }

    
    public function modifying_submission_allowed($userid) {

        $now = time();
        $ignoredeadlines = has_capability('mod/workshop:ignoredeadlines', $this->context, $userid);

        if ($this->phase != self::PHASE_SUBMISSION) {
                        return false;
        }
        if (!$ignoredeadlines and !empty($this->submissionstart) and $this->submissionstart > $now) {
                        return false;
        }
        if (!$ignoredeadlines and !empty($this->submissionend) and $now > $this->submissionend) {
                        return false;
        }
        return true;
    }

    
    public function assessing_allowed($userid) {

        if ($this->phase != self::PHASE_ASSESSMENT) {
                                    if ($this->phase != self::PHASE_EVALUATION or !has_capability('mod/workshop:overridegrades', $this->context, $userid)) {
                return false;
            }
        }

        $now = time();
        $ignoredeadlines = has_capability('mod/workshop:ignoredeadlines', $this->context, $userid);

        if (!$ignoredeadlines and !empty($this->assessmentstart) and $this->assessmentstart > $now) {
                        return false;
        }
        if (!$ignoredeadlines and !empty($this->assessmentend) and $now > $this->assessmentend) {
                        return false;
        }
                return true;
    }

    
    public function assessing_examples_allowed() {
        if (empty($this->useexamples)) {
            return null;
        }
        if (self::EXAMPLES_VOLUNTARY == $this->examplesmode) {
            return true;
        }
        if (self::EXAMPLES_BEFORE_SUBMISSION == $this->examplesmode and self::PHASE_SUBMISSION == $this->phase) {
            return true;
        }
        if (self::EXAMPLES_BEFORE_ASSESSMENT == $this->examplesmode and self::PHASE_ASSESSMENT == $this->phase) {
            return true;
        }
        return false;
    }

    
    public function assessments_available() {
        return $this->phase == self::PHASE_CLOSED;
    }

    
    public function switch_phase($newphase) {
        global $DB;

        $known = $this->available_phases_list();
        if (!isset($known[$newphase])) {
            return false;
        }

        if (self::PHASE_CLOSED == $newphase) {
                        $workshop = new stdclass();
            foreach ($this as $property => $value) {
                $workshop->{$property} = $value;
            }
            $workshop->course     = $this->course->id;
            $workshop->cmidnumber = $this->cm->id;
            $workshop->modname    = 'workshop';
            workshop_update_grades($workshop);
        }

        $DB->set_field('workshop', 'phase', $newphase, array('id' => $this->id));
        $this->phase = $newphase;
        $eventdata = array(
            'objectid' => $this->id,
            'context' => $this->context,
            'other' => array(
                'workshopphase' => $this->phase
            )
        );
        $event = \mod_workshop\event\phase_switched::create($eventdata);
        $event->trigger();
        return true;
    }

    
    public function set_peer_grade($assessmentid, $grade) {
        global $DB;

        if (is_null($grade)) {
            return false;
        }
        $data = new stdclass();
        $data->id = $assessmentid;
        $data->grade = $grade;
        $data->timemodified = time();
        $DB->update_record('workshop_assessments', $data);
        return $grade;
    }

    
    public function prepare_grading_report_data($userid, $groupid, $page, $perpage, $sortby, $sorthow) {
        global $DB;

        $canviewall     = has_capability('mod/workshop:viewallassessments', $this->context, $userid);
        $isparticipant  = $this->is_participant($userid);

        if (!$canviewall and !$isparticipant) {
                        return array();
        }

        if (!in_array($sortby, array('lastname', 'firstname', 'submissiontitle', 'submissionmodified',
                'submissiongrade', 'gradinggrade'))) {
            $sortby = 'lastname';
        }

        if (!($sorthow === 'ASC' or $sorthow === 'DESC')) {
            $sorthow = 'ASC';
        }

                if ($canviewall) {
            $participants = $this->get_participants(false, $groupid);
        } else {
                        $participants = array($userid => (object)array('id' => $userid));
        }

                $numofparticipants = count($participants);

        if ($numofparticipants > 0) {
                        list($participantids, $params) = $DB->get_in_or_equal(array_keys($participants), SQL_PARAMS_NAMED);
            $params['workshopid1'] = $this->id;
            $params['workshopid2'] = $this->id;
            $sqlsort = array();
            $sqlsortfields = array($sortby => $sorthow) + array('lastname' => 'ASC', 'firstname' => 'ASC', 'u.id' => 'ASC');
            foreach ($sqlsortfields as $sqlsortfieldname => $sqlsortfieldhow) {
                $sqlsort[] = $sqlsortfieldname . ' ' . $sqlsortfieldhow;
            }
            $sqlsort = implode(',', $sqlsort);
            $picturefields = user_picture::fields('u', array(), 'userid');
            $sql = "SELECT $picturefields, s.title AS submissiontitle, s.timemodified AS submissionmodified,
                           s.grade AS submissiongrade, ag.gradinggrade
                      FROM {user} u
                 LEFT JOIN {workshop_submissions} s ON (s.authorid = u.id AND s.workshopid = :workshopid1 AND s.example = 0)
                 LEFT JOIN {workshop_aggregations} ag ON (ag.userid = u.id AND ag.workshopid = :workshopid2)
                     WHERE u.id $participantids
                  ORDER BY $sqlsort";
            $participants = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);
        } else {
            $participants = array();
        }

                $userinfo = array();

                $additionalnames = get_all_user_name_fields();
        foreach ($participants as $participant) {
            if (!isset($userinfo[$participant->userid])) {
                $userinfo[$participant->userid]            = new stdclass();
                $userinfo[$participant->userid]->id        = $participant->userid;
                $userinfo[$participant->userid]->picture   = $participant->picture;
                $userinfo[$participant->userid]->imagealt  = $participant->imagealt;
                $userinfo[$participant->userid]->email     = $participant->email;
                foreach ($additionalnames as $addname) {
                    $userinfo[$participant->userid]->$addname = $participant->$addname;
                }
            }
        }

                $submissions = $this->get_submissions(array_keys($participants));

                foreach ($submissions as $submission) {
            if (!isset($userinfo[$submission->gradeoverby])) {
                $userinfo[$submission->gradeoverby]            = new stdclass();
                $userinfo[$submission->gradeoverby]->id        = $submission->gradeoverby;
                $userinfo[$submission->gradeoverby]->picture   = $submission->overpicture;
                $userinfo[$submission->gradeoverby]->imagealt  = $submission->overimagealt;
                $userinfo[$submission->gradeoverby]->email     = $submission->overemail;
                foreach ($additionalnames as $addname) {
                    $temp = 'over' . $addname;
                    $userinfo[$submission->gradeoverby]->$addname = $submission->$temp;
                }
            }
        }

                $reviewers = array();

        if ($submissions) {
            list($submissionids, $params) = $DB->get_in_or_equal(array_keys($submissions), SQL_PARAMS_NAMED);
            list($sort, $sortparams) = users_order_by_sql('r');
            $picturefields = user_picture::fields('r', array(), 'reviewerid');
            $sql = "SELECT a.id AS assessmentid, a.submissionid, a.grade, a.gradinggrade, a.gradinggradeover, a.weight,
                           $picturefields, s.id AS submissionid, s.authorid
                      FROM {workshop_assessments} a
                      JOIN {user} r ON (a.reviewerid = r.id)
                      JOIN {workshop_submissions} s ON (a.submissionid = s.id AND s.example = 0)
                     WHERE a.submissionid $submissionids
                  ORDER BY a.weight DESC, $sort";
            $reviewers = $DB->get_records_sql($sql, array_merge($params, $sortparams));
            foreach ($reviewers as $reviewer) {
                if (!isset($userinfo[$reviewer->reviewerid])) {
                    $userinfo[$reviewer->reviewerid]            = new stdclass();
                    $userinfo[$reviewer->reviewerid]->id        = $reviewer->reviewerid;
                    $userinfo[$reviewer->reviewerid]->picture   = $reviewer->picture;
                    $userinfo[$reviewer->reviewerid]->imagealt  = $reviewer->imagealt;
                    $userinfo[$reviewer->reviewerid]->email     = $reviewer->email;
                    foreach ($additionalnames as $addname) {
                        $userinfo[$reviewer->reviewerid]->$addname = $reviewer->$addname;
                    }
                }
            }
        }

                $reviewees = array();
        if ($participants) {
            list($participantids, $params) = $DB->get_in_or_equal(array_keys($participants), SQL_PARAMS_NAMED);
            list($sort, $sortparams) = users_order_by_sql('e');
            $params['workshopid'] = $this->id;
            $picturefields = user_picture::fields('e', array(), 'authorid');
            $sql = "SELECT a.id AS assessmentid, a.submissionid, a.grade, a.gradinggrade, a.gradinggradeover, a.reviewerid, a.weight,
                           s.id AS submissionid, $picturefields
                      FROM {user} u
                      JOIN {workshop_assessments} a ON (a.reviewerid = u.id)
                      JOIN {workshop_submissions} s ON (a.submissionid = s.id AND s.example = 0)
                      JOIN {user} e ON (s.authorid = e.id)
                     WHERE u.id $participantids AND s.workshopid = :workshopid
                  ORDER BY a.weight DESC, $sort";
            $reviewees = $DB->get_records_sql($sql, array_merge($params, $sortparams));
            foreach ($reviewees as $reviewee) {
                if (!isset($userinfo[$reviewee->authorid])) {
                    $userinfo[$reviewee->authorid]            = new stdclass();
                    $userinfo[$reviewee->authorid]->id        = $reviewee->authorid;
                    $userinfo[$reviewee->authorid]->picture   = $reviewee->picture;
                    $userinfo[$reviewee->authorid]->imagealt  = $reviewee->imagealt;
                    $userinfo[$reviewee->authorid]->email     = $reviewee->email;
                    foreach ($additionalnames as $addname) {
                        $userinfo[$reviewee->authorid]->$addname = $reviewee->$addname;
                    }
                }
            }
        }

                $grades = $participants;

        foreach ($participants as $participant) {
                        $grades[$participant->userid]->submissionid = null;
            $grades[$participant->userid]->submissiontitle = null;
            $grades[$participant->userid]->submissiongrade = null;
            $grades[$participant->userid]->submissiongradeover = null;
            $grades[$participant->userid]->submissiongradeoverby = null;
            $grades[$participant->userid]->submissionpublished = null;
            $grades[$participant->userid]->reviewedby = array();
            $grades[$participant->userid]->reviewerof = array();
        }
        unset($participants);
        unset($participant);

        foreach ($submissions as $submission) {
            $grades[$submission->authorid]->submissionid = $submission->id;
            $grades[$submission->authorid]->submissiontitle = $submission->title;
            $grades[$submission->authorid]->submissiongrade = $this->real_grade($submission->grade);
            $grades[$submission->authorid]->submissiongradeover = $this->real_grade($submission->gradeover);
            $grades[$submission->authorid]->submissiongradeoverby = $submission->gradeoverby;
            $grades[$submission->authorid]->submissionpublished = $submission->published;
        }
        unset($submissions);
        unset($submission);

        foreach($reviewers as $reviewer) {
            $info = new stdclass();
            $info->userid = $reviewer->reviewerid;
            $info->assessmentid = $reviewer->assessmentid;
            $info->submissionid = $reviewer->submissionid;
            $info->grade = $this->real_grade($reviewer->grade);
            $info->gradinggrade = $this->real_grading_grade($reviewer->gradinggrade);
            $info->gradinggradeover = $this->real_grading_grade($reviewer->gradinggradeover);
            $info->weight = $reviewer->weight;
            $grades[$reviewer->authorid]->reviewedby[$reviewer->reviewerid] = $info;
        }
        unset($reviewers);
        unset($reviewer);

        foreach($reviewees as $reviewee) {
            $info = new stdclass();
            $info->userid = $reviewee->authorid;
            $info->assessmentid = $reviewee->assessmentid;
            $info->submissionid = $reviewee->submissionid;
            $info->grade = $this->real_grade($reviewee->grade);
            $info->gradinggrade = $this->real_grading_grade($reviewee->gradinggrade);
            $info->gradinggradeover = $this->real_grading_grade($reviewee->gradinggradeover);
            $info->weight = $reviewee->weight;
            $grades[$reviewee->reviewerid]->reviewerof[$reviewee->authorid] = $info;
        }
        unset($reviewees);
        unset($reviewee);

        foreach ($grades as $grade) {
            $grade->gradinggrade = $this->real_grading_grade($grade->gradinggrade);
        }

        $data = new stdclass();
        $data->grades = $grades;
        $data->userinfo = $userinfo;
        $data->totalcount = $numofparticipants;
        $data->maxgrade = $this->real_grade(100);
        $data->maxgradinggrade = $this->real_grading_grade(100);
        return $data;
    }

    
    public function real_grade_value($value, $max) {
        $localized = true;
        if (is_null($value) or $value === '') {
            return null;
        } elseif ($max == 0) {
            return 0;
        } else {
            return format_float($max * $value / 100, $this->gradedecimals, $localized);
        }
    }

    
    public function raw_grade_value($value, $max) {
        if (is_null($value) or $value === '') {
            return null;
        }
        if ($max == 0 or $value < 0) {
            return 0;
        }
        $p = $value / $max * 100;
        if ($p > 100) {
            return $max;
        }
        return grade_floatval($p);
    }

    
    public function real_grade($value) {
        return $this->real_grade_value($value, $this->grade);
    }

    
    public function real_grading_grade($value) {
        return $this->real_grade_value($value, $this->gradinggrade);
    }

    
    public function clear_assessments() {
        global $DB;

        $submissions = $this->get_submissions();
        if (empty($submissions)) {
                        return;
        }
        $submissions = array_keys($submissions);
        list($sql, $params) = $DB->get_in_or_equal($submissions, SQL_PARAMS_NAMED);
        $sql = "submissionid $sql";
        $DB->set_field_select('workshop_assessments', 'grade', null, $sql, $params);
        $DB->set_field_select('workshop_assessments', 'gradinggrade', null, $sql, $params);
    }

    
    public function clear_submission_grades($restrict=null) {
        global $DB;

        $sql = "workshopid = :workshopid AND example = 0";
        $params = array('workshopid' => $this->id);

        if (is_null($restrict)) {
                    } elseif (!empty($restrict)) {
            list($usql, $uparams) = $DB->get_in_or_equal($restrict, SQL_PARAMS_NAMED);
            $sql .= " AND authorid $usql";
            $params = array_merge($params, $uparams);
        } else {
            throw new coding_exception('Empty value is not a valid parameter here');
        }

        $DB->set_field_select('workshop_submissions', 'grade', null, $sql, $params);
    }

    
    public function aggregate_submission_grades($restrict=null) {
        global $DB;

                $sql = 'SELECT s.id AS submissionid, s.grade AS submissiongrade,
                       a.weight, a.grade
                  FROM {workshop_submissions} s
             LEFT JOIN {workshop_assessments} a ON (a.submissionid = s.id)
                 WHERE s.example=0 AND s.workshopid=:workshopid';         $params = array('workshopid' => $this->id);

        if (is_null($restrict)) {
                    } elseif (!empty($restrict)) {
            list($usql, $uparams) = $DB->get_in_or_equal($restrict, SQL_PARAMS_NAMED);
            $sql .= " AND s.authorid $usql";
            $params = array_merge($params, $uparams);
        } else {
            throw new coding_exception('Empty value is not a valid parameter here');
        }

        $sql .= ' ORDER BY s.id'; 
        $rs         = $DB->get_recordset_sql($sql, $params);
        $batch      = array();            $previous   = null;       
        foreach ($rs as $current) {
            if (is_null($previous)) {
                                $previous   = $current;
            }
            if ($current->submissionid == $previous->submissionid) {
                                $batch[] = $current;
            } else {
                                $this->aggregate_submission_grades_process($batch);
                                $batch      = array($current);
                $previous   = $current;
            }
        }
                $this->aggregate_submission_grades_process($batch);
        $rs->close();
    }

    
    public function clear_grading_grades($restrict=null) {
        global $DB;

        $sql = "workshopid = :workshopid";
        $params = array('workshopid' => $this->id);

        if (is_null($restrict)) {
                    } elseif (!empty($restrict)) {
            list($usql, $uparams) = $DB->get_in_or_equal($restrict, SQL_PARAMS_NAMED);
            $sql .= " AND userid $usql";
            $params = array_merge($params, $uparams);
        } else {
            throw new coding_exception('Empty value is not a valid parameter here');
        }

        $DB->set_field_select('workshop_aggregations', 'gradinggrade', null, $sql, $params);
    }

    
    public function aggregate_grading_grades($restrict=null) {
        global $DB;

                $sql = 'SELECT a.reviewerid, a.gradinggrade, a.gradinggradeover,
                       ag.id AS aggregationid, ag.gradinggrade AS aggregatedgrade
                  FROM {workshop_assessments} a
            INNER JOIN {workshop_submissions} s ON (a.submissionid = s.id)
             LEFT JOIN {workshop_aggregations} ag ON (ag.userid = a.reviewerid AND ag.workshopid = s.workshopid)
                 WHERE s.example=0 AND s.workshopid=:workshopid';         $params = array('workshopid' => $this->id);

        if (is_null($restrict)) {
                    } elseif (!empty($restrict)) {
            list($usql, $uparams) = $DB->get_in_or_equal($restrict, SQL_PARAMS_NAMED);
            $sql .= " AND a.reviewerid $usql";
            $params = array_merge($params, $uparams);
        } else {
            throw new coding_exception('Empty value is not a valid parameter here');
        }

        $sql .= ' ORDER BY a.reviewerid'; 
        $rs         = $DB->get_recordset_sql($sql, $params);
        $batch      = array();            $previous   = null;       
        foreach ($rs as $current) {
            if (is_null($previous)) {
                                $previous   = $current;
            }
            if ($current->reviewerid == $previous->reviewerid) {
                                $batch[] = $current;
            } else {
                                $this->aggregate_grading_grades_process($batch);
                                $batch      = array($current);
                $previous   = $current;
            }
        }
                $this->aggregate_grading_grades_process($batch);
        $rs->close();
    }

    
    public function get_feedbackreviewer_form(moodle_url $actionurl, stdclass $assessment, $options=array()) {
        global $CFG;
        require_once(dirname(__FILE__) . '/feedbackreviewer_form.php');

        $current = new stdclass();
        $current->asid                      = $assessment->id;
        $current->weight                    = $assessment->weight;
        $current->gradinggrade              = $this->real_grading_grade($assessment->gradinggrade);
        $current->gradinggradeover          = $this->real_grading_grade($assessment->gradinggradeover);
        $current->feedbackreviewer          = $assessment->feedbackreviewer;
        $current->feedbackreviewerformat    = $assessment->feedbackreviewerformat;
        if (is_null($current->gradinggrade)) {
            $current->gradinggrade = get_string('nullgrade', 'workshop');
        }
        if (!isset($options['editable'])) {
            $editable = true;           } else {
            $editable = (bool)$options['editable'];
        }

                $current = file_prepare_standard_editor($current, 'feedbackreviewer', array());

        return new workshop_feedbackreviewer_form($actionurl,
                array('workshop' => $this, 'current' => $current, 'editoropts' => array(), 'options' => $options),
                'post', '', null, $editable);
    }

    
    public function get_feedbackauthor_form(moodle_url $actionurl, stdclass $submission, $options=array()) {
        global $CFG;
        require_once(dirname(__FILE__) . '/feedbackauthor_form.php');

        $current = new stdclass();
        $current->submissionid          = $submission->id;
        $current->published             = $submission->published;
        $current->grade                 = $this->real_grade($submission->grade);
        $current->gradeover             = $this->real_grade($submission->gradeover);
        $current->feedbackauthor        = $submission->feedbackauthor;
        $current->feedbackauthorformat  = $submission->feedbackauthorformat;
        if (is_null($current->grade)) {
            $current->grade = get_string('nullgrade', 'workshop');
        }
        if (!isset($options['editable'])) {
            $editable = true;           } else {
            $editable = (bool)$options['editable'];
        }

                $current = file_prepare_standard_editor($current, 'feedbackauthor', array());

        return new workshop_feedbackauthor_form($actionurl,
                array('workshop' => $this, 'current' => $current, 'editoropts' => array(), 'options' => $options),
                'post', '', null, $editable);
    }

    
    public function get_gradebook_grades($userid) {
        global $CFG;
        require_once($CFG->libdir.'/gradelib.php');

        if (empty($userid)) {
            throw new coding_exception('User id expected, empty value given.');
        }

                $gradebook = grade_get_grades($this->course->id, 'mod', 'workshop', $this->id, $userid);

        $grades = new workshop_final_grades();

        if (has_capability('mod/workshop:submit', $this->context, $userid)) {
            if (!empty($gradebook->items[0]->grades)) {
                $submissiongrade = reset($gradebook->items[0]->grades);
                if (!is_null($submissiongrade->grade)) {
                    if (!$submissiongrade->hidden or has_capability('moodle/grade:viewhidden', $this->context, $userid)) {
                        $grades->submissiongrade = $submissiongrade;
                    }
                }
            }
        }

        if (has_capability('mod/workshop:peerassess', $this->context, $userid)) {
            if (!empty($gradebook->items[1]->grades)) {
                $assessmentgrade = reset($gradebook->items[1]->grades);
                if (!is_null($assessmentgrade->grade)) {
                    if (!$assessmentgrade->hidden or has_capability('moodle/grade:viewhidden', $this->context, $userid)) {
                        $grades->assessmentgrade = $assessmentgrade;
                    }
                }
            }
        }

        if (!is_null($grades->submissiongrade) or !is_null($grades->assessmentgrade)) {
            return $grades;
        }

        return false;
    }

    
    public function submission_content_options() {
        global $CFG;
        require_once($CFG->dirroot.'/repository/lib.php');

        return array(
            'trusttext' => true,
            'subdirs' => false,
            'maxfiles' => $this->nattachments,
            'maxbytes' => $this->maxbytes,
            'context' => $this->context,
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
          );
    }

    
    public function submission_attachment_options() {
        global $CFG;
        require_once($CFG->dirroot.'/repository/lib.php');

        $options = array(
            'subdirs' => true,
            'maxfiles' => $this->nattachments,
            'maxbytes' => $this->maxbytes,
            'return_types' => FILE_INTERNAL,
        );

        if ($acceptedtypes = self::normalize_file_extensions($this->submissionfiletypes)) {
            $options['accepted_types'] = $acceptedtypes;
        }

        return $options;
    }

    
    public function overall_feedback_content_options() {
        global $CFG;
        require_once($CFG->dirroot.'/repository/lib.php');

        return array(
            'subdirs' => 0,
            'maxbytes' => $this->overallfeedbackmaxbytes,
            'maxfiles' => $this->overallfeedbackfiles,
            'changeformat' => 1,
            'context' => $this->context,
            'return_types' => FILE_INTERNAL,
        );
    }

    
    public function overall_feedback_attachment_options() {
        global $CFG;
        require_once($CFG->dirroot.'/repository/lib.php');

        $options = array(
            'subdirs' => 1,
            'maxbytes' => $this->overallfeedbackmaxbytes,
            'maxfiles' => $this->overallfeedbackfiles,
            'return_types' => FILE_INTERNAL,
        );

        if ($acceptedtypes = self::normalize_file_extensions($this->overallfeedbackfiletypes)) {
            $options['accepted_types'] = $acceptedtypes;
        }

        return $options;
    }

    
    public function reset_userdata(stdClass $data) {

        $componentstr = get_string('pluginname', 'workshop').': '.format_string($this->name);
        $status = array();

        if (!empty($data->reset_workshop_assessments) or !empty($data->reset_workshop_submissions)) {
                                    $result = $this->reset_userdata_assessments($data);
            if ($result === true) {
                $status[] = array(
                    'component' => $componentstr,
                    'item' => get_string('resetassessments', 'mod_workshop'),
                    'error' => false,
                );
            } else {
                $status[] = array(
                    'component' => $componentstr,
                    'item' => get_string('resetassessments', 'mod_workshop'),
                    'error' => $result,
                );
            }
        }

        if (!empty($data->reset_workshop_submissions)) {
                        $result = $this->reset_userdata_submissions($data);
            if ($result === true) {
                $status[] = array(
                    'component' => $componentstr,
                    'item' => get_string('resetsubmissions', 'mod_workshop'),
                    'error' => false,
                );
            } else {
                $status[] = array(
                    'component' => $componentstr,
                    'item' => get_string('resetsubmissions', 'mod_workshop'),
                    'error' => $result,
                );
            }
        }

        if (!empty($data->reset_workshop_phase)) {
                                    $this->reset_phase();
            $status[] = array(
                'component' => $componentstr,
                'item' => get_string('resetsubmissions', 'mod_workshop'),
                'error' => false,
            );
        }

        return $status;
    }


            
    
    protected function aggregate_submission_grades_process(array $assessments) {
        global $DB;

        $submissionid   = null;         $current        = null;         $finalgrade     = null;         $sumgrades      = 0;
        $sumweights     = 0;

        foreach ($assessments as $assessment) {
            if (is_null($submissionid)) {
                                $submissionid = $assessment->submissionid;
            }
            if (is_null($current)) {
                                $current = $assessment->submissiongrade;
            }
            if (is_null($assessment->grade)) {
                                continue;
            }
            if ($assessment->weight == 0) {
                                continue;
            }
            $sumgrades  += $assessment->grade * $assessment->weight;
            $sumweights += $assessment->weight;
        }
        if ($sumweights > 0 and is_null($finalgrade)) {
            $finalgrade = grade_floatval($sumgrades / $sumweights);
        }
                if (grade_floats_different($finalgrade, $current)) {
                        $record = new stdclass();
            $record->id = $submissionid;
            $record->grade = $finalgrade;
            $record->timegraded = time();
            $DB->update_record('workshop_submissions', $record);
        }
    }

    
    protected function aggregate_grading_grades_process(array $assessments, $timegraded = null) {
        global $DB;

        $reviewerid = null;         $current    = null;         $finalgrade = null;         $agid       = null;         $sumgrades  = 0;
        $count      = 0;

        if (is_null($timegraded)) {
            $timegraded = time();
        }

        foreach ($assessments as $assessment) {
            if (is_null($reviewerid)) {
                                $reviewerid = $assessment->reviewerid;
            }
            if (is_null($agid)) {
                                $agid = $assessment->aggregationid;
            }
            if (is_null($current)) {
                                $current = $assessment->aggregatedgrade;
            }
            if (!is_null($assessment->gradinggradeover)) {
                                $sumgrades += $assessment->gradinggradeover;
                $count++;
            } else {
                if (!is_null($assessment->gradinggrade)) {
                    $sumgrades += $assessment->gradinggrade;
                    $count++;
                }
            }
        }
        if ($count > 0) {
            $finalgrade = grade_floatval($sumgrades / $count);
        }

                $params = array(
            'context' => $this->context,
            'courseid' => $this->course->id,
            'relateduserid' => $reviewerid
        );

                if (grade_floats_different($finalgrade, $current)) {
            $params['other'] = array(
                'currentgrade' => $current,
                'finalgrade' => $finalgrade
            );

                        if (is_null($agid)) {
                                $record = new stdclass();
                $record->workshopid = $this->id;
                $record->userid = $reviewerid;
                $record->gradinggrade = $finalgrade;
                $record->timegraded = $timegraded;
                $record->id = $DB->insert_record('workshop_aggregations', $record);
                $params['objectid'] = $record->id;
                $event = \mod_workshop\event\assessment_evaluated::create($params);
                $event->trigger();
            } else {
                $record = new stdclass();
                $record->id = $agid;
                $record->gradinggrade = $finalgrade;
                $record->timegraded = $timegraded;
                $DB->update_record('workshop_aggregations', $record);
                $params['objectid'] = $agid;
                $event = \mod_workshop\event\assessment_reevaluated::create($params);
                $event->trigger();
            }
        }
    }

    
    protected function get_users_with_capability_sql($capability, $musthavesubmission, $groupid) {
        global $CFG;
        
        static $inc = 0;
        $inc++;

                                if (empty($groupid) and $this->cm->groupingid) {
            $groupingid = $this->cm->groupingid;
            $groupinggroupids = array_keys(groups_get_all_groups($this->cm->course, 0, $this->cm->groupingid, 'g.id'));
            $sql = array();
            $params = array();
            foreach ($groupinggroupids as $groupinggroupid) {
                if ($groupinggroupid > 0) {                     list($gsql, $gparams) = $this->get_users_with_capability_sql($capability, $musthavesubmission, $groupinggroupid);
                    $sql[] = $gsql;
                    $params = array_merge($params, $gparams);
                }
            }
            $sql = implode(PHP_EOL." UNION ".PHP_EOL, $sql);
            return array($sql, $params);
        }

        list($esql, $params) = get_enrolled_sql($this->context, $capability, $groupid, true);

        $userfields = user_picture::fields('u');

        $sql = "SELECT $userfields
                  FROM {user} u
                  JOIN ($esql) je ON (je.id = u.id AND u.deleted = 0) ";

        if ($musthavesubmission) {
            $sql .= " JOIN {workshop_submissions} ws ON (ws.authorid = u.id AND ws.example = 0 AND ws.workshopid = :workshopid{$inc}) ";
            $params['workshopid'.$inc] = $this->id;
        }

                        $info = new \core_availability\info_module($this->cm);
        list ($listsql, $listparams) = $info->get_user_list_sql(false);
        if ($listsql) {
            $sql .= " JOIN ($listsql) restricted ON restricted.id = u.id ";
            $params = array_merge($params, $listparams);
        }

        return array($sql, $params);
    }

    
    protected function get_participants_sql($musthavesubmission=false, $groupid=0) {

        list($sql1, $params1) = $this->get_users_with_capability_sql('mod/workshop:submit', $musthavesubmission, $groupid);
        list($sql2, $params2) = $this->get_users_with_capability_sql('mod/workshop:peerassess', $musthavesubmission, $groupid);

        if (empty($sql1) or empty($sql2)) {
            if (empty($sql1) and empty($sql2)) {
                return array('', array());
            } else if (empty($sql1)) {
                $sql = $sql2;
                $params = $params2;
            } else {
                $sql = $sql1;
                $params = $params1;
            }
        } else {
            $sql = $sql1.PHP_EOL." UNION ".PHP_EOL.$sql2;
            $params = array_merge($params1, $params2);
        }

        return array($sql, $params);
    }

    
    protected function available_phases_list() {
        return array(
            self::PHASE_SETUP       => true,
            self::PHASE_SUBMISSION  => true,
            self::PHASE_ASSESSMENT  => true,
            self::PHASE_EVALUATION  => true,
            self::PHASE_CLOSED      => true,
        );
    }

    
    protected function log_convert_url(moodle_url $fullurl) {
        static $baseurl;

        if (!isset($baseurl)) {
            $baseurl = new moodle_url('/mod/workshop/');
            $baseurl = $baseurl->out();
        }

        return substr($fullurl->out(), strlen($baseurl));
    }

    
    protected function reset_userdata_assessments(stdClass $data) {
        global $DB;

        $sql = "SELECT a.id
                  FROM {workshop_assessments} a
                  JOIN {workshop_submissions} s ON (a.submissionid = s.id)
                 WHERE s.workshopid = :workshopid
                       AND (s.example = 0 OR (s.example = 1 AND a.weight = 0))";

        $assessments = $DB->get_records_sql($sql, array('workshopid' => $this->id));
        $this->delete_assessment(array_keys($assessments));

        $DB->delete_records('workshop_aggregations', array('workshopid' => $this->id));

        return true;
    }

    
    protected function reset_userdata_submissions(stdClass $data) {
        global $DB;

        $submissions = $this->get_submissions();
        foreach ($submissions as $submission) {
            $this->delete_submission($submission);
        }

        return true;
    }

    
    protected function reset_phase() {
        global $DB;

        $DB->set_field('workshop', 'phase', self::PHASE_SETUP, array('id' => $this->id));
        $this->phase = self::PHASE_SETUP;
    }
}



class workshop_user_plan implements renderable {

    
    public $userid;
    
    public $workshop;
    
    public $phases = array();
    
    protected $examples = null;

    
    public function __construct(workshop $workshop, $userid) {
        global $DB;

        $this->workshop = $workshop;
        $this->userid   = $userid;

                                $phase = new stdclass();
        $phase->title = get_string('phasesetup', 'workshop');
        $phase->tasks = array();
        if (has_capability('moodle/course:manageactivities', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('taskintro', 'workshop');
            $task->link = $workshop->updatemod_url();
            $task->completed = !(trim($workshop->intro) == '');
            $phase->tasks['intro'] = $task;
        }
        if (has_capability('moodle/course:manageactivities', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('taskinstructauthors', 'workshop');
            $task->link = $workshop->updatemod_url();
            $task->completed = !(trim($workshop->instructauthors) == '');
            $phase->tasks['instructauthors'] = $task;
        }
        if (has_capability('mod/workshop:editdimensions', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('editassessmentform', 'workshop');
            $task->link = $workshop->editform_url();
            if ($workshop->grading_strategy_instance()->form_ready()) {
                $task->completed = true;
            } elseif ($workshop->phase > workshop::PHASE_SETUP) {
                $task->completed = false;
            }
            $phase->tasks['editform'] = $task;
        }
        if ($workshop->useexamples and has_capability('mod/workshop:manageexamples', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('prepareexamples', 'workshop');
            if ($DB->count_records('workshop_submissions', array('example' => 1, 'workshopid' => $workshop->id)) > 0) {
                $task->completed = true;
            } elseif ($workshop->phase > workshop::PHASE_SETUP) {
                $task->completed = false;
            }
            $phase->tasks['prepareexamples'] = $task;
        }
        if (empty($phase->tasks) and $workshop->phase == workshop::PHASE_SETUP) {
                                    $task = new stdclass();
            $task->title = get_string('undersetup', 'workshop');
            $task->completed = 'info';
            $phase->tasks['setupinfo'] = $task;
        }
        $this->phases[workshop::PHASE_SETUP] = $phase;

                                $phase = new stdclass();
        $phase->title = get_string('phasesubmission', 'workshop');
        $phase->tasks = array();
        if (has_capability('moodle/course:manageactivities', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('taskinstructreviewers', 'workshop');
            $task->link = $workshop->updatemod_url();
            if (trim($workshop->instructreviewers)) {
                $task->completed = true;
            } elseif ($workshop->phase >= workshop::PHASE_ASSESSMENT) {
                $task->completed = false;
            }
            $phase->tasks['instructreviewers'] = $task;
        }
        if ($workshop->useexamples and $workshop->examplesmode == workshop::EXAMPLES_BEFORE_SUBMISSION
                and has_capability('mod/workshop:submit', $workshop->context, $userid, false)
                    and !has_capability('mod/workshop:manageexamples', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('exampleassesstask', 'workshop');
            $examples = $this->get_examples();
            $a = new stdclass();
            $a->expected = count($examples);
            $a->assessed = 0;
            foreach ($examples as $exampleid => $example) {
                if (!is_null($example->grade)) {
                    $a->assessed++;
                }
            }
            $task->details = get_string('exampleassesstaskdetails', 'workshop', $a);
            if ($a->assessed == $a->expected) {
                $task->completed = true;
            } elseif ($workshop->phase >= workshop::PHASE_ASSESSMENT) {
                $task->completed = false;
            }
            $phase->tasks['examples'] = $task;
        }
        if (has_capability('mod/workshop:submit', $workshop->context, $userid, false)) {
            $task = new stdclass();
            $task->title = get_string('tasksubmit', 'workshop');
            $task->link = $workshop->submission_url();
            if ($DB->record_exists('workshop_submissions', array('workshopid'=>$workshop->id, 'example'=>0, 'authorid'=>$userid))) {
                $task->completed = true;
            } elseif ($workshop->phase >= workshop::PHASE_ASSESSMENT) {
                $task->completed = false;
            } else {
                $task->completed = null;                }
            $phase->tasks['submit'] = $task;
        }
        if (has_capability('mod/workshop:allocate', $workshop->context, $userid)) {
            if ($workshop->phaseswitchassessment) {
                $task = new stdClass();
                $allocator = $DB->get_record('workshopallocation_scheduled', array('workshopid' => $workshop->id));
                if (empty($allocator)) {
                    $task->completed = false;
                } else if ($allocator->enabled and is_null($allocator->resultstatus)) {
                    $task->completed = true;
                } else if ($workshop->submissionend > time()) {
                    $task->completed = null;
                } else {
                    $task->completed = false;
                }
                $task->title = get_string('setup', 'workshopallocation_scheduled');
                $task->link = $workshop->allocation_url('scheduled');
                $phase->tasks['allocatescheduled'] = $task;
            }
            $task = new stdclass();
            $task->title = get_string('allocate', 'workshop');
            $task->link = $workshop->allocation_url();
            $numofauthors = $workshop->count_potential_authors(false);
            $numofsubmissions = $DB->count_records('workshop_submissions', array('workshopid'=>$workshop->id, 'example'=>0));
            $sql = 'SELECT COUNT(s.id) AS nonallocated
                      FROM {workshop_submissions} s
                 LEFT JOIN {workshop_assessments} a ON (a.submissionid=s.id)
                     WHERE s.workshopid = :workshopid AND s.example=0 AND a.submissionid IS NULL';
            $params['workshopid'] = $workshop->id;
            $numnonallocated = $DB->count_records_sql($sql, $params);
            if ($numofsubmissions == 0) {
                $task->completed = null;
            } elseif ($numnonallocated == 0) {
                $task->completed = true;
            } elseif ($workshop->phase > workshop::PHASE_SUBMISSION) {
                $task->completed = false;
            } else {
                $task->completed = null;                }
            $a = new stdclass();
            $a->expected    = $numofauthors;
            $a->submitted   = $numofsubmissions;
            $a->allocate    = $numnonallocated;
            $task->details  = get_string('allocatedetails', 'workshop', $a);
            unset($a);
            $phase->tasks['allocate'] = $task;

            if ($numofsubmissions < $numofauthors and $workshop->phase >= workshop::PHASE_SUBMISSION) {
                $task = new stdclass();
                $task->title = get_string('someuserswosubmission', 'workshop');
                $task->completed = 'info';
                $phase->tasks['allocateinfo'] = $task;
            }

        }
        if ($workshop->submissionstart) {
            $task = new stdclass();
            $task->title = get_string('submissionstartdatetime', 'workshop', workshop::timestamp_formats($workshop->submissionstart));
            $task->completed = 'info';
            $phase->tasks['submissionstartdatetime'] = $task;
        }
        if ($workshop->submissionend) {
            $task = new stdclass();
            $task->title = get_string('submissionenddatetime', 'workshop', workshop::timestamp_formats($workshop->submissionend));
            $task->completed = 'info';
            $phase->tasks['submissionenddatetime'] = $task;
        }
        if (($workshop->submissionstart < time()) and $workshop->latesubmissions) {
            $task = new stdclass();
            $task->title = get_string('latesubmissionsallowed', 'workshop');
            $task->completed = 'info';
            $phase->tasks['latesubmissionsallowed'] = $task;
        }
        if (isset($phase->tasks['submissionstartdatetime']) or isset($phase->tasks['submissionenddatetime'])) {
            if (has_capability('mod/workshop:ignoredeadlines', $workshop->context, $userid)) {
                $task = new stdclass();
                $task->title = get_string('deadlinesignored', 'workshop');
                $task->completed = 'info';
                $phase->tasks['deadlinesignored'] = $task;
            }
        }
        $this->phases[workshop::PHASE_SUBMISSION] = $phase;

                                $phase = new stdclass();
        $phase->title = get_string('phaseassessment', 'workshop');
        $phase->tasks = array();
        $phase->isreviewer = has_capability('mod/workshop:peerassess', $workshop->context, $userid);
        if ($workshop->phase == workshop::PHASE_SUBMISSION and $workshop->phaseswitchassessment
                and has_capability('mod/workshop:switchphase', $workshop->context, $userid)) {
            $task = new stdClass();
            $task->title = get_string('switchphase30auto', 'mod_workshop', workshop::timestamp_formats($workshop->submissionend));
            $task->completed = 'info';
            $phase->tasks['autoswitchinfo'] = $task;
        }
        if ($workshop->useexamples and $workshop->examplesmode == workshop::EXAMPLES_BEFORE_ASSESSMENT
                and $phase->isreviewer and !has_capability('mod/workshop:manageexamples', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('exampleassesstask', 'workshop');
            $examples = $workshop->get_examples_for_reviewer($userid);
            $a = new stdclass();
            $a->expected = count($examples);
            $a->assessed = 0;
            foreach ($examples as $exampleid => $example) {
                if (!is_null($example->grade)) {
                    $a->assessed++;
                }
            }
            $task->details = get_string('exampleassesstaskdetails', 'workshop', $a);
            if ($a->assessed == $a->expected) {
                $task->completed = true;
            } elseif ($workshop->phase > workshop::PHASE_ASSESSMENT) {
                $task->completed = false;
            }
            $phase->tasks['examples'] = $task;
        }
        if (empty($phase->tasks['examples']) or !empty($phase->tasks['examples']->completed)) {
            $phase->assessments = $workshop->get_assessments_by_reviewer($userid);
            $numofpeers     = 0;                $numofpeerstodo = 0;                $numofself      = 0;                $numofselftodo  = 0;                foreach ($phase->assessments as $a) {
                if ($a->authorid == $userid) {
                    $numofself++;
                    if (is_null($a->grade)) {
                        $numofselftodo++;
                    }
                } else {
                    $numofpeers++;
                    if (is_null($a->grade)) {
                        $numofpeerstodo++;
                    }
                }
            }
            unset($a);
            if ($numofpeers) {
                $task = new stdclass();
                if ($numofpeerstodo == 0) {
                    $task->completed = true;
                } elseif ($workshop->phase > workshop::PHASE_ASSESSMENT) {
                    $task->completed = false;
                }
                $a = new stdclass();
                $a->total = $numofpeers;
                $a->todo  = $numofpeerstodo;
                $task->title = get_string('taskassesspeers', 'workshop');
                $task->details = get_string('taskassesspeersdetails', 'workshop', $a);
                unset($a);
                $phase->tasks['assesspeers'] = $task;
            }
            if ($workshop->useselfassessment and $numofself) {
                $task = new stdclass();
                if ($numofselftodo == 0) {
                    $task->completed = true;
                } elseif ($workshop->phase > workshop::PHASE_ASSESSMENT) {
                    $task->completed = false;
                }
                $task->title = get_string('taskassessself', 'workshop');
                $phase->tasks['assessself'] = $task;
            }
        }
        if ($workshop->assessmentstart) {
            $task = new stdclass();
            $task->title = get_string('assessmentstartdatetime', 'workshop', workshop::timestamp_formats($workshop->assessmentstart));
            $task->completed = 'info';
            $phase->tasks['assessmentstartdatetime'] = $task;
        }
        if ($workshop->assessmentend) {
            $task = new stdclass();
            $task->title = get_string('assessmentenddatetime', 'workshop', workshop::timestamp_formats($workshop->assessmentend));
            $task->completed = 'info';
            $phase->tasks['assessmentenddatetime'] = $task;
        }
        if (isset($phase->tasks['assessmentstartdatetime']) or isset($phase->tasks['assessmentenddatetime'])) {
            if (has_capability('mod/workshop:ignoredeadlines', $workshop->context, $userid)) {
                $task = new stdclass();
                $task->title = get_string('deadlinesignored', 'workshop');
                $task->completed = 'info';
                $phase->tasks['deadlinesignored'] = $task;
            }
        }
        $this->phases[workshop::PHASE_ASSESSMENT] = $phase;

                                $phase = new stdclass();
        $phase->title = get_string('phaseevaluation', 'workshop');
        $phase->tasks = array();
        if (has_capability('mod/workshop:overridegrades', $workshop->context)) {
            $expected = $workshop->count_potential_authors(false);
            $calculated = $DB->count_records_select('workshop_submissions',
                    'workshopid = ? AND (grade IS NOT NULL OR gradeover IS NOT NULL)', array($workshop->id));
            $task = new stdclass();
            $task->title = get_string('calculatesubmissiongrades', 'workshop');
            $a = new stdclass();
            $a->expected    = $expected;
            $a->calculated  = $calculated;
            $task->details  = get_string('calculatesubmissiongradesdetails', 'workshop', $a);
            if ($calculated >= $expected) {
                $task->completed = true;
            } elseif ($workshop->phase > workshop::PHASE_EVALUATION) {
                $task->completed = false;
            }
            $phase->tasks['calculatesubmissiongrade'] = $task;

            $expected = $workshop->count_potential_reviewers(false);
            $calculated = $DB->count_records_select('workshop_aggregations',
                    'workshopid = ? AND gradinggrade IS NOT NULL', array($workshop->id));
            $task = new stdclass();
            $task->title = get_string('calculategradinggrades', 'workshop');
            $a = new stdclass();
            $a->expected    = $expected;
            $a->calculated  = $calculated;
            $task->details  = get_string('calculategradinggradesdetails', 'workshop', $a);
            if ($calculated >= $expected) {
                $task->completed = true;
            } elseif ($workshop->phase > workshop::PHASE_EVALUATION) {
                $task->completed = false;
            }
            $phase->tasks['calculategradinggrade'] = $task;

        } elseif ($workshop->phase == workshop::PHASE_EVALUATION) {
            $task = new stdclass();
            $task->title = get_string('evaluategradeswait', 'workshop');
            $task->completed = 'info';
            $phase->tasks['evaluateinfo'] = $task;
        }

        if (has_capability('moodle/course:manageactivities', $workshop->context, $userid)) {
            $task = new stdclass();
            $task->title = get_string('taskconclusion', 'workshop');
            $task->link = $workshop->updatemod_url();
            if (trim($workshop->conclusion)) {
                $task->completed = true;
            } elseif ($workshop->phase >= workshop::PHASE_EVALUATION) {
                $task->completed = false;
            }
            $phase->tasks['conclusion'] = $task;
        }

        $this->phases[workshop::PHASE_EVALUATION] = $phase;

                                $phase = new stdclass();
        $phase->title = get_string('phaseclosed', 'workshop');
        $phase->tasks = array();
        $this->phases[workshop::PHASE_CLOSED] = $phase;

                foreach ($this->phases as $phasecode => $phase) {
            $phase->title       = isset($phase->title)      ? $phase->title     : '';
            $phase->tasks       = isset($phase->tasks)      ? $phase->tasks     : array();
            if ($phasecode == $workshop->phase) {
                $phase->active = true;
            } else {
                $phase->active = false;
            }
            if (!isset($phase->actions)) {
                $phase->actions = array();
            }

            foreach ($phase->tasks as $taskcode => $task) {
                $task->title        = isset($task->title)       ? $task->title      : '';
                $task->link         = isset($task->link)        ? $task->link       : null;
                $task->details      = isset($task->details)     ? $task->details    : '';
                $task->completed    = isset($task->completed)   ? $task->completed  : null;
            }
        }

                if (has_capability('mod/workshop:switchphase', $workshop->context, $userid)) {
            foreach ($this->phases as $phasecode => $phase) {
                if (! $phase->active) {
                    $action = new stdclass();
                    $action->type = 'switchphase';
                    $action->url  = $workshop->switchphase_url($phasecode);
                    $phase->actions[] = $action;
                }
            }
        }
    }

    
    public function get_examples() {
        if (is_null($this->examples)) {
            $this->examples = $this->workshop->get_examples_for_reviewer($this->userid);
        }
        return $this->examples;
    }
}


abstract class workshop_submission_base {

    
    protected $anonymous;

    
    protected $fields = array();

    
    protected $workshop;

    
    public function __construct(workshop $workshop, stdClass $submission, $showauthor = false) {

        $this->workshop = $workshop;

        foreach ($this->fields as $field) {
            if (!property_exists($submission, $field)) {
                throw new coding_exception('Submission record must provide public property ' . $field);
            }
            if (!property_exists($this, $field)) {
                throw new coding_exception('Renderable component must accept public property ' . $field);
            }
            $this->{$field} = $submission->{$field};
        }

        if ($showauthor) {
            $this->anonymous = false;
        } else {
            $this->anonymize();
        }
    }

    
    public function anonymize() {
        $authorfields = explode(',', user_picture::fields());
        foreach ($authorfields as $field) {
            $prefixedusernamefield = 'author' . $field;
            unset($this->{$prefixedusernamefield});
        }
        $this->anonymous = true;
    }

    
    public function is_anonymous() {
        return $this->anonymous;
    }
}


class workshop_submission_summary extends workshop_submission_base implements renderable {

    
    public $id;
    
    public $title;
    
    public $status;
    
    public $timecreated;
    
    public $timemodified;
    
    public $authorid;
    
    public $authorfirstname;
    
    public $authorlastname;
    
    public $authorfirstnamephonetic;
    
    public $authorlastnamephonetic;
    
    public $authormiddlename;
    
    public $authoralternatename;
    
    public $authorpicture;
    
    public $authorimagealt;
    
    public $authoremail;
    
    public $url;

    
    protected $fields = array(
        'id', 'title', 'timecreated', 'timemodified',
        'authorid', 'authorfirstname', 'authorlastname', 'authorfirstnamephonetic', 'authorlastnamephonetic',
        'authormiddlename', 'authoralternatename', 'authorpicture',
        'authorimagealt', 'authoremail');
}


class workshop_submission extends workshop_submission_summary implements renderable {

    
    public $content;
    
    public $contentformat;
    
    public $contenttrust;
    
    public $attachment;

    
    protected $fields = array(
        'id', 'title', 'timecreated', 'timemodified', 'content', 'contentformat', 'contenttrust',
        'attachment', 'authorid', 'authorfirstname', 'authorlastname', 'authorfirstnamephonetic', 'authorlastnamephonetic',
        'authormiddlename', 'authoralternatename', 'authorpicture', 'authorimagealt', 'authoremail');
}


class workshop_example_submission_summary extends workshop_submission_base implements renderable {

    
    public $id;
    
    public $title;
    
    public $status;
    
    public $gradeinfo;
    
    public $url;
    
    public $editurl;
    
    public $assesslabel;
    
    public $assessurl;
    
    public $editable = false;

    
    protected $fields = array('id', 'title');

    
    public function is_anonymous() {
        return true;
    }
}


class workshop_example_submission extends workshop_example_submission_summary implements renderable {

    
    public $content;
    
    public $contentformat;
    
    public $contenttrust;
    
    public $attachment;

    
    protected $fields = array('id', 'title', 'content', 'contentformat', 'contenttrust', 'attachment');
}



abstract class workshop_assessment_base {

    
    public $title = '';

    
    public $form;

    
    public $url;

    
    public $realgrade = null;

    
    public $maxgrade;

    
    public $reviewer = null;

    
    public $author = null;

    
    public $actions = array();

    
    protected $fields = array();

    
    protected $workshop;

    
    public function __construct(workshop $workshop, stdClass $record, array $options = array()) {

        $this->workshop = $workshop;
        $this->validate_raw_record($record);

        foreach ($this->fields as $field) {
            if (!property_exists($record, $field)) {
                throw new coding_exception('Assessment record must provide public property ' . $field);
            }
            if (!property_exists($this, $field)) {
                throw new coding_exception('Renderable component must accept public property ' . $field);
            }
            $this->{$field} = $record->{$field};
        }

        if (!empty($options['showreviewer'])) {
            $this->reviewer = user_picture::unalias($record, null, 'revieweridx', 'reviewer');
        }

        if (!empty($options['showauthor'])) {
            $this->author = user_picture::unalias($record, null, 'authorid', 'author');
        }
    }

    
    public function add_action(moodle_url $url, $label, $method = 'get') {

        $action = new stdClass();
        $action->url = $url;
        $action->label = $label;
        $action->method = $method;

        $this->actions[] = $action;
    }

    
    protected function validate_raw_record(stdClass $record) {
            }
}



class workshop_assessment extends workshop_assessment_base implements renderable {

    
    public $id;

    
    public $submissionid;

    
    public $weight;

    
    public $timecreated;

    
    public $timemodified;

    
    public $grade;

    
    public $gradinggrade;

    
    public $gradinggradeover;

    
    public $feedbackauthor;

    
    public $feedbackauthorformat;

    
    public $feedbackauthorattachment;

    
    protected $fields = array('id', 'submissionid', 'weight', 'timecreated',
        'timemodified', 'grade', 'gradinggrade', 'gradinggradeover', 'feedbackauthor',
        'feedbackauthorformat', 'feedbackauthorattachment');

    
    public function get_overall_feedback_content() {

        if ($this->workshop->overallfeedbackmode == 0) {
            return false;
        }

        if (trim($this->feedbackauthor) === '') {
            return null;
        }

        $content = file_rewrite_pluginfile_urls($this->feedbackauthor, 'pluginfile.php', $this->workshop->context->id,
            'mod_workshop', 'overallfeedback_content', $this->id);
        $content = format_text($content, $this->feedbackauthorformat,
            array('overflowdiv' => true, 'context' => $this->workshop->context));

        return $content;
    }

    
    public function get_overall_feedback_attachments() {

        if ($this->workshop->overallfeedbackmode == 0) {
            return false;
        }

        if ($this->workshop->overallfeedbackfiles == 0) {
            return false;
        }

        if (empty($this->feedbackauthorattachment)) {
            return array();
        }

        $attachments = array();
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->workshop->context->id, 'mod_workshop', 'overallfeedback_attachment', $this->id);
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $filepath = $file->get_filepath();
            $filename = $file->get_filename();
            $fileurl = moodle_url::make_pluginfile_url($this->workshop->context->id, 'mod_workshop',
                'overallfeedback_attachment', $this->id, $filepath, $filename, true);
            $previewurl = new moodle_url(moodle_url::make_pluginfile_url($this->workshop->context->id, 'mod_workshop',
                'overallfeedback_attachment', $this->id, $filepath, $filename, false), array('preview' => 'bigthumb'));
            $attachments[] = (object)array(
                'filepath' => $filepath,
                'filename' => $filename,
                'fileurl' => $fileurl,
                'previewurl' => $previewurl,
                'mimetype' => $file->get_mimetype(),

            );
        }

        return $attachments;
    }
}



class workshop_example_assessment extends workshop_assessment implements renderable {

    
    protected function validate_raw_record(stdClass $record) {
        if ($record->weight != 0) {
            throw new coding_exception('Invalid weight of example submission assessment');
        }
        parent::validate_raw_record($record);
    }
}



class workshop_example_reference_assessment extends workshop_assessment implements renderable {

    
    protected function validate_raw_record(stdClass $record) {
        if ($record->weight != 1) {
            throw new coding_exception('Invalid weight of the reference example submission assessment');
        }
        parent::validate_raw_record($record);
    }
}



class workshop_message implements renderable {

    const TYPE_INFO     = 10;
    const TYPE_OK       = 20;
    const TYPE_ERROR    = 30;

    
    protected $text = '';
    
    protected $type = self::TYPE_INFO;
    
    protected $actionurl = null;
    
    protected $actionlabel = '';

    
    public function __construct($text = null, $type = self::TYPE_INFO) {
        $this->set_text($text);
        $this->set_type($type);
    }

    
    public function set_text($text) {
        $this->text = $text;
    }

    
    public function set_type($type = self::TYPE_INFO) {
        if (in_array($type, array(self::TYPE_OK, self::TYPE_ERROR, self::TYPE_INFO))) {
            $this->type = $type;
        } else {
            throw new coding_exception('Unknown message type.');
        }
    }

    
    public function set_action(moodle_url $url, $label) {
        $this->actionurl    = $url;
        $this->actionlabel  = $label;
    }

    
    public function get_message() {
        return s($this->text);
    }

    
    public function get_type() {
        return $this->type;
    }

    
    public function get_action_url() {
        return $this->actionurl;
    }

    
    public function get_action_label() {
        return $this->actionlabel;
    }
}



class workshop_grading_report implements renderable {

    
    protected $data;
    
    protected $options;

    
    public function __construct(stdClass $data, stdClass $options) {
        $this->data     = $data;
        $this->options  = $options;
    }

    
    public function get_data() {
        return $this->data;
    }

    
    public function get_options() {
        return $this->options;
    }
}



abstract class workshop_feedback {

    
    protected $provider = null;

    
    protected $content = null;

    
    protected $format = null;

    
    public function get_provider() {

        if (is_null($this->provider)) {
            throw new coding_exception('Feedback provider not set');
        }

        return $this->provider;
    }

    
    public function get_content() {

        if (is_null($this->content)) {
            throw new coding_exception('Feedback content not set');
        }

        return $this->content;
    }

    
    public function get_format() {

        if (is_null($this->format)) {
            throw new coding_exception('Feedback text format not set');
        }

        return $this->format;
    }
}



class workshop_feedback_author extends workshop_feedback implements renderable {

    
    public function __construct(stdClass $submission) {

        $this->provider = user_picture::unalias($submission, null, 'gradeoverbyx', 'gradeoverby');
        $this->content  = $submission->feedbackauthor;
        $this->format   = $submission->feedbackauthorformat;
    }
}



class workshop_feedback_reviewer extends workshop_feedback implements renderable {

    
    public function __construct(stdClass $assessment) {

        $this->provider = user_picture::unalias($assessment, null, 'gradinggradeoverbyx', 'overby');
        $this->content  = $assessment->feedbackreviewer;
        $this->format   = $assessment->feedbackreviewerformat;
    }
}



class workshop_final_grades implements renderable {

    
    public $submissiongrade = null;

    
    public $assessmentgrade = null;
}
