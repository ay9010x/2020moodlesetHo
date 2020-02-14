<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/lib.php');  require_once($CFG->libdir . '/gradelib.php');


class workshop_best_evaluation extends workshop_evaluation {

    
    protected $workshop;

    
    protected $settings;

    
    public function __construct(workshop $workshop) {
        global $DB;
        $this->workshop = $workshop;
        $this->settings = $DB->get_record('workshopeval_best_settings', array('workshopid' => $this->workshop->id));
    }

    
    public function update_grading_grades(stdclass $settings, $restrict=null) {
        global $DB;

                if (empty($this->settings)) {
            $record = new stdclass();
            $record->workshopid = $this->workshop->id;
            $record->comparison = $settings->comparison;
            $DB->insert_record('workshopeval_best_settings', $record);
        } elseif ($this->settings->comparison != $settings->comparison) {
            $DB->set_field('workshopeval_best_settings', 'comparison', $settings->comparison,
                    array('workshopid' => $this->workshop->id));
        }

                $grader = $this->workshop->grading_strategy_instance();

                $diminfo = $grader->get_dimensions_info();

                $rs         = $grader->get_assessments_recordset($restrict);
        $batch      = array();            $previous   = null;               foreach ($rs as $current) {
            if (is_null($previous)) {
                                $previous = $current;
            }
            if ($current->submissionid == $previous->submissionid) {
                $batch[] = $current;
            } else {
                                $this->process_assessments($batch, $diminfo, $settings);
                                $batch = array($current);
                $previous = $current;
            }
        }
                $this->process_assessments($batch, $diminfo, $settings);
        $rs->close();
    }

    
    public function get_settings_form(moodle_url $actionurl=null) {

        $customdata['workshop'] = $this->workshop;
        $customdata['current'] = $this->settings;
        $attributes = array('class' => 'evalsettingsform best');

        return new workshop_best_evaluation_settings_form($actionurl, $customdata, 'post', '', $attributes);
    }

    
    public static function delete_instance($workshopid) {
        global $DB;

        $DB->delete_records('workshopeval_best_settings', array('workshopid' => $workshopid));
    }

            
    
