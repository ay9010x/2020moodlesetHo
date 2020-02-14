<?php



defined('MOODLE_INTERNAL') || die();



define('ACTION_NONE',             0);

define('ACTION_GENERATE_HTML',    1);

define('ACTION_GENERATE_XML',     2);

define('ACTION_HAVE_SUBACTIONS',  1);


define ('XMLDB_TYPE_INCORRECT',   0);

define ('XMLDB_TYPE_INTEGER',     1);

define ('XMLDB_TYPE_NUMBER',      2);

define ('XMLDB_TYPE_FLOAT',       3);

define ('XMLDB_TYPE_CHAR',        4);

define ('XMLDB_TYPE_TEXT',        5);

define ('XMLDB_TYPE_BINARY',      6);

define ('XMLDB_TYPE_DATETIME',    7);

define ('XMLDB_TYPE_TIMESTAMP',   8);


define ('XMLDB_KEY_INCORRECT',     0);

define ('XMLDB_KEY_PRIMARY',       1);

define ('XMLDB_KEY_UNIQUE',        2);

define ('XMLDB_KEY_FOREIGN',       3);

define ('XMLDB_KEY_CHECK',         4);

define ('XMLDB_KEY_FOREIGN_UNIQUE',5);


define ('XMLDB_UNSIGNED',        true);

define ('XMLDB_NOTNULL',         true);

define ('XMLDB_SEQUENCE',        true);

define ('XMLDB_INDEX_UNIQUE',    true);

define ('XMLDB_INDEX_NOTUNIQUE',false);


define ('XMLDB_LINEFEED', "\n");

define ('XMLDB_PHP_HEADER', '    if ($oldversion < XXXXXXXXXX) {' . XMLDB_LINEFEED);

define ('XMLDB_PHP_FOOTER', '    }' . XMLDB_LINEFEED);
