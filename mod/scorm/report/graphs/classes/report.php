<?php


namespace scormreport_graphs;

defined('MOODLE_INTERNAL') || die();


class report extends \mod_scorm\report {
    
    public function display($scorm, $cm, $course, $download) {
        global $DB, $OUTPUT, $PAGE;

        if ($groupmode = groups_get_activity_groupmode($cm)) {               groups_print_activity_menu($cm, new \moodle_url($PAGE->url));
        }

        if ($scoes = $DB->get_records('scorm_scoes', array("scorm" => $scorm->id), 'sortorder, id')) {
            foreach ($scoes as $sco) {
                if ($sco->launch != '') {
                    $imageurl = new \moodle_url('/mod/scorm/report/graphs/graph.php',
                            array('scoid' => $sco->id));
                    $graphname = $sco->title;
                    echo $OUTPUT->heading($graphname, 3);
                    echo \html_writer::tag('div', \html_writer::empty_tag('img',
                            array('src' => $imageurl, 'alt' => $graphname)),
                            array('class' => 'graph'));
                }
            }
        }
    }
}
