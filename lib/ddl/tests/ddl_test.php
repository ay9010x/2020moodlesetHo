<?php



defined('MOODLE_INTERNAL') || die();

class core_ddl_testcase extends database_driver_testcase {
    private $tables = array();
    private $records= array();

    protected function setUp() {
        parent::setUp();
        $dbman = $this->tdb->get_manager(); 
        $table = new xmldb_table('test_table0');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'general');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('intro', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
        $table->add_field('logo', XMLDB_TYPE_BINARY, 'big', null, null, null);
        $table->add_field('assessed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('assesstimestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('assesstimefinish', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('scale', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('maxbytes', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('forcesubscribe', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('trackingtype', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('rsstype', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('rssarticles', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('grade', XMLDB_TYPE_NUMBER, '20,0', null, null, null, null);
        $table->add_field('percent', XMLDB_TYPE_NUMBER, '5,2', null, null, null, 66.6);
        $table->add_field('warnafter', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('blockafter', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('blockperiod', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course', XMLDB_KEY_UNIQUE, array('course'));
        $table->add_index('type-name', XMLDB_INDEX_UNIQUE, array('type', 'name'));
        $table->add_index('rsstype', XMLDB_INDEX_NOTUNIQUE, array('rsstype'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

                $this->records[$table->getName()] = array(
            (object)array(
                'course' => '1',
                'type'   => 'general',
                'name'   => 'record',
                'intro'  => 'first record'),
            (object)array(
                'course' => '2',
                'type'   => 'social',
                'name'   => 'record',
                'intro'  => 'second record'));

                $table = new xmldb_table ('test_table1');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, null, null, 'Moodle');
        $table->add_field('secondname', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('thirdname', XMLDB_TYPE_CHAR, '30', null, null, null, '');         $table->add_field('intro', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null);
        $table->add_field('avatar', XMLDB_TYPE_BINARY, 'medium', null, null, null, null);
        $table->add_field('grade', XMLDB_TYPE_NUMBER, '20,10', null, null, null);
        $table->add_field('gradefloat', XMLDB_TYPE_FLOAT, '20,0', null, null, null, null);
        $table->add_field('percentfloat', XMLDB_TYPE_FLOAT, '5,2', null, null, null, 99.9);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course', XMLDB_KEY_FOREIGN_UNIQUE, array('course'), 'test_table0', array('course'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

                $this->records[$table->getName()] = array(
            (object)array(
                'course' => '1',
                'secondname'   => 'first record',                 'intro'  => 'first record'),
            (object)array(
                'course'       => '2',
                'secondname'   => 'second record',                 'intro'  => 'second record'));
    }

    private function create_deftable($tablename) {
        $dbman = $this->tdb->get_manager();

        if (!isset($this->tables[$tablename])) {
            return null;
        }

        $table = $this->tables[$tablename];

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        return $table;
    }

    
    private function fill_deftable($tablename) {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        if (!isset($this->records[$tablename])) {
            return null;
        }

        if ($dbman->table_exists($tablename)) {
            foreach ($this->records[$tablename] as $row) {
                $DB->insert_record($tablename, $row);
            }
        } else {
            return null;
        }

        return count($this->records[$tablename]);
    }

    
    public function test_table_exists() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

                $table = $this->tables['test_table0'];

        try {
            $result = $DB->get_records('test_table0');
        } catch (dml_exception $e) {
            $result = false;
        }
        $this->resetDebugging();

        $this->assertFalse($result);

        $this->assertFalse($dbman->table_exists('test_table0'));         $this->assertFalse($dbman->table_exists($table));        
                $dbman->create_table($table);

        $this->assertSame(array(), $DB->get_records('test_table0'));
        $this->assertTrue($dbman->table_exists('test_table0'));         $this->assertTrue($dbman->table_exists($table));        
                $dbman->drop_table($table);

        try {
            $result = $DB->get_records('test_table0');
        } catch (dml_exception $e) {
            $result = false;
        }
        $this->resetDebugging();

        $this->assertFalse($result);

        $this->assertFalse($dbman->table_exists('test_table0'));         $this->assertFalse($dbman->table_exists($table));            }

    
    public function test_create_table() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

                $table = $this->tables['test_table1'];

        $dbman->create_table($table);
        $this->assertTrue($dbman->table_exists($table));

                $tables = $DB->get_tables();
        $this->assertArrayHasKey('test_table1', $tables);

                $columns = $DB->get_columns('test_table1');
        $this->assertSame('R', $columns['id']->meta_type);
        $this->assertSame('I', $columns['course']->meta_type);
        $this->assertSame('C', $columns['name']->meta_type);
        $this->assertSame('C', $columns['secondname']->meta_type);
        $this->assertSame('C', $columns['thirdname']->meta_type);
        $this->assertSame('X', $columns['intro']->meta_type);
        $this->assertSame('B', $columns['avatar']->meta_type);
        $this->assertSame('N', $columns['grade']->meta_type);
        $this->assertSame('N', $columns['percentfloat']->meta_type);
        $this->assertSame('I', $columns['userid']->meta_type);
                $this->assertTrue($columns['course']->has_default);
        $this->assertEquals(0, $columns['course']->default_value);
        $this->assertTrue($columns['name']->has_default);
        $this->assertSame('Moodle', $columns['name']->default_value);
        $this->assertTrue($columns['secondname']->has_default);
        $this->assertSame('', $columns['secondname']->default_value);
        $this->assertTrue($columns['thirdname']->has_default);
        $this->assertSame('', $columns['thirdname']->default_value);
        $this->assertTrue($columns['percentfloat']->has_default);
        $this->assertEquals(99.9, $columns['percentfloat']->default_value);
        $this->assertTrue($columns['userid']->has_default);
        $this->assertEquals(0, $columns['userid']->default_value);

                $indexes = $DB->get_indexes('test_table1');
        $courseindex = reset($indexes);
        $this->assertEquals(1, $courseindex['unique']);
        $this->assertSame('course', $courseindex['columns'][0]);

                $rec = (object)array(
            'course'     => 10,
            'secondname' => 'not important',
            'intro'      => 'not important');
        $this->assertSame(1, $DB->insert_record('test_table1', $rec));

                $dbrec = $DB->get_record('test_table1', array('id' => 1));
        $this->assertSame('Moodle', $dbrec->name);
        $this->assertSame('', $dbrec->thirdname);

                $table = new xmldb_table ('test_table2');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('rid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('primaryx', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

                $table = new xmldb_table ('test_table2');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

                $table = new xmldb_table('test_table0123456789_____xyz');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '2');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        $dbman->create_table($table);
        $this->assertTrue($dbman->table_exists($table));
        $dbman->drop_table($table);

                $table = new xmldb_table('test_table0123456789_____xyz9');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '2');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_tableCD');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '2');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table3');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('abcdef____0123456789_______xyz', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '2');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        $dbman->create_table($table);
        $this->assertTrue($dbman->table_exists($table));
        $dbman->drop_table($table);

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('abcdeabcdeabcdeabcdeabcdeabcdez', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '2');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('abCD', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '2');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '21', null, XMLDB_NOTNULL, null, '2');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 'x');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('num', XMLDB_TYPE_NUMBER, '21,10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('num', XMLDB_TYPE_NUMBER, '10,11', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('num', XMLDB_TYPE_NUMBER, '10,5', null, XMLDB_NOTNULL, null, 'x');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('num', XMLDB_TYPE_FLOAT, '21,10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('num', XMLDB_TYPE_FLOAT, '10,11', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

                $table = new xmldb_table('test_table4');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('num', XMLDB_TYPE_FLOAT, '10,5', null, XMLDB_NOTNULL, null, 'x');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->setComment("This is a test'n drop table. You can drop it safely");

        $this->tables[$table->getName()] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
    }

    
    public function test_row_size_limits() {

        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $text = str_repeat('š', 1333);

        $data = new stdClass();
        $data->name = 'test';
        $table = new xmldb_table('test_innodb');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        for ($i = 0; $i < 20; $i++) {
            $table->add_field('text'.$i, XMLDB_TYPE_TEXT, null, null, null, null, null);
            $data->{'text'.$i} = $text;
        }
        $dbman->create_table($table);

        try {
            $id = $DB->insert_record('test_innodb', $data);
            $expected = (array)$data;
            $expected['id'] = (string)$id;
            $this->assertEquals($expected, (array)$DB->get_record('test_innodb', array('id' => $id)), '', 0, 10, true);
        } catch (dml_exception $e) {
                        if ($DB->get_dbfamily() === 'mysql') {
                $engine = strtolower($DB->get_dbengine());
                if ($engine === 'innodb' or $engine === 'xtradb') {
                    if (!$DB->is_compressed_row_format_supported()) {
                        $this->fail("Row size limit reached in MySQL using InnoDB, configure server to use innodb_file_format=Barracuda and innodb_file_per_table=1");
                    }
                }
            }
            throw $e;
        }

        $dbman->drop_table($table);

        $data = new stdClass();
        $data->name = 'test';
        $table = new xmldb_table('test_innodb');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $dbman->create_table($table);
        $DB->insert_record('test_innodb', array('name' => 'test'));

        for ($i = 0; $i < 20; $i++) {
            $field = new xmldb_field('text'.$i, XMLDB_TYPE_TEXT, null, null, null, null, null);
            $dbman->add_field($table, $field);
            $data->{'text'.$i} = $text;

            $id = $DB->insert_record('test_innodb', $data);
            $expected = (array)$data;
            $expected['id'] = (string)$id;
            $this->assertEquals($expected, (array)$DB->get_record('test_innodb', array('id' => $id)), '', 0, 10, true);
        }

        $dbman->drop_table($table);

                $data = new stdClass();
        $data->name = 'test';
        $table = new xmldb_table('test_innodb');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        for ($i = 0; $i < 15; $i++) {
            $table->add_field('text'.$i, XMLDB_TYPE_CHAR, '1333', null, null, null, null);
            $data->{'text'.$i} = $text;
        }
        $dbman->create_table($table);

        $id = $DB->insert_record('test_innodb', $data);
        $expected = (array)$data;
        $expected['id'] = (string)$id;
        $this->assertEquals($expected, (array)$DB->get_record('test_innodb', array('id' => $id)), '', 0, 10, true);

        $dbman->drop_table($table);
    }

    
    public function test_drop_table() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

                $this->assertFalse($dbman->table_exists('test_table0'));

                $table = $this->create_deftable('test_table0');
        $this->assertTrue($dbman->table_exists('test_table0'));

                $this->fill_deftable('test_table0');

                $dbman->drop_table($table);
        $this->assertFalse($dbman->table_exists('test_table0'));

                $tables = $DB->get_tables();
        $this->assertArrayNotHasKey('test_table0', $tables);

                $columns = $DB->get_columns('test_table0');
        $this->assertEmpty($columns);

        $indexes = $DB->get_indexes('test_table0');
        $this->assertEmpty($indexes);
    }

    
    public function test_rename_table() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');

                $insertedrows = $this->fill_deftable('test_table1');

        $this->assertFalse($dbman->table_exists('test_table_cust1'));
        $dbman->rename_table($table, 'test_table_cust1');
        $this->assertTrue($dbman->table_exists('test_table_cust1'));

                $rec = (object)array(
            'course'     => 20,
            'secondname' => 'not important',
            'intro'      => 'not important');
        $this->assertSame($insertedrows+1, $DB->insert_record('test_table_cust1', $rec));

                $sourcetable = $this->create_deftable('test_table0');
        $targettable = $this->create_deftable('test_table1');
        try {
            $dbman->rename_table($sourcetable, $targettable->getName());
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
            $this->assertEquals('Table "test_table1" already exists (can not rename table)', $e->getMessage());
        }
    }

    
    public function test_field_exists() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table0');

                        try {
            $dbman->field_exists('nonexistenttable', 'id');
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }

                $this->assertFalse($dbman->field_exists('test_table0', 'nonexistentfield'));

                $this->assertTrue($dbman->field_exists('test_table0', 'id'));

                $realfield = $table->getField('id');

                $nonexistenttable = new xmldb_table('nonexistenttable');
        try {
            $dbman->field_exists($nonexistenttable, $realfield);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }

                $nonexistentfield = new xmldb_field('nonexistentfield');
        $this->assertFalse($dbman->field_exists($table, $nonexistentfield));

                $this->assertTrue($dbman->field_exists($table, $realfield));

                        $this->assertTrue($dbman->field_exists($table, 'id'));
        $this->assertTrue($dbman->field_exists('test_table0', $realfield));
                try {
            $this->assertFalse($dbman->field_exists($nonexistenttable, 'id'));
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
        try {
            $this->assertFalse($dbman->field_exists('nonexistenttable', $realfield));
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('moodle_exception', $e);
        }
                $this->assertFalse($dbman->field_exists($table, 'nonexistentfield'));
        $this->assertFalse($dbman->field_exists('test_table0', $nonexistentfield));
    }

    
    public function test_add_field() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');

                $this->fill_deftable('test_table1');

                $field = new xmldb_field('onefield');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, null);
        try {
            $dbman->add_field($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

                $field = new xmldb_field('course');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, 2);
        try {
            $dbman->add_field($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

                        
                $field = new xmldb_field('oneinteger');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, 2);
        $dbman->add_field($table, $field);
        $this->assertTrue($dbman->field_exists($table, 'oneinteger'));
        $columns = $DB->get_columns('test_table1');
        $this->assertEquals('oneinteger', $columns['oneinteger']->name);
        $this->assertTrue($columns['oneinteger']->not_null);
                $this->assertFalse($columns['oneinteger']->primary_key);
        $this->assertFalse($columns['oneinteger']->binary);
        $this->assertTrue($columns['oneinteger']->has_default);
        $this->assertEquals(2, $columns['oneinteger']->default_value);
        $this->assertSame('I', $columns['oneinteger']->meta_type);
        $this->assertEquals(2, $DB->get_field('test_table1', 'oneinteger', array(), IGNORE_MULTIPLE)); 
                $field = new xmldb_field('onenumber');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '6,3', null, XMLDB_NOTNULL, null, 2.55);
        $dbman->add_field($table, $field);
        $this->assertTrue($dbman->field_exists($table, 'onenumber'));
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('onenumber', $columns['onenumber']->name);
        $this->assertEquals(6, $columns['onenumber']->max_length);
        $this->assertEquals(3, $columns['onenumber']->scale);
        $this->assertTrue($columns['onenumber']->not_null);
        $this->assertFalse($columns['onenumber']->primary_key);
        $this->assertFalse($columns['onenumber']->binary);
        $this->assertTrue($columns['onenumber']->has_default);
        $this->assertEquals(2.550, $columns['onenumber']->default_value);
        $this->assertSame('N', $columns['onenumber']->meta_type);
        $this->assertEquals(2.550, $DB->get_field('test_table1', 'onenumber', array(), IGNORE_MULTIPLE)); 
                $field = new xmldb_field('onenumberwith0scale');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '6,0', null, XMLDB_NOTNULL, null, 2);
        $dbman->add_field($table, $field);
        $this->assertTrue($dbman->field_exists($table, 'onenumberwith0scale'));
        $columns = $DB->get_columns('test_table1');
        $this->assertEquals(6, $columns['onenumberwith0scale']->max_length);
                $this->assertSame('0', strval($columns['onenumberwith0scale']->scale));

                $field = new xmldb_field('onefloat');
        $field->set_attributes(XMLDB_TYPE_FLOAT, '6,3', null, XMLDB_NOTNULL, null, 3.550);
        $dbman->add_field($table, $field);
        $this->assertTrue($dbman->field_exists($table, 'onefloat'));
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('onefloat', $columns['onefloat']->name);
        $this->assertTrue($columns['onefloat']->not_null);
                $this->assertFalse($columns['onefloat']->primary_key);
        $this->assertFalse($columns['onefloat']->binary);
        $this->assertTrue($columns['onefloat']->has_default);
        $this->assertEquals(3.550, $columns['onefloat']->default_value);
        $this->assertSame('N', $columns['onefloat']->meta_type);
                                        $this->assertEquals(3.550, round($DB->get_field('test_table1', 'onefloat', array(), IGNORE_MULTIPLE), 7)); 
                $field = new xmldb_field('onechar');
        $field->set_attributes(XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, 'Nice dflt!');
        $dbman->add_field($table, $field);
        $this->assertTrue($dbman->field_exists($table, 'onechar'));
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('onechar', $columns['onechar']->name);
        $this->assertEquals(25, $columns['onechar']->max_length);
        $this->assertNull($columns['onechar']->scale);
        $this->assertTrue($columns['onechar']->not_null);
        $this->assertFalse($columns['onechar']->primary_key);
        $this->assertFalse($columns['onechar']->binary);
        $this->assertTrue($columns['onechar']->has_default);
        $this->assertSame('Nice dflt!', $columns['onechar']->default_value);
        $this->assertSame('C', $columns['onechar']->meta_type);
        $this->assertEquals('Nice dflt!', $DB->get_field('test_table1', 'onechar', array(), IGNORE_MULTIPLE)); 
                $field = new xmldb_field('onetext');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'big');
        $dbman->add_field($table, $field);
        $this->assertTrue($dbman->field_exists($table, 'onetext'));
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('onetext', $columns['onetext']->name);
        $this->assertEquals(-1, $columns['onetext']->max_length);         $this->assertNull($columns['onetext']->scale);
        $this->assertFalse($columns['onetext']->not_null);
        $this->assertFalse($columns['onetext']->primary_key);
        $this->assertFalse($columns['onetext']->binary);
        $this->assertFalse($columns['onetext']->has_default);
        $this->assertNull($columns['onetext']->default_value);
        $this->assertSame('X', $columns['onetext']->meta_type);

                $field = new xmldb_field('mediumtext');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium');
        $dbman->add_field($table, $field);
        $columns = $DB->get_columns('test_table1');
        $this->assertTrue(($columns['mediumtext']->max_length == -1) or ($columns['mediumtext']->max_length >= 16777215)); 
                $field = new xmldb_field('smalltext');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small');
        $dbman->add_field($table, $field);
        $columns = $DB->get_columns('test_table1');
        $this->assertTrue(($columns['smalltext']->max_length == -1) or ($columns['smalltext']->max_length >= 65535)); 
                $field = new xmldb_field('onebinary');
        $field->set_attributes(XMLDB_TYPE_BINARY);
        $dbman->add_field($table, $field);
        $this->assertTrue($dbman->field_exists($table, 'onebinary'));
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('onebinary', $columns['onebinary']->name);
        $this->assertEquals(-1, $columns['onebinary']->max_length);
        $this->assertNull($columns['onebinary']->scale);
        $this->assertFalse($columns['onebinary']->not_null);
        $this->assertFalse($columns['onebinary']->primary_key);
        $this->assertTrue($columns['onebinary']->binary);
        $this->assertFalse($columns['onebinary']->has_default);
        $this->assertNull($columns['onebinary']->default_value);
        $this->assertSame('B', $columns['onebinary']->meta_type);

            }

    
    public function test_drop_field() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table0');

                $this->fill_deftable('test_table0');

                $field = new xmldb_field('type');         $this->assertTrue($dbman->field_exists($table, 'type'));
        try {
            $dbman->drop_field($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_dependency_exception', $e);
        }
        $this->assertTrue($dbman->field_exists($table, 'type')); 
                $field = $table->getField('course');         $this->assertTrue($dbman->field_exists($table, $field));
        try {
            $dbman->drop_field($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_dependency_exception', $e);
        }
        $this->assertTrue($dbman->field_exists($table, $field)); 
                $field = new xmldb_field('nonexistingfield');
        $this->assertFalse($dbman->field_exists($table, $field));
        try {
            $dbman->drop_field($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_field_missing_exception', $e);
        }

                $field = new xmldb_field('forcesubscribe');         $this->assertTrue($dbman->field_exists($table, 'forcesubscribe'));
        $dbman->drop_field($table, $field);
        $this->assertFalse($dbman->field_exists($table, 'forcesubscribe'));

                $field = new xmldb_field('trackingtype');         $this->assertTrue($dbman->field_exists($table, $field));
        $dbman->drop_field($table, $field);
        $this->assertFalse($dbman->field_exists($table, $field));
    }

    
    public function test_change_field_type() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

                        $table = new xmldb_table('test_table_cust0');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('onenumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '2');
        $table->add_field('anothernumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '4');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('onenumber', XMLDB_INDEX_NOTUNIQUE, array('onenumber'));
        $dbman->create_table($table);

        $record = new stdClass();
        $record->onenumber = 2;
        $record->anothernumber = 4;
        $recoriginal = $DB->insert_record('test_table_cust0', $record);

                $field = new xmldb_field('onenumber');
        $field->set_attributes(XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'test');
        try {
            $dbman->change_field_type($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_dependency_exception', $e);
        }
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('I', $columns['onenumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'test');
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('C', $columns['anothernumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '8', null, XMLDB_NOTNULL, null, '5');
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('I', $columns['anothernumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, "test'n drop");
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('C', $columns['anothernumber']->meta_type);
        
                $record = new stdClass();
        $record->onenumber = 7;
        $record->anothernumber = 'string value';
        $rectodrop = $DB->insert_record('test_table_cust0', $record);
        $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '5');
        try {
            $dbman->change_field_type($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_change_structure_exception', $e);
        }
                $this->assertSame('C', $columns['anothernumber']->meta_type);
                $DB->delete_records('test_table_cust0', array('id' => $rectodrop)); 
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_FLOAT, '20,10', null, null, null, null);
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('N', $columns['anothernumber']->meta_type);         
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'test');
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('C', $columns['anothernumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '20,10', null, null, null, null);
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('N', $columns['anothernumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('I', $columns['anothernumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('X', $columns['anothernumber']->meta_type);

                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '20,10', null, null, null, null);
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('N', $columns['anothernumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('X', $columns['anothernumber']->meta_type);
        
                $field = new xmldb_field('anothernumber');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 10);
        $dbman->change_field_type($table, $field);
                $columns = $DB->get_columns('test_table_cust0');
        $this->assertSame('I', $columns['anothernumber']->meta_type);
        
                $this->assertnotEmpty($rec = $DB->get_record('test_table_cust0', array('id' => $recoriginal)));
        $this->assertEquals(4, $rec->anothernumber);

        $dbman->drop_table($table);
        $this->assertFalse($dbman->table_exists($table));
    }

    
    public function test_change_field_precision() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');

                $this->fill_deftable('test_table1');

                $field = new xmldb_field('intro');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_precision($table, $field);
        $columns = $DB->get_columns('test_table1');
                $this->assertSame('X', $columns['intro']->meta_type);
        
                $field = new xmldb_field('secondname');
        $field->set_attributes(XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_precision($table, $field);
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('C', $columns['secondname']->meta_type);
        
                $field = new xmldb_field('secondname');
        $field->set_attributes(XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        try {
            $dbman->change_field_precision($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_change_structure_exception', $e);
        }
                $columns = $DB->get_columns('test_table1');
        $this->assertSame('C', $columns['secondname']->meta_type);
        
                $field = new xmldb_field('grade');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '10,2', null, null, null, null);
        $dbman->change_field_precision($table, $field);
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('N', $columns['grade']->meta_type);
        
                $field = new xmldb_field('userid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_precision($table, $field);
        $columns = $DB->get_columns('test_table1');
        $this->assertSame('I', $columns['userid']->meta_type);
        
                $field = new xmldb_field('userid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '6', null, null, null, null);
        $dbman->change_field_precision($table, $field);
                $columns = $DB->get_columns('test_table1');
        $this->assertSame('I', $columns['userid']->meta_type);
        
                $record = new stdClass();
        $record->course = 10;
        $record->secondname  = 'third record';
        $record->intro  = 'third record';
        $record->userid = 123456;
        $DB->insert_record('test_table1', $record);
                $field = new xmldb_field('userid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        try {
            $dbman->change_field_precision($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_change_structure_exception', $e);
        }
                $columns = $DB->get_columns('test_table1');
        $this->assertSame('I', $columns['userid']->meta_type);
        
                $field = new xmldb_field('course');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        try {
            $dbman->change_field_precision($table, $field);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_dependency_exception', $e);
        }
                $columns = $DB->get_columns('test_table1');
        $this->assertSame('I', $columns['course']->meta_type);
            }

    public function testChangeFieldNullability() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = new xmldb_table('test_table_cust0');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $dbman->create_table($table);

        $record = new stdClass();
        $record->name = null;

        try {
            $result = $DB->insert_record('test_table_cust0', $record, false);
        } catch (dml_exception $e) {
            $result = false;
        }
        $this->resetDebugging();
        $this->assertFalse($result);

        $field = new xmldb_field('name');
        $field->set_attributes(XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $dbman->change_field_notnull($table, $field);

        $this->assertTrue($DB->insert_record('test_table_cust0', $record, false));

                $DB->delete_records('test_table_cust0');

        $field = new xmldb_field('name');
        $field->set_attributes(XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_notnull($table, $field);

        try {
            $result = $DB->insert_record('test_table_cust0', $record, false);
        } catch (dml_exception $e) {
            $result = false;
        }
        $this->resetDebugging();
        $this->assertFalse($result);

        $dbman->drop_table($table);
    }

    public function testChangeFieldDefault() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = new xmldb_table('test_table_cust0');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('onenumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'Moodle');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $dbman->create_table($table);

        $field = new xmldb_field('name');
        $field->set_attributes(XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'Moodle2');
        $dbman->change_field_default($table, $field);

        $record = new stdClass();
        $record->onenumber = 666;
        $id = $DB->insert_record('test_table_cust0', $record);

        $record = $DB->get_record('test_table_cust0', array('id'=>$id));
        $this->assertSame('Moodle2', $record->name);

        $field = new xmldb_field('onenumber');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 666);
        $dbman->change_field_default($table, $field);

        $record = new stdClass();
        $record->name = 'something';
        $id = $DB->insert_record('test_table_cust0', $record);

        $record = $DB->get_record('test_table_cust0', array('id'=>$id));
        $this->assertSame('666', $record->onenumber);

        $dbman->drop_table($table);
    }

    public function testAddUniqueIndex() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = new xmldb_table('test_table_cust0');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('onenumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'Moodle');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $dbman->create_table($table);

        $record = new stdClass();
        $record->onenumber = 666;
        $record->name = 'something';
        $DB->insert_record('test_table_cust0', $record, false);

        $index = new xmldb_index('onenumber-name');
        $index->set_attributes(XMLDB_INDEX_UNIQUE, array('onenumber', 'name'));
        $dbman->add_index($table, $index);

        try {
            $result = $DB->insert_record('test_table_cust0', $record, false);
        } catch (dml_exception $e) {
            $result = false;
        }
        $this->resetDebugging();
        $this->assertFalse($result);

        $dbman->drop_table($table);
    }

    public function testAddNonUniqueIndex() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $index = new xmldb_index('secondname');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('course', 'name'));
        $dbman->add_index($table, $index);
        $this->assertTrue($dbman->index_exists($table, $index));

        try {
            $dbman->add_index($table, $index);
            $this->fail('Exception expected for duplicate indexes');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

        $index = new xmldb_index('third');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('course'));
        try {
            $dbman->add_index($table, $index);
            $this->fail('Exception expected for duplicate indexes');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

        $table = new xmldb_table('test_table_cust0');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('onenumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'Moodle');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('onenumber', XMLDB_KEY_FOREIGN, array('onenumber'));

        try {
            $table->add_index('onenumber', XMLDB_INDEX_NOTUNIQUE, array('onenumber'));
            $this->fail('Coding exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

        $table = new xmldb_table('test_table_cust0');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('onenumber', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'Moodle');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('onenumber', XMLDB_INDEX_NOTUNIQUE, array('onenumber'));

        try {
            $table->add_key('onenumber', XMLDB_KEY_FOREIGN, array('onenumber'));
            $this->fail('Coding exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }

    }

    public function testFindIndexName() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $index = new xmldb_index('secondname');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('course', 'name'));
        $dbman->add_index($table, $index);

                $result = $dbman->find_index_name($table, $index);
        $this->assertTrue(!empty($result));

        $nonexistentindex = new xmldb_index('nonexistentindex');
        $nonexistentindex->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('name'));
        $this->assertFalse($dbman->find_index_name($table, $nonexistentindex));
    }

    public function testDropIndex() {
        $DB = $this->tdb; 
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $index = new xmldb_index('secondname');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('course', 'name'));
        $dbman->add_index($table, $index);

        $dbman->drop_index($table, $index);
        $this->assertFalse($dbman->find_index_name($table, $index));

                        $indexname = 'test-index-with-hyphens';
        switch ($DB->get_dbfamily()) {
            case 'mysql':
                $indexname = '`' . $indexname . '`';
                break;
            default:
                $indexname = '"' . $indexname . '"';
        }
        $stmt = "CREATE INDEX {$indexname} ON {$DB->get_prefix()}test_table1 (course, name)";
        $DB->change_database_structure($stmt);
        $this->assertNotEmpty($dbman->find_index_name($table, $index));
                $index = new xmldb_index('indexname', XMLDB_INDEX_NOTUNIQUE, array('course', 'name'));
        $dbman->drop_index($table, $index);
        $this->assertFalse($dbman->find_index_name($table, $index));
    }

    public function testAddUniqueKey() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $key = new xmldb_key('id-course-grade');
        $key->set_attributes(XMLDB_KEY_UNIQUE, array('id', 'course', 'grade'));
        $dbman->add_key($table, $key);

                $this->assertTrue(true);
    }

    public function testAddForeignUniqueKey() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $this->create_deftable('test_table0');

        $key = new xmldb_key('course');
        $key->set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('course'), 'test_table0', array('id'));
        $dbman->add_key($table, $key);

                $this->assertTrue(true);
    }

    public function testDropKey() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $this->create_deftable('test_table0');

        $key = new xmldb_key('course');
        $key->set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('course'), 'test_table0', array('id'));
        $dbman->add_key($table, $key);

        $dbman->drop_key($table, $key);

                $this->assertTrue(true);
    }

    public function testAddForeignKey() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $this->create_deftable('test_table0');

        $key = new xmldb_key('course');
        $key->set_attributes(XMLDB_KEY_FOREIGN, array('course'), 'test_table0', array('id'));
        $dbman->add_key($table, $key);

                $this->assertTrue(true);
    }

    public function testDropForeignKey() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table1');
        $this->create_deftable('test_table0');

        $key = new xmldb_key('course');
        $key->set_attributes(XMLDB_KEY_FOREIGN, array('course'), 'test_table0', array('id'));
        $dbman->add_key($table, $key);

        $dbman->drop_key($table, $key);

                $this->assertTrue(true);
    }

    public function testRenameField() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table0');
        $field = new xmldb_field('type');
        $field->set_attributes(XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'general', 'course');

        $dbman->rename_field($table, $field, 'newfieldname');

        $columns = $DB->get_columns('test_table0');

        $this->assertArrayNotHasKey('type', $columns);
        $this->assertArrayHasKey('newfieldname', $columns);
    }

    public function testIndexExists() {
            }

    public function testFindKeyName() {
        $dbman = $this->tdb->get_manager();

        $table = $this->create_deftable('test_table0');
        $key = $table->getKey('primary');

                $result = $dbman->find_key_name($table, $key);
        $this->assertTrue(!empty($result));
    }

    public function testDeleteTablesFromXmldbFile() {
        $dbman = $this->tdb->get_manager();

        $this->create_deftable('test_table1');

        $this->assertTrue($dbman->table_exists('test_table1'));

                try {
            $dbman->delete_tables_from_xmldb_file('fpsoiudfposui');
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->resetDebugging();
            $this->assertInstanceOf('moodle_exception', $e);
        }

        try {
            $dbman->delete_tables_from_xmldb_file(__DIR__ . '/fixtures/invalid.xml');
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->resetDebugging();
            $this->assertInstanceOf('moodle_exception', $e);
        }

                $this->assertTrue($dbman->table_exists('test_table1'));

                        $dbman->delete_tables_from_xmldb_file(__DIR__ . '/fixtures/xmldb_table.xml');

                $this->assertFalse($dbman->table_exists('test_table1'));
    }

    public function testInstallFromXmldbFile() {
        $dbman = $this->tdb->get_manager();

                try {
            $dbman->install_from_xmldb_file('fpsoiudfposui');
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->resetDebugging();
            $this->assertInstanceOf('moodle_exception', $e);
        }

        try {
            $dbman->install_from_xmldb_file(__DIR__ . '/fixtures/invalid.xml');
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->resetDebugging();
            $this->assertInstanceOf('moodle_exception', $e);
        }

                $this->assertFalse($dbman->table_exists('test_table1'));

                $dbman->install_from_xmldb_file(__DIR__ . '/fixtures/xmldb_table.xml');
        $this->assertTrue($dbman->table_exists('test_table1'));
    }

    public function test_temp_tables() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

                $table0 = $this->tables['test_table0'];
        $dbman->create_temp_table($table0);
        $this->assertTrue($dbman->table_exists('test_table0'));

                $dupetable = $this->tables['test_table0'];
        try {
            $dbman->create_temp_table($dupetable);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

                $dupetable = $this->tables['test_table0'];
        try {
            $dbman->create_table($dupetable);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_exception', $e);
        }

                $table1 = $this->tables['test_table1'];
        $dbman->create_temp_table($table1);
        $this->assertTrue($dbman->table_exists('test_table1'));

                $columns = $DB->get_columns('test_table1');
        $this->assertCount(11, $columns);
        $this->assertTrue($columns['name'] instanceof database_column_info);
        $this->assertEquals(30, $columns['name']->max_length);
        $this->assertTrue($columns['name']->has_default);
        $this->assertEquals('Moodle', $columns['name']->default_value);

                $inserted = $this->fill_deftable('test_table1');
        $records = $DB->get_records('test_table1');
        $this->assertCount($inserted, $records);
        $this->assertSame($records[1]->course, $this->records['test_table1'][0]->course);
        $this->assertSame($records[1]->secondname, $this->records['test_table1'][0]->secondname);
        $this->assertSame($records[2]->intro, $this->records['test_table1'][1]->intro);

                $DB->update_temp_table_stats();

                $dbman->drop_table($table1);
        $this->assertFalse($dbman->table_exists('test_table1'));

                $noetable = $this->tables['test_table1'];
        try {
            $dbman->drop_table($noetable);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('ddl_table_missing_exception', $e);
        }

                $DB->update_temp_table_stats();

        
                $dbman->drop_table($table0);
        $this->assertFalse($dbman->table_exists('test_table0'));

                $table1 = $this->tables['test_table1'];
        $dbman->create_temp_table($table1);
        $this->assertTrue($dbman->table_exists('test_table1'));

                $dbman->drop_temp_table($table1);
        $this->assertFalse($dbman->table_exists('test_table1'));
        $this->assertDebuggingCalled();

                $table1 = new xmldb_table('test_table');
        $table1->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table1->add_field('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table1->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $dbman->create_table($table1);

        $table2 = new xmldb_table('test_temp');
        $table2->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table2->add_field('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table2->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $dbman->create_temp_table($table2);

        $record = array('name' => 'a');
        $DB->insert_record('test_table', $record);
        $DB->insert_record('test_temp', $record);

        $record = array('name' => 'b');
        $DB->insert_record('test_table', $record);

        $record = array('name' => 'c');
        $DB->insert_record('test_temp', $record);

        $sql = "SELECT *
                  FROM {test_table} n
                  JOIN {test_temp} t ON t.name = n.name";
        $records = $DB->get_records_sql($sql);
        $this->assertCount(1, $records);

                $dbman->drop_table($table2);
        $this->assertFalse($dbman->table_exists('test_temp'));
    }

    public function test_concurrent_temp_tables() {
        $DB = $this->tdb;         $dbman = $this->tdb->get_manager();

                $record1 = (object)array(
            'course'     =>  1,
            'secondname' => '11 important',
            'intro'      => '111 important');
        $record2 = (object)array(
            'course'     =>  2,
            'secondname' => '22 important',
            'intro'      => '222 important');

                $table = $this->tables['test_table1'];
        $dbman->create_temp_table($table);
        $this->assertTrue($dbman->table_exists('test_table1'));
        $inserted = $DB->insert_record('test_table1', $record1);

                $cfg = $DB->export_dbconfig();
        if (!isset($cfg->dboptions)) {
            $cfg->dboptions = array();
        }
        $DB2 = moodle_database::get_driver_instance($cfg->dbtype, $cfg->dblibrary);
        $DB2->connect($cfg->dbhost, $cfg->dbuser, $cfg->dbpass, $cfg->dbname, $cfg->prefix, $cfg->dboptions);
        $dbman2 = $DB2->get_manager();
        $this->assertFalse($dbman2->table_exists('test_table1')); 
                $table = $this->tables['test_table1'];
        $dbman2->create_temp_table($table);
        $this->assertTrue($dbman2->table_exists('test_table1'));
        $inserted = $DB2->insert_record('test_table1', $record2);

        $dbman2->drop_table($table);         $this->assertFalse($dbman2->table_exists('test_table1'));
        $DB2->dispose(); 
        $this->assertTrue($dbman->table_exists('test_table1'));         $dbman->drop_table($table);         $this->assertFalse($dbman->table_exists('test_table1'));
    }

    public function test_reset_sequence() {
        $DB = $this->tdb;
        $dbman = $DB->get_manager();

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $tablename = $table->getName();
        $this->tables[$tablename] = $table;

        $record = (object)array('id'=>666, 'course'=>10);
        $DB->import_record('testtable', $record);
        $DB->delete_records('testtable'); 
        $dbman->reset_sequence($table);         $this->assertEquals(1, $DB->insert_record('testtable', (object)array('course'=>13)));

        $record = (object)array('id'=>666, 'course'=>10);
        $DB->import_record('testtable', $record);
        $DB->delete_records('testtable', array()); 
        $dbman->reset_sequence($table);         $this->assertEquals(1, $DB->insert_record('testtable', (object)array('course'=>13)),
            'Some versions of MySQL 5.6.x are known to not support lowering of auto-increment numbers.');

        $DB->import_record('testtable', $record);
        $dbman->reset_sequence($tablename);         $this->assertEquals(667, $DB->insert_record('testtable', (object)array('course'=>13)));

        $dbman->drop_table($table);
    }

    public function test_reserved_words() {
        $reserved = sql_generator::getAllReservedWords();
        $this->assertTrue(count($reserved) > 1);
    }

    public function test_index_hints() {
        $DB = $this->tdb;
        $dbman = $DB->get_manager();

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table->add_field('path', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, array('name'), array('xxxx,yyyy'));
        $table->add_index('path', XMLDB_INDEX_NOTUNIQUE, array('path'), array('varchar_pattern_ops'));

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $tablename = $table->getName();
        $this->tables[$tablename] = $table;

        $table = new xmldb_table('testtable');
        $index = new xmldb_index('name', XMLDB_INDEX_NOTUNIQUE, array('name'), array('xxxx,yyyy'));
        $this->assertTrue($dbman->index_exists($table, $index));

        $table = new xmldb_table('testtable');
        $index = new xmldb_index('path', XMLDB_INDEX_NOTUNIQUE, array('path'), array('varchar_pattern_ops'));
        $this->assertTrue($dbman->index_exists($table, $index));

                $dbman->drop_table($this->tables[$tablename]);

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('path', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('path', XMLDB_INDEX_UNIQUE, array('path'), array('varchar_pattern_ops'));
        $dbman->create_table($table);
        $this->tables[$tablename] = $table;

        $table = new xmldb_table('testtable');
        $index = new xmldb_index('path', XMLDB_INDEX_UNIQUE, array('path'), array('varchar_pattern_ops'));
        $this->assertTrue($dbman->index_exists($table, $index));
    }

    public function test_index_max_bytes() {
        $DB = $this->tdb;
        $dbman = $DB->get_manager();

        $maxstr = '';
        for ($i=0; $i<255; $i++) {
            $maxstr .= '言';         }

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, array('name'));

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $tablename = $table->getName();
        $this->tables[$tablename] = $table;

        $rec = new stdClass();
        $rec->name = $maxstr;

        $id = $DB->insert_record($tablename, $rec);
        $this->assertTrue(!empty($id));

        $rec = $DB->get_record($tablename, array('id'=>$id));
        $this->assertSame($maxstr, $rec->name);

        $dbman->drop_table($table);

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255+1, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, array('name'));

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
    }

    public function test_index_composed_max_bytes() {
        $DB = $this->tdb;
        $dbman = $DB->get_manager();

        $maxstr = '';
        for ($i=0; $i<200; $i++) {
            $maxstr .= '言';
        }
        $reststr = '';
        for ($i=0; $i<133; $i++) {
            $reststr .= '言';
        }

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name1', XMLDB_TYPE_CHAR, 200, null, XMLDB_NOTNULL, null);
        $table->add_field('name2', XMLDB_TYPE_CHAR, 133, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('name1-name2', XMLDB_INDEX_NOTUNIQUE, array('name1', 'name2'));

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $tablename = $table->getName();
        $this->tables[$tablename] = $table;

        $rec = new stdClass();
        $rec->name1 = $maxstr;
        $rec->name2 = $reststr;

        $id = $DB->insert_record($tablename, $rec);
        $this->assertTrue(!empty($id));

        $rec = $DB->get_record($tablename, array('id'=>$id));
        $this->assertSame($maxstr, $rec->name1);
        $this->assertSame($reststr, $rec->name2);

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name1', XMLDB_TYPE_CHAR, 201, null, XMLDB_NOTNULL, null);
        $table->add_field('name2', XMLDB_TYPE_CHAR, 133, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('name1-name2', XMLDB_INDEX_NOTUNIQUE, array('name1', 'name2'));

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
    }

    public function test_char_size_limit() {
        $DB = $this->tdb;
        $dbman = $DB->get_manager();

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, xmldb_field::CHAR_MAX_LENGTH, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $tablename = $table->getName();
        $this->tables[$tablename] = $table;

                $maxstr = '';
        for ($i=0; $i<xmldb_field::CHAR_MAX_LENGTH; $i++) {
            $maxstr .= 'a';         }

        $rec = new stdClass();
        $rec->name = $maxstr;

        $id = $DB->insert_record($tablename, $rec);
        $this->assertTrue(!empty($id));

        $rec = $DB->get_record($tablename, array('id'=>$id));
        $this->assertSame($maxstr, $rec->name);

                $maxstr = '';
        for ($i=0; $i<xmldb_field::CHAR_MAX_LENGTH; $i++) {
            $maxstr .= '言';         }

        $rec = new stdClass();
        $rec->name = $maxstr;

        try {
            $id = $DB->insert_record($tablename, $rec);
            $this->assertTrue(!empty($id));

            $rec = $DB->get_record($tablename, array('id'=>$id));
            $this->assertSame($maxstr, $rec->name);
        } catch (dml_exception $e) {
            if ($DB->get_dbfamily() === 'oracle') {
                $this->fail('Oracle does not support text fields larger than 4000 bytes, this is not a big problem for mostly ascii based languages');
            } else {
                throw $e;
            }
        }

        $table = new xmldb_table('testtable');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, xmldb_field::CHAR_MAX_LENGTH+1, null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $tablename = $table->getName();
        $this->tables[$tablename] = $table;

        try {
            $dbman->create_table($table);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
    }

    public function test_object_name() {
        $gen = $this->tdb->get_manager()->generator;

                $table = 'tablename';
        $fields = 'id';
        $suffix = 'pk';
        for ($i=0; $i<12; $i++) {
            $this->assertLessThanOrEqual($gen->names_max_length,
                    strlen($gen->getNameForObject($table, $fields, $suffix)),
                    'Generated object name is too long. $i = '.$i);
        }

                $table = 'aaaa_bbbb_cccc_dddd_eeee_ffff_gggg';
        $fields = 'aaaaa,bbbbb,ccccc,ddddd';
        $suffix = 'idx';
        for ($i=0; $i<12; $i++) {
            $this->assertEquals($gen->names_max_length,
                    strlen($gen->getNameForObject($table, $fields, $suffix)),
                    'Generated object name is too long. $i = '.$i);
        }

                $table = 'bbbb_cccc_dddd_eeee_ffff_gggg_hhhh';
        $fields = 'aaaaa,bbbbb,ccccc,ddddd';
        $suffix = '';
        for ($i=0; $i<12; $i++) {
            $this->assertEquals($gen->names_max_length,
                    strlen($gen->getNameForObject($table, $fields, $suffix)),
                    'Generated object name is too long. $i = '.$i);
        }

                $table = 'cccc_dddd_eeee_ffff_gggg_hhhh_iiii';
        $fields = 'id';
        $suffix = 'idx';
                        while (strlen($table) && strlen($gen->prefix.preg_replace('/_/','',$table).'_id_'.$suffix) >= $gen->names_max_length) {
            $table = rtrim(substr($table, 0, strlen($table) - 1), '_');
        }
        if (strlen($table)) {
            $this->assertEquals($gen->names_max_length - 1,
                        strlen($gen->getNameForObject($table, $fields, $suffix)));
            for ($i=0; $i<12; $i++) {
                $this->assertEquals($gen->names_max_length,
                        strlen($gen->getNameForObject($table, $fields, $suffix)),
                        'Generated object name is too long. $i = '.$i);
            }
        }
    }

        

}
