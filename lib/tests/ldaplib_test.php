<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/ldaplib.php');

class core_ldaplib_testcase extends advanced_testcase {

    public function test_ldap_addslashes() {
                
        $tests = array(
            array (
                'test' => 'Simplest',
                'expected' => 'Simplest',
            ),
            array (
                'test' => 'Simple case',
                'expected' => 'Simple\\20case',
            ),
            array (
                'test' => 'Medium ‒ case',
                'expected' => 'Medium\\20‒\\20case',
            ),
            array (
                'test' => '#Harder+case#',
                'expected' => '\\23Harder\\2bcase\\23',
            ),
            array (
                'test' => ' Harder (and); harder case ',
                'expected' => '\\20Harder\\20(and)\\3b\\20harder\\20case\\20',
            ),
            array (
                'test' => 'Really \\0 (hard) case!\\',
                'expected' => 'Really\\20\\5c0\\20(hard)\\20case!\\5c',
            ),
            array (
                'test' => 'James "Jim" = Smith, III',
                'expected' => 'James\\20\\22Jim\22\\20\\3d\\20Smith\\2c\\20III',
            ),
            array (
                'test' => '  <jsmith@example.com> ',
                'expected' => '\\20\\20\\3cjsmith@example.com\\3e\\20',
            ),
        );


        foreach ($tests as $test) {
            $this->assertSame($test['expected'], ldap_addslashes($test['test']));
        }
    }

    public function test_ldap_stripslashes() {
                
                                                        
        $tests = array(
            array (
                'test' => 'Simplest',
                'expected' => 'Simplest',
            ),
            array (
                'test' => 'Simple\\20case',
                'expected' => 'Simple case',
            ),
            array (
                'test' => 'Simple\\ case',
                'expected' => 'Simple case',
            ),
            array (
                'test' => 'Simple\\ \\63\\61\\73\\65',
                'expected' => 'Simple case',
            ),
            array (
                'test' => 'Medium\\ ‒\\ case',
                'expected' => 'Medium ‒ case',
            ),
            array (
                'test' => 'Medium\\20‒\\20case',
                'expected' => 'Medium ‒ case',
            ),
            array (
                'test' => 'Medium\\20\\E2\\80\\92\\20case',
                'expected' => 'Medium ‒ case',
            ),
            array (
                'test' => '\\23Harder\\2bcase\\23',
                'expected' => '#Harder+case#',
            ),
            array (
                'test' => '\\#Harder\\+case\\#',
                'expected' => '#Harder+case#',
            ),
            array (
                'test' => '\\20Harder\\20(and)\\3b\\20harder\\20case\\20',
                'expected' => ' Harder (and); harder case ',
            ),
            array (
                'test' => '\\ Harder\\ (and)\\;\\ harder\\ case\\ ',
                'expected' => ' Harder (and); harder case ',
            ),
            array (
                'test' => 'Really\\20\\5c0\\20(hard)\\20case!\\5c',
                'expected' => 'Really \\0 (hard) case!\\',
            ),
            array (
                'test' => 'Really\\ \\\\0\\ (hard)\\ case!\\\\',
                'expected' => 'Really \\0 (hard) case!\\',
            ),
            array (
                'test' => 'James\\20\\22Jim\\22\\20\\3d\\20Smith\\2c\\20III',
                'expected' => 'James "Jim" = Smith, III',
            ),
            array (
                'test' => 'James\\ \\"Jim\\" \\= Smith\\, III',
                'expected' => 'James "Jim" = Smith, III',
            ),
            array (
                'test' => '\\20\\20\\3cjsmith@example.com\\3e\\20',
                'expected' => '  <jsmith@example.com> ',
            ),
            array (
                'test' => '\\ \\<jsmith@example.com\\>\\ ',
                'expected' => ' <jsmith@example.com> ',
            ),
            array (
                'test' => 'Lu\\C4\\8Di\\C4\\87',
                'expected' => 'Lučić',
            ),
        );

        foreach ($tests as $test) {
            $this->assertSame($test['expected'], ldap_stripslashes($test['test']));
        }
    }

    
    public function test_ldap_normalise_objectclass($args, $expected) {
        $this->assertEquals($expected, call_user_func_array('ldap_normalise_objectclass', $args));
    }

    
    public function ldap_normalise_objectclass_provider() {
        return array(
            'Empty value' => array(
                array(null),
                '(objectClass=*)',
            ),
            'Empty value with different default' => array(
                array(null, 'lion'),
                '(objectClass=lion)',
            ),
            'Supplied unwrapped objectClass' => array(
                array('objectClass=tiger'),
                '(objectClass=tiger)',
            ),
            'Supplied string value' => array(
                array('leopard'),
                '(objectClass=leopard)',
            ),
            'Supplied complex' => array(
                array('(&(objectClass=cheetah)(enabledMoodleUser=1))'),
                '(&(objectClass=cheetah)(enabledMoodleUser=1))',
            ),
        );
    }
}
