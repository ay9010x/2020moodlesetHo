<?php



defined('MOODLE_INTERNAL') || die();


class core_regex_testcase extends advanced_testcase {
    public function test_whitespace_replacement_with_u() {
        $unicode = "Теорія і практика використання системи управління навчанням Moo
dleКиївський національний університет будівництва і архітектури, 21-22 тра
вня 2015 р.http://2015.moodlemoot.in.ua/";

        $whitespaced = preg_replace('/\s+/u', ' ', $unicode);
        $this->assertSame(str_replace("\n", ' ', $unicode), $whitespaced);
    }
}


