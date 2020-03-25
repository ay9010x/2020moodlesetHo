<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/componentlib.class.php');

class core_componentlib_testcase extends advanced_testcase {

    public function test_component_installer() {
        global $CFG;

        $url = $this->getExternalTestFileUrl('');
        $ci = new component_installer($url, '', 'downloadtests.zip');
        $this->assertTrue($ci->check_requisites());

        $destpath = $CFG->dataroot.'/downloadtests';

                @unlink($destpath.'/'.'downloadtests.md5');
        @unlink($destpath.'/'.'test.html');
        @unlink($destpath.'/'.'test.jpg');
        @rmdir($destpath);

        $this->assertSame(COMPONENT_NEEDUPDATE, $ci->need_upgrade());

        $status = $ci->install();
        $this->assertSame(COMPONENT_INSTALLED, $status);
        $this->assertSame('9e94f74b3efb1ff6cf075dc6b2abf15c', $ci->get_component_md5());

                $this->assertSame(COMPONENT_UPTODATE, $ci->need_upgrade());
        $status = $ci->install();
        $this->assertSame(COMPONENT_UPTODATE, $status);

                $this->assertSame('2af180e813dc3f446a9bb7b6af87ce24', md5_file($destpath.'/'.'test.jpg'));
        $this->assertSame('47250a973d1b88d9445f94db4ef2c97a', md5_file($destpath.'/'.'test.html'));
    }

    
    public function test_lang_installer() {

                $installer = new testable_lang_installer();
        $this->assertFalse($installer->protected_is_queued());
        $installer->protected_add_to_queue('cs');
        $installer->protected_add_to_queue(array('cs', 'sk'));
        $this->assertTrue($installer->protected_is_queued());
        $this->assertTrue($installer->protected_is_queued('cs'));
        $this->assertTrue($installer->protected_is_queued('sk'));
        $this->assertFalse($installer->protected_is_queued('de_kids'));
        $installer->set_queue('de_kids');
        $this->assertFalse($installer->protected_is_queued('cs'));
        $this->assertFalse($installer->protected_is_queued('sk'));
        $this->assertFalse($installer->protected_is_queued('de'));
        $this->assertFalse($installer->protected_is_queued('de_du'));
        $this->assertTrue($installer->protected_is_queued('de_kids'));
        $installer->set_queue(array('cs', 'de_kids'));
        $this->assertTrue($installer->protected_is_queued('cs'));
        $this->assertFalse($installer->protected_is_queued('sk'));
        $this->assertFalse($installer->protected_is_queued('de'));
        $this->assertFalse($installer->protected_is_queued('de_du'));
        $this->assertTrue($installer->protected_is_queued('de_kids'));
        $installer->set_queue(array());
        $this->assertFalse($installer->protected_is_queued());
        unset($installer);

                $installer = new testable_lang_installer(array('cs', 'de_kids', 'xx'));
        $result = $installer->run();
        $this->assertSame($result['cs'], lang_installer::RESULT_UPTODATE);
        $this->assertSame($result['de_kids'], lang_installer::RESULT_INSTALLED);
        $this->assertSame($result['xx'], lang_installer::RESULT_DOWNLOADERROR);

                $this->assertSame($result['de_du'], lang_installer::RESULT_INSTALLED);
        $this->assertSame($result['de'], lang_installer::RESULT_UPTODATE);

                $installer = new testable_lang_installer(array('yy'));
        try {
            $installer->run();
            $this->fail('lang_installer_exception exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('lang_installer_exception', $e);
        }
    }
}



class testable_lang_installer extends lang_installer {

    
    public function protected_is_queued($langcode = '') {
        return $this->is_queued($langcode);
    }

    
    public function protected_add_to_queue($langcodes) {
        return $this->add_to_queue($langcodes);
    }

    
    protected function install_language_pack($langcode) {

        switch ($langcode) {
            case 'de_du':
            case 'de_kids':
                return self::RESULT_INSTALLED;

            case 'cs':
            case 'de':
                return self::RESULT_UPTODATE;

            case 'xx':
                return self::RESULT_DOWNLOADERROR;

            default:
                throw new lang_installer_exception('testing-unknown-exception', $langcode);
        }
    }

    
    protected function get_parent_language($langcode) {

        switch ($langcode) {
            case 'de_kids':
                return 'de_du';
            case 'de_du':
                return 'de';
            default:
                return '';
        }
    }
}
