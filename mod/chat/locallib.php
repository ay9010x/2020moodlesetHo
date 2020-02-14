<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/chat/lib.php');
require_once($CFG->libdir . '/portfolio/caller.php');


class chat_portfolio_caller extends portfolio_module_caller_base {
    
    private $chat;
    
    protected $start;
    
    protected $end;
    
    public static function expected_callbackargs() {
        return array(
            'id'    => true,
            'start' => false,
            'end'   => false,
        );
    }
    
    public function load_data() {
        global $DB;

        if (!$this->cm = get_coursemodule_from_id('chat', $this->id)) {
            throw new portfolio_caller_exception('invalidid', 'chat');
        }
        $this->chat = $DB->get_record('chat', array('id' => $this->cm->instance));
        $select = 'chatid = ?';
        $params = array($this->chat->id);
        if ($this->start && $this->end) {
            $select .= ' AND timestamp >= ? AND timestamp <= ?';
            $params[] = $this->start;
            $params[] = $this->end;
        }
        $this->messages = $DB->get_records_select(
                'chat_messages',
                $select,
                $params,
                'timestamp ASC'
            );
        $select .= ' AND userid = ?';
        $params[] = $this->user->id;
        $this->participated = $DB->record_exists_select(
            'chat_messages',
            $select,
            $params
        );
    }
    
    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_PLAINHTML);
    }
    
    public function expected_time() {
        return portfolio_expected_time_db(count($this->messages));
    }
    
    public function get_sha1() {
        $str = '';
        ksort($this->messages);
        foreach ($this->messages as $m) {
            $str .= implode('', (array)$m);
        }
        return sha1($str);
    }

    
    public function check_permissions() {
        $context = context_module::instance($this->cm->id);
        return has_capability('mod/chat:exportsession', $context)
            || ($this->participated
                && has_capability('mod/chat:exportparticipatedsession', $context));
    }

    
    public function prepare_package() {
        $content = '';
        $lasttime = 0;
        $sessiongap = 5 * 60;            foreach ($this->messages as $message) {              $m = clone $message;             $formatmessage = chat_format_message($m, $this->cm->course, $this->user);
            if (!isset($formatmessage->html)) {
                continue;
            }
            if (empty($lasttime) || (($message->timestamp - $lasttime) > $sessiongap)) {
                $content .= '<hr />';
                $content .= userdate($message->timestamp);
            }
            $content .= $formatmessage->html;
            $lasttime = $message->timestamp;
        }
        $content = preg_replace('/\<img[^>]*\>/', '', $content);

        $this->exporter->write_new_file($content, clean_filename($this->cm->name . '-session.html'), false);
    }

    
    public static function display_name() {
        return get_string('modulename', 'chat');
    }

    
    public function get_return_url() {
        global $CFG;

        return $CFG->wwwroot . '/mod/chat/report.php?id='
            . $this->cm->id . ((isset($this->start)) ? '&start=' . $this->start . '&end=' . $this->end : '');
    }
}


class event_message implements renderable {

    
    public $senderprofile;

    
    public $sendername;

    
    public $time;

    
    public $event;

    
    public $theme;

    
    public function __construct($senderprofile, $sendername, $time, $event, $theme) {

        $this->senderprofile = $senderprofile;
        $this->sendername = $sendername;
        $this->time = $time;
        $this->event = $event;
        $this->theme = $theme;
    }
}


class user_message implements renderable {

    
    public $senderprofile;

    
    public $sendername;

    
    public $avatar;

    
    public $mymessageclass;

    
    public $time;

    
    public $message;

    
    public $theme;

    
    public function __construct($senderprofile, $sendername, $avatar, $mymessageclass, $time, $message, $theme) {

        $this->senderprofile = $senderprofile;
        $this->sendername = $sendername;
        $this->avatar = $avatar;
        $this->mymessageclass = $mymessageclass;
        $this->time = $time;
        $this->message = $message;
        $this->theme = $theme;
    }
}
