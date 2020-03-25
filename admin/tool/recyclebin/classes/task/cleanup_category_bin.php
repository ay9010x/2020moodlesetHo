<?php



namespace tool_recyclebin\task;


class cleanup_category_bin extends \core\task\scheduled_task {

    
    public function get_name() {
        return get_string('taskcleanupcategorybin', 'tool_recyclebin');
    }

    
    public function execute() {
        global $DB;

                $lifetime = get_config('tool_recyclebin', 'categorybinexpiry');
        if (!\tool_recyclebin\category_bin::is_enabled() || $lifetime <= 0) {
            return true;
        }

                $items = $DB->get_recordset_select('tool_recyclebin_category', 'timecreated <= :timecreated',
            array('timecreated' => time() - $lifetime));
        foreach ($items as $item) {
            mtrace("[tool_recyclebin] Deleting item '{$item->id}' from the category recycle bin ...");
            $bin = new \tool_recyclebin\category_bin($item->categoryid);
            $bin->delete_item($item);
        }
        $items->close();

        return true;
    }
}
