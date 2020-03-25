<?php




defined('MOODLE_INTERNAL') || die();


class tool_installaddon_installer {

    
    protected $installfromzipform = null;

    
    public static function instance() {
        return new static();
    }

    
    public function index_url(array $params = null) {
        return new moodle_url('/admin/tool/installaddon/index.php', $params);
    }

    
    public function get_addons_repository_url() {
        global $CFG;

        if (!empty($CFG->config_php_settings['alternativeaddonsrepositoryurl'])) {
            $url = $CFG->config_php_settings['alternativeaddonsrepositoryurl'];
        } else {
            $url = 'https://moodle.org/plugins/get.php';
        }

        if (!$this->should_send_site_info()) {
            return new moodle_url($url);
        }

                $site = array(
            'fullname' => $this->get_site_fullname(),
            'url' => $this->get_site_url(),
            'majorversion' => $this->get_site_major_version(),
        );

        $site = $this->encode_site_information($site);

        return new moodle_url($url, array('site' => $site));
    }

    
    public function get_installfromzip_form() {
        if (!is_null($this->installfromzipform)) {
            return $this->installfromzipform;
        }

        $action = $this->index_url();
        $customdata = array('installer' => $this);

        $this->installfromzipform = new tool_installaddon_installfromzip_form($action, $customdata);

        return $this->installfromzipform;
    }

    
    public function make_installfromzip_storage() {
        return make_unique_writable_directory(make_temp_directory('tool_installaddon'));
    }

    
    public function get_plugin_types_menu() {
        global $CFG;

        $pluginman = core_plugin_manager::instance();

        $menu = array('' => get_string('choosedots'));
        foreach (array_keys($pluginman->get_plugin_types()) as $plugintype) {
            $menu[$plugintype] = $pluginman->plugintype_name($plugintype).' ('.$plugintype.')';
        }

        return $menu;
    }

    
    public function handle_remote_request(tool_installaddon_renderer $output, $request) {

        if (is_null($request)) {
            return;
        }

        $data = $this->decode_remote_request($request);

        if ($data === false) {
            echo $output->remote_request_invalid_page($this->index_url());
            exit();
        }

        list($plugintype, $pluginname) = core_component::normalize_component($data->component);
        $pluginman = core_plugin_manager::instance();

        $plugintypepath = $pluginman->get_plugintype_root($plugintype);

        if (file_exists($plugintypepath.'/'.$pluginname)) {
            echo $output->remote_request_alreadyinstalled_page($data, $this->index_url());
            exit();
        }

        if (!$pluginman->is_plugintype_writable($plugintype)) {
            $continueurl = $this->index_url(array('installaddonrequest' => $request));
            echo $output->remote_request_permcheck_page($data, $plugintypepath, $continueurl, $this->index_url());
            exit();
        }

        if (!$pluginman->is_remote_plugin_installable($data->component, $data->version, $reason)) {
            $data->reason = $reason;
            echo $output->remote_request_non_installable_page($data, $this->index_url());
            exit();
        }

        $continueurl = $this->index_url(array(
            'installremote' => $data->component,
            'installremoteversion' => $data->version
        ));

        echo $output->remote_request_confirm_page($data, $continueurl, $this->index_url());
        exit();
    }

    
    public function detect_plugin_component($zipfilepath) {

        $workdir = make_request_directory();
        $versionphp = $this->extract_versionphp_file($zipfilepath, $workdir);

        if (empty($versionphp)) {
            return false;
        }

        return $this->detect_plugin_component_from_versionphp(file_get_contents($workdir.'/'.$versionphp));
    }

    
    
    protected function __construct() {
    }

    
    protected function get_site_fullname() {
        global $SITE;

        return strip_tags($SITE->fullname);
    }

    
    protected function get_site_url() {
        global $CFG;

        return $CFG->wwwroot;
    }

    
    protected function get_site_major_version() {
        return moodle_major_version();
    }

    
    protected function encode_site_information(array $info) {
        return base64_encode(json_encode($info));
    }

    
    protected function should_send_site_info() {
        return true;
    }

    
    protected function decode_remote_request($request) {

        $data = base64_decode($request, true);

        if ($data === false) {
            return false;
        }

        $data = json_decode($data);

        if (is_null($data)) {
            return false;
        }

        if (!isset($data->name) or !isset($data->component) or !isset($data->version)) {
            return false;
        }

        $data->name = s(strip_tags($data->name));

        if ($data->component !== clean_param($data->component, PARAM_COMPONENT)) {
            return false;
        }

        list($plugintype, $pluginname) = core_component::normalize_component($data->component);

        if ($plugintype === 'core') {
            return false;
        }

        if ($data->component !== $plugintype.'_'.$pluginname) {
            return false;
        }

        if (!core_component::is_valid_plugin_name($plugintype, $pluginname)) {
            return false;
        }

        $plugintypes = core_component::get_plugin_types();
        if (!isset($plugintypes[$plugintype])) {
            return false;
        }

                if (!preg_match('/^[0-9]+$/', $data->version)) {
            return false;
        }

        return $data;
    }

    
    protected function extract_versionphp_file($zipfilepath, $targetdir) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $fp = get_file_packer('application/zip');
        $files = $fp->list_files($zipfilepath);

        if (empty($files)) {
            return false;
        }

        $rootdirname = null;
        $found = null;

        foreach ($files as $file) {
                                    $pathnameitems = explode('/', $file->pathname);

            if (empty($pathnameitems)) {
                return false;
            }

                                    if ($rootdirname === null) {
                $rootdirname = $pathnameitems[0];
            }

                                    if ($rootdirname !== $pathnameitems[0]) {
                return false;
            }

                        if ($pathnameitems[1] === 'version.php' and !$file->is_directory and $file->size > 0) {
                $found = $file->pathname;
            }
        }

        if (empty($found)) {
            return false;
        }

        $extracted = $fp->extract_to_pathname($zipfilepath, $targetdir, array($found));

        if (empty($extracted)) {
            return false;
        }

                return array_keys($extracted)[0];
    }

    
    protected function detect_plugin_component_from_versionphp($code) {

        $result = preg_match_all('#^\s*\$plugin\->component\s*=\s*([\'"])(.+?_.+?)\1\s*;#m', $code, $matches);

                if ($result === 1 and !empty($matches[2][0])) {
            return $matches[2][0];
        }

        return false;
    }
}
