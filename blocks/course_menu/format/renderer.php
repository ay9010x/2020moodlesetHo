<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/blocks/course_menu/format/locallib.php');

class block_course_menu_renderer extends format_section_renderer_base {
    
    
    protected $courserenderer = null;

    
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
        $this->courserenderer = $page->get_renderer('core', 'course');
    }

    
    protected function start_section_list($name = 'topics') {
        return html_writer::start_tag('ul', array('class' => $name));
    }

    
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    
    protected function page_title($name = 'topics') {
        return get_string('course_'.$name, 'block_course_menu');
    }

    
    protected function section_edit_controls2($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());
        $controls = array();

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }
    
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused, $activitytypes = null) {
        global $PAGE;
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $context = context_course::instance($course->id);
        $allowtypes = array();
        if(!empty($activitytypes)){
            $allowtypes[] = $activitytypes;
        }else{
            $activitytypes = $course->format;
        }        

        echo $this->output->heading(get_string('pluginname', $activitytypes), 2, '');
        echo $this->course_activity_clipboard($course, 0);
        echo $this->start_section_list($activitytypes);

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            //if ($section == 0) {
            //    continue;
            //}   // by YCJ
            if ($section > $course->numsections) {
                continue;
            }
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available && $thissection->showavailability
                    && !empty($thissection->availableinfo));
            if (!$showsection) {
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section);
                }
                continue;
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    echo $this->local_section_cm_list($course, $thissection, $allowtypes, 0);
                    echo $this->local_section_add_cm_control($course, $section, $allowtypes, 0, null);
                }
                echo $this->section_footer();
            }
        }
        echo $this->end_section_list();
    }
    
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;

        $o = $currenttext = $sectionstyle = '';

        if ($section->section != 0) {
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)));

        $o.= html_writer::start_tag('div', array('class' => 'content'));

        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        if ($hasnamenotsecpg || $hasnamesecpg) {
            $o.= $this->output->heading($this->section_title($section, $course), 3, 'sectionname');
        }

        $o.= html_writer::start_tag('div', array('class' => 'summary'));
        $o.= $this->format_summary_text($section);
        $context = context_course::instance($course->id);
        
        $o.= html_writer::end_tag('div');
        
        $o .= $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));

        return $o;
    }
    
        private function local_section_cm_list($course, $section, $allowtypes = array(), $sectionreturn = null, $displayoptions = array()) {
        global $USER;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    continue;
                }

                if(!empty($allowtypes)){
                    if(in_array($mod->modname, $allowtypes)){
                        if ($modulehtml = $this->courserenderer->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                            $moduleshtml[$modnumber] = $modulehtml;
                        }
                    }
                }else{
                    if ($modulehtml = $this->courserenderer->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                        $moduleshtml[$modnumber] = $modulehtml;
                    }
                }
            }
        }

        if (!empty($moduleshtml) || $ismoving) {
            $output .= html_writer::start_tag('ul', array('class' => 'section img-text'));
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $output .= html_writer::tag('li', html_writer::link($movingurl, $this->output->render($movingpix)),
                            array('class' => 'movehere', 'title' => $strmovefull));
                }

                $mod = $modinfo->cms[$modnumber];
                $modclasses = 'activity '. $mod->modname. ' modtype_'.$mod->modname;
                $output .= html_writer::start_tag('li', array('class' => $modclasses, 'id' => 'module-'. $mod->id));
                $output .= $modulehtml;
                $output .= html_writer::end_tag('li');
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $output .= html_writer::tag('li', html_writer::link($movingurl, $this->output->render($movingpix)),
                        array('class' => 'movehere', 'title' => $strmovefull));
            }

            $output .= html_writer::end_tag('ul');         }
        return $output;
    }
    
    private function local_section_add_cm_control($course, $section, $allowtypes = array(), $sectionreturn = null, $displayoptions = array()) {
        global $CFG;

        $vertical = !empty($displayoptions['inblock']);
        if (!has_capability('moodle/course:manageactivities', context_course::instance($course->id))
                || !$this->page->user_is_editing()
                || !($modnames = get_module_types_names()) || empty($modnames)) {
            return '';
        }
        
        $modules = block_course_menu_format_get_module_metadata($course, $modnames, $sectionreturn);         $urlparams = array('section' => $section);

        $activities = array(MOD_CLASS_ACTIVITY => array(), MOD_CLASS_RESOURCE => array());

        $newmodules = array();
        foreach ($modules as $module) {
            $activityclass = MOD_CLASS_ACTIVITY;
            if(!empty($allowtypes)){
                if (in_array($module->name, $allowtypes)) {
                    $activityclass = MOD_CLASS_RESOURCE;
                    $link = $module->link->out(true, $urlparams);
                    $activities[$activityclass][$link] = $module->title;
                    $newmodules[] = $module;
                } else if ($module->archetype === MOD_ARCHETYPE_SYSTEM) {
                    continue;
                }
            }else{
                $activityclass = MOD_CLASS_RESOURCE;
                $link = $module->link->out(true, $urlparams);
                $activities[$activityclass][$link] = $module->title;
                $newmodules[] = $module;                
            }
        }

        $straddresource = get_string('addresource');
        $sectionname = get_section_name($course, $section);
        $strresourcelabel = get_string('addresourcetosection', null, $sectionname);

        $output = html_writer::start_tag('div', array('class' => 'section_add_menus', 'id' => 'add_menus-section-' . $section));
        if (!$vertical) {
            $output .= html_writer::start_tag('div', array('class' => 'horizontal'));
        }

        if (!empty($activities[MOD_CLASS_RESOURCE])) {
            $select = new url_select($activities[MOD_CLASS_RESOURCE], '', array(''=>$straddresource), "ressection$section");
            $select->set_help_icon('resources');
            $select->set_label($strresourcelabel, array('class' => 'accesshide'));
            $output .= $this->output->render($select);
        }

        if (!$vertical) {
            $output .= html_writer::end_tag('div');
        }

        $output .= html_writer::end_tag('div');

        if (course_ajax_enabled($course) && $course->id == $this->page->course->id) {
            $straddeither = get_string('addresourceoractivity');
            $modchooser = html_writer::start_tag('div', array('class' => 'mdl-right'));
            $modchooser.= html_writer::start_tag('div', array('class' => 'section-modchooser'));
            $icon = $this->output->pix_icon('t/add', '');
            $span = html_writer::tag('span', $straddeither, array('class' => 'section-modchooser-text'));
            $modchooser .= html_writer::tag('span', $icon . $span, array('class' => 'section-modchooser-link'));
            $modchooser.= html_writer::end_tag('div');
            $modchooser.= html_writer::end_tag('div');
            
            $usemodchooser = get_user_preferences('usemodchooser', $CFG->modchooserdefault);
            if ($usemodchooser) {
                $output = html_writer::tag('div', $output, array('class' => 'hiddenifjs addresourcedropdown'));
                $modchooser = html_writer::tag('div', $modchooser, array('class' => 'visibleifjs addresourcemodchooser'));
            } else {
                $output = html_writer::tag('div', $output, array('class' => 'show addresourcedropdown'));
                $modchooser = html_writer::tag('div', $modchooser, array('class' => 'hide addresourcemodchooser'));
            }
            $output = $this->courserenderer->course_modchooser($newmodules, $course) . $modchooser . $output;
        }
        return $output;
    }
}