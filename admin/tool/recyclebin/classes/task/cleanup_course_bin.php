<?php



namespace tool_recyclebin\task;


class cleanup_course_bin extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('taskcleanupcoursebin', 'tool_recyclebin');
    }

    
    public function execute() {
        global $DB;

                $lifetime = get_config('tool_recyclebin', 'coursebinexpiry');
        if (!\tool_recyclebin\course_bin::is_enabled() || $lifetime <= 0) {
            return true;
        }

                $items = $DB->get_recordset_select('tool_recyclebin_course', 'timecreated <= :timecreated',
            array('timecreated' => time() - $lifetime));
        foreach ($items as $item) {
            mtrace("[tool_recyclebin] Deleting item '{$item->id}' from the course recycle bin ...");
            $bin = new \tool_recyclebin\course_bin($item->courseid);
            $bin->delete_item($item);
        }
        $items->close();

        return true;
    }
}
