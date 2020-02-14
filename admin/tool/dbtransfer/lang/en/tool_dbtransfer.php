<?php



$string['clidriverlist'] = 'Available database drivers for migration';
$string['cliheading'] = 'Database migration - make sure nobody is accessing the server during migration!';
$string['climigrationnotice'] = 'Database migration in progress, please wait until the migration completes and server administrator updates configuration and deletes the $CFG->dataroot/climaintenance.html file.';
$string['convertinglogdisplay'] = 'Converting log display actions';
$string['dbexport'] = 'Database export';
$string['dbtransfer'] = 'Database migration';
$string['enablemaintenance'] = 'Enable maintenance mode';
$string['enablemaintenance_help'] = 'This option enables maintanance mode during and after the database migration, it prevents access of all users until the migration is completed. Please note that administrator has to manually delete $CFG->dataroot/climaintenance.html file after updating config.php settings to resume normal operation.';
$string['exportdata'] = 'Export data';
$string['notargetconectexception'] = 'Can not connect target database, sorry.';
$string['options'] = 'Options';
$string['pluginname'] = 'Database transfer';
$string['targetdatabase'] = 'Target database';
$string['targetdatabasenotempty'] = 'Target database must not contain any tables with given prefix!';
$string['transferdata'] = 'Transfer data';
$string['transferdbintro'] = 'This script will transfer the entire contents of this database to another database server. It is often used for migration of data to different database type.';
$string['transferdbtoserver'] = 'Transfer this Moodle database to another server';
$string['transferringdbto'] = 'Transferring this {$a->dbtypefrom} database to {$a->dbtype} database "{$a->dbname}" on "{$a->dbhost}"';

