<?php



defined('MOODLE_INTERNAL') || die();


class renderer_base {
    
    protected $opencontainers;

    
    protected $page;

    
    protected $target;

    
    private $mustache;

    
    protected function get_mustache() {
        global $CFG;

        if ($this->mustache === null) {
            require_once($CFG->dirroot . '/lib/mustache/src/Mustache/Autoloader.php');
            Mustache_Autoloader::register();

            $themename = $this->page->theme->name;
            $themerev = theme_get_revision();

            $cachedir = make_localcache_directory("mustache/$themerev/$themename");

            $loader = new \core\output\mustache_filesystem_loader();
            $stringhelper = new \core\output\mustache_string_helper();
            $quotehelper = new \core\output\mustache_quote_helper();
            $jshelper = new \core\output\mustache_javascript_helper($this->page->requires);
            $pixhelper = new \core\output\mustache_pix_helper($this);

                        $safeconfig = $this->page->requires->get_config_for_javascript($this->page, $this);

            $helpers = array('config' => $safeconfig,
                             'str' => array($stringhelper, 'str'),
                             'quote' => array($quotehelper, 'quote'),
                             'js' => array($jshelper, 'help'),
                             'pix' => array($pixhelper, 'pix'));

            $this->mustache = new Mustache_Engine(array(
                'cache' => $cachedir,
                'escape' => 's',
                'loader' => $loader,
                'helpers' => $helpers,
                'pragmas' => [Mustache_Engine::PRAGMA_BLOCKS]));

        }

        return $this->mustache;
    }


    
    public function __construct(moodle_page $page, $target) {
        $this->opencontainers = $page->opencontainers;
        $this->page = $page;
        $this->target = $target;
    }

    
    public function render_from_template($templatename, $context) {
        static $templatecache = array();
        $mustache = $this->get_mustache();

                                        $mustache->addHelper('uniqid', new \core\output\mustache_uniqid_helper());
        if (isset($templatecache[$templatename])) {
            $template = $templatecache[$templatename];
        } else {
            try {
                $template = $mustache->loadTemplate($templatename);
                $templatecache[$templatename] = $template;
            } catch (Mustache_Exception_UnknownTemplateException $e) {
                throw new moodle_exception('Unknown template: ' . $templatename);
            }
        }
        return trim($template->render($context));
    }


    
    public function render(renderable $widget) {
        $classname = get_class($widget);
                $classname = preg_replace('/^.*\\\/', '', $classname);
                $classname = preg_replace('/_renderable$/', '', $classname);

        $rendermethod = 'render_'.$classname;
        if (method_exists($this, $rendermethod)) {
            return $this->$rendermethod($widget);
        }
        throw new coding_exception('Can not render widget, renderer method ('.$rendermethod.') not found.');
    }

    
    public function add_action_handler(component_action $action, $id = null) {
        if (!$id) {
            $id = html_writer::random_id($action->event);
        }
        $this->page->requires->event_handler("#$id", $action->event, $action->jsfunction, $action->jsfunctionargs);
        return $id;
    }

    
    public function has_started() {
        return $this->page->state >= moodle_page::STATE_IN_BODY;
    }

    
    public static function prepare_classes($classes) {
        if (is_array($classes)) {
            return implode(' ', array_unique($classes));
        }
        return $classes;
    }

    
    public function pix_url($imagename, $component = 'moodle') {
        return $this->page->theme->pix_url($imagename, $component);
    }
}



class plugin_renderer_base extends renderer_base {

    
    protected $output;

    
    public function __construct(moodle_page $page, $target) {
        if (empty($target) && $page->pagelayout === 'maintenance') {
                                                $target = RENDERER_TARGET_MAINTENANCE;
        }
        $this->output = $page->get_renderer('core', null, $target);
        parent::__construct($page, $target);
    }

    
    public function render(renderable $widget) {
        $classname = get_class($widget);
                $classname = preg_replace('/^.*\\\/', '', $classname);
                $deprecatedmethod = 'render_'.$classname;
                $classname = preg_replace('/_renderable$/', '', $classname);

        $rendermethod = 'render_'.$classname;
        if (method_exists($this, $rendermethod)) {
            return $this->$rendermethod($widget);
        }
        if ($rendermethod !== $deprecatedmethod && method_exists($this, $deprecatedmethod)) {
                                                                                                            static $debugged = array();
            if (!isset($debugged[$deprecatedmethod])) {
                debugging(sprintf('Deprecated call. Please rename your renderables render method from %s to %s.',
                    $deprecatedmethod, $rendermethod), DEBUG_DEVELOPER);
                $debugged[$deprecatedmethod] = true;
            }
            return $this->$deprecatedmethod($widget);
        }
                return $this->output->render($widget);
    }

    
    public function __call($method, $arguments) {
        if (method_exists('renderer_base', $method)) {
            throw new coding_exception('Protected method called against '.get_class($this).' :: '.$method);
        }
        if (method_exists($this->output, $method)) {
            return call_user_func_array(array($this->output, $method), $arguments);
        } else {
            throw new coding_exception('Unknown method called against '.get_class($this).' :: '.$method);
        }
    }
}



class core_renderer extends renderer_base {
    
