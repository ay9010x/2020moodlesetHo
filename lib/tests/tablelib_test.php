<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/tests/fixtures/testable_flexible_table.php');


class core_tablelib_testcase extends basic_testcase {
    protected function generate_columns($cols) {
        $columns = array();
        foreach (range(0, $cols - 1) as $j) {
            array_push($columns, 'column' . $j);
        }
        return $columns;
    }

    protected function generate_headers($cols) {
        $columns = array();
        foreach (range(0, $cols - 1) as $j) {
            array_push($columns, 'Column ' . $j);
        }
        return $columns;
    }

    protected function generate_data($rows, $cols) {
        $data = array();

        foreach (range(0, $rows - 1) as $i) {
            $row = array();
            foreach (range(0, $cols - 1) as $j) {
                $val =  'row ' . $i . ' col ' . $j;
                $row['column' . $j] = $val;
            }
            array_push($data, $row);
        }
        return $data;
    }

    
    protected function run_table_test($columns, $headers, $sortable, $collapsible, $suppress, $nosorting, $data, $pagesize) {
        $table = $this->create_and_setup_table($columns, $headers, $sortable, $collapsible, $suppress, $nosorting);
        $table->pagesize($pagesize, count($data));
        foreach ($data as $row) {
            $table->add_data_keyed($row);
        }
        $table->finish_output();
    }

    
    protected function create_and_setup_table($columns, $headers, $sortable, $collapsible, $suppress, $nosorting) {
        $table = new flexible_table('tablelib_test');

        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->define_baseurl('/invalid.php');

        $table->sortable($sortable);
        $table->collapsible($collapsible);
        foreach ($suppress as $column) {
            $table->column_suppress($column);
        }

        foreach ($nosorting as $column) {
            $table->no_sorting($column);
        }

        $table->setup();
        return $table;
    }

    public function test_empty_table() {
        $this->expectOutputRegex('/' . get_string('nothingtodisplay') . '/');
        $this->run_table_test(
            array('column1', 'column2'),                   array('Column 1', 'Column 2'),                 true,                                          false,                                         array(),                                       array(),                                       array(),                                       10                                         );
    }

