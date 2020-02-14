<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/behat/classes/behat_command.php');
require_once($CFG->libdir . '/behat/classes/behat_config_manager.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/behat/steps_definitions_form.php');


class tool_behat {

    
    public static function stepsdefinitions($type, $component, $filter) {

                        behat_command::behat_setup_problem();

                behat_config_manager::update_config_file($component, false);

                if ($type) {
            $filter .= '&&' . $type;
        }

        if ($filter) {
            $filteroption = ' -d "' . $filter . '"';
        } else {
            $filteroption = ' -di';
        }

                $options = ' --config="'.behat_config_manager::get_steps_list_config_filepath(). '" '.$filteroption;
        list($steps, $code) = behat_command::run($options);

        return $steps;
    }

}