    protected function process_assessments(array $assessments, array $diminfo, stdclass $settings) {
        global $DB;

        if (empty($assessments)) {
            return;
        }

                $assessments = $this->prepare_data_from_recordset($assessments);

                $assessments = $this->normalize_grades($assessments, $diminfo);

                $average = $this->average_assessment($assessments);

                if (is_null($average)) {
            foreach ($assessments as $asid => $assessment) {
                if (!is_null($assessment->gradinggrade)) {
                    $DB->set_field('workshop_assessments', 'gradinggrade', null, array('id' => $asid));
                }
            }
            return;
        }

                $variances = $this->weighted_variance($assessments);
        foreach ($variances as $dimid => $variance) {
            $diminfo[$dimid]->variance = $variance;
        }

                $distances = array();
        foreach ($assessments as $asid => $assessment) {
            $distances[$asid] = $this->assessments_distance($assessment, $average, $diminfo, $settings);
        }

                $bestids = array_keys($distances, min($distances));

                $distances = array();
        foreach ($bestids as $bestid) {
            $best = $assessments[$bestid];
            foreach ($assessments as $asid => $assessment) {
                $d = $this->assessments_distance($assessment, $best, $diminfo, $settings);
                if (!is_null($d) and (!isset($distances[$asid]) or $d < $distances[$asid])) {
                    $distances[$asid] = $d;
                }
            }
        }

                foreach ($distances as $asid => $distance) {
            $gradinggrade = (100 - $distance);
            if ($gradinggrade < 0) {
                $gradinggrade = 0;
            }
            if ($gradinggrade > 100) {
                $gradinggrade = 100;
            }
            $grades[$asid] = grade_floatval($gradinggrade);
        }

                        foreach ($grades as $assessmentid => $grade) {
            if (grade_floats_different($grade, $assessments[$assessmentid]->gradinggrade)) {
                                $record = new stdclass();
                $record->id = $assessmentid;
                $record->gradinggrade = grade_floatval($grade);
                                                $DB->update_record('workshop_assessments', $record, true);              }
        }

            }

    
    protected function prepare_data_from_recordset($assessments) {
        $data = array();            foreach ($assessments as $a) {
            $id = $a->assessmentid;             if (!isset($data[$id])) {
                $data[$id] = new stdclass();
                $data[$id]->assessmentid = $a->assessmentid;
                $data[$id]->weight       = $a->assessmentweight;
                $data[$id]->reviewerid   = $a->reviewerid;
                $data[$id]->gradinggrade = $a->gradinggrade;
                $data[$id]->submissionid = $a->submissionid;
                $data[$id]->dimgrades    = array();
            }
            $data[$id]->dimgrades[$a->dimensionid] = $a->grade;
        }
        return $data;
    }

    
    protected function normalize_grades(array $assessments, array $diminfo) {
        foreach ($assessments as $asid => $assessment) {
            foreach ($assessment->dimgrades as $dimid => $dimgrade) {
                $dimmin = $diminfo[$dimid]->min;
                $dimmax = $diminfo[$dimid]->max;
                if ($dimmin == $dimmax) {
                    $assessment->dimgrades[$dimid] = grade_floatval($dimmax);
                } else {
                    $assessment->dimgrades[$dimid] = grade_floatval(($dimgrade - $dimmin) / ($dimmax - $dimmin) * 100);
                }
            }
        }
        return $assessments;
    }

    
    protected function average_assessment(array $assessments) {
        $sumdimgrades = array();
        foreach ($assessments as $a) {
            foreach ($a->dimgrades as $dimid => $dimgrade) {
                if (!isset($sumdimgrades[$dimid])) {
                    $sumdimgrades[$dimid] = 0;
                }
                $sumdimgrades[$dimid] += $dimgrade * $a->weight;
            }
        }

        $sumweights = 0;
        foreach ($assessments as $a) {
            $sumweights += $a->weight;
        }
        if ($sumweights == 0) {
                        return null;
        }

        $average = new stdclass();
        $average->dimgrades = array();
        foreach ($sumdimgrades as $dimid => $sumdimgrade) {
            $average->dimgrades[$dimid] = grade_floatval($sumdimgrade / $sumweights);
        }
        return $average;
    }

    
    protected function weighted_variance(array $assessments) {
        $first = reset($assessments);
        if (empty($first)) {
            return null;
        }
        $dimids = array_keys($first->dimgrades);
        $asids  = array_keys($assessments);
        $vars   = array();          foreach ($dimids as $dimid) {
            $n = 0;
            $s = 0;
            $sumweight = 0;
            foreach ($asids as $asid) {
                $x = $assessments[$asid]->dimgrades[$dimid];                    $weight = $assessments[$asid]->weight;                          if ($weight == 0) {
                    continue;
                }
                if ($n == 0) {
                    $n = 1;
                    $mean = $x;
                    $s = 0;
                    $sumweight = $weight;
                } else {
                    $n++;
                    $temp = $weight + $sumweight;
                    $q = $x - $mean;
                    $r = $q * $weight / $temp;
                    $s = $s + $sumweight * $q * $r;
                    $mean = $mean + $r;
                    $sumweight = $temp;
                }
            }
            if ($sumweight > 0 and $n > 1) {
                                                $vars[$dimid] = $s / $sumweight;
            } else {
                $vars[$dimid] = null;
            }
        }
        return $vars;
    }

    
    protected function assessments_distance(stdclass $assessment, stdclass $referential, array $diminfo, stdclass $settings) {
        $distance = 0;
        $n = 0;
        foreach (array_keys($assessment->dimgrades) as $dimid) {
            $agrade = $assessment->dimgrades[$dimid];
            $rgrade = $referential->dimgrades[$dimid];
            $var    = $diminfo[$dimid]->variance;
            $weight = $diminfo[$dimid]->weight;
            $n     += $weight;

                        $var = max($var, 0.01);

            if ($agrade != $rgrade) {
                $absdelta   = abs($agrade - $rgrade);
                $reldelta   = pow($agrade - $rgrade, 2) / ($settings->comparison * $var);
                $distance  += $absdelta * $reldelta * $weight;
            }
        }
        if ($n > 0) {
                        return round($distance / $n, 4);
        } else {
            return null;
        }
    }
}



class workshop_best_evaluation_settings_form extends workshop_evaluation_settings_form {

    
    protected function definition_sub() {
        $mform = $this->_form;

        $plugindefaults = get_config('workshopeval_best');
        $current = $this->_customdata['current'];

        $options = array();
        for ($i = 9; $i >= 1; $i = $i-2) {
            $options[$i] = get_string('comparisonlevel' . $i, 'workshopeval_best');
        }
        $mform->addElement('select', 'comparison', get_string('comparison', 'workshopeval_best'), $options);
        $mform->addHelpButton('comparison', 'comparison', 'workshopeval_best');
        $mform->setDefault('comparison', $plugindefaults->comparison);

        $this->set_data($current);
    }
}
