<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/csvlib.class.php');

class core_csvclass_testcase extends advanced_testcase {

    protected $testdata = array();
    protected $teststring = '';
    protected $teststring2 = '';
    protected $teststring3 = '';
    protected $teststring4 = '';

    protected function setUp() {

        $this->resetAfterTest();

        $csvdata = array();
        $csvdata[0][] = 'fullname';
        $csvdata[0][] = 'description of things';
        $csvdata[0][] = 'beer';
        $csvdata[1][] = 'William B Stacey';
        $csvdata[1][] = '<p>A field that contains "double quotes"</p>';
        $csvdata[1][] = 'Asahi';
        $csvdata[2][] = 'Phillip Jenkins';
        $csvdata[2][] = '<p>This field has </p>
<p>Multiple lines</p>
<p>and also contains "double quotes"</p>';
        $csvdata[2][] = 'Yebisu';
        $this->testdata = $csvdata;

                $this->teststring = 'fullname,"description of things",beer
"William B Stacey","<p>A field that contains ""double quotes""</p>",Asahi
"Phillip Jenkins","<p>This field has </p>
<p>Multiple lines</p>
<p>and also contains ""double quotes""</p>",Yebisu
';

        $this->teststring2 = 'fullname,"description of things",beer
"Fred Flint","<p>Find the stone inside the box</p>",Asahi,"A fourth column"
"Sarah Smith","<p>How are the people next door?</p>,Yebisu,"Forget the next"
';

        $this->teststring4 = 'fullname,"description of things",beer
"Douglas Dirk","<p>I am fine, thankyou.</p>",Becks

"Addelyn Francis","<p>Thanks for the cake</p>",Becks
"Josh Frankson","<p>Everything is fine</p>",Asahi


"Heath Forscyth","<p>We are going to make you lose your mind</p>",Fosters
';
    }

    public function test_csv_functions() {
        global $CFG;
        $csvexport = new csv_export_writer();
        $csvexport->set_filename('unittest');
        foreach ($this->testdata as $data) {
            $csvexport->add_data($data);
        }
        $csvoutput = $csvexport->print_csv_data(true);
        $this->assertSame($csvoutput, $this->teststring);

        $test_data = csv_export_writer::print_array($this->testdata, 'comma', '"', true);
        $this->assertSame($test_data, $this->teststring);

                $iid = csv_import_reader::get_new_iid('lib');
        $csvimport = new csv_import_reader($iid, 'lib');
        $contentcount = $csvimport->load_csv_content($this->teststring, 'utf-8', 'comma');
        $csvimport->init();
        $dataset = array();
        $dataset[] = $csvimport->get_columns();
        while ($record = $csvimport->next()) {
            $dataset[] = $record;
        }
        $csvimport->cleanup();
        $csvimport->close();
        $this->assertSame($dataset, $this->testdata);

                $errortext = get_string('csvweirdcolumns', 'error');
        $iid = csv_import_reader::get_new_iid('lib');
        $csvimport = new csv_import_reader($iid, 'lib');
        $contentcount = $csvimport->load_csv_content($this->teststring2, 'utf-8', 'comma');
        $importerror = $csvimport->get_error();
        $csvimport->cleanup();
        $csvimport->close();
        $this->assertSame($importerror, $errortext);

                $errortext = get_string('csvemptyfile', 'error');

        $iid = csv_import_reader::get_new_iid('lib');
        $csvimport = new csv_import_reader($iid, 'lib');
        $contentcount = $csvimport->load_csv_content($this->teststring3, 'utf-8', 'comma');
        $importerror = $csvimport->get_error();
        $csvimport->cleanup();
        $csvimport->close();
        $this->assertSame($importerror, $errortext);

                        $filename = $CFG->dirroot . '/lib/tests/fixtures/tabfile.csv';
        $fp = fopen($filename, 'r');
        $tabdata = fread($fp, filesize($filename));
        fclose($fp);
        $iid = csv_import_reader::get_new_iid('tab');
        $csvimport = new csv_import_reader($iid, 'tab');
        $contentcount = $csvimport->load_csv_content($tabdata, 'utf-8', 'tab');
                $this->assertEquals($contentcount, 4);

                $iid = csv_import_reader::get_new_iid('blanklines');
        $csvimport = new csv_import_reader($iid, 'blanklines');
        $contentcount = $csvimport->load_csv_content($this->teststring4, 'utf-8', 'comma');
                $this->assertEquals($contentcount, 5);
    }
}