    const MAIN_CONTENT_TOKEN = '[MAIN CONTENT GOES HERE]';

    
    protected $contenttype;

    
    protected $metarefreshtag = '';

    
    protected $unique_end_html_token;

    
    protected $unique_performance_info_token;

    
    protected $unique_main_content_token;

    
    public function __construct(moodle_page $page, $target) {
        $this->opencontainers = $page->opencontainers;
        $this->page = $page;
        $this->target = $target;

        $this->unique_end_html_token = '%%ENDHTML-'.sesskey().'%%';
        $this->unique_performance_info_token = '%%PERFORMANCEINFO-'.sesskey().'%%';
        $this->unique_main_content_token = '[MAIN CONTENT GOES HERE - '.sesskey().']';
    }

    
    public function doctype() {
        if ($this->page->theme->doctype === 'html5') {
            $this->contenttype = 'text/html; charset=utf-8';
            return "<!DOCTYPE html>\n";

        } else if ($this->page->theme->doctype === 'xhtml5') {
            $this->contenttype = 'application/xhtml+xml; charset=utf-8';
            return "<!DOCTYPE html>\n";

        } else {
                        $this->contenttype = 'text/html; charset=utf-8';
            return ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n");
        }
    }

    
    public function htmlattributes() {
        $return = get_html_lang(true);
        if ($this->page->theme->doctype !== 'html5') {
            $return .= ' xmlns="http://www.w3.org/1999/xhtml"';
        }
        return $return;
    }

    
    public function standard_head_html() {
        global $CFG, $SESSION;

                
                        foreach ($this->page->blocks->get_regions() as $region) {
            $this->page->blocks->ensure_content_created($region, $this);
        }

        $output = '';

                if (isset($CFG->urlrewriteclass) && !isset($CFG->upgraderunning)) {
            $class = $CFG->urlrewriteclass;
            $output .= $class::html_head_setup();
        }

        $output .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
        $output .= '<meta name="keywords" content="moodle, ' . $this->page->title . '" />' . "\n";
                $output .= $this->metarefreshtag;

                        if ($this->metarefreshtag=='' && $this->page->periodicrefreshdelay!==null) {
            $output .= '<meta http-equiv="refresh" content="'.$this->page->periodicrefreshdelay.';url='.$this->page->url->out().'" />';
        }

                $this->page->requires->js_function_call('M.util.load_flowplayer');

                $this->page->requires->js_init_call('M.util.help_popups.setup');

                $this->page->requires->yui_module('moodle-core-popuphelp', 'M.core.init_popuphelp');
        $this->page->requires->strings_for_js(array(
            'morehelp',
            'loadinghelp',
        ), 'moodle');

        $this->page->requires->js_function_call('setTimeout', array('fix_column_widths()', 20));

        $focus = $this->page->focuscontrol;
        if (!empty($focus)) {
            if (preg_match("#forms\['([a-zA-Z0-9]+)'\].elements\['([a-zA-Z0-9]+)'\]#", $focus, $matches)) {
                                                $this->page->requires->js_function_call('old_onload_focus', array($matches[1], $matches[2]));
            } else if (strpos($focus, '.')!==false) {
                                debugging('This code is using the old style focus event, Please update this code to focus on an element id or the moodleform focus method.', DEBUG_DEVELOPER);
                $this->page->requires->js_function_call('old_onload_focus', explode('.', $focus, 2));
            } else {
                                $this->page->requires->js_function_call('focuscontrol', array($focus));
            }
        }

                        $urls = $this->page->theme->css_urls($this->page);
        foreach ($urls as $url) {
            $this->page->requires->css_theme($url);
        }

                if ($jsurl = $this->page->theme->javascript_url(true)) {
            $this->page->requires->js($jsurl, true);
        }
        if ($jsurl = $this->page->theme->javascript_url(false)) {
            $this->page->requires->js($jsurl);
        }

                $output .= $this->page->requires->get_head_code($this->page, $this);

                foreach ($this->page->alternateversions as $type => $alt) {
            $output .= html_writer::empty_tag('link', array('rel' => 'alternate',
                    'type' => $type, 'title' => $alt->title, 'href' => $alt->url));
        }

        if (!empty($CFG->additionalhtmlhead)) {
            $output .= "\n".$CFG->additionalhtmlhead;
        }

        return $output;
    }

    
    public function standard_top_of_body_html() {
        global $CFG;
        $output = $this->page->requires->get_top_of_body_code();
        if ($this->page->pagelayout !== 'embedded' && !empty($CFG->additionalhtmltopofbody)) {
            $output .= "\n".$CFG->additionalhtmltopofbody;
        }
        $output .= $this->maintenance_warning();
        return $output;
    }

    
    public function maintenance_warning() {
        global $CFG;

        $output = '';
        if (isset($CFG->maintenance_later) and $CFG->maintenance_later > time()) {
            $timeleft = $CFG->maintenance_later - time();
                        $errorclass = ($timeleft < 30) ? 'error' : 'warning';
            $output .= $this->box_start($errorclass . ' moodle-has-zindex maintenancewarning');
            $a = new stdClass();
            $a->min = (int)($timeleft/60);
            $a->sec = (int)($timeleft % 60);
            $output .= get_string('maintenancemodeisscheduled', 'admin', $a) ;
            $output .= $this->box_end();
            $this->page->requires->yui_module('moodle-core-maintenancemodetimer', 'M.core.maintenancemodetimer',
                    array(array('timeleftinsec' => $timeleft)));
            $this->page->requires->strings_for_js(
                    array('maintenancemodeisscheduled', 'sitemaintenance'),
                    'admin');
        }
        return $output;
    }

    
    public function standard_footer_html() {
        global $CFG, $SCRIPT;

        if (during_initial_install()) {
                                    return '';
        }

                                $output = $this->unique_performance_info_token;
        if ($this->page->devicetypeinuse == 'legacy') {
                        $output .= html_writer::tag('div', get_string('legacythemeinuse'), array('class'=>'legacythemeinuse'));
        }

                $output .= $this->theme_switch_links();

        if (!empty($CFG->debugpageinfo)) {
            $output .= '<div class="performanceinfo pageinfo">This page is: ' . $this->page->debug_summary() . '</div>';
        }
        if (debugging(null, DEBUG_DEVELOPER) and has_capability('moodle/site:config', context_system::instance())) {                          if (function_exists('profiling_is_running') && profiling_is_running()) {
                $txt = get_string('profiledscript', 'admin');
                $title = get_string('profiledscriptview', 'admin');
                $url = $CFG->wwwroot . '/admin/tool/profiling/index.php?script=' . urlencode($SCRIPT);
                $link= '<a title="' . $title . '" href="' . $url . '">' . $txt . '</a>';
                $output .= '<div class="profilingfooter">' . $link . '</div>';
            }
            $purgeurl = new moodle_url('/admin/purgecaches.php', array('confirm' => 1,
                'sesskey' => sesskey(), 'returnurl' => $this->page->url->out_as_local_url(false)));
            $output .= '<div class="purgecaches">' .
                    html_writer::link($purgeurl, get_string('purgecaches', 'admin')) . '</div>';
        }
        if (!empty($CFG->debugvalidators)) {
                        $output .= '<div class="validators"><ul>
              <li><a href="http://validator.w3.org/check?verbose=1&amp;ss=1&amp;uri=' . urlencode(qualified_me()) . '">Validate HTML</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=-1&amp;url1=' . urlencode(qualified_me()) . '">Section 508 Check</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=0&amp;warnp2n3e=1&amp;url1=' . urlencode(qualified_me()) . '">WCAG 1 (2,3) Check</a></li>
            </ul></div>';
        }
        return $output;
    }

    
    public function main_content() {
                                                        return '<div role="main">'.$this->unique_main_content_token.'</div>';
    }

    
    public function standard_end_of_body_html() {
        global $CFG;

                                $output = '';
        if ($this->page->pagelayout !== 'embedded' && !empty($CFG->additionalhtmlfooter)) {
            $output .= "\n".$CFG->additionalhtmlfooter;
        }
        $output .= $this->unique_end_html_token;
        return $output;
    }

    
    public function login_info($withlinks = null) {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        $course = $this->page->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=".sesskey()."\"";
                $realuserinfo .= "title =\"".$loginastitle."\">$fullname</a>] ";
            } else {
                $realuserinfo = " [$fullname] ";
            }
        } else {
            $realuserinfo = '';
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();

        if (empty($course->id)) {
                        return '';
        } else if (isloggedin()) {
            $context = context_course::instance($course->id);

            $fullname = fullname($USER, true);
                        if ($withlinks) {
                $linktitle = get_string('viewprofile');
                $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
            } else {
                $username = $fullname;
            }
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid))) {
                if ($withlinks) {
                    $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
                } else {
                    $username .= " from {$idprovider->name}";
                }
            }
            if (isguestuser()) {
                $loggedinas = $realuserinfo.get_string('loggedinasguest');
                if (!$loginpage && $withlinks) {
                    $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
                }
            } else if (is_role_switched($course->id)) {                 $rolename = '';
                if ($role = $DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path]))) {
                    $rolename = ': '.role_get_name($role, $context);
                }
                $loggedinas = get_string('loggedinas', 'moodle', $username).$rolename;
                if ($withlinks) {
                    $url = new moodle_url('/course/switchrole.php', array('id'=>$course->id,'sesskey'=>sesskey(), 'switchrole'=>0, 'returnurl'=>$this->page->url->out_as_local_url(false)));
                    $loggedinas .= ' ('.html_writer::tag('a', get_string('switchrolereturn'), array('href' => $url)).')';
                }
            } else {
                $loggedinas = $realuserinfo.get_string('loggedinas', 'moodle', $username);
                if ($withlinks) {
                    $loggedinas .= " (<a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\">".get_string('logout').'</a>)';
                }
            }
        } else {
            $loggedinas = get_string('loggedinnot', 'moodle');
            if (!$loginpage && $withlinks) {
                $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }
        }

        $loggedinas = '<div class="logininfo">'.$loggedinas.'</div>';

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                                        require_once($CFG->dirroot . '/user/lib.php');
                    if ($count = user_count_login_failures($USER)) {
                        $loggedinas .= '<div class="loginfailures">';
                        $a = new stdClass();
                        $a->attempts = $count;
                        $loggedinas .= get_string('failedloginattempts', '', $a);
                        if (file_exists("$CFG->dirroot/report/log/index.php") and has_capability('report/log:view', context_system::instance())) {
                            $loggedinas .= ' ('.html_writer::link(new moodle_url('/report/log/index.php', array('chooselog' => 1,
                                    'id' => 0 , 'modid' => 'site_errors')), get_string('logs')).')';
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }

        return $loggedinas;
    }

    
    protected function is_login_page() {
                                return in_array(
            $this->page->url->out_as_local_url(false, array()),
            array(
                '/login/index.php',
                '/login/forgot_password.php',
            )
        );
    }

    
    public function home_link() {
        global $CFG, $SITE;

        if ($this->page->pagetype == 'site-index') {
                        return '<div class="sitelink">' .
                   '<a title="Moodle" href="http://moodle.org/">' .
                   '<img src="' . $this->pix_url('moodlelogo') . '" alt="'.get_string('moodlelogo').'" /></a></div>';

        } else if (!empty($CFG->target_release) && $CFG->target_release != $CFG->release) {
                        return '<div class="sitelink">'.
                   '<a title="Moodle" href="http://docs.moodle.org/en/Administrator_documentation" onclick="this.target=\'_blank\'">' .
                   '<img src="' . $this->pix_url('moodlelogo') . '" alt="'.get_string('moodlelogo').'" /></a></div>';

        } else if ($this->page->course->id == $SITE->id || strpos($this->page->pagetype, 'course-view') === 0) {
            return '<div class="homelink"><a href="' . $CFG->wwwroot . '/">' .
                    get_string('home') . '</a></div>';

        } else {
            return '<div class="homelink"><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $this->page->course->id . '">' .
                    format_string($this->page->course->shortname, true, array('context' => $this->page->context)) . '</a></div>';
        }
    }

    
    public function redirect_message($encodedurl, $message, $delay, $debugdisableredirect,
                                     $messagetype = \core\output\notification::NOTIFY_INFO) {
        global $CFG;
        $url = str_replace('&amp;', '&', $encodedurl);

        switch ($this->page->state) {
            case moodle_page::STATE_BEFORE_HEADER :
                                if (!$debugdisableredirect) {
                                        $this->metarefreshtag = '<meta http-equiv="refresh" content="'. $delay .'; url='. $encodedurl .'" />'."\n";
                    $this->page->requires->js_function_call('document.location.replace', array($url), false, ($delay + 3));
                }
                $output = $this->header();
                break;
            case moodle_page::STATE_PRINTING_HEADER :
                                throw new coding_exception('You cannot redirect while printing the page header');
                break;
            case moodle_page::STATE_IN_BODY :
                                debugging("You should really redirect before you start page output");
                if (!$debugdisableredirect) {
                    $this->page->requires->js_function_call('document.location.replace', array($url), false, $delay);
                }
                $output = $this->opencontainers->pop_all_but_last();
                break;
            case moodle_page::STATE_DONE :
                                throw new coding_exception('You cannot redirect after the entire page has been generated');
                break;
        }
        $output .= $this->notification($message, $messagetype);
        $output .= '<div class="continuebutton">(<a href="'. $encodedurl .'">'. get_string('continue') .'</a>)</div>';
        if ($debugdisableredirect) {
            $output .= '<p><strong>'.get_string('erroroutput', 'error').'</strong></p>';
        }
        $output .= $this->footer();
        return $output;
    }

    
    public function header() {
        global $USER, $CFG;

        if (\core\session\manager::is_loggedinas()) {
            $this->page->add_body_class('userloggedinas');
        }

                                if (!during_initial_install() && isloggedin() && is_role_switched($this->page->course->id)) {
            $this->page->add_body_class('userswitchedrole');
        }

                $this->page->theme->init_page($this->page);

        $this->page->set_state(moodle_page::STATE_PRINTING_HEADER);

                $layoutfile = $this->page->theme->layout_file($this->page->pagelayout);
                $rendered = $this->render_page_layout($layoutfile);

                $cutpos = strpos($rendered, $this->unique_main_content_token);
        if ($cutpos === false) {
            $cutpos = strpos($rendered, self::MAIN_CONTENT_TOKEN);
            $token = self::MAIN_CONTENT_TOKEN;
        } else {
            $token = $this->unique_main_content_token;
        }

        if ($cutpos === false) {
            throw new coding_exception('page layout file ' . $layoutfile . ' does not contain the main content placeholder, please include "<?php echo $OUTPUT->main_content() ?>" in theme layout file.');
        }
        $header = substr($rendered, 0, $cutpos);
        $footer = substr($rendered, $cutpos + strlen($token));

        if (empty($this->contenttype)) {
            debugging('The page layout file did not call $OUTPUT->doctype()');
            $header = $this->doctype() . $header;
        }

                if ((!isset($this->page->theme->settings->version) || $this->page->theme->settings->version < 2012101500) &&
                $this->page->pagelayout === 'course' && $this->page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                        $coursecontentheader = $this->course_content_header(true);
            $coursecontentfooter = $this->course_content_footer(true);
            if (!empty($coursecontentheader)) {
                                                                                debugging('The current theme is not optimised for 2.4, the course-specific header and footer defined in course format will not be output', DEBUG_DEVELOPER);
                $header .= $coursecontentheader;
                $footer = $coursecontentfooter. $footer;
            }
        }

        send_headers($this->contenttype, $this->page->cacheable);

        $this->opencontainers->push('header/footer', $footer);
        $this->page->set_state(moodle_page::STATE_IN_BODY);

        return $header . $this->skip_link_target('maincontent');
    }

    
    protected function render_page_layout($layoutfile) {
        global $CFG, $SITE, $USER;
                                                        $OUTPUT = $this;
        $PAGE = $this->page;
        $COURSE = $this->page->course;

        ob_start();
        include($layoutfile);
        $rendered = ob_get_contents();
        ob_end_clean();
        return $rendered;
    }

    
    public function footer() {
        global $CFG, $DB, $PAGE;

        $output = $this->container_end_all(true);

        $footer = $this->opencontainers->pop('header/footer');

        if (debugging() and $DB and $DB->is_transaction_started()) {
                    }

                $performanceinfo = '';
        if (defined('MDL_PERF') || (!empty($CFG->perfdebug) and $CFG->perfdebug > 7)) {
            $perf = get_performance_info();
            if (defined('MDL_PERFTOFOOT') || debugging() || $CFG->perfdebug > 7) {
                $performanceinfo = $perf['html'];
            }
        }

                if (MDL_PERF_TEST && strpos($footer, $this->unique_performance_info_token) === false) {
            $footer = $this->unique_performance_info_token . $footer;
        }
        $footer = str_replace($this->unique_performance_info_token, $performanceinfo, $footer);

                if (!empty($PAGE->context->id)) {
            $this->page->requires->js_call_amd('core/notification', 'init', array(
                $PAGE->context->id,
                \core\notification::fetch_as_array($this)
            ));
        }
        $footer = str_replace($this->unique_end_html_token, $this->page->requires->get_end_code(), $footer);

        $this->page->set_state(moodle_page::STATE_DONE);

        return $output . $footer;
    }

    
    public function container_end_all($shouldbenone = false) {
        return $this->opencontainers->pop_all_but_last($shouldbenone);
    }

    
    public function course_content_header($onlyifnotcalledbefore = false) {
        global $CFG;
        static $functioncalled = false;
        if ($functioncalled && $onlyifnotcalledbefore) {
                        return '';
        }

                $notifications = \core\notification::fetch();

        $bodynotifications = '';
        foreach ($notifications as $notification) {
            $bodynotifications .= $this->render_from_template(
                    $notification->get_template_name(),
                    $notification->export_for_template($this)
                );
        }

        $output = html_writer::span($bodynotifications, 'notifications', array('id' => 'user-notifications'));

        if ($this->page->course->id == SITEID) {
                        return $output;
        }

        require_once($CFG->dirroot.'/course/lib.php');
        $functioncalled = true;
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_content_header()) !== null) {
            $output .= html_writer::div($courseformat->get_renderer($this->page)->render($obj), 'course-content-header');
        }
        return $output;
    }

    
    public function course_content_footer($onlyifnotcalledbefore = false) {
        global $CFG;
        if ($this->page->course->id == SITEID) {
                        return '';
        }
        static $functioncalled = false;
        if ($functioncalled && $onlyifnotcalledbefore) {
                        return '';
        }
        $functioncalled = true;
        require_once($CFG->dirroot.'/course/lib.php');
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_content_footer()) !== null) {
            return html_writer::div($courseformat->get_renderer($this->page)->render($obj), 'course-content-footer');
        }
        return '';
    }

    
    public function course_header() {
        global $CFG;
        if ($this->page->course->id == SITEID) {
                        return '';
        }
        require_once($CFG->dirroot.'/course/lib.php');
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_header()) !== null) {
            return $courseformat->get_renderer($this->page)->render($obj);
        }
        return '';
    }

    
    public function course_footer() {
        global $CFG;
        if ($this->page->course->id == SITEID) {
                        return '';
        }
        require_once($CFG->dirroot.'/course/lib.php');
        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_footer()) !== null) {
            return $courseformat->get_renderer($this->page)->render($obj);
        }
        return '';
    }

    
    public function lang_menu() {
        global $CFG;

        if (empty($CFG->langmenu)) {
            return '';
        }

        if ($this->page->course != SITEID and !empty($this->page->course->lang)) {
                        return '';
        }

        $currlang = current_language();
        $langs = get_string_manager()->get_list_of_translations();

        if (count($langs) < 2) {
            return '';
        }

        $s = new single_select($this->page->url, 'lang', $langs, $currlang, null);
        $s->label = get_accesshide(get_string('language'));
        $s->class = 'langmenu';
        return $this->render($s);
    }

    
    public function block_controls($actions, $blockid = null) {
        global $CFG;
        if (empty($actions)) {
            return '';
        }
        $menu = new action_menu($actions);
        if ($blockid !== null) {
            $menu->set_owner_selector('#'.$blockid);
        }
        $menu->set_constraint('.block-region');
        $menu->attributes['class'] .= ' block-control-actions commands';
        if (isset($CFG->blockeditingmenu) && !$CFG->blockeditingmenu) {
            $menu->do_not_enhance();
        }
        return $this->render($menu);
    }

    
    public function render_action_menu(action_menu $menu) {
        $menu->initialise_js($this->page);

        $output = html_writer::start_tag('div', $menu->attributes);
        $output .= html_writer::start_tag('ul', $menu->attributesprimary);
        foreach ($menu->get_primary_actions($this) as $action) {
            if ($action instanceof renderable) {
                $content = $this->render($action);
            } else {
                $content = $action;
            }
            $output .= html_writer::tag('li', $content, array('role' => 'presentation'));
        }
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::start_tag('ul', $menu->attributessecondary);
        foreach ($menu->get_secondary_actions() as $action) {
            if ($action instanceof renderable) {
                $content = $this->render($action);
            } else {
                $content = $action;
            }
            $output .= html_writer::tag('li', $content, array('role' => 'presentation'));
        }
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    
    protected function render_action_menu_link(action_menu_link $action) {
        static $actioncount = 0;
        $actioncount++;

        $comparetoalt = '';
        $text = '';
        if (!$action->icon || $action->primary === false) {
            $text .= html_writer::start_tag('span', array('class'=>'menu-action-text', 'id' => 'actionmenuaction-'.$actioncount));
            if ($action->text instanceof renderable) {
                $text .= $this->render($action->text);
            } else {
                $text .= $action->text;
                $comparetoalt = (string)$action->text;
            }
            $text .= html_writer::end_tag('span');
        }

        $icon = '';
        if ($action->icon) {
            $icon = $action->icon;
            if ($action->primary || !$action->actionmenu->will_be_enhanced()) {
                $action->attributes['title'] = $action->text;
            }
            if (!$action->primary && $action->actionmenu->will_be_enhanced()) {
                if ((string)$icon->attributes['alt'] === $comparetoalt) {
                    $icon->attributes['alt'] = '';
                }
                if (isset($icon->attributes['title']) && (string)$icon->attributes['title'] === $comparetoalt) {
                    unset($icon->attributes['title']);
                }
            }
            $icon = $this->render($icon);
        }

                if (!empty($action->attributes['disabled'])) {
                        return html_writer::tag('span', $icon.$text, array('class'=>'currentlink', 'role' => 'menuitem'));
        }

        $attributes = $action->attributes;
        unset($action->attributes['disabled']);
        $attributes['href'] = $action->url;
        if ($text !== '') {
            $attributes['aria-labelledby'] = 'actionmenuaction-'.$actioncount;
        }

        return html_writer::tag('a', $icon.$text, $attributes);
    }

    
    protected function render_action_menu_filler(action_menu_filler $action) {
        return html_writer::span('&nbsp;', 'filler');
    }

    
    protected function render_action_menu_link_primary(action_menu_link_primary $action) {
        return $this->render_action_menu_link($action);
    }

    
    protected function render_action_menu_link_secondary(action_menu_link_secondary $action) {
        return $this->render_action_menu_link($action);
    }

    
    public function block(block_contents $bc, $region) {
        $bc = clone($bc);         if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }
        if (!empty($bc->blockinstanceid)) {
            $bc->attributes['data-instanceid'] = $bc->blockinstanceid;
        }
        $skiptitle = strip_tags($bc->title);
        if ($bc->blockinstanceid && !empty($skiptitle)) {
            $bc->attributes['aria-labelledby'] = 'instance-'.$bc->blockinstanceid.'-header';
        } else if (!empty($bc->arialabel)) {
            $bc->attributes['aria-label'] = $bc->arialabel;
        }
        if ($bc->dockable) {
            $bc->attributes['data-dockable'] = 1;
        }
        if ($bc->collapsible == block_contents::HIDDEN) {
            $bc->add_class('hidden');
        }
        if (!empty($bc->controls)) {
            $bc->add_class('block_with_controls');
        }


        if (empty($skiptitle)) {
            $output = '';
            $skipdest = '';
        } else {
            $output = html_writer::link('#sb-'.$bc->skipid, get_string('skipa', 'access', $skiptitle),
                      array('class' => 'skip skip-block', 'id' => 'fsb-' . $bc->skipid));
            $skipdest = html_writer::span('', 'skip-block-to',
                      array('id' => 'sb-' . $bc->skipid));
        }

        $output .= html_writer::start_tag('div', $bc->attributes);

        $output .= $this->block_header($bc);
        $output .= $this->block_content($bc);

        $output .= html_writer::end_tag('div');

        $output .= $this->block_annotation($bc);

        $output .= $skipdest;

        $this->init_block_hider_js($bc);
        return $output;
    }

    
    protected function block_header(block_contents $bc) {

        $title = '';
        if ($bc->title) {
            $attributes = array();
            if ($bc->blockinstanceid) {
                $attributes['id'] = 'instance-'.$bc->blockinstanceid.'-header';
            }
            $title = html_writer::tag('h2', $bc->title, $attributes);
        }

        $blockid = null;
        if (isset($bc->attributes['id'])) {
            $blockid = $bc->attributes['id'];
        }
        $controlshtml = $this->block_controls($bc->controls, $blockid);

        $output = '';
        if ($title || $controlshtml) {
            $output .= html_writer::tag('div', html_writer::tag('div', html_writer::tag('div', '', array('class'=>'block_action')). $title . $controlshtml, array('class' => 'title')), array('class' => 'header'));
        }
        return $output;
    }

    
    protected function block_content(block_contents $bc) {
        $output = html_writer::start_tag('div', array('class' => 'content'));
        if (!$bc->title && !$this->block_controls($bc->controls)) {
            $output .= html_writer::tag('div', '', array('class'=>'block_action notitle'));
        }
        $output .= $bc->content;
        $output .= $this->block_footer($bc);
        $output .= html_writer::end_tag('div');

        return $output;
    }

    
    protected function block_footer(block_contents $bc) {
        $output = '';
        if ($bc->footer) {
            $output .= html_writer::tag('div', $bc->footer, array('class' => 'footer'));
        }
        return $output;
    }

    
    protected function block_annotation(block_contents $bc) {
        $output = '';
        if ($bc->annotation) {
            $output .= html_writer::tag('div', $bc->annotation, array('class' => 'blockannotation'));
        }
        return $output;
    }

    
    protected function init_block_hider_js(block_contents $bc) {
        if (!empty($bc->attributes['id']) and $bc->collapsible != block_contents::NOT_HIDEABLE) {
            $config = new stdClass;
            $config->id = $bc->attributes['id'];
            $config->title = strip_tags($bc->title);
            $config->preference = 'block' . $bc->blockinstanceid . 'hidden';
            $config->tooltipVisible = get_string('hideblocka', 'access', $config->title);
            $config->tooltipHidden = get_string('showblocka', 'access', $config->title);

            $this->page->requires->js_init_call('M.util.init_block_hider', array($config));
            user_preference_allow_ajax_update($config->preference, PARAM_BOOL);
        }
    }

    
    public function list_block_contents($icons, $items) {
        $row = 0;
        $lis = array();
        foreach ($items as $key => $string) {
            $item = html_writer::start_tag('li', array('class' => 'r' . $row));
            if (!empty($icons[$key])) {                 $item .= html_writer::tag('div', $icons[$key], array('class' => 'icon column c0'));
            }
            $item .= html_writer::tag('div', $string, array('class' => 'column c1'));
            $item .= html_writer::end_tag('li');
            $lis[] = $item;
            $row = 1 - $row;         }
        return html_writer::tag('ul', implode("\n", $lis), array('class' => 'unlist'));
    }

    
    public function blocks_for_region($region) {
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $blocks = $this->page->blocks->get_blocks_for_region($region);
        $lastblock = null;
        $zones = array();
        foreach ($blocks as $block) {
            $zones[] = $block->title;
        }
        $output = '';

        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                $output .= $this->block($bc, $region);
                $lastblock = $bc->title;
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc, $zones, $lastblock, $region);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }

    
    public function block_move_target($target, $zones, $previous, $region) {
        if ($previous == null) {
            if (empty($zones)) {
                                $regions = $this->page->theme->get_all_block_regions();
                $position = get_string('moveblockinregion', 'block', $regions[$region]);
            } else {
                $position = get_string('moveblockbefore', 'block', $zones[0]);
            }
        } else {
            $position = get_string('moveblockafter', 'block', $previous);
        }
        return html_writer::tag('a', html_writer::tag('span', $position, array('class' => 'accesshide')), array('href' => $target->url, 'class' => 'blockmovetarget'));
    }

    
    public function action_link($url, $text, component_action $action = null, array $attributes = null, $icon = null) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $link = new action_link($url, $text, $action, $attributes, $icon);

        return $this->render($link);
    }

    
    protected function render_action_link(action_link $link) {
        global $CFG;

        $text = '';
        if ($link->icon) {
            $text .= $this->render($link->icon);
        }

        if ($link->text instanceof renderable) {
            $text .= $this->render($link->text);
        } else {
            $text .= $link->text;
        }

                if (!empty($link->attributes['disabled'])) {
                        return html_writer::tag('span', $text, array('class'=>'currentlink'));
        }

        $attributes = $link->attributes;
        unset($link->attributes['disabled']);
        $attributes['href'] = $link->url;

        if ($link->actions) {
            if (empty($attributes['id'])) {
                $id = html_writer::random_id('action_link');
                $attributes['id'] = $id;
            } else {
                $id = $attributes['id'];
            }
            foreach ($link->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }

        return html_writer::tag('a', $text, $attributes);
    }


    
    public function action_icon($url, pix_icon $pixicon, component_action $action = null, array $attributes = null, $linktext=false) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $attributes = (array)$attributes;

        if (empty($attributes['class'])) {
                        $attributes['class'] = 'action-icon';
        }

        if ($linktext) {
            $icon = '';
            $text = $pixicon->attributes['alt'];
        } else {
            $icon = $this->render($pixicon);
            $text = '';
        }

        return $this->action_link($url, $text.$icon, $action, $attributes);
    }

   
    public function confirm($message, $continue, $cancel) {
        if ($continue instanceof single_button) {
                    } else if (is_string($continue)) {
            $continue = new single_button(new moodle_url($continue), get_string('continue'), 'post');
        } else if ($continue instanceof moodle_url) {
            $continue = new single_button($continue, get_string('continue'), 'post');
        } else {
            throw new coding_exception('The continue param to $OUTPUT->confirm() must be either a URL (string/moodle_url) or a single_button instance.');
        }

        if ($cancel instanceof single_button) {
                    } else if (is_string($cancel)) {
            $cancel = new single_button(new moodle_url($cancel), get_string('cancel'), 'get');
        } else if ($cancel instanceof moodle_url) {
            $cancel = new single_button($cancel, get_string('cancel'), 'get');
        } else {
            throw new coding_exception('The cancel param to $OUTPUT->confirm() must be either a URL (string/moodle_url) or a single_button instance.');
        }

        $output = $this->box_start('generalbox', 'notice');
        $output .= html_writer::tag('p', $message);
        $output .= html_writer::tag('div', $this->render($continue) . $this->render($cancel), array('class' => 'buttons'));
        $output .= $this->box_end();
        return $output;
    }

    
    public function single_button($url, $label, $method='post', array $options=null) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $button = new single_button($url, $label, $method);

        foreach ((array)$options as $key=>$value) {
            if (array_key_exists($key, $button)) {
                $button->$key = $value;
            }
        }

        return $this->render($button);
    }

    
    protected function render_single_button(single_button $button) {
        $attributes = array('type'     => 'submit',
                            'value'    => $button->label,
                            'disabled' => $button->disabled ? 'disabled' : null,
                            'title'    => $button->tooltip);

        if ($button->actions) {
            $id = html_writer::random_id('single_button');
            $attributes['id'] = $id;
            foreach ($button->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }

                $output = html_writer::empty_tag('input', $attributes);

                $params = $button->url->params();
        if ($button->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $var => $val) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
        }

                $output = html_writer::tag('div', $output);

                if ($button->method === 'get') {
            $url = $button->url->out_omit_querystring(true);         } else {
            $url = $button->url->out_omit_querystring();             }
        if ($url === '') {
            $url = '#';         }
        $attributes = array('method' => $button->method,
                            'action' => $url,
                            'id'     => $button->formid);
        $output = html_writer::tag('form', $output, $attributes);

                return html_writer::tag('div', $output, array('class' => $button->class));
    }

    
    public function single_select($url, $name, array $options, $selected = '',
                                $nothing = array('' => 'choosedots'), $formid = null, $attributes = array()) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $select = new single_select($url, $name, $options, $selected, $nothing, $formid);

        if (array_key_exists('label', $attributes)) {
            $select->set_label($attributes['label']);
            unset($attributes['label']);
        }
        $select->attributes = $attributes;

        return $this->render($select);
    }

    
    public function download_dataformat_selector($label, $base, $name = 'dataformat', $params = array()) {

        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[] = array(
                    'value' => $format->name,
                    'label' => get_string('dataformat', $format->component),
                );
            }
        }
        $hiddenparams = array();
        foreach ($params as $key => $value) {
            $hiddenparams[] = array(
                'name' => $key,
                'value' => $value,
            );
        }
        $data = array(
            'label' => $label,
            'base' => $base,
            'name' => $name,
            'params' => $hiddenparams,
            'options' => $options,
            'sesskey' => sesskey(),
            'submit' => get_string('download'),
        );

        return $this->render_from_template('core/dataformat_selector', $data);
    }


    
    protected function render_single_select(single_select $select) {
        $select = clone($select);
        if (empty($select->formid)) {
            $select->formid = html_writer::random_id('single_select_f');
        }

        $output = '';
        $params = $select->url->params();
        if ($select->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $name=>$value) {
            $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>$name, 'value'=>$value));
        }

        if (empty($select->attributes['id'])) {
            $select->attributes['id'] = html_writer::random_id('single_select');
        }

        if ($select->disabled) {
            $select->attributes['disabled'] = 'disabled';
        }

        if ($select->tooltip) {
            $select->attributes['title'] = $select->tooltip;
        }

        $select->attributes['class'] = 'autosubmit';
        if ($select->class) {
            $select->attributes['class'] .= ' ' . $select->class;
        }

        if ($select->label) {
            $output .= html_writer::label($select->label, $select->attributes['id'], false, $select->labelattributes);
        }

        if ($select->helpicon instanceof help_icon) {
            $output .= $this->render($select->helpicon);
        }
        $output .= html_writer::select($select->options, $select->name, $select->selected, $select->nothing, $select->attributes);

        $go = html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('go')));
        $output .= html_writer::tag('noscript', html_writer::tag('div', $go), array('class' => 'inline'));

        $nothing = empty($select->nothing) ? false : key($select->nothing);
        $this->page->requires->yui_module('moodle-core-formautosubmit',
            'M.core.init_formautosubmit',
            array(array('selectid' => $select->attributes['id'], 'nothing' => $nothing))
        );

                $output = html_writer::tag('div', $output);

                if ($select->method === 'get') {
            $url = $select->url->out_omit_querystring(true);         } else {
            $url = $select->url->out_omit_querystring();             }
        $formattributes = array('method' => $select->method,
                                'action' => $url,
                                'id'     => $select->formid);
        $output = html_writer::tag('form', $output, $formattributes);

                return html_writer::tag('div', $output, array('class' => $select->class));
    }

    
    public function url_select(array $urls, $selected, $nothing = array('' => 'choosedots'), $formid = null) {
        $select = new url_select($urls, $selected, $nothing, $formid);
        return $this->render($select);
    }

    
    protected function render_url_select(url_select $select) {
        global $CFG;

        $select = clone($select);
        if (empty($select->formid)) {
            $select->formid = html_writer::random_id('url_select_f');
        }

        if (empty($select->attributes['id'])) {
            $select->attributes['id'] = html_writer::random_id('url_select');
        }

        if ($select->disabled) {
            $select->attributes['disabled'] = 'disabled';
        }

        if ($select->tooltip) {
            $select->attributes['title'] = $select->tooltip;
        }

        $output = '';

        if ($select->label) {
            $output .= html_writer::label($select->label, $select->attributes['id'], false, $select->labelattributes);
        }

        $classes = array();
        if (!$select->showbutton) {
            $classes[] = 'autosubmit';
        }
        if ($select->class) {
            $classes[] = $select->class;
        }
        if (count($classes)) {
            $select->attributes['class'] = implode(' ', $classes);
        }

        if ($select->helpicon instanceof help_icon) {
            $output .= $this->render($select->helpicon);
        }

                        $urls = array();
        foreach ($select->urls as $k=>$v) {
            if (is_array($v)) {
                                foreach ($v as $optgrouptitle => $optgroupoptions) {
                    foreach ($optgroupoptions as $optionurl => $optiontitle) {
                        if (empty($optionurl)) {
                            $safeoptionurl = '';
                        } else if (strpos($optionurl, $CFG->wwwroot.'/') === 0) {
                                                        $safeoptionurl = str_replace($CFG->wwwroot, '', $optionurl);
                        } else if (strpos($optionurl, '/') !== 0) {
                            debugging("Invalid url_select urls parameter inside optgroup: url '$optionurl' is not local relative url!");
                            continue;
                        } else {
                            $safeoptionurl = $optionurl;
                        }
                        $urls[$k][$optgrouptitle][$safeoptionurl] = $optiontitle;
                    }
                }
            } else {
                                if (empty($k)) {
                                    } else if (strpos($k, $CFG->wwwroot.'/') === 0) {
                    $k = str_replace($CFG->wwwroot, '', $k);
                } else if (strpos($k, '/') !== 0) {
                    debugging("Invalid url_select urls parameter: url '$k' is not local relative url!");
                    continue;
                }
                $urls[$k] = $v;
            }
        }
        $selected = $select->selected;
        if (!empty($selected)) {
            if (strpos($select->selected, $CFG->wwwroot.'/') === 0) {
                $selected = str_replace($CFG->wwwroot, '', $selected);
            } else if (strpos($selected, '/') !== 0) {
                debugging("Invalid value of parameter 'selected': url '$selected' is not local relative url!");
            }
        }

        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()));
        $output .= html_writer::select($urls, 'jump', $selected, $select->nothing, $select->attributes);

        if (!$select->showbutton) {
            $go = html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('go')));
            $output .= html_writer::tag('noscript', html_writer::tag('div', $go), array('class' => 'inline'));
            $nothing = empty($select->nothing) ? false : key($select->nothing);
            $this->page->requires->yui_module('moodle-core-formautosubmit',
                'M.core.init_formautosubmit',
                array(array('selectid' => $select->attributes['id'], 'nothing' => $nothing))
            );
        } else {
            $output .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>$select->showbutton));
        }

                $output = html_writer::tag('div', $output);

                $formattributes = array('method' => 'post',
                                'action' => new moodle_url('/course/jumpto.php'),
                                'id'     => $select->formid);
        $output = html_writer::tag('form', $output, $formattributes);

                return html_writer::tag('div', $output, array('class' => $select->class));
    }

    
    public function doc_link($path, $text = '', $forcepopup = false) {
        global $CFG;

        $icon = $this->pix_icon('docs', '', 'moodle', array('class'=>'iconhelp icon-pre', 'role'=>'presentation'));

        $url = new moodle_url(get_docs_url($path));

        $attributes = array('href'=>$url);
        if (!empty($CFG->doctonewwindow) || $forcepopup) {
            $attributes['class'] = 'helplinkpopup';
        }

        return html_writer::tag('a', $icon.$text, $attributes);
    }

    
    public function pix_icon($pix, $alt, $component='moodle', array $attributes = null) {
        $icon = new pix_icon($pix, $alt, $component, $attributes);
        return $this->render($icon);
    }

    
    protected function render_pix_icon(pix_icon $icon) {
        $data = $icon->export_for_template($this);
        return $this->render_from_template('core/pix_icon', $data);
    }

    
    protected function render_pix_emoticon(pix_emoticon $emoticon) {
        $attributes = $emoticon->attributes;
        $attributes['src'] = $this->pix_url($emoticon->pix, $emoticon->component);
        return html_writer::empty_tag('img', $attributes);
    }

    
    function render_rating(rating $rating) {
        global $CFG, $USER;

        if ($rating->settings->aggregationmethod == RATING_AGGREGATE_NONE) {
            return null;        }

        $ratingmanager = new rating_manager();
                $ratingmanager->initialise_rating_javascript($this->page);

        $strrate = get_string("rate", "rating");
        $ratinghtml = ''; 
                if ($rating->user_can_view_aggregate()) {

            $aggregatelabel = $ratingmanager->get_aggregate_label($rating->settings->aggregationmethod);
            $aggregatestr   = $rating->get_aggregate_string();

            $aggregatehtml  = html_writer::tag('span', $aggregatestr, array('id' => 'ratingaggregate'.$rating->itemid, 'class' => 'ratingaggregate')).' ';
            if ($rating->count > 0) {
                $countstr = "({$rating->count})";
            } else {
                $countstr = '-';
            }
            $aggregatehtml .= html_writer::tag('span', $countstr, array('id'=>"ratingcount{$rating->itemid}", 'class' => 'ratingcount')).' ';

            $ratinghtml .= html_writer::tag('span', $aggregatelabel, array('class'=>'rating-aggregate-label'));
            if ($rating->settings->permissions->viewall && $rating->settings->pluginpermissions->viewall) {

                $nonpopuplink = $rating->get_view_ratings_url();
                $popuplink = $rating->get_view_ratings_url(true);

                $action = new popup_action('click', $popuplink, 'ratings', array('height' => 400, 'width' => 600));
                $ratinghtml .= $this->action_link($nonpopuplink, $aggregatehtml, $action);
            } else {
                $ratinghtml .= $aggregatehtml;
            }
        }

        $formstart = null;
                        if ($rating->user_can_rate()) {

            $rateurl = $rating->get_rate_url();
            $inputs = $rateurl->params();

                        $formattrs = array(
                'id'     => "postrating{$rating->itemid}",
                'class'  => 'postratingform',
                'method' => 'post',
                'action' => $rateurl->out_omit_querystring()
            );
            $formstart  = html_writer::start_tag('form', $formattrs);
            $formstart .= html_writer::start_tag('div', array('class' => 'ratingform'));

                        foreach ($inputs as $name => $value) {
                $attributes = array('type' => 'hidden', 'class' => 'ratinginput', 'name' => $name, 'value' => $value);
                $formstart .= html_writer::empty_tag('input', $attributes);
            }

            if (empty($ratinghtml)) {
                $ratinghtml .= $strrate.': ';
            }
            $ratinghtml = $formstart.$ratinghtml;

            $scalearray = array(RATING_UNSET_RATING => $strrate.'...') + $rating->settings->scale->scaleitems;
            $scaleattrs = array('class'=>'postratingmenu ratinginput','id'=>'menurating'.$rating->itemid);
            $ratinghtml .= html_writer::label($rating->rating, 'menurating'.$rating->itemid, false, array('class' => 'accesshide'));
            $ratinghtml .= html_writer::select($scalearray, 'rating', $rating->rating, false, $scaleattrs);

                        $ratinghtml .= html_writer::start_tag('span', array('class'=>"ratingsubmit"));

            $attributes = array('type' => 'submit', 'class' => 'postratingmenusubmit', 'id' => 'postratingsubmit'.$rating->itemid, 'value' => s(get_string('rate', 'rating')));
            $ratinghtml .= html_writer::empty_tag('input', $attributes);

            if (!$rating->settings->scale->isnumeric) {
                                if (empty($rating->settings->scale->courseid) and $coursecontext = $rating->context->get_course_context(false)) {
                    $courseid = $coursecontext->instanceid;
                } else {
                    $courseid = $rating->settings->scale->courseid;
                }
                $ratinghtml .= $this->help_icon_scale($courseid, $rating->settings->scale);
            }
            $ratinghtml .= html_writer::end_tag('span');
            $ratinghtml .= html_writer::end_tag('div');
            $ratinghtml .= html_writer::end_tag('form');
        }

        return $ratinghtml;
    }

    
    public function heading_with_help($text, $helpidentifier, $component = 'moodle', $icon = '', $iconalt = '', $level = 2, $classnames = null) {
        $image = '';
        if ($icon) {
            $image = $this->pix_icon($icon, $iconalt, $component, array('class'=>'icon iconlarge'));
        }

        $help = '';
        if ($helpidentifier) {
            $help = $this->help_icon($helpidentifier, $component);
        }

        return $this->heading($image.$text.$help, $level, $classnames);
    }

    
    public function old_help_icon($helpidentifier, $title, $component = 'moodle', $linktext = '') {
        throw new coding_exception('old_help_icon() can not be used any more, please see help_icon().');
    }

    
    public function help_icon($identifier, $component = 'moodle', $linktext = '') {
        $icon = new help_icon($identifier, $component);
        $icon->diag_strings();
        if ($linktext === true) {
            $icon->linktext = get_string($icon->identifier, $icon->component);
        } else if (!empty($linktext)) {
            $icon->linktext = $linktext;
        }
        return $this->render($icon);
    }

    
    protected function render_help_icon(help_icon $helpicon) {
        global $CFG;

                $src = $this->pix_url('help');

        $title = get_string($helpicon->identifier, $helpicon->component);

        if (empty($helpicon->linktext)) {
            $alt = get_string('helpprefix2', '', trim($title, ". \t"));
        } else {
            $alt = get_string('helpwiththis');
        }

        $attributes = array('src'=>$src, 'alt'=>$alt, 'class'=>'iconhelp');
        $output = html_writer::empty_tag('img', $attributes);

                if (!empty($helpicon->linktext)) {
                        $output .= $helpicon->linktext;
        }

                $url = new moodle_url($CFG->httpswwwroot.'/help.php', array('component' => $helpicon->component, 'identifier' => $helpicon->identifier, 'lang'=>current_language()));

                $title = get_string('helpprefix2', '', trim($title, ". \t"));

        $attributes = array('href' => $url, 'title' => $title, 'aria-haspopup' => 'true', 'target'=>'_blank');
        $output = html_writer::tag('a', $output, $attributes);

                return html_writer::tag('span', $output, array('class' => 'helptooltip'));
    }

    
    public function help_icon_scale($courseid, stdClass $scale) {
        global $CFG;

        $title = get_string('helpprefix2', '', $scale->name) .' ('.get_string('newwindow').')';

        $icon = $this->pix_icon('help', get_string('scales'), 'moodle', array('class'=>'iconhelp'));

        $scaleid = abs($scale->id);

        $link = new moodle_url('/course/scales.php', array('id' => $courseid, 'list' => true, 'scaleid' => $scaleid));
        $action = new popup_action('click', $link, 'ratingscale');

        return html_writer::tag('span', $this->action_link($link, $icon, $action), array('class' => 'helplink'));
    }

    
    public function spacer(array $attributes = null, $br = false) {
        $attributes = (array)$attributes;
        if (empty($attributes['width'])) {
            $attributes['width'] = 1;
        }
        if (empty($attributes['height'])) {
            $attributes['height'] = 1;
        }
        $attributes['class'] = 'spacer';

        $output = $this->pix_icon('spacer', '', 'moodle', $attributes);

        if (!empty($br)) {
            $output .= '<br />';
        }

        return $output;
    }

    
    public function user_picture(stdClass $user, array $options = null) {
        $userpicture = new user_picture($user);
        foreach ((array)$options as $key=>$value) {
            if (array_key_exists($key, $userpicture)) {
                $userpicture->$key = $value;
            }
        }
        return $this->render($userpicture);
    }

    
    protected function render_user_picture(user_picture $userpicture) {
        global $CFG, $DB;

        $user = $userpicture->user;

        if ($userpicture->alttext) {
            if (!empty($user->imagealt)) {
                $alt = $user->imagealt;
            } else {
                $alt = get_string('pictureof', '', fullname($user));
            }
        } else {
            $alt = '';
        }

        if (empty($userpicture->size)) {
            $size = 35;
        } else if ($userpicture->size === true or $userpicture->size == 1) {
            $size = 100;
        } else {
            $size = $userpicture->size;
        }

        $class = $userpicture->class;

        if ($user->picture == 0) {
            $class .= ' defaultuserpic';
        }

        $src = $userpicture->get_url($this->page, $this);

        $attributes = array('src'=>$src, 'alt'=>$alt, 'title'=>$alt, 'class'=>$class, 'width'=>$size, 'height'=>$size);
        if (!$userpicture->visibletoscreenreaders) {
            $attributes['role'] = 'presentation';
        }

                $output = html_writer::empty_tag('img', $attributes);

                if (!$userpicture->link) {
            return $output;
        }

        if (empty($userpicture->courseid)) {
            $courseid = $this->page->course->id;
        } else {
            $courseid = $userpicture->courseid;
        }

        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $user->id));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
        }

        $attributes = array('href'=>$url);
        if (!$userpicture->visibletoscreenreaders) {
            $attributes['tabindex'] = '-1';
            $attributes['aria-hidden'] = 'true';
        }

        if ($userpicture->popup) {
            $id = html_writer::random_id('userpicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new popup_action('click', $url), $id);
        }

        return html_writer::tag('a', $output, $attributes);
    }

    
    public function htmllize_file_tree($dir) {
        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $result .= '<li>'.s($subdir['dirname']).' '.$this->htmllize_file_tree($subdir).'</li>';
        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $result .= '<li><span>'.html_writer::link($file->fileurl, $filename).'</span></li>';
        }
        $result .= '</ul>';

        return $result;
    }

    
    public function file_picker($options) {
        $fp = new file_picker($options);
        return $this->render($fp);
    }

    
    public function render_file_picker(file_picker $fp) {
        global $CFG, $OUTPUT, $USER;
        $options = $fp->options;
        $client_id = $options->client_id;
        $strsaved = get_string('filesaved', 'repository');
        $straddfile = get_string('openpicker', 'repository');
        $strloading  = get_string('loading', 'repository');
        $strdndenabled = get_string('dndenabled_inbox', 'moodle');
        $strdroptoupload = get_string('droptoupload', 'moodle');
        $icon_progress = $OUTPUT->pix_icon('i/loading_small', $strloading).'';

        $currentfile = $options->currentfile;
        if (empty($currentfile)) {
            $currentfile = '';
        } else {
            $currentfile .= ' - ';
        }
        if ($options->maxbytes) {
            $size = $options->maxbytes;
        } else {
            $size = get_max_upload_file_size();
        }
        if ($size == -1) {
            $maxsize = '';
        } else {
            $maxsize = get_string('maxfilesize', 'moodle', display_size($size));
        }
        if ($options->buttonname) {
            $buttonname = ' name="' . $options->buttonname . '"';
        } else {
            $buttonname = '';
        }
        $html = <<<EOD
<div class="filemanager-loading mdl-align" id='filepicker-loading-{$client_id}'>
$icon_progress
</div>
<div id="filepicker-wrapper-{$client_id}" class="mdl-left" style="display:none">
    <div>
        <input type="button" class="fp-btn-choose" id="filepicker-button-{$client_id}" value="{$straddfile}"{$buttonname}/>
        <span> $maxsize </span>
    </div>
EOD;
        if ($options->env != 'url') {
            $html .= <<<EOD
    <div id="file_info_{$client_id}" class="mdl-left filepicker-filelist" style="position: relative">
    <div class="filepicker-filename">
        <div class="filepicker-container">$currentfile<div class="dndupload-message">$strdndenabled <br/><div class="dndupload-arrow"></div></div></div>
        <div class="dndupload-progressbars"></div>
    </div>
    <div><div class="dndupload-target">{$strdroptoupload}<br/><div class="dndupload-arrow"></div></div></div>
    </div>
EOD;
        }
        $html .= '</div>';
        return $html;
    }

    
    public function update_module_button($cmid, $modulename) {
        global $CFG;
        if (has_capability('moodle/course:manageactivities', context_module::instance($cmid))) {
            $modulename = get_string('modulename', $modulename);
            $string = get_string('updatethis', '', $modulename);
            $url = new moodle_url("$CFG->wwwroot/course/mod.php", array('update' => $cmid, 'return' => true, 'sesskey' => sesskey()));
            return $this->single_button($url, $string);
        } else {
            return '';
        }
    }

    
    public function edit_button(moodle_url $url) {

        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }

        return $this->single_button($url, $editstring);
    }

    
    public function close_window_button($text='') {
        if (empty($text)) {
            $text = get_string('closewindow');
        }
        $button = new single_button(new moodle_url('#'), $text, 'get');
        $button->add_action(new component_action('click', 'close_window'));

        return $this->container($this->render($button), 'closewindow');
    }

    
    public function error_text($message) {
        if (empty($message)) {
            return '';
        }
        $message = $this->pix_icon('i/warning', get_string('error'), '', array('class' => 'icon icon-pre', 'title'=>'')) . $message;
        return html_writer::tag('span', $message, array('class' => 'error'));
    }

    
    public function fatal_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = "") {
        global $CFG;

        $output = '';
        $obbuffer = '';

        if ($this->has_started()) {
                                    $output .= $this->opencontainers->pop_all_but_last();

        } else {
                                                error_reporting(0);             while (ob_get_level() > 0) {
                $buff = ob_get_clean();
                if ($buff === false) {
                    break;
                }
                $obbuffer .= $buff;
            }
            error_reporting($CFG->debug);

                        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            if (empty($_SERVER['HTTP_RANGE'])) {
                @header($protocol . ' 404 Not Found');
            } else if (core_useragent::check_safari_ios_version(602) && !empty($_SERVER['HTTP_X_PLAYBACK_SESSION_ID'])) {
                                @header($protocol . ' 403 Forbidden');
            } else {
                                                @header($protocol . ' 407 Proxy Authentication Required');
            }

            $this->page->set_context(null);             $this->page->set_url('/');                         $this->page->set_title(get_string('error'));
            $this->page->set_heading($this->page->course->fullname);
            $output .= $this->header();
        }

        $message = '<p class="errormessage">' . $message . '</p>'.
                '<p class="errorcode"><a href="' . $moreinfourl . '">' .
                get_string('moreinformation') . '</a></p>';
        if (empty($CFG->rolesactive)) {
            $message .= '<p class="errormessage">' . get_string('installproblem', 'error') . '</p>';
                    }
        $output .= $this->box($message, 'errorbox', null, array('data-rel' => 'fatalerror'));

        if ($CFG->debugdeveloper) {
            if (!empty($debuginfo)) {
                $debuginfo = s($debuginfo);                 $debuginfo = str_replace("\n", '<br />', $debuginfo);                 $output .= $this->notification('<strong>Debug info:</strong> '.$debuginfo, 'notifytiny');
            }
            if (!empty($backtrace)) {
                $output .= $this->notification('<strong>Stack trace:</strong> '.format_backtrace($backtrace), 'notifytiny');
            }
            if ($obbuffer !== '' ) {
                $output .= $this->notification('<strong>Output buffer:</strong> '.s($obbuffer), 'notifytiny');
            }
        }

        if (empty($CFG->rolesactive)) {
                    } else if (!empty($link)) {
            $output .= $this->continue_button($link);
        }

        $output .= $this->footer();

                $output .= str_repeat(' ', 512);

        return $output;
    }

    
    public function notification($message, $type = null) {
        $typemappings = [
                        'success'           => \core\output\notification::NOTIFY_SUCCESS,
            'info'              => \core\output\notification::NOTIFY_INFO,
            'warning'           => \core\output\notification::NOTIFY_WARNING,
            'error'             => \core\output\notification::NOTIFY_ERROR,

                        'notifyproblem'     => \core\output\notification::NOTIFY_ERROR,
            'notifytiny'        => \core\output\notification::NOTIFY_ERROR,
            'notifyerror'       => \core\output\notification::NOTIFY_ERROR,
            'notifysuccess'     => \core\output\notification::NOTIFY_SUCCESS,
            'notifymessage'     => \core\output\notification::NOTIFY_INFO,
            'notifyredirect'    => \core\output\notification::NOTIFY_INFO,
            'redirectmessage'   => \core\output\notification::NOTIFY_INFO,
        ];

        $extraclasses = [];

        if ($type) {
            if (strpos($type, ' ') === false) {
                                if (isset($typemappings[$type])) {
                    $type = $typemappings[$type];
                } else {
                                        $extraclasses = [$type];
                }
            } else {
                                $classarray = explode(' ', self::prepare_classes($type));

                                foreach ($classarray as $class) {
                    if (isset($typemappings[$class])) {
                        $type = $typemappings[$class];
                    } else {
                        $extraclasses[] = $class;
                    }
                }
            }
        }

        $notification = new \core\output\notification($message, $type);
        if (count($extraclasses)) {
            $notification->set_extra_classes($extraclasses);
        }

                return $this->render_from_template($notification->get_template_name(), $notification->export_for_template($this));
    }

    
    public function notify_problem($message) {
        debugging(__FUNCTION__ . ' is deprecated.' .
            'Please use \core\notification::add, or \core\output\notification as required',
            DEBUG_DEVELOPER);
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_ERROR);
        return $this->render($n);
    }

    
    public function notify_success($message) {
        debugging(__FUNCTION__ . ' is deprecated.' .
            'Please use \core\notification::add, or \core\output\notification as required',
            DEBUG_DEVELOPER);
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_SUCCESS);
        return $this->render($n);
    }

    
    public function notify_message($message) {
        debugging(__FUNCTION__ . ' is deprecated.' .
            'Please use \core\notification::add, or \core\output\notification as required',
            DEBUG_DEVELOPER);
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_INFO);
        return $this->render($n);
    }

    
    public function notify_redirect($message) {
        debugging(__FUNCTION__ . ' is deprecated.' .
            'Please use \core\notification::add, or \core\output\notification as required',
            DEBUG_DEVELOPER);
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_INFO);
        return $this->render($n);
    }

    
    protected function render_notification(\core\output\notification $notification) {
        return $this->render_from_template($notification->get_template_name(), $notification->export_for_template($this));
    }

    
    public function continue_button($url) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        $button = new single_button($url, get_string('continue'), 'get');
        $button->class = 'continuebutton';

        return $this->render($button);
    }

    
    public function paging_bar($totalcount, $page, $perpage, $baseurl, $pagevar = 'page') {
        $pb = new paging_bar($totalcount, $page, $perpage, $baseurl, $pagevar);
        return $this->render($pb);
    }

    
    protected function render_paging_bar(paging_bar $pagingbar) {
        $output = '';
        $pagingbar = clone($pagingbar);
        $pagingbar->prepare($this, $this->page, $this->target);

        if ($pagingbar->totalcount > $pagingbar->perpage) {
            $output .= get_string('page') . ':';

            if (!empty($pagingbar->previouslink)) {
                $output .= ' (' . $pagingbar->previouslink . ') ';
            }

            if (!empty($pagingbar->firstlink)) {
                $output .= ' ' . $pagingbar->firstlink . ' ...';
            }

            foreach ($pagingbar->pagelinks as $link) {
                $output .= "  $link";
            }

            if (!empty($pagingbar->lastlink)) {
                $output .= ' ... ' . $pagingbar->lastlink . ' ';
            }

            if (!empty($pagingbar->nextlink)) {
                $output .= '  (' . $pagingbar->nextlink . ')';
            }
        }

        return html_writer::tag('div', $output, array('class' => 'paging'));
    }

    
    public function skip_link_target($id = null) {
        return html_writer::span('', '', array('id' => $id));
    }

    
    public function heading($text, $level = 2, $classes = null, $id = null) {
        $level = (integer) $level;
        if ($level < 1 or $level > 6) {
            throw new coding_exception('Heading level must be an integer between 1 and 6.');
        }
        return html_writer::tag('h' . $level, $text, array('id' => $id, 'class' => renderer_base::prepare_classes($classes)));
    }

    
    public function box($contents, $classes = 'generalbox', $id = null, $attributes = array()) {
        return $this->box_start($classes, $id, $attributes) . $contents . $this->box_end();
    }

    
    public function box_start($classes = 'generalbox', $id = null, $attributes = array()) {
        $this->opencontainers->push('box', html_writer::end_tag('div'));
        $attributes['id'] = $id;
        $attributes['class'] = 'box ' . renderer_base::prepare_classes($classes);
        return html_writer::start_tag('div', $attributes);
    }

    
    public function box_end() {
        return $this->opencontainers->pop('box');
    }

    
    public function container($contents, $classes = null, $id = null) {
        return $this->container_start($classes, $id) . $contents . $this->container_end();
    }

    
    public function container_start($classes = null, $id = null) {
        $this->opencontainers->push('container', html_writer::end_tag('div'));
        return html_writer::start_tag('div', array('id' => $id,
                'class' => renderer_base::prepare_classes($classes)));
    }

    
    public function container_end() {
        return $this->opencontainers->pop('container');
    }

    
    public function tree_block_contents($items, $attrs = array()) {
                if (empty($items)) {
            return '';
        }
                $lis = array();
        foreach ($items as $item) {
                        $content = $item->content($this);
            $liclasses = array($item->get_css_type());
            if (!$item->forceopen || (!$item->forceopen && $item->collapse) || ($item->children->count()==0  && $item->nodetype==navigation_node::NODETYPE_BRANCH)) {
                $liclasses[] = 'collapsed';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $liattr = array('class'=>join(' ',$liclasses));
                        $divclasses = array('tree_item');
            if ($item->children->count()>0  || $item->nodetype==navigation_node::NODETYPE_BRANCH) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr) . $this->tree_block_contents($item->children);
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }
        return html_writer::tag('ul', implode("\n", $lis), $attrs);
    }

    
    public function search_box($id = false) {
        global $CFG;

                                if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', context_system::instance())) {
            return '';
        }

        if ($id == false) {
            $id = uniqid();
        } else {
                        $id = clean_param($id, PARAM_ALPHANUMEXT);
        }

                $this->page->requires->js_call_amd('core/search-input', 'init', array($id));

        $searchicon = html_writer::tag('div', $this->pix_icon('a/search', get_string('search', 'search'), 'moodle'),
            array('role' => 'button', 'tabindex' => 0));
        $formattrs = array('class' => 'search-input-form', 'action' => $CFG->wwwroot . '/search/index.php');
        $inputattrs = array('type' => 'text', 'name' => 'q', 'placeholder' => get_string('search', 'search'),
            'size' => 13, 'tabindex' => -1, 'id' => 'id_q_' . $id);

        $contents = html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
            array('for' => 'id_q_' . $id, 'class' => 'accesshide')) . html_writer::tag('input', '', $inputattrs);
        $searchinput = html_writer::tag('form', $contents, $formattrs);

        return html_writer::tag('div', $searchicon . $searchinput, array('class' => 'search-input-wrapper', 'id' => $id));
    }

    
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

                                if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

                $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

                if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
                if (!isloggedin()) {
            $returnstr = get_string('loggedinnot', 'moodle');
            if (!$loginpage) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

                if (isguestuser()) {
            $returnstr = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

                $opts = user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

                if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

                if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

                if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

                if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

                $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->initialise_js($this->page);
        $am->set_menu_trigger(
            $returnstr
        );
        $am->set_alignment(action_menu::TR, action_menu::BR);
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                                                $am->add($divider);
                        break;

                    case 'invalid':
                                                break;

                    case 'link':
                                                $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, $value->title, null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }

    
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $itemcount = count($items);
        if ($itemcount === 0) {
            return '';
        }

        $htmlblocks = array();
                $separator = get_separator();
        for ($i=0;$i < $itemcount;$i++) {
            $item = $items[$i];
            $item->hideicon = true;
            if ($i===0) {
                $content = html_writer::tag('li', $this->render($item));
            } else {
                $content = html_writer::tag('li', $separator.$this->render($item));
            }
            $htmlblocks[] = $content;
        }

                $navbarcontent = html_writer::tag('span', get_string('pagepath'),
                array('class' => 'accesshide', 'id' => 'navbar-label'));
        $navbarcontent .= html_writer::tag('nav',
                html_writer::tag('ul', join('', $htmlblocks)),
                array('aria-labelledby' => 'navbar-label'));
                return $navbarcontent;
    }

    
    protected function render_breadcrumb_navigation_node(breadcrumb_navigation_node $item) {

        if ($item->action instanceof moodle_url) {
            $content = $item->get_content();
            $title = $item->get_title();
            $attributes = array();
            $attributes['itemprop'] = 'url';
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::tag('span', $content, array('itemprop' => 'title'));
            $content = html_writer::link($item->action, $content, $attributes);

            $attributes = array();
            $attributes['itemscope'] = '';
            $attributes['itemtype'] = 'http://data-vocabulary.org/Breadcrumb';
            $content = html_writer::tag('span', $content, $attributes);

        } else {
            $content = $this->render_navigation_node($item);
        }
        return $content;
    }

    
    protected function render_navigation_node(navigation_node $item) {
        $content = $item->get_content();
        $title = $item->get_title();
        if ($item->icon instanceof renderable && !$item->hideicon) {
            $icon = $this->render($item->icon);
            $content = $icon.$content;         }
        if ($item->helpbutton !== null) {
            $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton', 'tabindex'=>'0'));
        }
        if ($content === '') {
            return '';
        }
        if ($item->action instanceof action_link) {
            $link = $item->action;
            if ($item->hidden) {
                $link->add_class('dimmed');
            }
            if (!empty($content)) {
                                $link->text = $content;
            }
            $content = $this->render($link);
        } else if ($item->action instanceof moodle_url) {
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::link($item->action, $content, $attributes);

        } else if (is_string($item->action) || empty($item->action)) {
            $attributes = array('tabindex'=>'0');             if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::tag('span', $content, $attributes);
        }
        return $content;
    }

    
    public function rarrow() {
        return $this->page->theme->rarrow;
    }

    
    public function larrow() {
        return $this->page->theme->larrow;
    }

    
    public function uarrow() {
        return $this->page->theme->uarrow;
    }

    
    public function custom_menu($custommenuitems = '') {
        global $CFG;
        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        if (empty($custommenuitems)) {
            return '';
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render($custommenu);
    }

    
    protected function render_custom_menu(custom_menu $menu) {
        static $menucount = 0;
                if (!$menu->has_children()) {
            return '';
        }
                        $menucount++;
                $jscode = js_writer::function_call_with_Y('M.core_custom_menu.init', array('custom_menu_'.$menucount));
        $jscode = "(function(){{$jscode}})";
        $this->page->requires->yui_module('node-menunav', $jscode);
                $content = html_writer::start_tag('div', array('id'=>'custom_menu_'.$menucount, 'class'=>'yui3-menu yui3-menu-horizontal javascript-disabled custom-menu'));
        $content .= html_writer::start_tag('div', array('class'=>'yui3-menu-content'));
        $content .= html_writer::start_tag('ul');
                foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item);
        }
                $content .= html_writer::end_tag('ul');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
                return $content;
    }

    
    protected function render_custom_menu_item(custom_menu_item $menunode) {
                static $submenucount = 0;
        if ($menunode->has_children()) {
                        $submenucount++;
            $content = html_writer::start_tag('li');
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('class'=>'yui3-menu-label', 'title'=>$menunode->get_title()));
            $content .= html_writer::start_tag('div', array('id'=>'cm_submenu_'.$submenucount, 'class'=>'yui3-menu custom_menu_submenu'));
            $content .= html_writer::start_tag('div', array('class'=>'yui3-menu-content'));
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('li');
        } else {
                                    $content = '';
            if (preg_match("/^#+$/", $menunode->get_text())) {

                                $content = html_writer::start_tag('li', array('class' => 'yui3-menuitem divider'));
            } else {
                $content = html_writer::start_tag(
                    'li',
                    array(
                        'class' => 'yui3-menuitem'
                    )
                );
                if ($menunode->get_url() !== null) {
                    $url = $menunode->get_url();
                } else {
                    $url = '#';
                }
                $content .= html_writer::link(
                    $url,
                    $menunode->get_text(),
                    array('class' => 'yui3-menuitem-content', 'title' => $menunode->get_title())
                );
            }
            $content .= html_writer::end_tag('li');
        }
                return $content;
    }

    
    protected function theme_switch_links() {

        $actualdevice = core_useragent::get_device_type();
        $currentdevice = $this->page->devicetypeinuse;
        $switched = ($actualdevice != $currentdevice);

        if (!$switched && $currentdevice == 'default' && $actualdevice == 'default') {
                                    return '';
        }

        if ($switched) {
            $linktext = get_string('switchdevicerecommended');
            $devicetype = $actualdevice;
        } else {
            $linktext = get_string('switchdevicedefault');
            $devicetype = 'default';
        }
        $linkurl = new moodle_url('/theme/switchdevice.php', array('url' => $this->page->url, 'device' => $devicetype, 'sesskey' => sesskey()));

        $content  = html_writer::start_tag('div', array('id' => 'theme_switch_link'));
        $content .= html_writer::link($linkurl, $linktext, array('rel' => 'nofollow'));
        $content .= html_writer::end_tag('div');

        return $content;
    }

    
    public final function tabtree($tabs, $selected = null, $inactive = null) {
        return $this->render(new tabtree($tabs, $selected, $inactive));
    }

    
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $str = '';
        $str .= html_writer::start_tag('div', array('class' => 'tabtree'));
        $str .= $this->render_tabobject($tabtree);
        $str .= html_writer::end_tag('div').
                html_writer::tag('div', ' ', array('class' => 'clearer'));
        return $str;
    }

    
    protected function render_tabobject(tabobject $tabobject) {
        $str = '';

                if ($tabobject instanceof tabtree) {
                    } else if ($tabobject->inactive || $tabobject->activated || ($tabobject->selected && !$tabobject->linkedwhenselected)) {
                        $str .= html_writer::tag('a', html_writer::span($tabobject->text), array('class' => 'nolink moodle-has-zindex'));
        } else {
                        if (!($tabobject->link instanceof moodle_url)) {
                                $str .= "<a href=\"$tabobject->link\" title=\"$tabobject->title\"><span>$tabobject->text</span></a>";
            } else {
                $str .= html_writer::link($tabobject->link, html_writer::span($tabobject->text), array('title' => $tabobject->title));
            }
        }

        if (empty($tabobject->subtree)) {
            if ($tabobject->selected) {
                $str .= html_writer::tag('div', '&nbsp;', array('class' => 'tabrow'. ($tabobject->level + 1). ' empty'));
            }
            return $str;
        }

                if ($tabobject->level == 0 || $tabobject->selected || $tabobject->activated) {
            $str .= html_writer::start_tag('ul', array('class' => 'tabrow'. $tabobject->level));
            $cnt = 0;
            foreach ($tabobject->subtree as $tab) {
                $liclass = '';
                if (!$cnt) {
                    $liclass .= ' first';
                }
                if ($cnt == count($tabobject->subtree) - 1) {
                    $liclass .= ' last';
                }
                if ((empty($tab->subtree)) && (!empty($tab->selected))) {
                    $liclass .= ' onerow';
                }

                if ($tab->selected) {
                    $liclass .= ' here selected';
                } else if ($tab->activated) {
                    $liclass .= ' here active';
                }

                                $str .= html_writer::tag('li', $this->render($tab), array('class' => trim($liclass)));
                $cnt++;
            }
            $str .= html_writer::end_tag('ul');
        }

        return $str;
    }

    
    public function blocks($region, $classes = array(), $tag = 'aside') {
        $displayregion = $this->page->apply_theme_region_manipulations($region);
        $classes = (array)$classes;
        $classes[] = 'block-region';
        $attributes = array(
            'id' => 'block-region-'.preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $displayregion),
            'class' => join(' ', $classes),
            'data-blockregion' => $displayregion,
            'data-droptarget' => '1'
        );
        if ($this->page->blocks->region_has_content($displayregion, $this)) {
            $content = $this->blocks_for_region($displayregion);
        } else {
            $content = '';
        }
        return html_writer::tag($tag, $content, $attributes);
    }

    
    public function custom_block_region($regionname) {
        if ($this->page->theme->get_block_render_method() === 'blocks') {
            return $this->blocks($regionname);
        } else {
            return $this->blocks_for_region($regionname);
        }
    }

    
    public function body_css_classes(array $additionalclasses = array()) {
                        $usedregions = array();
        foreach ($this->page->blocks->get_regions() as $region) {
            $additionalclasses[] = 'has-region-'.$region;
            if ($this->page->blocks->region_has_content($region, $this)) {
                $additionalclasses[] = 'used-region-'.$region;
                $usedregions[] = $region;
            } else {
                $additionalclasses[] = 'empty-region-'.$region;
            }
            if ($this->page->blocks->region_completely_docked($region, $this)) {
                $additionalclasses[] = 'docked-region-'.$region;
            }
        }
        if (!$usedregions) {
                        $additionalclasses[] = 'content-only';
        } else if (count($usedregions) === 1) {
                        $region = array_shift($usedregions);
            $additionalclasses[] = $region . '-only';
        }
        foreach ($this->page->layout_options as $option => $value) {
            if ($value) {
                $additionalclasses[] = 'layout-option-'.$option;
            }
        }
        $css = $this->page->bodyclasses .' '. join(' ', $additionalclasses);
        return $css;
    }

    
    public function body_id() {
        return $this->page->bodyid;
    }

    
    public function body_attributes($additionalclasses = array()) {
        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }
        return ' id="'. $this->body_id().'" class="'.$this->body_css_classes($additionalclasses).'"';
    }

    
    public function page_heading($tag = 'h1') {
        return html_writer::tag($tag, $this->page->heading);
    }

    
    public function page_heading_button() {
        return $this->page->button;
    }

    
    public function page_doc_link($text = null) {
        if ($text === null) {
            $text = get_string('moodledocslink');
        }
        $path = page_get_doc_link_path($this->page);
        if (!$path) {
            return '';
        }
        return $this->doc_link($path, $text);
    }

    
    public function page_heading_menu() {
        return $this->page->headingmenu;
    }

    
    public function page_title() {
        return $this->page->title;
    }

    
    public function favicon() {
        return $this->pix_url('favicon', 'theme');
    }

    
    public function render_preferences_groups(preferences_groups $renderable) {
        $html = '';
        $html .= html_writer::start_div('row-fluid');
        $html .= html_writer::start_tag('div', array('class' => 'span12 preferences-groups'));
        $i = 0;
        $open = false;
        foreach ($renderable->groups as $group) {
            if ($i == 0 || $i % 3 == 0) {
                if ($open) {
                    $html .= html_writer::end_tag('div');
                }
                $html .= html_writer::start_tag('div', array('class' => 'row-fluid'));
                $open = true;
            }
            $html .= $this->render($group);
            $i++;
        }

        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_div();
        return $html;
    }

    
    public function render_preferences_group(preferences_group $renderable) {
        $html = '';
        $html .= html_writer::start_tag('div', array('class' => 'span4 preferences-group'));
        $html .= $this->heading($renderable->title, 3);
        $html .= html_writer::start_tag('ul');
        foreach ($renderable->nodes as $node) {
            if ($node->has_children()) {
                debugging('Preferences nodes do not support children', DEBUG_DEVELOPER);
            }
            $html .= html_writer::tag('li', $this->render($node));
        }
        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('div');
        return $html;
    }

    
    public function context_header($headerinfo = null, $headinglevel = 1) {
        global $DB, $USER, $CFG;
        $context = $this->page->context;
                if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = null;
        }
        $imagedata = null;
        $subheader = null;
        $userbuttons = null;
                if (isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }
                        if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }
                        if (!isset($heading)) {
                $heading = fullname($user);
            }

            $imagedata = $this->user_picture($user, array('size' => 100));
                        if (!empty($CFG->messaging) && $USER->id != $user->id && has_capability('moodle/site:sendmessage', $context)) {
                $userbuttons = array(
                    'messages' => array(
                        'buttontype' => 'message',
                        'title' => get_string('message', 'message'),
                        'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                        'image' => 'message',
                        'linkattributes' => message_messenger_sendmessage_link_params($user),
                        'page' => $this->page
                    )
                );
                $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
            }
        }

        $contextheader = new context_header($heading, $headinglevel, $imagedata, $userbuttons);
        return $this->render_context_header($contextheader);
    }

     
    protected function render_context_header(context_header $contextheader) {

                $html = html_writer::start_div('page-context-header');

                if (isset($contextheader->imagedata)) {
                        $html .= html_writer::div($contextheader->imagedata, 'page-header-image');
        }

                if (!isset($contextheader->heading)) {
            $headings = $this->heading($this->page->heading, $contextheader->headinglevel);
        } else {
            $headings = $this->heading($contextheader->heading, $contextheader->headinglevel);
        }

        $html .= html_writer::tag('div', $headings, array('class' => 'page-header-headings'));

                if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('btn-group header-button-group');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                                        if ($button['buttontype'] === 'message') {
                        message_messenger_requirejs();
                    }
                    $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                        'class' => 'iconsmall',
                        'role' => 'presentation'
                    ));
                    $image .= html_writer::span($button['title'], 'header-button-title');
                } else {
                    $image = html_writer::empty_tag('img', array(
                        'src' => $button['formattedimage'],
                        'role' => 'presentation'
                    ));
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();

        return $html;
    }

    
    public function full_header() {
        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'clearfix'));
        $html .= $this->context_header();
        $html .= html_writer::start_div('clearfix', array('id' => 'page-navbar'));
        $html .= html_writer::tag('div', $this->navbar(), array('class' => 'breadcrumb-nav'));
        $html .= html_writer::div($this->page_heading_button(), 'breadcrumb-button');
        $html .= html_writer::end_div();
        $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::end_tag('header');
        return $html;
    }

    
    public function tag_list($tags, $label = null, $classes = '', $limit = 10, $pagecontext = null) {
        $list = new \core_tag\output\taglist($tags, $label, $classes, $limit, $pagecontext);
        return $this->render_from_template('core_tag/taglist', $list->export_for_template($this));
    }

    
    public function render_inplace_editable(\core\output\inplace_editable $element) {
        return $this->render_from_template('core/inplace_editable', $element->export_for_template($this));
    }
}


