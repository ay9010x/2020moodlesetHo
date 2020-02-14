<?php

class local_mooccourse_renderer extends core_course_renderer {
    
    public function local_mooccourse_course_info_item(stdClass $course) {
        $content = '';
        $content .= $this->output->box_start('generalbox info');
        $chelper = new coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);

        $content .= $this->local_mooccourse_infobox($chelper, $course);

        $content .= $this->output->box_end();
    
        return $content;
    }

    protected function local_mooccourse_infobox(coursecat_helper $chelper, $course) {
        global $CFG;
        rebuild_course_cache($course->id);
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        
        if ($course instanceof stdClass) {
            require_once($CFG->dirroot .'/local/mooccourse/coursecatlib.php');
            $course = new local_mooccourse_course_in_list($course);
        }
        $content = "";
        $classes = trim('coursebox clearfix ');
        $nametag = 'h3';

        $content .= html_writer::start_tag('div', array('class' => 'info'));
        $content .= html_writer::start_tag('div', array('class' => $classes));

        $coursename = $chelper->get_course_formatted_name($course);
        if($course->startdate > time()){
            $coursenamelink = html_writer::link(new moodle_url('/local/mooccourse/information.php', array('id' => $course->id)),
                $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        }else{
            $coursenamelink = html_writer::link(new moodle_url('/local/mooccourse/information.php', array('id' => $course->id)),
                $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        }
        
        $content .= html_writer::start_tag('div', array('class' => 'content'));
        $content .= $this->local_mooccourse_infocontent($chelper, $course, $nametag, $coursenamelink);            
        $content .= html_writer::end_tag('div');
        
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }

    protected function local_mooccourse_infocontent(coursecat_helper $chelper, $course, $coursenametag, $coursenamelink) {
        global $DB, $CFG, $PAGE, $USER, $OUTPUT;
        
        if ($course instanceof stdClass) {
            require_once($CFG->dirroot .'/local/mooccourse/coursecatlib.php');
            $course = new local_mooccourse_course_in_list($course);
        }

        $context = context_course::instance($course->id, MUST_EXIST);
        $content = html_writer::start_tag('div', array('class' => 'row-fluid'));
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                                
            if ($isimage) {
                $course_image = '<div class="cimbox" style="background: rgba(185, 228, 249, 0.22) url('.$url.') no-repeat center center; background-size: contain;"></div>';
            }
        }
        if(empty($course_image)){
                        $url = $PAGE->theme->setting_file_url('frontpagerendererdefaultimage', 'frontpagerendererdefaultimage');
            $course_image = '<div class="cimbox" style="background: rgba(185, 228, 249, 0.22) url('.$url.') no-repeat center center; background-size: contain;"></div>';
        }
$content .= '<table ><tr><td>';  //monkey

        $content .= html_writer::start_tag('div', array('class' => 'span4'));
        $content .= $course_image;
        $content .= html_writer::end_tag('div');
$content .= '</td><td>';
        //$content .= html_writer::start_tag('div', array('class' => 'span8 pull-right'));
        $content .= html_writer::tag($coursenametag, $course->fullname, array('class' => 'coursename'));;
        
        //$content .= html_writer::start_tag('div', array('class' => 'summary'));
        if ($cate = coursecat::get($course->category, IGNORE_MISSING)) {
            $maincategory = $cate->get_formatted_name();    
            
            $content .= html_writer::tag('div', get_string('categories').':'.$maincategory);
        }
        if ($course->has_course_contacts()) {
            $i=0;
            foreach ($course->get_course_contacts(true) as $userid => $coursecontact) {
                $rolename[$i] = $coursecontact['rolename'];
                $name = ' , '.$coursecontact['username']; 
                if($coursecontact['role']->id == 3 ){
                    if($i==0)
                        $name = $rolename[$i].': '.$coursecontact['username']; 
                    else if($rolename[$i] != $rolename[$i-1]){
                        $name = $rolename[$i].': '.$coursecontact['username'];  
                        $content .=  '<br />';
                    }
                    $content .= html_writer::tag('span', $name);
                }
                $i++; 
            }
        }       
        $content .= '<!-- JiaThis Button BEGIN -->';
        $content .= '<div class="jiathis_style_32x32">';
        $content .= '    <a class="jiathis_button_fb"></a>';
        $content .= '    <a class="jiathis_button_googleplus"></a>';
        $content .= '    <a href="http://www.jiathis.com/share" class="jiathis jiathis_txt jtico jtico_jiathis" target="_blank"></a>';
        $content .= '    <a class="jiathis_counter_style"></a>';
        $content .= '</div>';
        $content .= '<script type="text/javascript" src="http://v3.jiathis.com/code/jia.js" charset="utf-8"></script>';
        $content .= '<!-- JiaThis Button END -->';
        $content .= html_writer::end_tag('div');        
        $content .= html_writer::end_tag('div');        
        $content .= html_writer::end_tag('div');        

$content .= '</td></table>';

        return $content;
    }

    public function local_mooccourse_course_info_detail(stdClass $course) {
        global $CFG, $DB;

        if ($course instanceof stdClass) {
            require_once($CFG->dirroot .'/local/mooccourse/coursecatlib.php');
            require_once($CFG->dirroot .'/course/renderer.php');
            $course = new local_mooccourse_course_in_list($course);
        }
        $content ='';                
        $chelper = new coursecat_helper();
        if ($course->has_summary()){
            $content .= html_writer::start_tag('div', array('class'=>'row-fluid'));
            $content .= html_writer::start_tag('div', array('class' => 'span panel panel-default coursebox hover'));
            $content .= html_writer::tag('h3', get_string('summary'), array('style'=>"text-decoration: underline;"));
            $content .= $chelper->get_course_formatted_summary($course, array('overflowdiv' => true, 'noclean' => false, 'para' => false));
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
        }
        if ($course->has_detail('outline', 'hasoutline')){
            $content .= html_writer::start_tag('div', array('class'=>'row-fluid'));
            $content .= html_writer::start_tag('div', array('class' => 'span panel panel-default coursebox hover'));
            $content .= html_writer::tag('h3', get_string('course_outline','local_mooccourse'), array('style'=>"text-decoration: underline;"));
            $content .= $this->get_course_formatted_detail($course, array('overflowdiv' => true, 'noclean' => false, 'para' => false), 'outline');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
        }
        if ($course->has_detail('point', 'haspoint')){
            $content .= html_writer::start_tag('div', array('class'=>'row-fluid'));
            $content .= html_writer::start_tag('div', array('class' => 'span panel panel-default coursebox hover'));
            $content .= html_writer::tag('h3', get_string('course_point','local_mooccourse'), array('style'=>"text-decoration: underline;"));
            $content .= $this->get_course_formatted_detail($course, array('overflowdiv' => true, 'noclean' => false, 'para' => false), 'point');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
        }
        if ($course->has_detail('officehour', 'hasofficehour')){
            $content .= html_writer::start_tag('div', array('class'=>'row-fluid'));
            $content .= html_writer::start_tag('div', array('class' => 'span panel panel-default coursebox hover'));
            $content .= html_writer::tag('h3', get_string('course_officehour','local_mooccourse'), array('style'=>"text-decoration: underline;"));
            $content .= $this->get_course_formatted_detail($course, array('overflowdiv' => true, 'noclean' => false, 'para' => false), 'officehour');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
        }
        
        // by YCJ
        if ($course->has_detail('bible', 'hasbible')){
            $content .= html_writer::start_tag('div', array('class'=>'row-fluid'));
            $content .= html_writer::start_tag('div', array('class' => 'span panel panel-default coursebox hover', 'id' => 'ccbible'));
            $content .= html_writer::tag('h3', get_string('course_bible','local_mooccourse'), array('style'=>"text-decoration: underline;"));
            $content .= $this->get_course_formatted_detail($course, array('overflowdiv' => true, 'noclean' => false, 'para' => false), 'bible');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
        }
        if ($course->has_detail('qna', 'hasqna')){
            $content .= html_writer::start_tag('div', array('class'=>'row-fluid'));
            $content .= html_writer::start_tag('div', array('class' => 'span panel panel-default coursebox hover', 'id' => 'ccqna'));
            $content .= html_writer::tag('h3', get_string('course_qna','local_mooccourse'), array('style'=>"text-decoration: underline;"));
            $content .= $this->get_course_formatted_detail($course, array('overflowdiv' => true, 'noclean' => false, 'para' => false), 'qna');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
        }
                
        return $content;
    }
    

    
    public function local_mooccourse_all_courses_list($perpage, $courses, $totalcount) {
        global $CFG;
        
        $chelper = new coursecat_helper();

        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
                set_courses_display_options(array(
                    'recursive' => true,
                    'limit' => $perpage));

        $chelper->set_attributes(array('class' => 'all-course-list-all'));
        return $this->local_moocccourse_courses($chelper, $courses, $totalcount);
    }

    public function local_moocccourse_courses(coursecat_helper $chelper, $courses, $totalcount = null) {
        global $CFG;
        if ($totalcount === null) {
            $totalcount = count($courses);
        }
        if (!$totalcount) {
                        return '';
        }

                $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
                        if ($paginationurl) {
                                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar($totalcount, $page, $perpage,
                        $paginationurl->out(false, array('perpage' => $perpage)));
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => 'all')),
                            get_string('showall', '', $totalcount)), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                                $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag('div', html_writer::link($viewmoreurl, $viewmoretext),
                        array('class' => 'paging paging-morelink'));
            }
        }
        else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
                        $pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)), array('class' => 'paging paging-showperpage'));
        }

                $attributes = $chelper->get_and_erase_attributes('courses');
        $content = html_writer::start_tag('div', $attributes);

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        $coursecount = 0;
        foreach ($courses as $course) {
            $coursecount ++;
            $classes = ($coursecount%2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= count($courses)) {
                $classes .= ' last';
            }
            $content .= $this->local_mooccourse_course_box($chelper, $course, $classes); 
        }

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        $content .= html_writer::end_tag('div');
        
        return $content;
    }

    function local_mooccourse_course_box(coursecat_helper $chelper, $course, $additionalclasses = '') {
        global $CFG;
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }

        if ($course instanceof stdClass) {
            require_once($CFG->dirroot .'/local/mooccourse/coursecatlib.php');
            $course = new local_mooccourse_course_in_list($course);
        }

        $content = '';
        $classes = trim('coursebox clearfix '. $additionalclasses);
        $nametag = 'h2';
        $content .= html_writer::start_tag('div', array('class' => $classes));

        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
            $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        $content .= html_writer::start_tag('div', array('class' => 'row-fluid'));

        $content .= $this->local_mooccourse_course_box_content($chelper, $course, $nametag, $coursenamelink);
        
        $content .= html_writer::end_tag('div');   
        $content .= html_writer::end_tag('div');
        
        return $content;
    }

    function local_mooccourse_course_box_content(coursecat_helper $chelper, $course, $coursenametag, $coursenamelink) {
        global $CFG, $USER, $PAGE, $DB;
        
        if ($course instanceof stdClass) {
            require_once($CFG->dirroot .'/local/mooccourse/coursecatlib.php');
            $course = new local_mooccourse_course_in_list($course);
        }

        $content = html_writer::start_tag('div', array('class' => 'content'));
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                                
            if ($isimage) {
                $course_image = '<div class="cimbox" style="background: rgba(185, 228, 249, 0.22) url('.$url.') no-repeat center center; background-size: contain;"></div>';
            }
        }
        if(empty($course_image)){
                        $url = $PAGE->theme->setting_file_url('frontpagerendererdefaultimage', 'frontpagerendererdefaultimage');
            $course_image = '<div class="cimbox" style="background: rgba(185, 228, 249, 0.22) url('.$url.') no-repeat center center; background-size: contain;"></div>';
        }
        $content .= html_writer::start_tag('div', array('class' => 'span2'));
        $cosinfoURL = $CFG->wwwroot . '/local/mooccourse/information.php?id='.$course->id;
        $content .= '<a href="'.$cosinfoURL.'" target="_blank">'.$course_image.'</a>';
        $content .= html_writer::end_tag('div');
        
        $content .= html_writer::start_tag('div', array('class' => 'span10 pull-right'));
        $content .= html_writer::tag($coursenametag, $course->fullname, array('class' => 'coursename'));;
        
        $content .= html_writer::start_tag('div', array('class' => 'summary'));
                
        require_once($CFG->libdir. '/coursecatlib.php');
        if ($cat = coursecat::get($course->category, IGNORE_MISSING)) {
            $maincategory = $cat->get_formatted_name();
            $content .= html_writer::tag('span', get_string('categories').':'.$maincategory).'<br />';
        }

        if($course->hourcategories != ""){
            $hourcategories = explode(',', $course->hourcategories);
            $name = '';
            foreach($hourcategories as $hc){
                if(empty($hc)){
                    continue;
                }
                if(!empty($name)){
                    $name .=',';
                }
                $name .= $DB->get_field('mooccourse_hour_categories', 'name', array('id'=>$hc));
            }
            $content .= html_writer::tag('div', get_string('course_hourcategories', 'local_mooccourse').':'.$name);
        }
        
        $lc = $DB->get_field('longlearn_categories', 'name', array('id'=>$course->longlearn_category));
        $content .= html_writer::tag('div', get_string('course_longlearncategory', 'local_mooccourse').':'.$lc);
        
        $model = $DB->get_field('mooccourse_course_code', 'name', array('id'=>$course->model));
        $content .= html_writer::tag('div', get_string('course_model', 'local_mooccourse').':'.$model);
        
        $unit = $DB->get_field('mooccourse_course_code', 'name', array('id'=>$course->unit));
        $content .= html_writer::tag('div', get_string('course_unit', 'local_mooccourse').':'.$course->hours.$unit);
                $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class' => 'detail2'));
        $enroldate = get_string('course_enrolduration', 'local_mooccourse').':'.date("Y/m/d H:i", $course->enrolstartdate) . '~' . date("Y/m/d H:i", $course->enrolenddate);
        $content .= html_writer::tag('div', $enroldate);
        $duration = get_string('course_duration', 'local_mooccourse').':'.date("Y/m/d H:i", $course->startdate) .'~'. date("Y/m/d H:i", $course->enddate);   
        $content .= html_writer::tag('div', $duration);
        
        $city = $DB->get_field('mooccourse_course_code', 'name', array('id'=>$course->city));
        $content .= html_writer::tag('div', get_string('course_city', 'local_mooccourse').':'.$city);
        $credit = $DB->get_field('mooccourse_course_code', 'name', array('id'=>$course->credit));
        $content .= html_writer::tag('div', get_string('course_credit', 'local_mooccourse').':'.$credit);
        $content .= html_writer::end_tag('div');
        
        $content .= html_writer::end_tag('div');        $content .= html_writer::end_tag('div');        
        return $content;
    }
    
        public function get_course_formatted_detail($course, $options = array(), $name) {
        global $CFG;
        require_once($CFG->libdir. '/filelib.php');
        $options = (array)$options;
        $context = context_course::instance($course->id);

        $detail = file_rewrite_pluginfile_urls($course->$name, 'pluginfile.php', $context->id, 'course', $name, null);
        $detail = format_text($detail, $course->summaryformat, $options, $course->id);
        if (!empty($this->searchcriteria['search'])) {
            $detail = highlight($this->searchcriteria['search'], $detail);
        }
        return $detail;
    }
}