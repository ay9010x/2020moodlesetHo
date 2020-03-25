<?php



defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->libdir.'/portfoliolib.php');
require_once($CFG->libdir.'/portfolio/formats.php');


class core_portfoliolib_testcase extends advanced_testcase {

    public function test_portfolio_rewrite_pluginfile_urls() {
        $this->resetAfterTest();

                $context = context_system::instance();
        $component = 'core_test';
        $filearea = 'fixture';
        $filepath = '/';
        $itemid = 0;
        $filenameimg = 'file.png';
        $filenamepdf = 'file.pdf';

                $fs = get_file_storage();
        $filerecord = array(
            'contextid' => $context->id,
            'component' => $component,
            'filearea'  => $filearea,
            'itemid'    => $itemid,
            'filepath'  => $filepath,
            'filename'  => $filenameimg,
        );
        $fileimg = $fs->create_file_from_string($filerecord, 'test');

        $filerecord['filename']  = $filenamepdf;
        $filepdf = $fs->create_file_from_string($filerecord, 'test');

                $format = '';
        $options = null;
        $input = '<div>Here, the <a href="nowhere">@@PLUGINFILE@@' . $filepath . $filenamepdf .
            ' is</a> not supposed to be an actual URL placeholder.</div>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($input, $output);

        $input = '<div>Here, the <img src="nowhere" />@@PLUGINFILE@@' . $filepath . $filenameimg .
            ' is</a> not supposed to be an actual URL placeholder.</div>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($input, $output);

                $format = new core_portfolio_format_dummytest();
        $options = null;

                $input = '<p>Come and <a href="@@PLUGINFILE@@' . $filepath . $filenamepdf . '">join us!</a>?</p>';
        $expected = '<p>Come and <a href="files/' . $filenamepdf . '">' . $filenamepdf . '</a>?</p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

        $input = '<p>Come and <a href="@@PLUGINFILE@@' . $filepath . $filenamepdf . '"><em>join us!</em></a>?</p>';
        $expected = '<p>Come and <a href="files/' . $filenamepdf . '">' . $filenamepdf . '</a>?</p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

                $input = '<p>Here is an image <img src="@@PLUGINFILE@@' . $filepath . $filenameimg . '"></p>';         $expected = '<p>Here is an image <img src="files/' . $filenameimg . '"/></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

        $input = '<p>Here is an image <img src="@@PLUGINFILE@@' . $filepath . $filenameimg . '" /></p>';         $expected = '<p>Here is an image <img src="files/' . $filenameimg . '"/></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

                $input = '<p><a title="hurray!" href="@@PLUGINFILE@@' . $filepath . $filenamepdf . '" target="_blank">join us!</a></p>';
        $expected = '<p><a title="hurray!" href="files/' . $filenamepdf . '" target="_blank">' . $filenamepdf . '</a></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

        $input = '<p><img alt="before" src="@@PLUGINFILE@@' . $filepath . $filenameimg . '" title="after"/></p>';
        $expected = '<p><img alt="before" src="files/' . $filenameimg . '" title="after"/></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

                $input = '<p><span title="@@PLUGINFILE/a.txt"><a href="@@PLUGINFILE@@' . $filepath . $filenamepdf . '">' .
            '<em>join</em> <b>us!</b></a></span></p>';
        $expected = '<p><span title="@@PLUGINFILE/a.txt"><a href="files/' . $filenamepdf . '">' . $filenamepdf . '</a></span></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

        $input = '<p><span title="@@PLUGINFILE/a.txt"><img src="@@PLUGINFILE@@' . $filepath . $filenameimg . '"/></span></p>';
        $expected = '<p><span title="@@PLUGINFILE/a.txt"><img src="files/' . $filenameimg . '"/></span></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

                $input = '<p><a rel="1" href="@@PLUGINFILE@@' . $filepath . $filenamepdf . '">join us!</a>' .
            '<a rel="2" href="@@PLUGINFILE@@' . $filepath . $filenamepdf . '">join us!</a></p>';
        $expected = '<p><a rel="1" href="files/' . $filenamepdf . '">' . $filenamepdf . '</a>' .
            '<a rel="2" href="files/' . $filenamepdf . '">' . $filenamepdf . '</a></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

        $input = '<p><img rel="1" src="@@PLUGINFILE@@' . $filepath . $filenameimg . '"/>' .
            '<img rel="2" src="@@PLUGINFILE@@' . $filepath . $filenameimg . '"/></p>';
        $expected = '<p><img rel="1" src="files/' . $filenameimg . '"/><img rel="2" src="files/' . $filenameimg . '"/></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);

        $input = '<p><a href="@@PLUGINFILE@@' . $filepath . $filenamepdf . '">join us!</a>' .
            '<img src="@@PLUGINFILE@@' . $filepath . $filenameimg . '"/></p>';
        $expected = '<p><a href="files/' . $filenamepdf . '">' . $filenamepdf . '</a>' .
            '<img src="files/' . $filenameimg . '"/></p>';
        $output = portfolio_rewrite_pluginfile_urls($input, $context->id, $component, $filearea, $itemid, $format, $options);
        $this->assertSame($expected, $output);
    }
}


class core_portfolio_format_dummytest extends portfolio_format {

    public static function file_output($file, $options = null) {
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            $attributes = $options['attributes'];
        } else {
            $attributes = array();
        }
        $path = 'files/' . $file->get_filename();
        return self::make_tag($file, $path, $attributes);
    }

}