class core_renderer_cli extends core_renderer {

    
    public function header() {
        return $this->page->heading . "\n";
    }

    
    public function heading($text, $level = 2, $classes = 'main', $id = null) {
        $text .= "\n";
        switch ($level) {
            case 1:
                return '=>' . $text;
            case 2:
                return '-->' . $text;
            default:
                return $text;
        }
    }

    
    public function fatal_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = "") {
        global $CFG;

        $output = "!!! $message !!!\n";

        if ($CFG->debugdeveloper) {
            if (!empty($debuginfo)) {
                $output .= $this->notification($debuginfo, 'notifytiny');
            }
            if (!empty($backtrace)) {
                $output .= $this->notification('Stack trace: ' . format_backtrace($backtrace, true), 'notifytiny');
            }
        }

        return $output;
    }

    
    public function notification($message, $type = null) {
        $message = clean_text($message);
        if ($type === 'notifysuccess' || $type === 'success') {
            return "++ $message ++\n";
        }
        return "!! $message !!\n";
    }

    
    public function footer() {}

    
    public function render_notification(\core\output\notification $notification) {
        return $this->notification($notification->get_message(), $notification->get_message_type());
    }
}



class core_renderer_ajax extends core_renderer {

    
    public function fatal_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = "") {
        global $CFG;

        $this->page->set_context(null); 
        $e = new stdClass();
        $e->error      = $message;
        $e->errorcode  = $errorcode;
        $e->stacktrace = NULL;
        $e->debuginfo  = NULL;
        $e->reproductionlink = NULL;
        if (!empty($CFG->debug) and $CFG->debug >= DEBUG_DEVELOPER) {
            $link = (string) $link;
            if ($link) {
                $e->reproductionlink = $link;
            }
            if (!empty($debuginfo)) {
                $e->debuginfo = $debuginfo;
            }
            if (!empty($backtrace)) {
                $e->stacktrace = format_backtrace($backtrace, true);
            }
        }
        $this->header();
        return json_encode($e);
    }

    
    public function notification($message, $type = null) {}

    
    public function redirect_message($encodedurl, $message, $delay, $debugdisableredirect,
                                     $messagetype = \core\output\notification::NOTIFY_INFO) {}

    
    public function header() {
                if (!empty($_FILES)) {
            @header('Content-type: text/plain; charset=utf-8');
            if (!core_useragent::supports_json_contenttype()) {
                @header('X-Content-Type-Options: nosniff');
            }
        } else if (!core_useragent::supports_json_contenttype()) {
            @header('Content-type: text/plain; charset=utf-8');
            @header('X-Content-Type-Options: nosniff');
        } else {
            @header('Content-type: application/json; charset=utf-8');
        }

                @header('Cache-Control: no-store, no-cache, must-revalidate');
        @header('Cache-Control: post-check=0, pre-check=0', false);
        @header('Pragma: no-cache');
        @header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
        @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        @header('Accept-Ranges: none');
    }

    
    public function footer() {}

    
    public function heading($text, $level = 2, $classes = 'main', $id = null) {}
}



