<?php



namespace tool_lpmigrate;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use moodle_exception;
use core_competency\api;
use core_competency\competency;
use core_competency\course_competency;
use core_competency\course_module_competency;


class framework_processor {

    
    protected $coursescompetencies = array();
    
    protected $coursescompetenciesoutcomes = array();
    
    protected $modulecompetencies = array();
    
    protected $modulecompetenciesoutcomes = array();
    
    protected $fromids = array();
    
    protected $mappings = array();

    
    protected $coursesfound = array();
    
    protected $cmsfound = array();
    
    protected $coursecompetencyexpectedmigrations = 0;
    
    protected $coursecompetencymigrations = 0;
    
    protected $coursecompetencyremovals = 0;
    
    protected $modulecompetencyexpectedmigrations = 0;
    
    protected $modulecompetencymigrations = 0;
    
    protected $modulecompetencyremovals = 0;
    
    protected $missingmappings = array();
    
    protected $errors = array();
    
    protected $warnings = array();

    
    protected $allowedcourses = array();
    
    protected $coursestartdatefrom = 0;
    
    protected $disallowedcourses = array();
    
    protected $removeoriginalwhenalreadypresent = false;
    
    protected $removewhenmappingismissing = false;

    
    protected $proceeded = false;
    
    protected $mapper;
    
