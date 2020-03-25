<?php



namespace tool_lp;
defined('MOODLE_INTERNAL') || die();

use moodle_url;


class url_resolver {

    
    public function competency($competencyid, $pagecontextid) {
        return new moodle_url('/admin/tool/lp/editcompetency.php', array(
            'id' => $competencyid,
            'pagecontextid' => $pagecontextid
        ));
    }

    
    public function framework($frameworkid, $pagecontextid) {
        return new moodle_url('/admin/tool/lp/competencies.php', array(
            'competencyframeworkid' => $frameworkid,
            'pagecontextid' => $pagecontextid
        ));
    }

    
    public function frameworks($pagecontextid) {
        return new moodle_url('/admin/tool/lp/competencyframeworks.php', array('pagecontextid' => $pagecontextid));
    }

    
    public function plan($planid) {
        return new moodle_url('/admin/tool/lp/plan.php', array('id' => $planid));
    }

    
    public function plans($userid) {
        return new moodle_url('/admin/tool/lp/plans.php', array('userid' => $userid));
    }

    
    public function template($templateid, $pagecontextid) {
        return new moodle_url('/admin/tool/lp/templatecompetencies.php', array(
            'templateid' => $templateid,
            'pagecontextid' => $pagecontextid
        ));
    }

    
    public function templates($pagecontextid) {
        return new moodle_url('/admin/tool/lp/learningplans.php', array('pagecontextid' => $pagecontextid));
    }

    
    public function user_competency($usercompetencyid) {
        return new moodle_url('/admin/tool/lp/user_competency.php', array('id' => $usercompetencyid));
    }

    
    public function user_competency_in_course($userid, $competencyid, $courseid) {
        return new moodle_url('/admin/tool/lp/user_competency_in_course.php', array(
            'userid' => $userid,
            'competencyid' => $competencyid,
            'courseid' => $courseid
        ));
    }

    
    public function user_competency_in_plan($userid, $competencyid, $planid) {
        return new moodle_url('/admin/tool/lp/user_competency_in_plan.php', array(
            'userid' => $userid,
            'competencyid' => $competencyid,
            'planid' => $planid
        ));
    }

    
    public function user_evidence($userevidenceid) {
        return new moodle_url('/admin/tool/lp/user_evidence.php', array('id' => $userevidenceid));
    }

}
