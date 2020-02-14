<?php



namespace core\output;


class notification implements \renderable, \templatable {

    
    const NOTIFY_SUCCESS = 'success';

    
    const NOTIFY_WARNING = 'warning';

    
    const NOTIFY_INFO = 'info';

    
    const NOTIFY_ERROR = 'error';

    
    const NOTIFY_MESSAGE = 'message';

    
    const NOTIFY_PROBLEM = 'problem';

    
    const NOTIFY_REDIRECT = 'redirect';

    
    protected $message = '';

    
    protected $messagetype = self::NOTIFY_WARNING;

    
    protected $announce = true;

    
    protected $closebutton = true;

    
    protected $extraclasses = array();

    
    public function __construct($message, $messagetype = null) {
        $this->message = $message;

        if (empty($messagetype)) {
            $messagetype = self::NOTIFY_ERROR;
        }

        $this->messagetype = $messagetype;

        switch ($messagetype) {
            case self::NOTIFY_PROBLEM:
            case self::NOTIFY_REDIRECT:
            case self::NOTIFY_MESSAGE:
                debugging('Use of ' . $messagetype . ' has been deprecated. Please switch to an alternative type.');
        }
    }

    
    public function set_announce($announce = false) {
        $this->announce = (bool) $announce;

        return $this;
    }

    
    public function set_show_closebutton($button = false) {
        $this->closebutton = (bool) $button;

        return $this;
    }

    
    public function set_extra_classes($classes = array()) {
        $this->extraclasses = $classes;

        return $this;
    }

    
    public function get_message() {
        return $this->message;
    }

    
    public function get_message_type() {
        return $this->messagetype;
    }

    
    public function export_for_template(\renderer_base $output) {
        return array(
            'message'       => clean_text($this->message),
            'extraclasses'  => implode(' ', $this->extraclasses),
            'announce'      => $this->announce,
            'closebutton'   => $this->closebutton,
        );
    }

    public function get_template_name() {
        $templatemappings = [
                        'success'           => 'core/notification_success',
            'info'              => 'core/notification_info',
            'warning'           => 'core/notification_warning',
            'error'             => 'core/notification_error',
        ];

        if (isset($templatemappings[$this->messagetype])) {
            return $templatemappings[$this->messagetype];
        }
        return $templatemappings['error'];
    }
}
