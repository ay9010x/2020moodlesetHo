<?php




defined('MOODLE_INTERNAL') || die();


class core_html2text_testcase extends basic_testcase {

    
    public function test_images() {
        $this->assertSame('[edit]', html_to_text('<img src="edit.png" alt="edit" />'));

        $text = 'xx<img src="gif.gif" alt="some gif" />xx';
        $result = html_to_text($text, null, false, false);
        $this->assertSame($result, 'xx[some gif]xx');
    }

    
    public function test_no_strip_slashes() {
        $this->assertSame('[\edit]', html_to_text('<img src="edit.png" alt="\edit" />'));

        $text = '\\magic\\quotes\\are\\\\horrible';
        $result = html_to_text($text, null, false, false);
        $this->assertSame($result, $text);
    }

    
    public function test_core_text() {
        $text = '<strong>Žluťoučký koníček</strong>';
        $result = html_to_text($text, null, false, false);
        $this->assertSame($result, 'ŽLUŤOUČKÝ KONÍČEK');
    }

    
    public function test_zero() {
        $text = '0';
        $result = html_to_text($text, null, false, false);
        $this->assertSame($result, $text);

        $this->assertSame('0', html_to_text('0'));
    }

    
    public function test_build_link_list() {

                $text = 'Total of <a title="List of integrated issues"
            href="http://tr.mdl.org/sh.jspa?r=1&j=p+%3D+%22I+d%22+%3D">     
            <strong>27 issues</strong></a> and <a href="http://another.url/?f=a&amp;b=2">some</a> other
have been fixed <strong><a href="http://third.url/view.php">last week</a></strong>';

                $result = html_to_text($text, 5000, false);
        $this->assertSame('Total of 27 ISSUES and some other have been fixed LAST WEEK', $result);

                $result = html_to_text($text, 5000, true);
        $this->assertSame(0, strpos($result, 'Total of 27 ISSUES [1] and some [2] other have been fixed LAST WEEK [3]'));
        $this->assertSame(false, strpos($result, '[0]'));
        $this->assertSame(1, preg_match('|^'.preg_quote('[1] http://tr.mdl.org/sh.jspa?r=1&j=p+%3D+%22I+d%22+%3D').'$|m', $result));
        $this->assertSame(1, preg_match('|^'.preg_quote('[2] http://another.url/?f=a&amp;b=2').'$|m', $result));
        $this->assertSame(1, preg_match('|^'.preg_quote('[3] http://third.url/view.php').'$|m', $result));
        $this->assertSame(false, strpos($result, '[4]'));

                $text = '<p>See <a href="http://moodle.org">moodle.org</a>,
            <a href="http://www.google.fr">google</a>, <a href="http://www.univ-lemans.fr">univ-lemans</a>
            and <a href="http://www.google.fr">google</a>.
            Also try <a href="https://www.google.fr">google via HTTPS</a>.';
        $result = html_to_text($text, 5000, true);
        $this->assertSame(0, strpos($result, 'See moodle.org [1], google [2], univ-lemans [3] and google [2]. Also try google via HTTPS [4].'));
        $this->assertSame(false, strpos($result, '[0]'));
        $this->assertSame(1, preg_match('|^'.preg_quote('[1] http://moodle.org').'$|m', $result));
        $this->assertSame(1, preg_match('|^'.preg_quote('[2] http://www.google.fr').'$|m', $result));
        $this->assertSame(1, preg_match('|^'.preg_quote('[3] http://www.univ-lemans.fr').'$|m', $result));
        $this->assertSame(1, preg_match('|^'.preg_quote('[4] https://www.google.fr').'$|m', $result));
        $this->assertSame(false, strpos($result, '[5]'));
    }

    
    public function test_invalid_html() {
        $text = 'Gin & Tonic';
        $result = html_to_text($text, null, false, false);
        $this->assertSame($result, $text);

        $text = 'Gin > Tonic';
        $result = html_to_text($text, null, false, false);
        $this->assertSame($result, $text);

        $text = 'Gin < Tonic';
        $result = html_to_text($text, null, false, false);
        $this->assertSame($result, $text);
    }

    
    public function test_simple() {
        $this->assertSame("_Hello_ WORLD!\n", html_to_text('<p><i>Hello</i> <b>world</b>!</p>'));
        $this->assertSame("All the WORLD’S a stage.\n\n-- William Shakespeare\n", html_to_text('<p>All the <strong>world’s</strong> a stage.</p><p>-- William Shakespeare</p>'));
        $this->assertSame("HELLO WORLD!\n\n", html_to_text('<h1>Hello world!</h1>'));
        $this->assertSame("Hello\nworld!", html_to_text('Hello<br />world!'));
    }

    
    public function test_text_nowrap() {
        $long = "Here is a long string, more than 75 characters long, since by default html_to_text wraps text at 75 chars.";
        $wrapped = "Here is a long string, more than 75 characters long, since by default\nhtml_to_text wraps text at 75 chars.";
        $this->assertSame($long, html_to_text($long, 0));
        $this->assertSame($wrapped, html_to_text($long));
    }

    
    public function test_trailing_whitespace() {
        $this->assertSame('With trailing whitespace and some more text', html_to_text("With trailing whitespace   \nand some   more text", 0));
    }

    
    public function test_html_to_text_pre_parsing_problem() {
        $strorig = 'Consider the following function:<br /><pre><span style="color: rgb(153, 51, 102);">void FillMeUp(char* in_string) {'.
            '<br />  int i = 0;<br />  while (in_string[i] != \'\0\') {<br />    in_string[i] = \'X\';<br />    i++;<br />  }<br />'.
            '}</span></pre>What would happen if a non-terminated string were input to this function?<br /><br />';

                $strconv = 'Consider the following function:

void FillMeUp(char* in_string) {
  int i = 0;
  while (in_string[i] != \'\0\') {
    in_string[i] = \'X\';
    i++;
  }
}
What would happen if a non-terminated string were input to this function?

';

        $this->assertSame($strconv, html_to_text($strorig));
    }

    
    public function test_strip_scripts() {
        $this->assertSame('Interesting text',
                html_to_text('Interesting <script type="text/javascript">var what_a_mess = "Yuck!";</script> text', 0));
    }
}
