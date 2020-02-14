<?php




defined('MOODLE_INTERNAL') || die();


class tool_installaddon_renderer extends plugin_renderer_base {

    
    protected $installer = null;

    
    public function set_installer_instance(tool_installaddon_installer $installer) {
        if (is_null($this->installer)) {
            $this->installer = $installer;
        } else {
            throw new coding_exception('Attempting to reset the installer instance.');
        }
    }

    
    public function index_page() {

        if (is_null($this->installer)) {
            throw new coding_exception('Installer instance has not been set.');
        }

        $permcheckurl = new moodle_url('/admin/tool/installaddon/permcheck.php');
        $this->page->requires->yui_module('moodle-tool_installaddon-permcheck', 'M.tool_installaddon.permcheck.init',
            array(array('permcheckurl' => $permcheckurl->out())));
        $this->page->requires->strings_for_js(
            array('permcheckprogress', 'permcheckresultno', 'permcheckresultyes', 'permcheckerror', 'permcheckrepeat'),
            'tool_installaddon');

        $out = $this->output->header();
        $out .= $this->index_page_heading();
        $out .= $this->index_page_repository();
        $out .= $this->index_page_upload();
        $out .= $this->output->footer();

        return $out;
    }

    
    public function zip_not_valid_plugin_package_page(moodle_url $continueurl) {

        $out = $this->output->header();
        $out .= $this->output->heading(get_string('installfromzip', 'tool_installaddon'));
        $out .= $this->output->box(get_string('installfromzipinvalid', 'tool_installaddon'), 'generalbox', 'notice');
        $out .= $this->output->continue_button($continueurl, 'get');
        $out .= $this->output->footer();

        return $out;
    }

    
    public function remote_request_invalid_page(moodle_url $continueurl) {

        $out = $this->output->header();
        $out .= $this->output->heading(get_string('installfromrepo', 'tool_installaddon'));
        $out .= $this->output->box(get_string('remoterequestinvalid', 'tool_installaddon'), 'generalbox', 'notice');
        $out .= $this->output->continue_button($continueurl, 'get');
        $out .= $this->output->footer();

        return $out;
    }

    
    public function remote_request_alreadyinstalled_page(stdClass $data, moodle_url $continueurl) {

        $out = $this->output->header();
        $out .= $this->output->heading(get_string('installfromrepo', 'tool_installaddon'));
        $out .= $this->output->box(get_string('remoterequestalreadyinstalled', 'tool_installaddon', $data), 'generalbox', 'notice');
        $out .= $this->output->continue_button($continueurl, 'get');
        $out .= $this->output->footer();

        return $out;
    }

    
    public function remote_request_confirm_page(stdClass $data, moodle_url $continueurl, moodle_url $cancelurl) {

        $out = $this->output->header();
        $out .= $this->output->heading(get_string('installfromrepo', 'tool_installaddon'));
        $out .= $this->output->confirm(get_string('remoterequestconfirm', 'tool_installaddon', $data), $continueurl, $cancelurl);
        $out .= $this->output->footer();

        return $out;
    }

    
    public function remote_request_permcheck_page(stdClass $data, $plugintypepath, moodle_url $continueurl, moodle_url $cancelurl) {

        $data->typepath = $plugintypepath;

        $out = $this->output->header();
        $out .= $this->output->heading(get_string('installfromrepo', 'tool_installaddon'));
        $out .= $this->output->confirm(get_string('remoterequestpermcheck', 'tool_installaddon', $data), $continueurl, $cancelurl);
        $out .= $this->output->footer();

        return $out;
    }

    
    public function remote_request_non_installable_page(stdClass $data, moodle_url $continueurl) {

        $out = $this->output->header();
        $out .= $this->output->heading(get_string('installfromrepo', 'tool_installaddon'));
        $out .= $this->output->box(get_string('remoterequestnoninstallable', 'tool_installaddon', $data), 'generalbox', 'notice');
        $out .= $this->output->continue_button($continueurl, 'get');
        $out .= $this->output->footer();

        return $out;
    }

    
    
    protected function index_page_heading() {
        return $this->output->heading(get_string('pluginname', 'tool_installaddon'));
    }

    
    protected function index_page_repository() {

        $url = $this->installer->get_addons_repository_url();

        $out = $this->box(
            $this->output->single_button($url, get_string('installfromrepo', 'tool_installaddon'), 'get').
            $this->output->help_icon('installfromrepo', 'tool_installaddon'),
            'generalbox', 'installfromrepobox'
        );

        return $out;
    }

    
    protected function index_page_upload() {

        $form = $this->installer->get_installfromzip_form();

        ob_start();
        $form->display();
        $out = ob_get_clean();

        $out = $this->box($out, 'generalbox', 'installfromzipbox');

        return $out;
    }
}
