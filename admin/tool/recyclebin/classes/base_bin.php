<?php



namespace tool_recyclebin;

defined('MOODLE_INTERNAL') || die();


abstract class base_bin {

    
    public static function is_enabled() {
        return false;
    }

    
    public abstract function get_item($itemid);

    
    public abstract function get_items();

    
    public abstract function store_item($item);

    
    public abstract function restore_item($item);

    
    public abstract function delete_item($item);

    
    public function delete_all_items() {
                $items = $this->get_items();
        foreach ($items as $item) {
            if ($this->can_delete()) {
                $this->delete_item($item);
            }
        }
    }

    
    public abstract function can_view();

    
    public abstract function can_restore();

    
    public abstract function can_delete();
}
