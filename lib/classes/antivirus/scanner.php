<?php



namespace core\antivirus;

defined('MOODLE_INTERNAL') || die();


abstract class scanner {
    
    protected $config;

    
    public function __construct() {
                                $ref = new \ReflectionClass(get_class($this));
        $this->config = get_config($ref->getNamespaceName());
    }

    
    public function get_config($property) {
        if (property_exists($this->config, $property)) {
            return $this->config->$property;
        }
        throw new \coding_exception('Config property "' . $property . '" doesn\'t exist');
    }

    
    public abstract function is_configured();

    
    public abstract function scan_file($file, $filename, $deleteinfected);

    
    public function message_admins($notice) {

        $site = get_site();

        $subject = get_string('emailsubject', 'antivirus', format_string($site->fullname));
        $admins = get_admins();
        foreach ($admins as $admin) {
            $eventdata = new \stdClass();
            $eventdata->component         = 'moodle';
            $eventdata->name              = 'errors';
            $eventdata->userfrom          = get_admin();
            $eventdata->userto            = $admin;
            $eventdata->subject           = $subject;
            $eventdata->fullmessage       = $notice;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }
    }
}