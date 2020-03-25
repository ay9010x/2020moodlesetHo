<?php



defined('MOODLE_INTERNAL') || die();


class testable_flexible_table extends flexible_table {

    
    public function can_be_reset() {
        return parent::can_be_reset();
    }
}
