<?php


defined('MOODLE_INTERNAL') || die();


class atto_equation_upgradelib_testcase extends advanced_testcase {
    
    const SETTING_PLUGIN = 'atto_equation';

    
    const SETTING_NAME = 'librarygroup4';

    
    public function setUp() {
        $this->resetAfterTest();
    }

    
    public function test_update_librarygroup4_update() {
        global $CFG;
        require_once($CFG->libdir . '/editor/atto/plugins/equation/db/upgradelib.php');

        $originaldefaults = [
            '\sum{a,b}',
            '\int_{a}^{b}{c}',
            '\iint_{a}^{b}{c}',
            '\iiint_{a}^{b}{c}',
            '\oint{a}',
            '(a)',
            '[a]',
            '\lbrace{a}\rbrace',
            '\left| \begin{matrix} a_1 & a_2 \\ a_3 & a_4 \end{matrix} \right|',
        ];

        $newconfig = '
\sum{a,b}
\sqrt[a]{b+c}
\int_{a}^{b}{c}
\iint_{a}^{b}{c}
\iiint_{a}^{b}{c}
\oint{a}
(a)
[a]
\lbrace{a}\rbrace
\left| \begin{matrix} a_1 & a_2 \\ a_3 & a_4 \end{matrix} \right|
\frac{a}{b+c}
\vec{a}
\binom {a} {b}
{a \brack b}
{a \brace b}
';

                $originaldefaultswindows = "\r\n" . implode("\r\n", $originaldefaults) . "\r\n";
        set_config(self::SETTING_NAME, $originaldefaultswindows, self::SETTING_PLUGIN);
        atto_equation_update_librarygroup4_setting();

        $this->assertEquals($newconfig, get_config(self::SETTING_PLUGIN, self::SETTING_NAME));

                $originaldefaultslinux = "\n" . implode("\n", $originaldefaults) . "\n";
        set_config(self::SETTING_NAME, $originaldefaultslinux, self::SETTING_PLUGIN);
        atto_equation_update_librarygroup4_setting();

        $this->assertEquals($newconfig, get_config(self::SETTING_PLUGIN, self::SETTING_NAME));

                $alteredconfig = array_slice($originaldefaults, 0, -1);

                $alteredconfigwindows = "\r\n" . implode("\r\n", $alteredconfig) . "\r\n";
        set_config(self::SETTING_NAME, $alteredconfigwindows, self::SETTING_PLUGIN);
        atto_equation_update_librarygroup4_setting();

        $this->assertEquals($alteredconfigwindows, get_config(self::SETTING_PLUGIN, self::SETTING_NAME));

                $alteredconfiglinux = "\n" . implode("\n", $alteredconfig) . "\n";
        set_config(self::SETTING_NAME, $alteredconfiglinux, self::SETTING_PLUGIN);
        atto_equation_update_librarygroup4_setting();

        $this->assertEquals($alteredconfiglinux, get_config(self::SETTING_PLUGIN, self::SETTING_NAME));

                unset_config(self::SETTING_NAME, self::SETTING_PLUGIN);
        atto_equation_update_librarygroup4_setting();

        $this->assertFalse(get_config(self::SETTING_PLUGIN, self::SETTING_NAME));
    }
}