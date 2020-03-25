<?php



define('CLI_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'courses'           => false,
        'fix'               => false,
        'help'              => false
    ),
    array(
        'h' => 'help',
        'c' => 'courses',
        'f' => 'fix'
    )
);

if ($options['help'] || empty($options['courses'])) {
    $help =
"Checks and fixes that course modules and sections reference each other correctly.

Compares DB fields course_sections.sequence and course_modules.section
checking that:
- course_sections.sequence contains each module id not more than once in the course
- for each moduleid from course_sections.sequence the field course_modules.section
  refers to the same section id (this means course_sections.sequence is more
  important if they are different)
- each module in the course is present in one of course_sections.sequence
- section sequences do not contain non-existing course modules

If there are any mismatches, the message is displayed. If --fix is specified,
the records in DB are corrected.

This script may run for a long time on big systems if called for all courses.

Avoid executing the script when another user may simultaneously edit any of the
courses being checked (recommended to run in mainenance mode).

Options:
-c, --courses         List courses that need to be checked (comma-separated
                      values or * for all). Required
-f, --fix             Fix the mismatches in DB. If not specified check only and
                      report problems to STDERR
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/fix_course_sequence.php --courses=*
\$sudo -u www-data /usr/bin/php admin/cli/fix_course_sequence.php --courses=2,3,4 --fix
";

    echo $help;
    die;
}

$courseslist = preg_split('/\s*,\s*/', $options['courses'], -1, PREG_SPLIT_NO_EMPTY);
if (in_array('*', $courseslist)) {
    $where = '';
    $params = array();
} else {
    list($sql, $params) = $DB->get_in_or_equal($courseslist, SQL_PARAMS_NAMED, 'id');
    $where = 'WHERE id '. $sql;
}
$coursescount = $DB->get_field_sql('SELECT count(id) FROM {course} '. $where, $params);

if (!$coursescount) {
    cli_error('No courses found');
}
echo "Checking $coursescount courses...\n\n";

require_once($CFG->dirroot. '/course/lib.php');

$problems = array();
$courses = $DB->get_fieldset_sql('SELECT id FROM {course} '. $where, $params);
foreach ($courses as $courseid) {
    $errors = course_integrity_check($courseid, null, null, true, empty($options['fix']));
    if ($errors) {
        if (!empty($options['fix'])) {
                        rebuild_course_cache($courseid, true);
        }
        foreach ($errors as $error) {
            cli_problem($error);
        }
        $problems[] = $courseid;
    } else {
        echo "Course [$courseid] is OK\n";
    }
}
if (!count($problems)) {
    echo "\n...All courses are OK\n";
} else {
    if (!empty($options['fix'])) {
        echo "\n...Found and fixed ".count($problems)." courses with problems". "\n";
    } else {
        echo "\n...Found ".count($problems)." courses with problems. To fix run:\n";
        echo "\$sudo -u www-data /usr/bin/php admin/cli/fix_course_sequence.php --courses=".join(',', $problems)." --fix". "\n";
    }
}