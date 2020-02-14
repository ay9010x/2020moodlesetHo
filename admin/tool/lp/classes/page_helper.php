<?php



namespace tool_lp;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use context;
use moodle_exception;
use moodle_url;
use core_user;
use context_user;
use context_course;
use stdClass;


class page_helper {

    
    public static function setup_for_course(moodle_url $url, $course, $subtitle = '') {
        global $PAGE;

        $context = context_course::instance($course->id);

        $PAGE->set_course($course);

        if (!empty($subtitle)) {
            $title = $subtitle;
        } else {
            $title = get_string('coursecompetencies', 'tool_lp');
        }

        $returnurl = new moodle_url('/admin/tool/lp/coursecompetencies.php', array('courseid' => $course->id));

        $heading = $context->get_context_name();
        $PAGE->set_pagelayout('incourse');
        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($heading);

        if (!empty($subtitle)) {
            $PAGE->navbar->add(get_string('coursecompetencies', 'tool_lp'), $returnurl);
                        $PAGE->navbar->add($subtitle, $url);
        }

        return array($title, $subtitle, $returnurl);
    }

    
    public static function setup_for_template($pagecontextid, moodle_url $url, $template = null, $subtitle = '',
                                              $returntype = null) {
        global $PAGE, $SITE;

        $pagecontext = context::instance_by_id($pagecontextid);
        $context = $pagecontext;
        if (!empty($template)) {
            $context = $template->get_context();
        }

        $templatesurl = new moodle_url('/admin/tool/lp/learningplans.php', array('pagecontextid' => $pagecontextid));
        $templateurl = null;
        if ($template) {
            $templateurl = new moodle_url('/admin/tool/lp/templatecompetencies.php', [
                'templateid' => $template->get_id(),
                'pagecontextid' => $pagecontextid
            ]);
        }

        $returnurl = $templatesurl;
        if ($returntype != 'templates' && $templateurl) {
            $returnurl = $templateurl;
        }

        $PAGE->navigation->override_active_url($templatesurl);
        $PAGE->set_context($pagecontext);

        if (!empty($template)) {
            $title = format_string($template->get_shortname(), true, array('context' => $context));
        } else {
            $title = get_string('templates', 'tool_lp');
        }

        if ($pagecontext->contextlevel == CONTEXT_SYSTEM) {
            $heading = $SITE->fullname;
        } else if ($pagecontext->contextlevel == CONTEXT_COURSECAT) {
            $heading = $pagecontext->get_context_name();
        } else {
            throw new coding_exception('Unexpected context!');
        }

        $PAGE->set_pagelayout('admin');
        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($heading);

        if (!empty($template)) {
            $PAGE->navbar->add($title, $templateurl);
            if (!empty($subtitle)) {
                $PAGE->navbar->add($subtitle, $url);
            }

        } else if (!empty($subtitle)) {
                        $PAGE->navbar->add($subtitle, $url);
        }

        return array($title, $subtitle, $returnurl);
    }

    
    public static function setup_for_plan($userid, moodle_url $url, $plan = null, $subtitle = '', $returntype = null) {
        global $PAGE, $USER;

                $user = core_user::get_user($userid);
        if (!$user || !core_user::is_real_user($userid)) {
            throw new \moodle_exception('invaliduser', 'error');
        }

        $context = context_user::instance($user->id);

        $plansurl = new moodle_url('/admin/tool/lp/plans.php', array('userid' => $userid));
        $planurl = null;
        if ($plan) {
            $planurl = new moodle_url('/admin/tool/lp/plan.php', array('id' => $plan->get_id()));
        }

        $returnurl = $plansurl;
        if ($returntype != 'plans' && $planurl) {
            $returnurl = $planurl;
        }

        $PAGE->navigation->override_active_url($plansurl);
        $PAGE->set_context($context);

                $iscurrentuser = ($USER->id == $user->id);
        if (!$iscurrentuser) {
            $PAGE->navigation->extend_for_user($user);
            $PAGE->navigation->set_userid_for_parent_checks($user->id);
        }

        if (!empty($plan)) {
            $title = format_string($plan->get_name(), true, array('context' => $context));
        } else {
            $title = get_string('learningplans', 'tool_lp');
        }

        $PAGE->set_pagelayout('standard');
        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($title);

        if (!empty($plan)) {
            $PAGE->navbar->add($title, $planurl);
            if (!empty($subtitle)) {
                $PAGE->navbar->add($subtitle, $url);
            }
        } else if (!empty($subtitle)) {
                        $PAGE->navbar->add($subtitle, $url);
        }

        return array($title, $subtitle, $returnurl);
    }

    
    public static function setup_for_user_evidence($userid, moodle_url $url, $evidence = null, $subtitle = '', $returntype = null) {
        global $PAGE, $USER;

                $user = core_user::get_user($userid);
        if (!$user || !core_user::is_real_user($userid)) {
            throw new \moodle_exception('invaliduser', 'error');
        }

        $context = context_user::instance($user->id);

        $evidencelisturl = new moodle_url('/admin/tool/lp/user_evidence_list.php', array('userid' => $userid));
        $evidenceurl = null;
        if ($evidence) {
            $evidenceurl = new moodle_url('/admin/tool/lp/user_evidence.php', array('id' => $evidence->get_id()));
        }

        $returnurl = $evidencelisturl;
        if ($returntype != 'list' && $evidenceurl) {
            $returnurl = $evidenceurl;
        }

        $PAGE->navigation->override_active_url($evidencelisturl);
        $PAGE->set_context($context);

                $iscurrentuser = ($USER->id == $user->id);
        if (!$iscurrentuser) {
            $PAGE->navigation->extend_for_user($user);
            $PAGE->navigation->set_userid_for_parent_checks($user->id);
        }

        if (!empty($evidence)) {
            $title = format_string($evidence->get_name(), true, array('context' => $context));
        } else {
            $title = get_string('userevidence', 'tool_lp');
        }

        $PAGE->set_pagelayout('standard');
        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($title);

        if (!empty($evidence)) {
            $PAGE->navbar->add($title, $evidenceurl);
            if (!empty($subtitle)) {
                $PAGE->navbar->add($subtitle, $url);
            }
        } else if (!empty($subtitle)) {
                        $PAGE->navbar->add($subtitle, $url);
        }

        return array($title, $subtitle, $returnurl);
    }

    
    public static function setup_for_framework($id, $pagecontextid, $framework = null, $returntype = null) {
        global $PAGE;

                $url = new moodle_url("/admin/tool/lp/editcompetencyframework.php", array('id' => $id, 'pagecontextid' => $pagecontextid));
        if ($returntype) {
            $url->param('return', $returntype);
        }
        $frameworksurl = new moodle_url('/admin/tool/lp/competencyframeworks.php', array('pagecontextid' => $pagecontextid));

        $PAGE->navigation->override_active_url($frameworksurl);
        $title = get_string('competencies', 'core_competency');
        if (empty($id)) {
            $pagetitle = get_string('competencyframeworks', 'tool_lp');
            $pagesubtitle = get_string('addnewcompetencyframework', 'tool_lp');

            $url->remove_params(array('id'));
            $PAGE->navbar->add($pagesubtitle, $url);
        } else {
            $pagetitle = $framework->get_shortname();
            $pagesubtitle = get_string('editcompetencyframework', 'tool_lp');
            if ($returntype == 'competencies') {
                $frameworksurl = new moodle_url('/admin/tool/lp/competencies.php', array(
                    'pagecontextid' => $pagecontextid,
                    'competencyframeworkid' => $id
                ));
            } else {
                $frameworksurl->param('competencyframeworkid', $id);
            }

            $PAGE->navbar->add($pagetitle, $frameworksurl);
            $PAGE->navbar->add($pagesubtitle, $url);
        }

        $PAGE->set_context(context::instance_by_id($pagecontextid));
        $PAGE->set_pagelayout('admin');
        $PAGE->set_url($url);
        $PAGE->set_title($title);
        $PAGE->set_heading($title);
        return array($pagetitle, $pagesubtitle, $url, $frameworksurl);
    }

    
    public static function setup_for_competency($pagecontextid, moodle_url $url, $framework, $competency = null, $parent = null) {
        global $PAGE, $SITE;

                $pagecontext = context::instance_by_id($pagecontextid);
        $PAGE->set_context($pagecontext);

                if ($pagecontext->contextlevel == CONTEXT_SYSTEM) {
            $heading = $SITE->fullname;
        } else if ($pagecontext->contextlevel == CONTEXT_COURSECAT) {
            $heading = $pagecontext->get_context_name();
        } else {
            throw new coding_exception('Unexpected context!');
        }
        $PAGE->set_heading($heading);

                $frameworksurl = new moodle_url('/admin/tool/lp/competencyframeworks.php', ['pagecontextid' => $pagecontextid]);
        $PAGE->navigation->override_active_url($frameworksurl);

                $returnurloptions = [
            'competencyframeworkid' => $framework->get_id(),
            'pagecontextid' => $pagecontextid
        ];
        $returnurl = new moodle_url('/admin/tool/lp/competencies.php', $returnurloptions);
        $PAGE->navbar->add($framework->get_shortname(), $returnurl);

                $PAGE->set_pagelayout('admin');

        if (empty($competency)) {
                        $title = format_string($framework->get_shortname(), true, ['context' => $pagecontext]);

                        $level = $parent ? $parent->get_level() + 1 : 1;
            $subtitle = get_string('taxonomy_add_' . $framework->get_taxonomy($level), 'tool_lp');

        } else {
                        $title = format_string($competency->get_shortname(), true, ['context' => $competency->get_context()]);

                        $PAGE->navbar->add($title);

                        $subtitle = get_string('taxonomy_edit_' . $framework->get_taxonomy($competency->get_level()), 'tool_lp');
        }

                $PAGE->set_title($title);

                $PAGE->set_url($url);

                if (!empty($subtitle)) {
            $PAGE->navbar->add($subtitle, $url);
        }

        return [$title, $subtitle, $returnurl];
    }
}
