<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;    
require_once(dirname(dirname(__FILE__)) . '/lib.php');                  require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');    require_once(dirname(__FILE__) . '/settings_form.php');                 

class workshop_random_allocator implements workshop_allocator {

    
    const MSG_SUCCESS       = 1;

    
    protected $workshop;

    
    protected $mform;

    
    public function __construct(workshop $workshop) {
        $this->workshop = $workshop;
    }

    
    public function init() {
        global $PAGE;

        $result = new workshop_allocation_result($this);
        $customdata = array();
        $customdata['workshop'] = $this->workshop;
        $this->mform = new workshop_random_allocator_form($PAGE->url, $customdata);
        if ($this->mform->is_cancelled()) {
            redirect($this->workshop->view_url());
        } else if ($settings = $this->mform->get_data()) {
            $settings = workshop_random_allocator_setting::instance_from_object($settings);
            $this->execute($settings, $result);
            return $result;
        } else {
                                                $result->set_status(workshop_allocation_result::STATUS_VOID);
            return $result;
        }
    }

    
    public function execute(workshop_random_allocator_setting $settings, workshop_allocation_result $result) {

        $authors        = $this->workshop->get_potential_authors();
        $authors        = $this->workshop->get_grouped($authors);
        $reviewers      = $this->workshop->get_potential_reviewers(!$settings->assesswosubmission);
        $reviewers      = $this->workshop->get_grouped($reviewers);
        $assessments    = $this->workshop->get_all_assessments();
        $newallocations = array();      
        if ($settings->numofreviews) {
            if ($settings->removecurrent) {
                                $curassessments = array();
            } else {
                $curassessments = $assessments;
            }
            $options                     = array();
            $options['numofreviews']     = $settings->numofreviews;
            $options['numper']           = $settings->numper;
            $options['excludesamegroup'] = $settings->excludesamegroup;
            $randomallocations  = $this->random_allocation($authors, $reviewers, $curassessments, $result, $options);
            $newallocations     = array_merge($newallocations, $randomallocations);
            $result->log(get_string('numofrandomlyallocatedsubmissions', 'workshopallocation_random', count($randomallocations)));
            unset($randomallocations);
        }
        if ($settings->addselfassessment) {
            $selfallocations    = $this->self_allocation($authors, $reviewers, $assessments);
            $newallocations     = array_merge($newallocations, $selfallocations);
            $result->log(get_string('numofselfallocatedsubmissions', 'workshopallocation_random', count($selfallocations)));
            unset($selfallocations);
        }
        if (empty($newallocations)) {
            $result->log(get_string('noallocationtoadd', 'workshopallocation_random'), 'info');
        } else {
            $newnonexistingallocations = $newallocations;
            $this->filter_current_assessments($newnonexistingallocations, $assessments);
            $this->add_new_allocations($newnonexistingallocations, $authors, $reviewers);
            $allreviewers = $reviewers[0];
            $allreviewersreloaded = false;
            foreach ($newallocations as $newallocation) {
                list($reviewerid, $authorid) = each($newallocation);
                $a = new stdClass();
                if (isset($allreviewers[$reviewerid])) {
                    $a->reviewername = fullname($allreviewers[$reviewerid]);
                } else {
                                                                                if (!$allreviewersreloaded) {
                        $allreviewers = $this->workshop->get_potential_reviewers(false);
                        $allreviewersreloaded = true;
                    }
                    if (isset($allreviewers[$reviewerid])) {
                        $a->reviewername = fullname($allreviewers[$reviewerid]);
                    } else {
                                                                        $a->reviewername = '#'.$reviewerid;
                    }
                }
                if (isset($authors[0][$authorid])) {
                    $a->authorname = fullname($authors[0][$authorid]);
                } else {
                    $a->authorname = '#'.$authorid;
                }
                if (in_array($newallocation, $newnonexistingallocations)) {
                    $result->log(get_string('allocationaddeddetail', 'workshopallocation_random', $a), 'ok', 1);
                } else {
                    $result->log(get_string('allocationreuseddetail', 'workshopallocation_random', $a), 'ok', 1);
                }
            }
        }
        if ($settings->removecurrent) {
            $delassessments = $this->get_unkept_assessments($assessments, $newallocations, $settings->addselfassessment);
                                    $result->log(get_string('numofdeallocatedassessment', 'workshopallocation_random', count($delassessments)), 'info');
            foreach ($delassessments as $delassessmentkey => $delassessmentid) {
                $a = new stdclass();
                $a->authorname      = fullname((object)array(
                        'lastname'  => $assessments[$delassessmentid]->authorlastname,
                        'firstname' => $assessments[$delassessmentid]->authorfirstname));
                $a->reviewername    = fullname((object)array(
                        'lastname'  => $assessments[$delassessmentid]->reviewerlastname,
                        'firstname' => $assessments[$delassessmentid]->reviewerfirstname));
                if (!is_null($assessments[$delassessmentid]->grade)) {
                    $result->log(get_string('allocationdeallocategraded', 'workshopallocation_random', $a), 'error', 1);
                    unset($delassessments[$delassessmentkey]);
                } else {
                    $result->log(get_string('assessmentdeleteddetail', 'workshopallocation_random', $a), 'info', 1);
                }
            }
            $this->workshop->delete_assessment($delassessments);
        }
        $result->set_status(workshop_allocation_result::STATUS_EXECUTED);
    }

    
    public function ui() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_workshop');

