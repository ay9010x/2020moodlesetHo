<?php



namespace gradereport_singleview\local\ui;

defined('MOODLE_INTERNAL') || die;


class range extends attribute_format {

    
    public function __construct($item) {
        $this->item = $item;
    }

    
    public function determine_format() {
        $decimals = $this->item->get_decimals();

        $min = format_float($this->item->grademin, $decimals);
        $max = format_float($this->item->grademax, $decimals);

        return new empty_element("$min - $max");
    }
}
