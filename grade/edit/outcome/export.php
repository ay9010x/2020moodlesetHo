<?php



require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/gradelib.php';

$courseid = optional_param('id', 0, PARAM_INT);
$action   = optional_param('action', '', PARAM_ALPHA);

if ($courseid) {
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('nocourseid');
    }
    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('moodle/grade:manage', $context);

    if (empty($CFG->enableoutcomes)) {
        redirect('../../index.php?id='.$courseid);
    }

} else {
    require_once $CFG->libdir.'/adminlib.php';
    admin_externalpage_setup('outcomes');
}

require_sesskey();

header("Content-Type: text/csv; charset=utf-8");
header('Content-Disposition: attachment; filename=outcomes.csv');

$header = array('outcome_name', 'outcome_shortname', 'outcome_description', 'scale_name', 'scale_items', 'scale_description');
echo format_csv($header, ';', '"');

$outcomes = array();
if ( $courseid ) {
    $outcomes = array_merge(grade_outcome::fetch_all_global(), grade_outcome::fetch_all_local($courseid));
} else {
    $outcomes = grade_outcome::fetch_all_global();
}

foreach($outcomes as $outcome) {

    $line = array();

    $line[] = $outcome->get_name();
    $line[] = $outcome->get_shortname();
    $line[] = $outcome->get_description();

    $scale = $outcome->load_scale();
    $line[] = $scale->get_name();
    $line[] = $scale->compact_items();
    $line[] = $scale->get_description();

    echo format_csv($line, ';', '"');
}


function format_csv($fields = array(), $delimiter = ';', $enclosure = '"') {
    $str = '';
    $escape_char = '\\';
    foreach ($fields as $value) {
        if (strpos($value, $delimiter) !== false ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false) {
            $str2 = $enclosure;
            $escaped = 0;
            $len = strlen($value);
            for ($i=0;$i<$len;$i++) {
                if ($value[$i] == $escape_char) {
                    $escaped = 1;
                } else if (!$escaped && $value[$i] == $enclosure) {
                    $str2 .= $enclosure;
                } else {
                    $escaped = 0;
                }
                $str2 .= $value[$i];
            }
            $str2 .= $enclosure;
            $str .= $str2.$delimiter;
        } else {
            $str .= $value.$delimiter;
        }
    }
    $str = substr($str,0,-1);
    $str .= "\n";

    return $str;
}