        $m = optional_param('m', null, PARAM_INT);          $message = new workshop_message();
        if ($m == self::MSG_SUCCESS) {
            $message->set_text(get_string('randomallocationdone', 'workshopallocation_random'));
            $message->set_type(workshop_message::TYPE_OK);
        }

        $out  = $output->container_start('random-allocator');
        $out .= $output->render($message);
                        ob_start();
        $this->mform->display();
        $out .= ob_get_contents();
        ob_end_clean();

                $gmode = groups_get_activity_groupmode($this->workshop->cm, $this->workshop->course);
        if (VISIBLEGROUPS == $gmode or SEPARATEGROUPS == $gmode) {
            $users = $this->workshop->get_potential_authors() + $this->workshop->get_potential_reviewers();
            $users = $this->workshop->get_grouped($users);
            if (isset($users[0])) {
                $nogroupusers = $users[0];
                foreach ($users as $groupid => $groupusers) {
                    if ($groupid == 0) {
                        continue;
                    }
                    foreach ($groupusers as $groupuserid => $groupuser) {
                        unset($nogroupusers[$groupuserid]);
                    }
                }
                if (!empty($nogroupusers)) {
                    $list = array();
                    foreach ($nogroupusers as $nogroupuser) {
                        $list[] = fullname($nogroupuser);
                    }
                    $a = implode(', ', $list);
                    $out .= $output->box(get_string('nogroupusers', 'workshopallocation_random', $a), 'generalbox warning nogroupusers');
                }
            }
        }

        
        $out .= $output->container_end();