    protected $progress;

    
    public function __construct(framework_mapper $mapper, \core\progress\base $progress = null) {
        $this->mapper = $mapper;

        if ($progress == null) {
            $progress = new \core\progress\none();
        }
        $this->progress = $progress;
    }

    
    protected function process_mapping() {
        $this->mappings = $this->mapper->get_mappings();
        $this->fromids = $this->mapper->get_all_from();
    }

    
    protected function find_coursescompetencies() {
        global $DB;
        $this->progress->start_progress(get_string('findingcoursecompetencies', 'tool_lpmigrate'), 3);
        $this->progress->increment_progress();

        $joins = array();
        $conditions = array();
        $params = array();

                list($insql, $inparams) = $DB->get_in_or_equal($this->fromids, SQL_PARAMS_NAMED);
        $conditions[] = "c.id $insql";
        $params += $inparams;

                if (!empty($this->allowedcourses)) {
            list($insql, $inparams) = $DB->get_in_or_equal($this->allowedcourses, SQL_PARAMS_NAMED);
            $conditions[] = "cc.courseid $insql";
            $params += $inparams;
        }
        if (!empty($this->disallowedcourses)) {
            list($insql, $inparams) = $DB->get_in_or_equal($this->disallowedcourses, SQL_PARAMS_NAMED, 'param', false);
            $conditions[] = "cc.courseid $insql";
            $params += $inparams;
        }

                if (!empty($this->coursestartdatefrom)) {
            $joins[] = "JOIN {course} co
                          ON co.id = cc.courseid";
            $conditions[] = "co.startdate >= :startdate";
            $params += array('startdate' => $this->coursestartdatefrom);
        }

                $ccs = array();
        $ccsoutcomes = array();
        $joins = implode(' ', $joins);
        $conditions = implode(' AND ', $conditions);
        $sql = "SELECT cc.id, cc.courseid, cc.competencyid, cc.ruleoutcome
                  FROM {" . course_competency::TABLE . "} cc
                  JOIN {" . competency::TABLE . "} c
                    ON c.id = cc.competencyid
                       $joins
                 WHERE $conditions
              ORDER BY cc.sortorder, cc.id";

        $records = $DB->get_recordset_sql($sql, $params);
        $this->progress->increment_progress();

        foreach ($records as $record) {
            if (!isset($ccs[$record->courseid])) {
                $ccs[$record->courseid] = array();
                $ccsoutcomes[$record->courseid] = array();
            }
            $ccs[$record->courseid][] = $record->competencyid;
            $ccsoutcomes[$record->courseid][$record->competencyid] = $record->ruleoutcome;
        }
        $records->close();

        $this->coursescompetencies = $ccs;
        $this->coursescompetenciesoutcomes = $ccsoutcomes;
        $this->coursesfound = $ccs;

        $this->progress->increment_progress();
        $this->progress->end_progress();
    }

    
    protected function find_modulecompetencies() {
        global $DB;
        if (empty($this->coursescompetencies)) {
            return;
        }

        $this->progress->start_progress(get_string('findingmodulecompetencies', 'tool_lpmigrate'), 3);
        $this->progress->increment_progress();

                list($inidsql, $inidparams) = $DB->get_in_or_equal($this->fromids, SQL_PARAMS_NAMED);

                list($incoursesql, $incourseparams) = $DB->get_in_or_equal(array_keys($this->coursescompetencies), SQL_PARAMS_NAMED);
        $sql = "SELECT mc.id, cm.course AS courseid, mc.cmid, mc.competencyid, mc.ruleoutcome
                  FROM {" . course_module_competency::TABLE . "} mc
                  JOIN {course_modules} cm
                    ON cm.id = mc.cmid
                   AND cm.course $incoursesql
                  JOIN {" . competency::TABLE . "} c
                    ON c.id = mc.competencyid
                 WHERE c.id $inidsql
              ORDER BY mc.sortorder, mc.id";
        $params = $inidparams + $incourseparams;

        $records = $DB->get_recordset_sql($sql, $params);
        $this->progress->increment_progress();
        $cmsfound = array();

        $cmcs = array();
        $cmcsoutcomes = array();
        foreach ($records as $record) {
            if (!isset($cmcs[$record->courseid])) {
                $cmcs[$record->courseid] = array();
                $cmcsoutcomes[$record->courseid] = array();
            }
            if (!isset($cmcs[$record->courseid][$record->cmid])) {
                $cmcs[$record->courseid][$record->cmid] = array();
                $cmcsoutcomes[$record->courseid][$record->cmid] = array();
            }
            $cmcs[$record->courseid][$record->cmid][] = $record->competencyid;
            $cmcsoutcomes[$record->courseid][$record->cmid][$record->competencyid] = $record->ruleoutcome;
            $cmsfound[$record->cmid] = true;
        }
        $records->close();

        $this->modulecompetencies = $cmcs;
        $this->modulecompetenciesoutcomes = $cmcsoutcomes;
        $this->cmsfound = $cmsfound;

        $this->progress->increment_progress();
        $this->progress->end_progress();
    }

    
    public function get_cms_found() {
        return $this->cmsfound;
    }

    
    public function get_cms_found_count() {
        return count($this->cmsfound);
    }

    
    public function get_courses_found() {
        return $this->coursesfound;
    }

    
    public function get_courses_found_count() {
        return count($this->coursesfound);
    }

    
    public function get_course_competency_migrations() {
        return $this->coursecompetencymigrations;
    }

    
    public function get_course_competency_removals() {
        return $this->coursecompetencyremovals;
    }

    
    public function get_expected_course_competency_migrations() {
        return $this->coursecompetencyexpectedmigrations;
    }

    
    public function get_expected_module_competency_migrations() {
        return $this->modulecompetencyexpectedmigrations;
    }

    
    public function get_module_competency_migrations() {
        return $this->modulecompetencymigrations;
    }

    
    public function get_module_competency_removals() {
        return $this->modulecompetencyremovals;
    }

    
    public function get_errors() {
        return $this->errors;
    }

    
    public function get_missing_mappings() {
        if (!$this->has_run()) {
            throw new coding_exception('The processor has not run yet.');
        }
        return $this->missingmappings;
    }

    
    public function get_warnings() {
        return $this->warnings;
    }

    
    public function has_run() {
        return $this->proceeded;
    }

    
    protected function log_error($courseid, $competencyid, $cmid, $message) {
        $this->errors[] = array(
            'courseid' => $courseid,
            'competencyid' => $competencyid,
            'cmid' => $cmid,
            'message' => $message
        );
    }

    
    protected function log_warning($courseid, $competencyid, $cmid, $message) {
        $this->warnings[] = array(
            'courseid' => $courseid,
            'competencyid' => $competencyid,
            'cmid' => $cmid,
            'message' => $message
        );
    }

    
    public function proceed() {
        if ($this->has_run()) {
            throw new coding_exception('The processor has already run.');
        } else if (!$this->mapper->has_mappings()) {
            throw new coding_exception('Mapping was not set.');
        }

        $this->proceeded = true;
        $this->process_mapping();
        $this->find_coursescompetencies();
        $this->find_modulecompetencies();
        $this->process_courses();
    }

    
    protected function process_courses() {
        global $DB;
        $this->progress->start_progress(get_string('migratingcourses', 'tool_lpmigrate'), count($this->coursescompetencies));

                foreach ($this->coursescompetencies as $courseid => $competencyids) {
            $this->progress->increment_progress();

            $competenciestoremovefromcourse = array();
            $skipcompetencies = array();

                        foreach ($competencyids as $key => $competencyid) {
                $this->coursecompetencyexpectedmigrations++;
                $mapto = isset($this->mappings[$competencyid]) ? $this->mappings[$competencyid] : false;

                                if ($mapto === false) {
                    $this->missingmappings[$competencyid] = true;

                    if ($this->removewhenmappingismissing) {
                        $competenciestoremovefromcourse[$competencyid] = true;
                    }

                    continue;
                }

                $transaction = $DB->start_delegated_transaction();
                try {
                                        if (api::add_competency_to_course($courseid, $mapto)) {

                                                $cc = course_competency::get_record(array('courseid' => $courseid, 'competencyid' => $mapto));

                                                api::set_course_competency_ruleoutcome($cc, $this->coursescompetenciesoutcomes[$courseid][$competencyid]);

                                                api::reorder_course_competency($courseid, $mapto, $competencyid);

                        $competenciestoremovefromcourse[$competencyid] = true;
                        $this->coursecompetencymigrations++;

                    } else {
                                                if ($this->removeoriginalwhenalreadypresent) {
                            $competenciestoremovefromcourse[$competencyid] = true;
                        } else {
                            $this->log_warning($courseid, $competencyid, null,
                                get_string('warningdestinationcoursecompetencyalreadyexists', 'tool_lpmigrate'));
                        }
                    }

                } catch (moodle_exception $e) {
                                        $skipcompetencies[$competencyid] = true;

                    $this->log_error($courseid, $competencyid, null,
                        get_string('errorwhilemigratingcoursecompetencywithexception', 'tool_lpmigrate', $e->getMessage()));

                    try {
                        $transaction->rollback($e);
                    } catch (moodle_exception $e) {
                                            }

                    continue;
                }
                $transaction->allow_commit();
            }

                        if (!empty($this->modulecompetencies[$courseid])) {
                foreach ($this->modulecompetencies[$courseid] as $cmid => $competencyids) {
                    foreach ($competencyids as $competencyid) {
                        $this->modulecompetencyexpectedmigrations++;

                                                if (!empty($skipcompetencies[$competencyid])) {
                            continue;
                        }

                        $remove = true;
                        $mapto = isset($this->mappings[$competencyid]) ? $this->mappings[$competencyid] : false;

                                                if ($mapto === false) {
                            if (!$this->removewhenmappingismissing) {
                                $remove = false;
                            }

                        } else {
                                                        $transaction = $DB->start_delegated_transaction();
                            try {
                                                                if (api::add_competency_to_course_module($cmid, $mapto)) {

                                                                        $mc = course_module_competency::get_record(array('cmid' => $cmid, 'competencyid' => $mapto));

                                                                        api::set_course_module_competency_ruleoutcome($mc,
                                        $this->modulecompetenciesoutcomes[$courseid][$cmid][$competencyid]);

                                                                        api::reorder_course_module_competency($cmid, $mapto, $competencyid);

                                    $this->modulecompetencymigrations++;

                                } else {
                                                                        if (!$this->removeoriginalwhenalreadypresent) {
                                        $remove = false;
                                        $competencieswithissues[$competencyid] = true;
                                        $this->log_warning($courseid, $competencyid, $cmid,
                                            get_string('warningdestinationmodulecompetencyalreadyexists', 'tool_lpmigrate'));
                                    }
                                }

                            } catch (moodle_exception $e) {
                                                                $competencieswithissues[$competencyid] = true;
                                $message = get_string('errorwhilemigratingmodulecompetencywithexception', 'tool_lpmigrate',
                                    $e->getMessage());
                                $this->log_error($courseid, $competencyid, $cmid, $message);

                                try {
                                    $transaction->rollback($e);
                                } catch (moodle_exception $e) {
                                                                    }

                                continue;
                            }
                            $transaction->allow_commit();
                        }

                        try {
                                                        if ($remove && api::remove_competency_from_course_module($cmid, $competencyid)) {
                                $this->modulecompetencyremovals++;
                            }
                        } catch (moodle_exception $e) {
                            $competencieswithissues[$competencyid] = true;
                            $this->log_warning($courseid, $competencyid, $cmid,
                                get_string('warningcouldnotremovemodulecompetency', 'tool_lpmigrate'));
                        }
                    }
                }
            }

                        foreach ($competenciestoremovefromcourse as $competencyid => $unused) {

                                if (isset($competencieswithissues[$competencyid])) {
                    continue;
                }

                try {
                                        api::remove_competency_from_course($courseid, $competencyid);
                    $this->coursecompetencyremovals++;
                } catch (moodle_exception $e) {
                    $this->log_warning($courseid, $competencyid, null,
                        get_string('warningcouldnotremovecoursecompetency', 'tool_lpmigrate'));
                }
            }
        }

        $this->progress->end_progress();
    }

    
    public function set_allowedcourses(array $courseids) {
        $this->allowedcourses = $courseids;
    }

    
    public function set_course_start_date_from($value) {
        $this->coursestartdatefrom = intval($value);
    }

    
    public function set_disallowedcourses(array $courseids) {
        $this->disallowedcourses = $courseids;
    }

    
    public function set_remove_original_when_destination_already_present($value) {
        $this->removeoriginalwhenalreadypresent = $value;
    }

    
    public function set_remove_when_mapping_is_missing($value) {
        $this->removewhenmappingismissing = $value;
    }

}
