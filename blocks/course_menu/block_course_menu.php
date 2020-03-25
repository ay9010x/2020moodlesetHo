<?php

defined('MOODLE_INTERNAL') || die();

class block_course_menu extends block_base {
    
    public $blockname = null;
    
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', 'block_course_menu');
    }

    function applicable_formats() {
        return array('all' => true, 'my' => false,'site' => false);
    }


    function instance_allow_multiple() {
    	  return false;
    }
    
    function instance_can_be_hidden() {
        return false;
    }
    
    function has_config() {
        return true;
    }
    
    function get_content() {
        global $CFG, $DB, $USER, $PAGE; 
        $PAGE->requires->css('/blocks/course_menu/css/font-awesome.min.css');
        $PAGE->requires->js('/blocks/course_menu/module.js');
        
        // To enhance the docking of course menu for students. by YCJ        
        if($PAGE->theme->name == 'snap') {
            $PAGE->requires->css('/blocks/course_menu/css/snap.css');
            $PAGE->requires->js('/blocks/course_menu/snap.js');
        }        
        
        if($this->content !== NULL) {
            return $this->content;
        }
                     
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        if (empty($this->instance)) {
            return $this->content;
        }

        $content ='';
        $course = $this->page->course;  
        if($course->id == SITEID){
            $this->content->text = $content;
            return $this->content;
        }
        $context = CONTEXT_COURSE::instance($course->id, MUST_EXIST);
        
        $switchrole='';
        if(!empty($USER->access['rsw'][$context->path])){
            $roleid = $USER->access['rsw'][$context->path];
            $switchrole = $DB->get_field('role', 'archetype', array('id'=>$roleid));
        }
        
        $content .= html_writer::start_tag('div', array('id'=>'coursemenudiv', 'class'=>'menulist', 'imglink'=>$CFG->wwwroot));  // by YCJ
        $config = get_config('block_course_menu');
                $mylang = current_language();
        if($mylang == 'zh_tw'){
            $menuname = 'name';
        }else{
            $menuname = 'ename';
        }
        if((has_capability('moodle/course:update', $context) && $switchrole == '') || has_capability('moodle/course:bulkmessaging', $context)){            $i = 1;
            $submenu = $mainmenu = array();
            while($i <= 24){
                $name = 'tmenu'.$i;
                if(isset($config->$name)){
                    $setting = unserialize($config->$name);
                    if($setting['visible']){
                        if(!empty($setting['icon'])){
                            $showname = '<i class="fa '. $setting['icon'] .'"></i>&nbsp;'.$setting[$menuname];
                        }else{
                            $showname = $setting[$menuname];
                        }
                        
                        if($setting['parent'] == 0){
                            $mainmenu[$name] = html_writer::tag('div', $showname, array('class' => 'mainmenu', 'onclick' => "SwitchCourseMenu('$name')", 'style' => "background:".$setting['color']));
                        }else{
                            $parentmenu = 'tmenu'.$setting['parent'];
                            if(!empty($setting['params'])){
                                if($setting['paramsvalue'] == 0){
                                    $paramsvalue = $course->id;
                                }
                                $link = html_writer::link($CFG->wwwroot.$setting['url'].'?'.$setting['params'].'='.$paramsvalue.$setting['anchor'], $showname);   // by YCJ
                            }else{
                                $link = html_writer::link($CFG->wwwroot.$setting['url'], $showname);   // by YCJ
                            }
                            
                            $menulink = html_writer::tag('li', $link, array('style' => "background:".$setting['color']));
                            $submenu[$parentmenu][] = $menulink;
                        }
                    }                    
                }
                $i++;
            }
            
            foreach($mainmenu as $k => $v){
                $content .= $v;
                if(isset($submenu[$k])){
                    $content .= html_writer::start_tag('span', array('id'=>$k, 'class' => 'submenu', 'style' => 'display: block;'));
                    foreach($submenu[$k] as $v2){
                        $content .= $v2;
                    }
                    $content .= html_writer::end_tag('span');
                }
                
            }
        }
        else if(!has_capability('moodle/course:update', $context) || $switchrole == 'student' || $switchrole == 'auditor'){
            $i = 1;
            $submenu = $mainmenu = array();
            while($i <= 14){
                $name = 'smenu'.$i;
                if(isset($config->$name)){
                    $setting = unserialize($config->$name);
                    if($setting['visible']){
                        if(!empty($setting['icon'])){
                            $showname = '<i class="fa '. $setting['icon'] .'"></i>&nbsp;'.$setting[$menuname];
                        }else{
                            $showname = $setting[$menuname];
                        }
                        
                        if($setting['parent'] == 0){
                            $mainmenu[$name] = html_writer::tag('div', $showname, array('class' => 'mainmenu', 'onclick' => "SwitchCourseMenu('$name')", 'style' => "background:".$setting['color']));
                        }else{
                            $parentmenu = 'smenu'.$setting['parent'];
                            if(!empty($setting['params'])){
                                if($setting['paramsvalue'] == 0){
                                    $paramsvalue = $course->id;
                                }
                                $link = html_writer::link($CFG->wwwroot.$setting['url'].'?'.$setting['params'].'='.$paramsvalue.$setting['anchor'], $showname);   // by YCJ
                            }else{
                                $link = html_writer::link($CFG->wwwroot.$setting['url'], $showname);   // by YCJ
                            }
                            
                            $menulink = html_writer::tag('li', $link, array('style' => "background:".$setting['color']));
                            $submenu[$parentmenu][] = $menulink;
                        }
                    }                    
                }
                $i++;
            }
            
            foreach($mainmenu as $k => $v){
                $content .= $v;
                if(isset($submenu[$k])){
                    $content .= html_writer::start_tag('span', array('id'=>$k, 'class' => 'submenu', 'style' => 'display: block;'));
                    foreach($submenu[$k] as $v2){
                        $content .= $v2;
                    }
                    $content .= html_writer::end_tag('span');
                }
                
            }
        }        

        $content .= html_writer::end_tag('div');
        
        $this->content->text = $content;
        return $this->content;
    }
}