        return $out;
    }

    
    public static function delete_instance($workshopid) {
        return;
    }

    
    public static function available_numofreviews_list() {
        $options = array();
        $options[30] = 30;
        $options[20] = 20;
        $options[15] = 15;
        for ($i = 10; $i >= 0; $i--) {
            $options[$i] = $i;
        }
        return $options;
    }

    
    protected function self_allocation($authors=array(), $reviewers=array(), $assessments=array()) {
        if (!isset($authors[0]) || !isset($reviewers[0])) {
                        return array();
        }
        $alreadyallocated = array();
        foreach ($assessments as $assessment) {
            if ($assessment->authorid == $assessment->reviewerid) {
                $alreadyallocated[$assessment->authorid] = 1;
            }
        }
        $add = array();         foreach ($authors[0] as $authorid => $author) {
                        if (isset($reviewers[0][$authorid])) {
                                if (!isset($alreadyallocated[$authorid])) {
                                        $add[] = array($authorid => $authorid);
                }
            }
        }
        return $add;
    }

    
    protected function add_new_allocations(array $newallocations, array $dataauthors, array $datareviewers) {
        global $DB;

        $newallocations = $this->get_unique_allocations($newallocations);
        $authorids      = $this->get_author_ids($newallocations);
        $submissions    = $this->workshop->get_submissions($authorids);
        $submissions    = $this->index_submissions_by_authors($submissions);
        foreach ($newallocations as $newallocation) {
            list($reviewerid, $authorid) = each($newallocation);
            if (!isset($submissions[$authorid])) {
                throw new moodle_exception('unabletoallocateauthorwithoutsubmission', 'workshop');
            }
            $submission = $submissions[$authorid];
            $status = $this->workshop->add_allocation($submission, $reviewerid, 1, true);               if (workshop::ALLOCATION_EXISTS == $status) {
                debugging('newallocations array contains existing allocation, this should not happen');
            }
        }
    }

    
    protected function index_submissions_by_authors($submissions) {
        $byauthor = array();
        if (is_array($submissions)) {
            foreach ($submissions as $submissionid => $submission) {
                if (isset($byauthor[$submission->authorid])) {
                    throw new moodle_exception('moresubmissionsbyauthor', 'workshop');
                }
                $byauthor[$submission->authorid] = $submission;
            }
        }
        return $byauthor;
    }

    
    protected function get_author_ids($newallocations) {
        $authors = array();
        foreach ($newallocations as $newallocation) {
            $authorid = reset($newallocation);
            if (!in_array($authorid, $authors)) {
                $authors[] = $authorid;
            }
        }
        return $authors;
    }

    
    protected function get_unique_allocations($newallocations) {
        return array_merge(array_map('unserialize', array_unique(array_map('serialize', $newallocations))));
    }

    
    protected function get_unkept_assessments($assessments, $newallocations, $keepselfassessments) {
        $keepids = array();         foreach ($assessments as $assessmentid => $assessment) {
            $aaid = $assessment->authorid;
            $arid = $assessment->reviewerid;
            if (($keepselfassessments) && ($aaid == $arid)) {
                $keepids[$assessmentid] = null;
                continue;
            }
            foreach ($newallocations as $newallocation) {
                list($nrid, $naid) = each($newallocation);
                if (array($arid, $aaid) == array($nrid, $naid)) {
                                        $keepids[$assessmentid] = null;
                    continue 2;
                }
            }
        }
        return array_keys(array_diff_key($assessments, $keepids));
    }

    
    protected function random_allocation($authors, $reviewers, $assessments, $result, array $options) {
        if (empty($authors) || empty($reviewers)) {
                        return array();
        }

        $numofreviews = $options['numofreviews'];
        $numper       = $options['numper'];

        if (workshop_random_allocator_setting::NUMPER_SUBMISSION == $numper) {
                        $result->log(get_string('resultnumperauthor', 'workshopallocation_random', $numofreviews), 'info');
            $allcircles = $authors;
            $allsquares = $reviewers;
                        list($circlelinks, $squarelinks) = $this->convert_assessments_to_links($assessments);
        } elseif (workshop_random_allocator_setting::NUMPER_REVIEWER == $numper) {
                        $result->log(get_string('resultnumperreviewer', 'workshopallocation_random', $numofreviews), 'info');
            $allcircles = $reviewers;
            $allsquares = $authors;
                        list($squarelinks, $circlelinks) = $this->convert_assessments_to_links($assessments);
        } else {
            throw new moodle_exception('unknownusertypepassed', 'workshop');
        }
                                if (isset($allcircles[0])) {
            $nogroupcircles = array_flip(array_keys($allcircles[0]));
        } else {
            $nogroupcircles = array();
        }
        foreach ($allcircles as $circlegroupid => $circles) {
            if ($circlegroupid == 0) {
                continue;
            }
            foreach ($circles as $circleid => $circle) {
                unset($nogroupcircles[$circleid]);
            }
        }
                        $squareworkload         = array();          $squaregroupsworkload   = array();            foreach ($allsquares as $squaregroupid => $squares) {
            $squaregroupsworkload[$squaregroupid] = 0;
            foreach ($squares as $squareid => $square) {
                if (!isset($squarelinks[$squareid])) {
                    $squarelinks[$squareid] = array();
                }
                $squareworkload[$squareid] = count($squarelinks[$squareid]);
                $squaregroupsworkload[$squaregroupid] += $squareworkload[$squareid];
            }
            $squaregroupsworkload[$squaregroupid] /= count($squares);
        }
        unset($squaregroupsworkload[0]);                            $gmode = groups_get_activity_groupmode($this->workshop->cm, $this->workshop->course);
        if (SEPARATEGROUPS == $gmode) {
                        $circlegroups = array_keys(array_diff_key($allcircles, array(0 => null)));
            shuffle($circlegroups);
        } else {
                        $circlegroups = array(0);
        }
                foreach ($circlegroups as $circlegroupid) {
            $result->log('processing circle group id ' . $circlegroupid, 'debug');
            $circles = $allcircles[$circlegroupid];
                                                            for ($requiredreviews = 1; $requiredreviews <= $numofreviews; $requiredreviews++) {
                $this->shuffle_assoc($circles);
                $result->log('iteration ' . $requiredreviews, 'debug');
                foreach ($circles as $circleid => $circle) {
                    if (VISIBLEGROUPS == $gmode and isset($nogroupcircles[$circleid])) {
                        $result->log('skipping circle id ' . $circleid, 'debug');
                        continue;
                    }
                    $result->log('processing circle id ' . $circleid, 'debug');
                    if (!isset($circlelinks[$circleid])) {
                        $circlelinks[$circleid] = array();
                    }
                    $keeptrying     = true;                         $failedgroups   = array();                                                                                                                      while ($keeptrying && (count($circlelinks[$circleid]) < $requiredreviews)) {
                                                if (NOGROUPS == $gmode) {
                            if (in_array(0, $failedgroups)) {
                                $keeptrying = false;
                                $result->log(get_string('resultnomorepeers', 'workshopallocation_random'), 'error', 1);
                                break;
                            }
                            $targetgroup = 0;
                        } elseif (SEPARATEGROUPS == $gmode) {
                            if (in_array($circlegroupid, $failedgroups)) {
                                $keeptrying = false;
                                $result->log(get_string('resultnomorepeersingroup', 'workshopallocation_random'), 'error', 1);
                                break;
                            }
                            $targetgroup = $circlegroupid;
                        } elseif (VISIBLEGROUPS == $gmode) {
                            $trygroups = array_diff_key($squaregroupsworkload, array(0 => null));                               $trygroups = array_diff_key($trygroups, array_flip($failedgroups));                                 if ($options['excludesamegroup']) {
                                                                $excludegroups = array();
                                foreach (array_diff_key($allcircles, array(0 => null)) as $exgroupid => $exgroupmembers) {
                                    if (array_key_exists($circleid, $exgroupmembers)) {
                                        $excludegroups[$exgroupid] = null;
                                    }
                                }
                                $trygroups = array_diff_key($trygroups, $excludegroups);
                            }
                            $targetgroup = $this->get_element_with_lowest_workload($trygroups);
                        }
                        if ($targetgroup === false) {
                            $keeptrying = false;
                            $result->log(get_string('resultnotenoughpeers', 'workshopallocation_random'), 'error', 1);
                            break;
                        }
                        $result->log('next square should be from group id ' . $targetgroup, 'debug', 1);
                                                $trysquares = array_intersect_key($squareworkload, $allsquares[$targetgroup]);
                                                unset($trysquares[$circleid]);                          $trysquares = array_diff_key($trysquares, array_flip($circlelinks[$circleid]));                         $targetsquare = $this->get_element_with_lowest_workload($trysquares);
                        if (false === $targetsquare) {
                            $result->log('unable to find an available square. trying another group', 'debug', 1);
                            $failedgroups[] = $targetgroup;
                            continue;
                        }
                        $result->log('target square = ' . $targetsquare, 'debug', 1);
                                                $circlelinks[$circleid][]       = $targetsquare;
                        $squarelinks[$targetsquare][]   = $circleid;
                        $squareworkload[$targetsquare]++;
                        $result->log('increasing square workload to ' . $squareworkload[$targetsquare], 'debug', 1);
                        if ($targetgroup) {
                                                        $squaregroupsworkload[$targetgroup] = 0;
                            foreach ($allsquares[$targetgroup] as $squareid => $square) {
                                $squaregroupsworkload[$targetgroup] += $squareworkload[$squareid];
                            }
                            $squaregroupsworkload[$targetgroup] /= count($allsquares[$targetgroup]);
                            $result->log('increasing group workload to ' . $squaregroupsworkload[$targetgroup], 'debug', 1);
                        }
                    }                 }             }         }         $returned = array();
        if (workshop_random_allocator_setting::NUMPER_SUBMISSION == $numper) {
                        foreach ($circlelinks as $circleid => $squares) {
                foreach ($squares as $squareid) {
                    $returned[] = array($squareid => $circleid);
                }
            }
        }
        if (workshop_random_allocator_setting::NUMPER_REVIEWER == $numper) {
                        foreach ($circlelinks as $circleid => $squares) {
                foreach ($squares as $squareid) {
                    $returned[] = array($circleid => $squareid);
                }
            }
        }
        return $returned;
    }

    
    protected function convert_assessments_to_links($assessments) {
        $authorlinks    = array();         $reviewerlinks  = array();         foreach ($assessments as $assessment) {
            if (!isset($authorlinks[$assessment->authorid])) {
                $authorlinks[$assessment->authorid] = array();
            }
            if (!isset($reviewerlinks[$assessment->reviewerid])) {
                $reviewerlinks[$assessment->reviewerid] = array();
            }
            $authorlinks[$assessment->authorid][]   = $assessment->reviewerid;
            $reviewerlinks[$assessment->reviewerid][] = $assessment->authorid;
            }
        return array($authorlinks, $reviewerlinks);
    }

    
    protected function get_element_with_lowest_workload($workload) {
        $precision = 10;

        if (empty($workload)) {
            return false;
        }
        $minload = round(min($workload), $precision);
        $minkeys = array();
        foreach ($workload as $key => $val) {
            if (round($val, $precision) == $minload) {
                $minkeys[$key] = $val;
            }
        }
        return array_rand($minkeys);
    }

    
    protected function shuffle_assoc(&$array) {
        if (count($array) > 1) {
                        $keys = array_keys($array);
            shuffle($keys);
            foreach($keys as $key) {
                $new[$key] = $array[$key];
            }
            $array = $new;
        }
        return true;     }

    
    protected function filter_current_assessments(&$newallocations, $assessments) {
        foreach ($assessments as $assessment) {
            $allocation     = array($assessment->reviewerid => $assessment->authorid);
            $foundat        = array_keys($newallocations, $allocation);
            $newallocations = array_diff_key($newallocations, array_flip($foundat));
        }
    }
}



