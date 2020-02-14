<?php


defined('MOODLE_INTERNAL') || die();


class core_phpunit_util_testcase extends advanced_testcase {
    
    public function test_set_table_modified_by_sql($sql, $expectations) {
        phpunit_util::reset_updated_table_list();
        phpunit_util::set_table_modified_by_sql($sql);
        foreach ($expectations as $table => $present) {
            $this->assertEquals($present, !empty(phpunit_util::$tableupdated[$table]));
        }
    }

    public function set_table_modified_by_sql_provider() {
        global $DB;
        $prefix = $DB->get_prefix();

        return array(
            'Basic update' => array(
                'sql'           => "UPDATE {$prefix}user SET username = username || '_test'",
                'expectations'  => array(
                    'user'      => true,
                    'course'    => false,
                ),
            ),
            'Basic update with a fieldname sharing the same prefix' => array(
                'sql'           => "UPDATE {$prefix}user SET {$prefix}username = username || '_test'",
                'expectations'  => array(
                    'user'      => true,
                    'course'    => false,
                ),
            ),
            'Basic update with a table which contains the prefix' => array(
                'sql'           => "UPDATE {$prefix}user{$prefix} SET username = username || '_test'",
                'expectations'  => array(
                    "user{$prefix}" => true,
                    'course'        => false,
                ),
            ),
            'Update table with a numeric name' => array(
                'sql'           => "UPDATE {$prefix}example42 SET username = username || '_test'",
                'expectations'  => array(
                    'example42' => true,
                    'user'      => false,
                    'course'    => false,
                ),
            ),
            'Drop basic table' => array(
                'sql'           => "DROP TABLE {$prefix}user",
                'expectations'  => array(
                    'user'      => true,
                    'course'    => false,
                ),
            ),
            'Drop table with a numeric name' => array(
                'sql'           => "DROP TABLE {$prefix}example42",
                'expectations'  => array(
                    'example42' => true,
                    'user'      => false,
                    'course'    => false,
                ),
            ),
            'Insert in table' => array(
                'sql'           => "INSERT INTO {$prefix}user (username,password) VALUES ('moodle', 'test')",
                'expectations'  => array(
                    'user'      => true,
                    'course'    => false,
                ),
            ),
        );
    }
}