    public function test_has_next_pagination() {

        $data = $this->generate_data(11, 2);
        $columns = $this->generate_columns(2);
        $headers = $this->generate_headers(2);

                $this->expectOutputRegex('/1.*2<\/a>.*' . get_string('next') . '<\/a>/');

        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );
    }

    public function test_has_hide() {

        $data = $this->generate_data(11, 2);
        $columns = $this->generate_columns(2);
        $headers = $this->generate_headers(2);

                $this->expectOutputRegex('/' . get_string('hide') . '/');

        $this->run_table_test(
            $columns,
            $headers,
            true,
            true,
            array(),
            array(),
            $data,
            10
        );
    }

    public function test_has_not_hide() {

        $data = $this->generate_data(11, 2);
        $columns = $this->generate_columns(2);
        $headers = $this->generate_headers(2);

        
        ob_start();
        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertNotContains(get_string('hide'), $output);
    }

    public function test_has_sort() {

        $data = $this->generate_data(11, 2);
        $columns = $this->generate_columns(2);
        $headers = $this->generate_headers(2);

                $this->expectOutputRegex('/' . get_string('sortby') . '/');

        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );
    }

    public function test_has_not_sort() {

        $data = $this->generate_data(11, 2);
        $columns = $this->generate_columns(2);
        $headers = $this->generate_headers(2);

        
        ob_start();
        $this->run_table_test(
            $columns,
            $headers,
            false,
            false,
            array(),
            array(),
            $data,
            10
        );
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertNotContains(get_string('sortby'), $output);
    }

    public function test_has_not_next_pagination() {

        $data = $this->generate_data(10, 2);
        $columns = $this->generate_columns(2);
        $headers = $this->generate_headers(2);

        
        ob_start();
        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );

        $output = ob_get_contents();
        ob_end_clean();
        $this->assertNotContains(get_string('next'), $output);
    }

    public function test_1_col() {

        $data = $this->generate_data(100, 1);
        $columns = $this->generate_columns(1);
        $headers = $this->generate_headers(1);

        $this->expectOutputRegex('/row 0 col 0/');

        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );
    }

    public function test_empty_rows() {

        $data = $this->generate_data(1, 5);
        $columns = $this->generate_columns(5);
        $headers = $this->generate_headers(5);

                $this->expectOutputRegex('/emptyrow.*r9_c4/');

        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );
    }

    public function test_5_cols() {

        $data = $this->generate_data(100, 5);
        $columns = $this->generate_columns(5);
        $headers = $this->generate_headers(5);

        $this->expectOutputRegex('/row 0 col 0/');

        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );
    }

    public function test_50_cols() {

        $data = $this->generate_data(100, 50);
        $columns = $this->generate_columns(50);
        $headers = $this->generate_headers(50);

        $this->expectOutputRegex('/row 0 col 0/');

        $this->run_table_test(
            $columns,
            $headers,
            true,
            false,
            array(),
            array(),
            $data,
            10
        );
    }

    public function test_get_row_html() {
        $data = $this->generate_data(1, 5);
        $columns = $this->generate_columns(5);
        $headers = $this->generate_headers(5);
        $data = array_keys(array_flip($data[0]));

        $table = new flexible_table('tablelib_test');
        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->define_baseurl('/invalid.php');

        $row = $table->get_row_html($data);
        $this->assertRegExp('/row 0 col 0/', $row);
        $this->assertRegExp('/<tr class=""/', $row);
        $this->assertRegExp('/<td class="cell c0"/', $row);
    }

    public function test_persistent_table() {
        global $SESSION;

        $data = $this->generate_data(5, 5);
        $columns = $this->generate_columns(5);
        $headers = $this->generate_headers(5);

                $table1 = new flexible_table('tablelib_test');
        $table1->define_columns($columns);
        $table1->define_headers($headers);
        $table1->define_baseurl('/invalid.php');

        $table1->sortable(true);
        $table1->collapsible(true);

        $table1->is_persistent(false);
        $_GET['thide'] = 'column0';
        $_GET['tsort'] = 'column1';
        $_GET['tifirst'] = 'A';
        $_GET['tilast'] = 'Z';

        foreach ($data as $row) {
            $table1->add_data_keyed($row);
        }
        $table1->setup();

                unset($SESSION->flextable);

        $table2 = new flexible_table('tablelib_test');
        $table2->define_columns($columns);
        $table2->define_headers($headers);
        $table2->define_baseurl('/invalid.php');

        $table2->sortable(true);
        $table2->collapsible(true);

        $table2->is_persistent(false);
        unset($_GET);

        foreach ($data as $row) {
            $table2->add_data_keyed($row);
        }
        $table2->setup();

        $this->assertNotEquals($table1, $table2);

        unset($SESSION->flextable);

                $table3 = new flexible_table('tablelib_test');
        $table3->define_columns($columns);
        $table3->define_headers($headers);
        $table3->define_baseurl('/invalid.php');

        $table3->sortable(true);
        $table3->collapsible(true);

        $table3->is_persistent(true);
        $_GET['thide'] = 'column0';
        $_GET['tsort'] = 'column1';
        $_GET['tifirst'] = 'A';
        $_GET['tilast'] = 'Z';

        foreach ($data as $row) {
            $table3->add_data_keyed($row);
        }
        $table3->setup();

        unset($SESSION->flextable);

        $table4 = new flexible_table('tablelib_test');
        $table4->define_columns($columns);
        $table4->define_headers($headers);
        $table4->define_baseurl('/invalid.php');

        $table4->sortable(true);
        $table4->collapsible(true);

        $table4->is_persistent(true);
        unset($_GET);

        foreach ($data as $row) {
            $table4->add_data_keyed($row);
        }
        $table4->setup();

        $this->assertEquals($table3, $table4);

        unset($SESSION->flextable);

                $table5 = new flexible_table('tablelib_test');
        $table5->define_columns($columns);
        $table5->define_headers($headers);
        $table5->define_baseurl('/invalid.php');

        $table5->sortable(true);
        $table5->collapsible(true);

        $table5->is_persistent(true);
        $_GET['thide'] = 'column0';
        $_GET['tsort'] = 'column1';
        $_GET['tifirst'] = 'A';
        $_GET['tilast'] = 'Z';

        foreach ($data as $row) {
            $table5->add_data_keyed($row);
        }
        $table5->setup();

        $table6 = new flexible_table('tablelib_test');
        $table6->define_columns($columns);
        $table6->define_headers($headers);
        $table6->define_baseurl('/invalid.php');

        $table6->sortable(true);
        $table6->collapsible(true);

        $table6->is_persistent(true);
        unset($_GET);

        foreach ($data as $row) {
            $table6->add_data_keyed($row);
        }
        $table6->setup();

        $this->assertEquals($table5, $table6);
    }

    
    protected function prepare_table_for_reset_test($tableid) {
        global $SESSION;

        unset($SESSION->flextable[$tableid]);

        $data = $this->generate_data(25, 3);
        $columns = array('column0', 'column1', 'column2');
        $headers = $this->generate_headers(3);

        $table = new testable_flexible_table($tableid);
        $table->define_baseurl('/invalid.php');
        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->collapsible(true);
        $table->is_persistent(false);

        return $table;
    }

    public function test_can_be_reset() {

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $table->setup();
        $this->assertFalse($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $table->sortable(true, 'column1', SORT_DESC);
        $table->setup();
        $this->assertFalse($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $table->sortable(true, 'column1', SORT_DESC);
        $_GET['tsort'] = 'column1';
        $table->setup();
        unset($_GET['tsort']);
        $this->assertTrue($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $table->sortable(true, 'column1', SORT_DESC);
        $_GET['tsort'] = 'column1';
        $table->setup();
        $table->setup();         unset($_GET['tsort']);
        $this->assertFalse($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $table->sortable(true, 'column1', SORT_DESC);
        $_GET['tsort'] = 'column2';
        $table->setup();
        unset($_GET['tsort']);
        $this->assertTrue($table->can_be_reset());

                        $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $table->sortable(true, 'column1', SORT_DESC);
        $_GET['tsort'] = 'column0';
        $table->setup();
        $_GET['tsort'] = 'column1';
        $table->setup();
        unset($_GET['tsort']);
        $this->assertTrue($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $_GET['thide'] = 'column2';
        $table->setup();
        unset($_GET['thide']);
        $this->assertTrue($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $_GET['tshow'] = 'column2';
        $table->setup();
        unset($_GET['tshow']);
        $this->assertFalse($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $_GET['thide'] = 'column0';
        $table->setup();
        $_GET['tshow'] = 'column0';
        $table->setup();
        unset($_GET['thide']);
        unset($_GET['tshow']);
        $this->assertFalse($table->can_be_reset());

                $table = $this->prepare_table_for_reset_test(uniqid('tablelib_test_'));
        $_GET['tifirst'] = 'A';
        $table->setup();
        unset($_GET['tifirst']);
        $this->assertTrue($table->can_be_reset());
    }
}
