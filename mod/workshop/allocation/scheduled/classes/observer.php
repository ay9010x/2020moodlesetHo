<?php



namespace workshopallocation_scheduled;
defined('MOODLE_INTERNAL') || die();


class observer {

    
    public static function workshop_viewed($event) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/workshop/locallib.php');

        $workshop = $event->get_record_snapshot('workshop', $event->objectid);
        $course   = $event->get_record_snapshot('course', $event->courseid);
        $cm       = $event->get_record_snapshot('course_modules', $event->contextinstanceid);

        $workshop = new \workshop($workshop, $cm, $course);
        $now = time();

                if ($workshop->phase == \workshop::PHASE_SUBMISSION and $workshop->submissionend > 0 and $workshop->submissionend < $now) {

                                    $sql = "SELECT a.id
                      FROM {workshopallocation_scheduled} a
                      JOIN {workshop} w ON a.workshopid = w.id
                     WHERE w.id = :workshopid
                           AND a.enabled = 1
                           AND w.phase = :phase
                           AND w.submissionend > 0
                           AND w.submissionend < :now
                           AND (a.timeallocated IS NULL OR a.timeallocated < w.submissionend)";
            $params = array('workshopid' => $workshop->id, 'phase' => \workshop::PHASE_SUBMISSION, 'now' => $now);

            if ($DB->record_exists_sql($sql, $params)) {
                                $allocator = $workshop->allocator_instance('scheduled');
                $result = $allocator->execute();
                            }
        }
        return true;
    }
}