class core_media_renderer extends plugin_renderer_base {
    
    private $players;
    
    private $embeddablemarkers;

    
    public function __construct() {
        global $CFG;
        require_once($CFG->libdir . '/medialib.php');
    }

    
    protected function get_players() {
        global $CFG;

                if (!$this->players) {
                        $players = $this->get_players_raw();

                        foreach ($players as $key => $player) {
                if (!$player->is_enabled()) {
                    unset($players[$key]);
                }
            }

                        usort($players, array('core_media_player', 'compare_by_rank'));
            $this->players = $players;
        }
        return $this->players;
    }

    
    protected function get_players_raw() {
        return array(
            'vimeo' => new core_media_player_vimeo(),
            'youtube' => new core_media_player_youtube(),
            'youtube_playlist' => new core_media_player_youtube_playlist(),
            'html5video' => new core_media_player_html5video(),
            'html5audio' => new core_media_player_html5audio(),
            'mp3' => new core_media_player_mp3(),
            'flv' => new core_media_player_flv(),
            'wmp' => new core_media_player_wmp(),
            'qt' => new core_media_player_qt(),
            'rm' => new core_media_player_rm(),
            'swf' => new core_media_player_swf(),
            'link' => new core_media_player_link(),
        );
    }

    
    public function embed_url(moodle_url $url, $name = '', $width = 0, $height = 0,
            $options = array()) {

                        $rawurl = $url->out(false);
        if (preg_match('/[?#]d=([\d]{1,4}%?)x([\d]{1,4}%?)/', $rawurl, $matches)) {
            $width = $matches[1];
            $height = $matches[2];
            $url = new moodle_url(str_replace($matches[0], '', $rawurl));
        }

                return $this->embed_alternatives(array($url), $name, $width, $height, $options);
    }

    
    public function embed_alternatives($alternatives, $name = '', $width = 0, $height = 0,
            $options = array()) {

                $players = $this->get_players();

                        $out = core_media_player::PLACEHOLDER;

                foreach ($players as $player) {
                        if (!empty($options[core_media::OPTION_FALLBACK_TO_BLANK]) &&
                    $player->get_rank() === 0 && $out === core_media_player::PLACEHOLDER) {
                continue;
            }

            $supported = $player->list_supported_urls($alternatives, $options);
            if ($supported) {
                                $text = $player->embed($supported, $name, $width, $height, $options);

                                $out = str_replace(core_media_player::PLACEHOLDER, $text, $out);
            }
        }

                $out = str_replace(core_media_player::PLACEHOLDER, '', $out);
        if (!empty($options[core_media::OPTION_BLOCK]) && $out !== '') {
            $out = html_writer::tag('div', $out, array('class' => 'resourcecontent'));
        }
        return $out;
    }

    
    public function can_embed_url(moodle_url $url, $options = array()) {
        return $this->can_embed_urls(array($url), $options);
    }

    
    public function can_embed_urls(array $urls, $options = array()) {
                foreach ($this->get_players() as $player) {
                        if ($player->get_rank() <= 0) {
                break;
            }
                        if ($player->list_supported_urls($urls, $options)) {
                return true;
            }
        }
        return false;
    }

    
    public function get_embeddable_markers() {
        if (empty($this->embeddablemarkers)) {
            $markers = '';
            foreach ($this->get_players() as $player) {
                foreach ($player->get_embeddable_markers() as $marker) {
                    if ($markers !== '') {
                        $markers .= '|';
                    }
                    $markers .= preg_quote($marker);
                }
            }
            $this->embeddablemarkers = $markers;
        }
        return $this->embeddablemarkers;
    }
}