class workshop_random_allocator_setting {

    
    const NUMPER_SUBMISSION = 1;
    
    const NUMPER_REVIEWER   = 2;

    
    public $numofreviews;
    
    public $numper;
    
    public $excludesamegroup;
    
    public $removecurrent;
    
    public $assesswosubmission;
    
    public $addselfassessment;

    
    protected function __construct() {
    }

    
    public static function instance_from_object(stdClass $data) {
        $i = new self();

        if (!isset($data->numofreviews)) {
            throw new coding_exception('Missing value of the numofreviews property');
        } else {
            $i->numofreviews = (int)$data->numofreviews;
        }

        if (!isset($data->numper)) {
            throw new coding_exception('Missing value of the numper property');
        } else {
            $i->numper = (int)$data->numper;
            if ($i->numper !== self::NUMPER_SUBMISSION and $i->numper !== self::NUMPER_REVIEWER) {
                throw new coding_exception('Invalid value of the numper property');
            }
        }

        foreach (array('excludesamegroup', 'removecurrent', 'assesswosubmission', 'addselfassessment') as $k) {
            if (isset($data->$k)) {
                $i->$k = (bool)$data->$k;
            } else {
                $i->$k = false;
            }
        }

        return $i;
    }

    
    public static function instance_from_text($text) {
        return self::instance_from_object(json_decode($text));
    }

    
    public function export_text() {
        $getvars = function($obj) { return get_object_vars($obj); };
        return json_encode($getvars($this));
    }
}
