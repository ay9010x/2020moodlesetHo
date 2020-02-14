<?php



defined('MOODLE_INTERNAL') || die();



class core_markdown_testcase extends basic_testcase {

    public function test_paragraphs() {
        $text = "one\n\ntwo";
        $result = "<p>one</p>\n\n<p>two</p>\n";
        $this->assertSame($result, markdown_to_html($text));
    }

    public function test_headings() {
        $text = "Header 1\n====================\n\n## Header 2";
        $result = "<h1>Header 1</h1>\n\n<h2>Header 2</h2>\n";
        $this->assertSame($result, markdown_to_html($text));
    }

    public function test_lists() {
        $text = "* one\n* two\n* three\n";
        $result = "<ul>\n<li>one</li>\n<li>two</li>\n<li>three</li>\n</ul>\n";
        $this->assertSame($result, markdown_to_html($text));
    }

    public function test_links() {
        $text = "some [example link](http://example.com/)";
        $result = "<p>some <a href=\"http://example.com/\">example link</a></p>\n";
        $this->assertSame($result, markdown_to_html($text));
    }
}
