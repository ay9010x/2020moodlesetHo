<?php



namespace mod_lti;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../locallib.php');
require_once(__DIR__.'/../servicelib.php');


class service_exception_handler {
    
    protected $log = false;

    
    protected $id = '';

    
    protected $type = 'unknownRequest';

    
    public function __construct($log) {
        $this->log = $log;
    }

    
    public function set_message_id($id) {
        if (!empty($id)) {
            $this->id = $id;
        }
    }

    
    public function set_message_type($type) {
        if (!empty($type)) {
            $this->type = $type;
        }
    }

    
    public function handle(\Exception $exception) {
        $message = $exception->getMessage();

                if (debugging('', DEBUG_DEVELOPER)) {
            $message .= "\n".format_backtrace(get_exception_info($exception)->backtrace, true);
        }

                $type = str_replace('Request', 'Response', $this->type);

                $response = lti_get_response_xml('failure', $message, $this->id, $type);

        $xml = $response->asXML();

                if ($this->log) {
            lti_log_response($xml, $exception);
        }

        echo $xml;
    }
}
