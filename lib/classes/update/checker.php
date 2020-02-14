<?php


namespace core\update;

use html_writer, coding_exception, core_component;

defined('MOODLE_INTERNAL') || die();


class checker {

    
    protected static $singletoninstance;
    
    protected $recentfetch = null;
    
    protected $recentresponse = null;
    
    protected $currentversion = null;
    
    protected $currentrelease = null;
    
    protected $currentbranch = null;
    
    protected $currentplugins = array();

    
    protected function __construct() {
    }

    
    protected function __clone() {
    }

    
    public static function instance() {
        if (is_null(self::$singletoninstance)) {
            self::$singletoninstance = new self();
        }
        return self::$singletoninstance;
    }

    
    public static function reset_caches($phpunitreset = false) {
        if ($phpunitreset) {
            self::$singletoninstance = null;
        }
    }

    
    public function enabled() {
        global $CFG;

        return empty($CFG->disableupdatenotifications);
    }

    
    public function get_last_timefetched() {

        $this->restore_response();

        if (!empty($this->recentfetch)) {
            return $this->recentfetch;

        } else {
            return null;
        }
    }

    
    public function fetch() {

        $response = $this->get_response();
        $this->validate_response($response);
        $this->store_response($response);

                        \core_plugin_manager::reset_caches();
    }

    
    public function get_update_info($component, array $options = array()) {

        if (!isset($options['minmaturity'])) {
            $options['minmaturity'] = 0;
        }

        if (!isset($options['notifybuilds'])) {
            $options['notifybuilds'] = false;
        }

        if ($component === 'core') {
            $this->load_current_environment();
        }

        $this->restore_response();

        if (empty($this->recentresponse['updates'][$component])) {
            return null;
        }

        $updates = array();
        foreach ($this->recentresponse['updates'][$component] as $info) {
            $update = new info($component, $info);
            if (isset($update->maturity) and ($update->maturity < $options['minmaturity'])) {
                continue;
            }
            if ($component === 'core') {
                if ($update->version <= $this->currentversion) {
                    continue;
                }
                if (empty($options['notifybuilds']) and $this->is_same_release($update->release)) {
                    continue;
                }
            }
            $updates[] = $update;
        }

        if (empty($updates)) {
            return null;
        }

        return $updates;
    }

    
    public function cron() {
        global $CFG;

        if (!$this->enabled() or !$this->cron_autocheck_enabled()) {
            $this->cron_mtrace('Automatic check for available updates not enabled, skipping.');
            return;
        }

        $now = $this->cron_current_timestamp();

        if ($this->cron_has_fresh_fetch($now)) {
            $this->cron_mtrace('Recently fetched info about available updates is still fresh enough, skipping.');
            return;
        }

        if ($this->cron_has_outdated_fetch($now)) {
            $this->cron_mtrace('Outdated or missing info about available updates, forced fetching ... ', '');
            $this->cron_execute();
            return;
        }

        $offset = $this->cron_execution_offset();
        $start = mktime(1, 0, 0, date('n', $now), date('j', $now), date('Y', $now));         if ($now > $start + $offset) {
            $this->cron_mtrace('Regular daily check for available updates ... ', '');
            $this->cron_execute();
            return;
        }
    }

    

    
    protected function get_response() {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $curl = new \curl(array('proxy' => true));
        $response = $curl->post($this->prepare_request_url(), $this->prepare_request_params(), $this->prepare_request_options());
        $curlerrno = $curl->get_errno();
        if (!empty($curlerrno)) {
            throw new checker_exception('err_response_curl', 'cURL error '.$curlerrno.': '.$curl->error);
        }
        $curlinfo = $curl->get_info();
        if ($curlinfo['http_code'] != 200) {
            throw new checker_exception('err_response_http_code', $curlinfo['http_code']);
        }
        return $response;
    }

    
    protected function validate_response($response) {

        $response = $this->decode_response($response);

        if (empty($response)) {
            throw new checker_exception('err_response_empty');
        }

        if (empty($response['status']) or $response['status'] !== 'OK') {
            throw new checker_exception('err_response_status', $response['status']);
        }

        if (empty($response['apiver']) or $response['apiver'] !== '1.3') {
            throw new checker_exception('err_response_format_version', $response['apiver']);
        }

        if (empty($response['forbranch']) or $response['forbranch'] !== moodle_major_version(true)) {
            throw new checker_exception('err_response_target_version', $response['forbranch']);
        }
    }

    
    protected function decode_response($response) {
        return json_decode($response, true);
    }

    
    protected function store_response($response) {

        set_config('recentfetch', time(), 'core_plugin');
        set_config('recentresponse', $response, 'core_plugin');

        if (defined('CACHE_DISABLE_ALL') and CACHE_DISABLE_ALL) {
                                    \cache_helper::purge_all(true);
        }

        $this->restore_response(true);
    }

    
    protected function restore_response($forcereload = false) {

        if (!$forcereload and !is_null($this->recentresponse)) {
                        return;
        }

        $config = get_config('core_plugin');

        if (!empty($config->recentresponse) and !empty($config->recentfetch)) {
            try {
                $this->validate_response($config->recentresponse);
                $this->recentfetch = $config->recentfetch;
                $this->recentresponse = $this->decode_response($config->recentresponse);
            } catch (checker_exception $e) {
                                                                                $this->recentresponse = array();
            }

        } else {
            $this->recentresponse = array();
        }
    }

    
    protected function compare_responses(array $old, array $new) {

        if (empty($new)) {
            return array();
        }

        if (!array_key_exists('updates', $new)) {
            throw new checker_exception('err_response_format');
        }

        if (empty($old)) {
            return $new['updates'];
        }

        if (!array_key_exists('updates', $old)) {
            throw new checker_exception('err_response_format');
        }

        $changes = array();

        foreach ($new['updates'] as $newcomponent => $newcomponentupdates) {
            if (empty($old['updates'][$newcomponent])) {
                $changes[$newcomponent] = $newcomponentupdates;
                continue;
            }
            foreach ($newcomponentupdates as $newcomponentupdate) {
                $inold = false;
                foreach ($old['updates'][$newcomponent] as $oldcomponentupdate) {
                    if ($newcomponentupdate['version'] == $oldcomponentupdate['version']) {
                        $inold = true;
                    }
                }
                if (!$inold) {
                    if (!isset($changes[$newcomponent])) {
                        $changes[$newcomponent] = array();
                    }
                    $changes[$newcomponent][] = $newcomponentupdate;
                }
            }
        }

        return $changes;
    }

    
    protected function prepare_request_url() {
        global $CFG;

        if (!empty($CFG->config_php_settings['alternativeupdateproviderurl'])) {
            return $CFG->config_php_settings['alternativeupdateproviderurl'];
        } else {
            return 'https://download.moodle.org/api/1.3/updates.php';
        }
    }

    
    protected function load_current_environment($forcereload=false) {
        global $CFG;

        if (!is_null($this->currentversion) and !$forcereload) {
                        return;
        }

        $version = null;
        $release = null;

        require($CFG->dirroot.'/version.php');
        $this->currentversion = $version;
        $this->currentrelease = $release;
        $this->currentbranch = moodle_major_version(true);

        $pluginman = \core_plugin_manager::instance();
        foreach ($pluginman->get_plugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                if (!$plugin->is_standard()) {
                    $this->currentplugins[$plugin->component] = $plugin->versiondisk;
                }
            }
        }
    }

    
    protected function prepare_request_params() {
        global $CFG;

        $this->load_current_environment();
        $this->restore_response();

        $params = array();
        $params['format'] = 'json';

        if (isset($this->recentresponse['ticket'])) {
            $params['ticket'] = $this->recentresponse['ticket'];
        }

        if (isset($this->currentversion)) {
            $params['version'] = $this->currentversion;
        } else {
            throw new coding_exception('Main Moodle version must be already known here');
        }

        if (isset($this->currentbranch)) {
            $params['branch'] = $this->currentbranch;
        } else {
            throw new coding_exception('Moodle release must be already known here');
        }

        $plugins = array();
        foreach ($this->currentplugins as $plugin => $version) {
            $plugins[] = $plugin.'@'.$version;
        }
        if (!empty($plugins)) {
            $params['plugins'] = implode(',', $plugins);
        }

        return $params;
    }

    
    protected function prepare_request_options() {
        $options = array(
            'CURLOPT_SSL_VERIFYHOST' => 2,                  'CURLOPT_SSL_VERIFYPEER' => true,
        );

        return $options;
    }

    
    protected function cron_current_timestamp() {
        return time();
    }

    
    protected function cron_mtrace($msg, $eol = PHP_EOL) {
        mtrace($msg, $eol);
    }

    
    protected function cron_autocheck_enabled() {
        global $CFG;

        if (empty($CFG->updateautocheck)) {
            return false;
        } else {
            return true;
        }
    }

    
    protected function cron_has_fresh_fetch($now) {
        $recent = $this->get_last_timefetched();

        if (empty($recent)) {
            return false;
        }

        if ($now < $recent) {
            $this->cron_mtrace('The most recent fetch is reported to be in the future, this is weird!');
            return true;
        }

        if ($now - $recent > 24 * HOURSECS) {
            return false;
        }

        return true;
    }

    
    protected function cron_has_outdated_fetch($now) {
        $recent = $this->get_last_timefetched();

        if (empty($recent)) {
            return true;
        }

        if ($now < $recent) {
            $this->cron_mtrace('The most recent fetch is reported to be in the future, this is weird!');
            return false;
        }

        if ($now - $recent > 48 * HOURSECS) {
            return true;
        }

        return false;
    }

    
    protected function cron_execution_offset() {
        global $CFG;

        if (empty($CFG->updatecronoffset)) {
            set_config('updatecronoffset', rand(1, 5 * HOURSECS));
        }

        return $CFG->updatecronoffset;
    }

    
    protected function cron_execute() {

        try {
            $this->restore_response();
            $previous = $this->recentresponse;
            $this->fetch();
            $this->restore_response(true);
            $current = $this->recentresponse;
            $changes = $this->compare_responses($previous, $current);
            $notifications = $this->cron_notifications($changes);
            $this->cron_notify($notifications);
            $this->cron_mtrace('done');
        } catch (checker_exception $e) {
            $this->cron_mtrace('FAILED!');
        }
    }

    
    protected function cron_notifications(array $changes) {
        global $CFG;

        if (empty($changes)) {
            return array();
        }

        $notifications = array();
        $pluginman = \core_plugin_manager::instance();
        $plugins = $pluginman->get_plugins();

        foreach ($changes as $component => $componentchanges) {
            if (empty($componentchanges)) {
                continue;
            }
            $componentupdates = $this->get_update_info($component,
                array('minmaturity' => $CFG->updateminmaturity, 'notifybuilds' => $CFG->updatenotifybuilds));
            if (empty($componentupdates)) {
                continue;
            }
                                    foreach ($componentchanges as $componentchange) {
                foreach ($componentupdates as $componentupdate) {
                    if ($componentupdate->version == $componentchange['version']) {
                        if ($component == 'core') {
                                                                                                                                                                                                    if ((string)$componentupdate->release === (string)$componentchange['release']) {
                                $notifications[] = $componentupdate;
                            }
                        } else {
                                                                                                                                            list($plugintype, $pluginname) = core_component::normalize_component($component);
                            if (!empty($plugins[$plugintype][$pluginname])) {
                                $availableupdates = $plugins[$plugintype][$pluginname]->available_updates();
                                if (!empty($availableupdates)) {
                                    foreach ($availableupdates as $availableupdate) {
                                        if ($availableupdate->version == $componentchange['version']) {
                                            $notifications[] = $componentupdate;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $notifications;
    }

    
    protected function cron_notify(array $notifications) {
        global $CFG;

        if (empty($notifications)) {
            $this->cron_mtrace('nothing to notify about. ', '');
            return;
        }

        $admins = get_admins();

        if (empty($admins)) {
            return;
        }

        $this->cron_mtrace('sending notifications ... ', '');

        $text = get_string('updatenotifications', 'core_admin') . PHP_EOL;
        $html = html_writer::tag('h1', get_string('updatenotifications', 'core_admin')) . PHP_EOL;

        $coreupdates = array();
        $pluginupdates = array();

        foreach ($notifications as $notification) {
            if ($notification->component == 'core') {
                $coreupdates[] = $notification;
            } else {
                $pluginupdates[] = $notification;
            }
        }

        if (!empty($coreupdates)) {
            $text .= PHP_EOL . get_string('updateavailable', 'core_admin') . PHP_EOL;
            $html .= html_writer::tag('h2', get_string('updateavailable', 'core_admin')) . PHP_EOL;
            $html .= html_writer::start_tag('ul') . PHP_EOL;
            foreach ($coreupdates as $coreupdate) {
                $html .= html_writer::start_tag('li');
                if (isset($coreupdate->release)) {
                    $text .= get_string('updateavailable_release', 'core_admin', $coreupdate->release);
                    $html .= html_writer::tag('strong', get_string('updateavailable_release', 'core_admin', $coreupdate->release));
                }
                if (isset($coreupdate->version)) {
                    $text .= ' '.get_string('updateavailable_version', 'core_admin', $coreupdate->version);
                    $html .= ' '.get_string('updateavailable_version', 'core_admin', $coreupdate->version);
                }
                if (isset($coreupdate->maturity)) {
                    $text .= ' ('.get_string('maturity'.$coreupdate->maturity, 'core_admin').')';
                    $html .= ' ('.get_string('maturity'.$coreupdate->maturity, 'core_admin').')';
                }
                $text .= PHP_EOL;
                $html .= html_writer::end_tag('li') . PHP_EOL;
            }
            $text .= PHP_EOL;
            $html .= html_writer::end_tag('ul') . PHP_EOL;

            $a = array('url' => $CFG->wwwroot.'/'.$CFG->admin.'/index.php');
            $text .= get_string('updateavailabledetailslink', 'core_admin', $a) . PHP_EOL;
            $a = array('url' => html_writer::link($CFG->wwwroot.'/'.$CFG->admin.'/index.php', $CFG->wwwroot.'/'.$CFG->admin.'/index.php'));
            $html .= html_writer::tag('p', get_string('updateavailabledetailslink', 'core_admin', $a)) . PHP_EOL;

            $text .= PHP_EOL . get_string('updateavailablerecommendation', 'core_admin') . PHP_EOL;
            $html .= html_writer::tag('p', get_string('updateavailablerecommendation', 'core_admin')) . PHP_EOL;
        }

        if (!empty($pluginupdates)) {
            $text .= PHP_EOL . get_string('updateavailableforplugin', 'core_admin') . PHP_EOL;
            $html .= html_writer::tag('h2', get_string('updateavailableforplugin', 'core_admin')) . PHP_EOL;

            $html .= html_writer::start_tag('ul') . PHP_EOL;
            foreach ($pluginupdates as $pluginupdate) {
                $html .= html_writer::start_tag('li');
                $text .= get_string('pluginname', $pluginupdate->component);
                $html .= html_writer::tag('strong', get_string('pluginname', $pluginupdate->component));

                $text .= ' ('.$pluginupdate->component.')';
                $html .= ' ('.$pluginupdate->component.')';

                $text .= ' '.get_string('updateavailable', 'core_plugin', $pluginupdate->version);
                $html .= ' '.get_string('updateavailable', 'core_plugin', $pluginupdate->version);

                $text .= PHP_EOL;
                $html .= html_writer::end_tag('li') . PHP_EOL;
            }
            $text .= PHP_EOL;
            $html .= html_writer::end_tag('ul') . PHP_EOL;

            $a = array('url' => $CFG->wwwroot.'/'.$CFG->admin.'/plugins.php');
            $text .= get_string('updateavailabledetailslink', 'core_admin', $a) . PHP_EOL;
            $a = array('url' => html_writer::link($CFG->wwwroot.'/'.$CFG->admin.'/plugins.php', $CFG->wwwroot.'/'.$CFG->admin.'/plugins.php'));
            $html .= html_writer::tag('p', get_string('updateavailabledetailslink', 'core_admin', $a)) . PHP_EOL;
        }

        $a = array('siteurl' => $CFG->wwwroot);
        $text .= PHP_EOL . get_string('updatenotificationfooter', 'core_admin', $a) . PHP_EOL;
        $a = array('siteurl' => html_writer::link($CFG->wwwroot, $CFG->wwwroot));
        $html .= html_writer::tag('footer', html_writer::tag('p', get_string('updatenotificationfooter', 'core_admin', $a),
            array('style' => 'font-size:smaller; color:#333;')));

        foreach ($admins as $admin) {
            $message = new \stdClass();
            $message->component         = 'moodle';
            $message->name              = 'availableupdate';
            $message->userfrom          = get_admin();
            $message->userto            = $admin;
            $message->subject           = get_string('updatenotificationsubject', 'core_admin', array('siteurl' => $CFG->wwwroot));
            $message->fullmessage       = $text;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml   = $html;
            $message->smallmessage      = get_string('updatenotifications', 'core_admin');
            $message->notification      = 1;
            message_send($message);
        }
    }

    
    protected function is_same_release($remote, $local=null) {

        if (is_null($local)) {
            $this->load_current_environment();
            $local = $this->currentrelease;
        }

        $pattern = '/^([0-9\.\+]+)([^(]*)/';

        preg_match($pattern, $remote, $remotematches);
        preg_match($pattern, $local, $localmatches);

        $remotematches[1] = str_replace('+', '', $remotematches[1]);
        $localmatches[1] = str_replace('+', '', $localmatches[1]);

        if ($remotematches[1] === $localmatches[1] and rtrim($remotematches[2]) === rtrim($localmatches[2])) {
            return true;
        } else {
            return false;
        }
    }
}
