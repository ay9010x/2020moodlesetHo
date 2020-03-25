<?php



defined('MOODLE_INTERNAL') || die();


class portfolio_exception extends moodle_exception {}


class portfolio_export_exception extends portfolio_exception {

    
    public function __construct($exporter, $errorcode, $module=null, $continue=null, $a=null) {
        global $CFG;
                                                        static $inconstructor = false;

        if (!$inconstructor && !empty($exporter) &&
                $exporter instanceof portfolio_exporter) {
            $inconstructor = true;
            try {
                if (empty($continue)) {
                    $caller = $exporter->get('caller');
                    if (!empty($caller) && $caller instanceof portfolio_caller_base) {
                        $continue = $exporter->get('caller')->get_return_url();
                    }
                }
                                                                $exporter->process_stage_cleanup();
            } catch(Exception $e) {
                                            }
            $inconstructor = false;
        }
        parent::__construct($errorcode, $module, $continue, $a);
    }
}


class portfolio_caller_exception extends portfolio_exception {}


class portfolio_plugin_exception extends portfolio_exception {}


class portfolio_button_exception extends portfolio_exception {}


class portfolio_format_leap2a_exception extends portfolio_exception {}