class core_renderer_maintenance extends core_renderer {

    
    public function __construct(moodle_page $page, $target) {
        if ($target !== RENDERER_TARGET_MAINTENANCE || $page->pagelayout !== 'maintenance') {
            throw new coding_exception('Invalid request for the maintenance renderer.');
        }
        parent::__construct($page, $target);
    }

    
    public function block(block_contents $bc, $region) {
                        return '';
    }

    
    public function blocks($region, $classes = array(), $tag = 'aside') {
                        return '';
    }

    
    public function blocks_for_region($region) {
                        return '';
    }

    
    public function course_content_header($onlyifnotcalledbefore = false) {
                        return '';
    }

    
    public function course_content_footer($onlyifnotcalledbefore = false) {
                        return '';
    }

    
    public function course_header() {
                        return '';
    }

    
    public function course_footer() {
                        return '';
    }

    
    public function custom_menu($custommenuitems = '') {
                        return '';
    }

    
    public function file_picker($options) {
                        return '';
    }

    
    public function htmllize_file_tree($dir) {
                                return '';

    }

    
    public function init_block_hider_js(block_contents $bc) {
                    }

    
    public function lang_menu() {
                        return '';
    }

    
    public function login_info($withlinks = null) {
                        return '';
    }

    
    public function user_picture(stdClass $user, array $options = null) {
                        return '';
    }
}
