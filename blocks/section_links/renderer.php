<?php




class block_section_links_renderer extends plugin_renderer_base {

    
    public function render_section_links(stdClass $course, array $sections, $jumptosection = false) {
        $html = html_writer::start_tag('ol', array('class' => 'inline-list'));
        foreach ($sections as $section) {
            $attributes = array();
            if (!$section->visible) {
                $attributes['class'] = 'dimmed';
            }
            $html .= html_writer::start_tag('li');
            $sectiontext = $section->section;
            if ($section->highlight) {
                $sectiontext = html_writer::tag('strong', $sectiontext);
            }
            $html .= html_writer::link(course_get_url($course, $section->section), $sectiontext, $attributes);
            $html .= html_writer::end_tag('li').' ';
        }
        $html .= html_writer::end_tag('ol');
        if ($jumptosection && isset($sections[$jumptosection])) {

            if ($course->format == 'weeks') {
                $linktext = new lang_string('jumptocurrentweek', 'block_section_links');
            } else if ($course->format == 'topics') {
                $linktext = new lang_string('jumptocurrenttopic', 'block_section_links');
            }

            $attributes = array();
            if (!$sections[$jumptosection]->visible) {
                $attributes['class'] = 'dimmed';
            }
            $html .= html_writer::link(course_get_url($course, $jumptosection), $linktext, $attributes);
        }

        return $html;
    }
}