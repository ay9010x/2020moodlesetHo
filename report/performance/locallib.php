<?php



defined('MOODLE_INTERNAL') || die;


class report_performance_issue {
    
    public $issue;
    
    public $name;
    
    public $statusstr;
    
    public $status;
    
    public $comment;
    
    public $details;
    
    public $configlink;
}


class report_performance {
    
    const REPORT_PERFORMANCE_OK = 'ok';

    
    const REPORT_PERFORMANCE_WARNING = 'warning';

    
    const REPORT_PERFORMANCE_SERIOUS = 'serious';

    
    const REPORT_PERFORMANCE_CRITICAL = 'critical';

    
    public function get_issue_list() {
        return array(
            'report_performance_check_themedesignermode',
            'report_performance_check_cachejs',
            'report_performance_check_debugmsg',
            'report_performance_check_automatic_backup',
            'report_performance_check_enablestats'
        );
    }

    
    public function doc_link($issue, $name) {
        global $CFG, $OUTPUT;

        if (empty($CFG->docroot)) {
            return $name;
        }

        return $OUTPUT->doc_link('report/performance/'.$issue, $name);
    }

    
    public function add_issue_to_table(&$table, $issueresult, $detailed = false) {
        global $OUTPUT;
        $statusarr = array(self::REPORT_PERFORMANCE_OK => 'statusok',
                        self::REPORT_PERFORMANCE_WARNING => 'statuswarning',
                        self::REPORT_PERFORMANCE_SERIOUS => 'statusserious',
                        self::REPORT_PERFORMANCE_CRITICAL => 'statuscritical');

        $row = array();
        if ($detailed) {
            $row[0] = $this->doc_link($issueresult->issue, $issueresult->name);
        } else {
            $url = new moodle_url('/report/performance/index.php', array('issue' => $issueresult->issue));
            $row[0] = html_writer::link($url, $issueresult->name);
        }
        $row[1] = html_writer::tag('span', $issueresult->statusstr, array('class' => $statusarr[$issueresult->status]));
        $row[2] = $issueresult->comment;
        if (!empty($issueresult->configlink)) {
            $editicon = html_writer::empty_tag('img', array('alt' => $issueresult->issue, 'class' => 'icon',
                'src' => $OUTPUT->pix_url('i/settings')));
            $row[3] = $OUTPUT->action_link($issueresult->configlink, $editicon);
        } else {
            $row[3] = '';
        }

        $table->data[] = $row;
    }

    
    public static function report_performance_check_themedesignermode() {
        global $CFG;
        $issueresult = new report_performance_issue();
        $issueresult->issue = 'report_performance_check_themedesignermode';
        $issueresult->name = get_string('themedesignermode', 'admin');

        if (empty($CFG->themedesignermode)) {
            $issueresult->statusstr = get_string('disabled', 'report_performance');
            $issueresult->status = self::REPORT_PERFORMANCE_OK;
            $issueresult->comment = get_string('check_themedesignermode_comment_disable', 'report_performance');
        } else {
            $issueresult->statusstr = get_string('enabled', 'report_performance');
            $issueresult->status = self::REPORT_PERFORMANCE_CRITICAL;
            $issueresult->comment = get_string('check_themedesignermode_comment_enable', 'report_performance');
        }

        $issueresult->details = get_string('check_themedesignermode_details', 'report_performance');
        $issueresult->configlink = new moodle_url('/admin/search.php', array('query' => 'themedesignermode'));
        return $issueresult;
    }

    
    public static function report_performance_check_cachejs() {
        global $CFG;
        $issueresult = new report_performance_issue();
        $issueresult->issue = 'report_performance_check_cachejs';
        $issueresult->name = get_string('cachejs', 'admin');

        if (empty($CFG->cachejs)) {
            $issueresult->statusstr = get_string('disabled', 'report_performance');
            $issueresult->status = self::REPORT_PERFORMANCE_CRITICAL;
            $issueresult->comment = get_string('check_cachejs_comment_disable', 'report_performance');
        } else {
            $issueresult->statusstr = get_string('enabled', 'report_performance');
            $issueresult->status = self::REPORT_PERFORMANCE_OK;
            $issueresult->comment = get_string('check_cachejs_comment_enable', 'report_performance');
        }

        $issueresult->details = get_string('check_cachejs_details', 'report_performance');
        $issueresult->configlink = new moodle_url('/admin/search.php', array('query' => 'cachejs'));
        return $issueresult;
    }

    
    public static function report_performance_check_debugmsg() {
        global $CFG;
        $issueresult = new report_performance_issue();
        $issueresult->issue = 'report_performance_check_debugmsg';
        $issueresult->name = get_string('debug', 'admin');
        $debugchoices = array(DEBUG_NONE  => 'debugnone',
                            DEBUG_MINIMAL => 'debugminimal',
                            DEBUG_NORMAL => 'debugnormal',
                            DEBUG_ALL => 'debugall',
                            DEBUG_DEVELOPER => 'debugdeveloper');

        $issueresult->statusstr = get_string($debugchoices[$CFG->debug], 'admin');
        if (!$CFG->debugdeveloper) {
            $issueresult->status = self::REPORT_PERFORMANCE_OK;
            $issueresult->comment = get_string('check_debugmsg_comment_nodeveloper', 'report_performance');
        } else {
            $issueresult->status = self::REPORT_PERFORMANCE_WARNING;
            $issueresult->comment = get_string('check_debugmsg_comment_developer', 'report_performance');
        }

        $issueresult->details = get_string('check_debugmsg_details', 'report_performance');

        $issueresult->configlink = new moodle_url('/admin/settings.php', array('section' => 'debugging'));
        return $issueresult;
    }

    
    public static function report_performance_check_automatic_backup() {
        global $CFG;
        require_once($CFG->dirroot . '/backup/util/helper/backup_cron_helper.class.php');

        $issueresult = new report_performance_issue();
        $issueresult->issue = 'report_performance_check_automatic_backup';
        $issueresult->name = get_string('check_backup', 'report_performance');

        $automatedbackupsenabled = get_config('backup', 'backup_auto_active');
        if ($automatedbackupsenabled == backup_cron_automated_helper::AUTO_BACKUP_ENABLED) {
            $issueresult->statusstr = get_string('autoactiveenabled', 'backup');
            $issueresult->status = self::REPORT_PERFORMANCE_WARNING;
            $issueresult->comment = get_string('check_backup_comment_enable', 'report_performance');
        } else {
            if ($automatedbackupsenabled == backup_cron_automated_helper::AUTO_BACKUP_DISABLED) {
                $issueresult->statusstr = get_string('autoactivedisabled', 'backup');
            } else {
                $issueresult->statusstr = get_string('autoactivemanual', 'backup');
            }
            $issueresult->status = self::REPORT_PERFORMANCE_OK;
            $issueresult->comment = get_string('check_backup_comment_disable', 'report_performance');
        }

        $issueresult->details = get_string('check_backup_details', 'report_performance');
        $issueresult->configlink = new moodle_url('/admin/search.php', array('query' => 'backup_auto_active'));
        return $issueresult;
    }

    
    public static function report_performance_check_enablestats() {
        global $CFG;
        $issueresult = new report_performance_issue();
        $issueresult->issue = 'report_performance_check_enablestats';
        $issueresult->name = get_string('enablestats', 'admin');

        if (!empty($CFG->enablestats)) {
            $issueresult->statusstr = get_string('enabled', 'report_performance');
            $issueresult->status = self::REPORT_PERFORMANCE_WARNING;
            $issueresult->comment = get_string('check_enablestats_comment_enable', 'report_performance');
        } else {
            $issueresult->statusstr = get_string('disabled', 'report_performance');
            $issueresult->status = self::REPORT_PERFORMANCE_OK;
            $issueresult->comment = get_string('check_enablestats_comment_disable', 'report_performance');
        }

        $issueresult->details = get_string('check_enablestats_details', 'report_performance');
        $issueresult->configlink = new moodle_url('/admin/search.php', array('query' => 'enablestats'));
        return $issueresult;
    }
}
