<?php




class block_section_links extends block_base {

    
    public function init() {
        $this->title = get_string('pluginname', 'block_section_links');
    }

    
    public function applicable_formats() {
        return array(
            'course-view-weeks' => true,
            'course-view-topics' => true
        );
    }

    
    public function get_content() {

                        if (isset($this->config)){
            $config = $this->config;
        } else{
            $config = get_config('block_section_links');
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text   = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $course = $this->page->course;
        $courseformat = course_get_format($course);
        $courseformatoptions = $courseformat->get_format_options();
        $context = context_course::instance($course->id);

                if (empty($courseformatoptions['numsections'])) {
            return $this->content;
        }

                if ($course->format == 'weeks') {
            $highlight = ceil((time() - $course->startdate) / 604800);
        } else if ($course->format == 'topics') {
            $highlight = $course->marker;
        } else {
            $highlight = 0;
        }

                if (!empty($config->numsections1) and ($courseformatoptions['numsections'] > $config->numsections1)) {
            $inc = $config->incby1;
        } else if ($courseformatoptions['numsections'] > 22) {
            $inc = 2;
        } else {
            $inc = 1;
        }
        if (!empty($config->numsections2) and ($courseformatoptions['numsections'] > $config->numsections2)) {
            $inc = $config->incby2;
        } else {
            if ($courseformatoptions['numsections'] > 40) {
                $inc = 5;
            }
        }

                $sections = array();
        $canviewhidden = has_capability('moodle/course:update', $context);
        $coursesections = $courseformat->get_sections();
        $coursesectionscount = count($coursesections);
        for ($i = $inc; $i <= $coursesectionscount; $i += $inc) {
            if ($i > $courseformatoptions['numsections'] || !isset($coursesections[$i])) {
                continue;
            }
            $section = $coursesections[$i];
            if ($section->section && ($section->visible || $canviewhidden)) {
                $sections[$i] = (object)array(
                    'section' => $section->section,
                    'visible' => $section->visible,
                    'highlight' => ($section->section == $highlight)
                );
            }
        }

        if (!empty($sections)) {
            $sectiontojumpto = false;
            if ($highlight && isset($sections[$highlight]) && ($sections[$highlight]->visible || $canviewhidden)) {
                $sectiontojumpto = $highlight;
            }
                        $renderer = $this->page->get_renderer('block_section_links');
            $this->content->text = $renderer->render_section_links($this->page->course, $sections, $sectiontojumpto);
        }

        return $this->content;
    }
    
    public function instance_allow_config() {
        return true;
    }

    
    public function has_config() {
        return true;
    }
}


