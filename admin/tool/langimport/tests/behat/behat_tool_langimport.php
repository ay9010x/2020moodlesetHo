<?php




require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Moodle\BehatExtension\Exception\SkippedException;


class behat_tool_langimport extends behat_base {

    
    public function remote_langimport_tests_are_enabled() {
        if (!defined('TOOL_LANGIMPORT_REMOTE_TESTS')) {
            throw new SkippedException('To run the remote langimport tests you must '.
                'define TOOL_LANGIMPORT_REMOTE_TESTS in config.php');
        }
    }

    
    public function outdated_langpack_is_installed($langcode) {
        global $CFG;
        require_once($CFG->libdir.'/componentlib.class.php');

                $dir = make_upload_directory('lang');
        $installer = new lang_installer($langcode);
        $result = $installer->run();

        if ($result[$langcode] !== lang_installer::RESULT_INSTALLED) {
            throw new coding_exception("Failed to install langpack '$langcode'");
        }

        $path = "$dir/$langcode/$langcode.md5";

        if (!file_exists($path)) {
            throw new coding_exception("Failed to find '$langcode' checksum");
        }
        file_put_contents($path, '000000');
    }
}
