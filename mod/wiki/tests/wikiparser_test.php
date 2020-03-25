<?php



defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/mod/wiki/parser/parser.php');


class mod_wiki_wikiparser_test extends basic_testcase {

    function testCreoleMarkup() {
        $this->assertTestFiles('creole');
    }

    function testNwikiMarkup() {
        $this->assertTestFiles('nwiki');
    }

    function testHtmlMarkup() {
        $this->assertTestFiles('html');
    }

    private function assertTestFile($num, $markup) {
        if(!file_exists(__DIR__."/fixtures/input/$markup/$num") || !file_exists(__DIR__."/fixtures/output/$markup/$num")) {
            return false;
        }
        $input = file_get_contents(__DIR__."/fixtures/input/$markup/$num");
        $output = file_get_contents(__DIR__."/fixtures/output/$markup/$num");

        $result = wiki_parser_proxy::parse($input, $markup, array('pretty_print' => true));

                $result['parsed_text'] = preg_replace('~[\r\n]~', '', $result['parsed_text']);
        $output                = preg_replace('~[\r\n]~', '', $output);

        $this->assertEquals($output, $result['parsed_text'], 'Failed asserting that two strings are equal. Markup = '.$markup.", num = $num");
        return true;
    }

    private function assertTestFiles($markup) {
        $i = 1;
        while($this->assertTestFile($i, $markup)) {
            $i++;
        }
    }

    
    public function test_special_headings() {

        
                $input = '<h1>Code &amp; Test</h1>';
        $output = '<h3><a name="toc-1"></a>Code &amp; Test <a href="edit.php?pageid=&amp;section=Code+%26amp%3B+Test" '.
            'class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Code &amp; Test <a href="edit.php?pageid=&amp;section=Code+%26amp%3B+'.
            'Test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'html', 'Code &amp; Test');
        $actual = wiki_parser_proxy::parse($input, 'html');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '<h1>Another áéíóúç€ test</h1>';
        $output = '<h3><a name="toc-1"></a>Another áéíóúç€ test <a href="edit.php?pageid=&amp;section=Another+%C'.
            '3%A1%C3%A9%C3%AD%C3%B3%C3%BA%C3%A7%E2%82%AC+test" class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Another áéíóúç€ test <a href="edit.php?pageid=&amp;section=Another+%C'.
            '3%A1%C3%A9%C3%AD%C3%B3%C3%BA%C3%A7%E2%82%AC+test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'html', 'Another áéíóúç€ test');
        $actual = wiki_parser_proxy::parse($input, 'html');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '<h1>Another http://moodle.org test</h1>';
        $output = '<h3><a name="toc-1"></a>Another <a href="http://moodle.org">http://moodle.org</a> test <a href="edit.php'.
            '?pageid=&amp;section=Another+http%3A%2F%2Fmoodle.org+test" class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Another http://moodle.org test <a href="edit.php?pageid=&amp;section='.
            'Another+http%3A%2F%2Fmoodle.org+test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'html', 'Another http://moodle.org test');
        $actual = wiki_parser_proxy::parse($input, 'html', array(
            'link_callback' => '/mod/wiki/locallib.php:wiki_parser_link'
        ));
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '<h1>[[Heading 1]]</h1><h2>[[Heading A]]</h2><h2>Heading D</h2>';
        $regexpoutput = '!<h3><a name="toc-1"></a>' .
            '<a class="wiki_newentry" href.*mod/wiki/create\.php\?.*title=Heading\+1.*action=new.*>Heading 1<.*' .
            '<h4><a name="toc-2"></a>' .
            '<a class="wiki_newentry" href.*mod/wiki/create\.php\?.*title=Heading\+A.*action=new.*>Heading A<.*' .
            '<h4><a name="toc-3"></a>' .
            'Heading D!ms';
        $regexptoc = '!<a href="#toc-1">Heading 1.*<a href="#toc-2">Heading A</a>.*<a href="#toc-3">Heading D</a>!ms';
        $section = wiki_parser_proxy::get_section($input, 'html', 'Another [[wikilinked]] test');
        $actual = wiki_parser_proxy::parse($input, 'html', array(
            'link_callback' => '/mod/wiki/locallib.php:wiki_parser_link',
            'link_callback_args' => array('swid' => 1)
        ));
        $this->assertRegExp($regexpoutput, $actual['parsed_text']);
        $this->assertRegExp($regexptoc, $actual['toc']);

                
                $input = '= Code & Test =';
        $output = '<h3><a name="toc-1"></a>Code &amp; Test <a href="edit.php?pageid=&amp;section=Code+%26amp%3B+Test" '.
            'class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Code &amp; Test <a href="edit.php?pageid=&amp;section=Code+%26amp%3B+'.
            'Test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'creole', 'Code &amp; Test');
        $actual = wiki_parser_proxy::parse($input, 'creole');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '= Another áéíóúç€ test =';
        $output = '<h3><a name="toc-1"></a>Another áéíóúç€ test <a href="edit.php?pageid=&amp;section=Another+%C'.
            '3%A1%C3%A9%C3%AD%C3%B3%C3%BA%C3%A7%E2%82%AC+test" class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Another áéíóúç€ test <a href="edit.php?pageid=&amp;section=Another+%C'.
            '3%A1%C3%A9%C3%AD%C3%B3%C3%BA%C3%A7%E2%82%AC+test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'creole', 'Another áéíóúç€ test');
        $actual = wiki_parser_proxy::parse($input, 'creole');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '= Another http://moodle.org test =';
        $output = '<h3><a name="toc-1"></a>Another http://moodle.org test <a href="edit.php'.
            '?pageid=&amp;section=Another+http%3A%2F%2Fmoodle.org+test" class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Another http://moodle.org test <a href="edit.php?pageid=&amp;section='.
            'Another+http%3A%2F%2Fmoodle.org+test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'creole', 'Another http://moodle.org test');
        $actual = wiki_parser_proxy::parse($input, 'creole');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                
                $input = '= Code & Test =';
        $output = '<h3><a name="toc-1"></a>Code & Test <a href="edit.php?pageid=&amp;section=Code+%26+Test" '.
            'class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Code & Test <a href="edit.php?pageid=&amp;section=Code+%26+'.
            'Test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'nwiki', 'Code & Test');
        $actual = wiki_parser_proxy::parse($input, 'nwiki');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '= Another áéíóúç€ test =';
        $output = '<h3><a name="toc-1"></a>Another áéíóúç€ test <a href="edit.php?pageid=&amp;section=Another+%C'.
            '3%A1%C3%A9%C3%AD%C3%B3%C3%BA%C3%A7%E2%82%AC+test" class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Another áéíóúç€ test <a href="edit.php?pageid=&amp;section=Another+%C'.
            '3%A1%C3%A9%C3%AD%C3%B3%C3%BA%C3%A7%E2%82%AC+test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'nwiki', 'Another áéíóúç€ test');
        $actual = wiki_parser_proxy::parse($input, 'nwiki');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '= Another http://moodle.org test =';
        $output = '<h3><a name="toc-1"></a>Another http://moodle.org test <a href="edit.php'.
            '?pageid=&amp;section=Another+http%3A%2F%2Fmoodle.org+test" class="wiki_edit_section">[edit]</a></h3>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Another http://moodle.org test <a href="edit.php?pageid=&amp;section='.
            'Another+http%3A%2F%2Fmoodle.org+test" class="wiki_edit_section">[edit]</a></a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'nwiki', 'Another http://moodle.org test');
        $actual = wiki_parser_proxy::parse($input, 'nwiki');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);

                $input = '<h3>Heading test</h3><h4>Subsection</h4>';
        $output = '<h3><a name="toc-1"></a>Heading test <a href="edit.php?pageid=&amp;section=Heading+test" '.
            'class="wiki_edit_section">[edit]</a></h3>'. "\n" . '<h4><a name="toc-2"></a>Subsection</h4>' . "\n";
        $toc = '<div class="wiki-toc"><p class="wiki-toc-title">Table of contents</p><p class="wiki-toc-section-1 '.
            'wiki-toc-section">1. <a href="#toc-1">Heading test <a href="edit.php?pageid=&amp;section=Heading+'.
            'test" class="wiki_edit_section">[edit]</a></a></p><p class="wiki-toc-section-2 wiki-toc-section">'.
            '1.1. <a href="#toc-2">Subsection</a></p></div>';
        $section = wiki_parser_proxy::get_section($input, 'html', 'Heading test');
        $actual = wiki_parser_proxy::parse($input, 'html');
        $this->assertEquals($output, $actual['parsed_text']);
        $this->assertEquals($toc, $actual['toc']);
        $this->assertNotEquals(false, $section);
    }

}
