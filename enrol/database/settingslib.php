<?php



defined('MOODLE_INTERNAL') || die();


class enrol_database_admin_setting_category extends admin_setting_configselect {
    public function __construct($name, $visiblename, $description) {
        parent::__construct($name, $visiblename, $description, 1, null);
    }

    public function load_choices() {
        if (is_array($this->choices)) {
            return true;
        }

        $this->choices = make_categories_options();
        return true;
    }
}
