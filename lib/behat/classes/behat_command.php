<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');


class behat_command {

    
    const DOCS_URL = 'http://docs.moodle.org/dev/Acceptance_testing';

    
    public static function get_behat_dir($runprocess = 0) {
        global $CFG;

                if (!isset($CFG->behat_dataroot)) {
            return "";
        }

        if (empty($runprocess)) {
            $behatdir = $CFG->behat_dataroot . '/behat';
        } else if (isset($CFG->behat_parallel_run[$runprocess - 1]['behat_dataroot'])) {
            $behatdir = $CFG->behat_parallel_run[$runprocess - 1]['behat_dataroot'] . '/behat';;
        } else {
            $behatdir = $CFG->behat_dataroot . $runprocess . '/behat';
        }

        if (!is_dir($behatdir)) {
            if (!mkdir($behatdir, $CFG->directorypermissions, true)) {
                behat_error(BEHAT_EXITCODE_PERMISSIONS, 'Directory ' . $behatdir . ' can not be created');
            }
        }

        if (!is_writable($behatdir)) {
            behat_error(BEHAT_EXITCODE_PERMISSIONS, 'Directory ' . $behatdir . ' is not writable');
        }

        return $behatdir;
    }

    
    public final static function get_behat_command($custombyterm = false, $parallerun = false, $absolutepath = false) {

        $separator = DIRECTORY_SEPARATOR;
        $exec = 'behat';

                if ($custombyterm && testing_is_cygwin()) {
            $separator = '/';

                        if (!testing_is_mingw()) {
                $exec = 'behat.bat';
            }
        }

                if ($absolutepath) {
            $pathprefix = testing_cli_argument_path('/') . $separator;
        } else {
            $pathprefix = '';
        }

        if (!$parallerun) {
            $command = $pathprefix . 'vendor' . $separator . 'bin' . $separator . $exec;
        } else {
            $command = 'php ' . $pathprefix . 'admin' . $separator . 'tool' . $separator . 'behat' . $separator . 'cli'
                . $separator . 'run.php';
        }
        return $command;
    }

    
    public final static function run($options = '') {
        global $CFG;

        $currentcwd = getcwd();
        chdir($CFG->dirroot);
        exec(self::get_behat_command() . ' ' . $options, $output, $code);
        chdir($currentcwd);

        return array($output, $code);
    }

    
    public static function behat_setup_problem() {
        global $CFG;

                if (!self::are_behat_dependencies_installed()) {

                        self::output_msg(get_string('errorcomposer', 'tool_behat'));
            return TESTING_EXITCODE_COMPOSER;
        }

                list($output, $code) = self::run(' --help');

        if ($code != 0) {

                        self::output_msg(get_string('errorbehatcommand', 'tool_behat', self::get_behat_command()));
            return TESTING_EXITCODE_COMPOSER;
        }

                if (empty($CFG->behat_dataroot) || empty($CFG->behat_prefix) || empty($CFG->behat_wwwroot)) {
            self::output_msg(get_string('errorsetconfig', 'tool_behat'));
            return BEHAT_EXITCODE_CONFIG;

        }

                                        if (!defined('BEHAT_SITE_RUNNING') &&
                ($CFG->behat_prefix == $CFG->prefix ||
                $CFG->behat_dataroot == $CFG->dataroot ||
                $CFG->behat_wwwroot == $CFG->wwwroot ||
                (!empty($CFG->phpunit_prefix) && $CFG->phpunit_prefix == $CFG->behat_prefix) ||
                (!empty($CFG->phpunit_dataroot) && $CFG->phpunit_dataroot == $CFG->behat_dataroot)
                )) {
            self::output_msg(get_string('erroruniqueconfig', 'tool_behat'));
            return BEHAT_EXITCODE_CONFIG;
        }

                if (!empty($CFG->behat_dataroot)) {
            $CFG->behat_dataroot = realpath($CFG->behat_dataroot);
        }
        if (empty($CFG->behat_dataroot) || !is_dir($CFG->behat_dataroot) || !is_writable($CFG->behat_dataroot)) {
            self::output_msg(get_string('errordataroot', 'tool_behat'));
            return BEHAT_EXITCODE_CONFIG;
        }

        return 0;
    }

    
    public static function are_behat_dependencies_installed() {
        if (!is_dir(__DIR__ . '/../../../vendor/behat')) {
            return false;
        }
        return true;
    }

    
    protected static function output_msg($msg) {
        global $CFG, $PAGE;

                if (!CLI_SCRIPT) {

            $renderer = $PAGE->get_renderer('tool_behat');
            echo $renderer->render_error($msg);

                        exit(1);

        } else {

                        $clibehaterrorstr = "Ensure you set \$CFG->behat_* vars in config.php " .
                "and you ran admin/tool/behat/cli/init.php.\n" .
                "More info in " . self::DOCS_URL . "#Installation\n\n";

            echo 'Error: ' . $msg . "\n\n" . $clibehaterrorstr;
        }
    }

}
