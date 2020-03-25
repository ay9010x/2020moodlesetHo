<?php



function _file_get_contents($file)
{
 	if (function_exists('file_get_contents')) return file_get_contents($file);

	$f = fopen($file,'r');
	if (!$f) return '';
	$t = '';

	while ($s = fread($f,100000)) $t .= $s;
	fclose($f);
	return $t;
}



if( !defined( 'XMLS_DEBUG' ) ) {
	define( 'XMLS_DEBUG', FALSE );
}


if( !defined( 'XMLS_PREFIX' ) ) {
	define( 'XMLS_PREFIX', '%%P' );
}


if( !defined( 'XMLS_PREFIX_MAXLEN' ) ) {
	define( 'XMLS_PREFIX_MAXLEN', 10 );
}


if( !defined( 'XMLS_EXECUTE_INLINE' ) ) {
	define( 'XMLS_EXECUTE_INLINE', FALSE );
}


if( !defined( 'XMLS_CONTINUE_ON_ERROR' ) ) {
	define( 'XMLS_CONTINUE_ON_ERROR', FALSE );
}


if( !defined( 'XMLS_SCHEMA_VERSION' ) ) {
	define( 'XMLS_SCHEMA_VERSION', '0.3' );
}


if( !defined( 'XMLS_DEFAULT_SCHEMA_VERSION' ) ) {
	define( 'XMLS_DEFAULT_SCHEMA_VERSION', '0.1' );
}


if( !defined( 'XMLS_MODE_INSERT' ) ) {
	define( 'XMLS_MODE_INSERT', 0 );
}
if( !defined( 'XMLS_MODE_UPDATE' ) ) {
	define( 'XMLS_MODE_UPDATE', 1 );
}
if( !defined( 'XMLS_MODE_IGNORE' ) ) {
	define( 'XMLS_MODE_IGNORE', 2 );
}
if( !defined( 'XMLS_EXISTING_DATA' ) ) {
	define( 'XMLS_EXISTING_DATA', XMLS_MODE_INSERT );
}


if( !defined( 'XMLS_DEFAULT_UPGRADE_METHOD' ) ) {
	define( 'XMLS_DEFAULT_UPGRADE_METHOD', 'ALTER' );
}


if( !defined( '_ADODB_LAYER' ) ) {
	require( 'adodb.inc.php' );
	require( 'adodb-datadict.inc.php' );
}


class dbObject {

	
	var $parent;

	
	var $currentElement;

	
	function __construct( &$parent, $attributes = NULL ) {
		$this->parent = $parent;
	}

	
	function _tag_open( &$parser, $tag, $attributes ) {

	}

	
	function _tag_cdata( &$parser, $cdata ) {

	}

	
	function _tag_close( &$parser, $tag ) {

	}

	function create(&$xmls) {
		return array();
	}

	
	function destroy() {
		unset( $this );
	}

	
	function supportedPlatform( $platform = NULL ) {
		return is_object( $this->parent ) ? $this->parent->supportedPlatform( $platform ) : TRUE;
	}

	
	function prefix( $name = '' ) {
		return is_object( $this->parent ) ? $this->parent->prefix( $name ) : $name;
	}

	
	function FieldID( $field ) {
		return strtoupper( preg_replace( '/^`(.+)`$/', '$1', $field ) );
	}
}


class dbTable extends dbObject {

	
	var $name;

	
	var $fields = array();

	
	var $indexes = array();

	
	var $opts = array();

	
	var $current_field;

	
	var $drop_table;

	
	var $drop_field = array();

	
	var $currentPlatform = true;


	
	function __construct( &$parent, $attributes = NULL ) {
		$this->parent = $parent;
		$this->name = $this->prefix($attributes['NAME']);
	}

	
	function _tag_open( &$parser, $tag, $attributes ) {
		$this->currentElement = strtoupper( $tag );

		switch( $this->currentElement ) {
			case 'INDEX':
				if( !isset( $attributes['PLATFORM'] ) OR $this->supportedPlatform( $attributes['PLATFORM'] ) ) {
					xml_set_object( $parser, $this->addIndex( $attributes ) );
				}
				break;
			case 'DATA':
				if( !isset( $attributes['PLATFORM'] ) OR $this->supportedPlatform( $attributes['PLATFORM'] ) ) {
					xml_set_object( $parser, $this->addData( $attributes ) );
				}
				break;
			case 'DROP':
				$this->drop();
				break;
			case 'FIELD':
								$fieldName = $attributes['NAME'];
				$fieldType = $attributes['TYPE'];
				$fieldSize = isset( $attributes['SIZE'] ) ? $attributes['SIZE'] : NULL;
				$fieldOpts = !empty( $attributes['OPTS'] ) ? $attributes['OPTS'] : NULL;

				$this->addField( $fieldName, $fieldType, $fieldSize, $fieldOpts );
				break;
			case 'KEY':
			case 'NOTNULL':
			case 'AUTOINCREMENT':
			case 'DEFDATE':
			case 'DEFTIMESTAMP':
			case 'UNSIGNED':
								$this->addFieldOpt( $this->current_field, $this->currentElement );
				break;
			case 'DEFAULT':
				
								if( $attributes['VALUE'] == '' ) {
					$attributes['VALUE'] = " '' ";
				}

				$this->addFieldOpt( $this->current_field, $this->currentElement, $attributes['VALUE'] );
				break;
			case 'OPT':
			case 'CONSTRAINT':
								$this->currentPlatform = ( !isset( $attributes['PLATFORM'] ) OR $this->supportedPlatform( $attributes['PLATFORM'] ) );
				break;
			default:
						}
	}

	
	function _tag_cdata( &$parser, $cdata ) {
		switch( $this->currentElement ) {
						case 'CONSTRAINT':
				if( isset( $this->current_field ) ) {
					$this->addFieldOpt( $this->current_field, $this->currentElement, $cdata );
				} else {
					$this->addTableOpt( $cdata );
				}
				break;
						case 'OPT':
				if( isset( $this->current_field ) ) {
					$this->addFieldOpt( $this->current_field, $cdata );
				} else {
				$this->addTableOpt( $cdata );
				}
				break;
			default:

		}
	}

	
	function _tag_close( &$parser, $tag ) {
		$this->currentElement = '';

		switch( strtoupper( $tag ) ) {
			case 'TABLE':
				$this->parent->addSQL( $this->create( $this->parent ) );
				xml_set_object( $parser, $this->parent );
				$this->destroy();
				break;
			case 'FIELD':
				unset($this->current_field);
				break;
			case 'OPT':
			case 'CONSTRAINT':
				$this->currentPlatform = true;
				break;
			default:

		}
	}

	
	function addIndex( $attributes ) {
		$name = strtoupper( $attributes['NAME'] );
		$this->indexes[$name] = new dbIndex( $this, $attributes );
		return $this->indexes[$name];
	}

	
	function addData( $attributes ) {
		if( !isset( $this->data ) ) {
			$this->data = new dbData( $this, $attributes );
		}
		return $this->data;
	}

	
	function addField( $name, $type, $size = NULL, $opts = NULL ) {
		$field_id = $this->FieldID( $name );

				$this->current_field = $field_id;

				$this->fields[$field_id]['NAME'] = $name;

				$this->fields[$field_id]['TYPE'] = $type;

				if( isset( $size ) ) {
			$this->fields[$field_id]['SIZE'] = $size;
		}

				if( isset( $opts ) ) {
			$this->fields[$field_id]['OPTS'] = array($opts);
		} else {
			$this->fields[$field_id]['OPTS'] = array();
		}
	}

	
	function addFieldOpt( $field, $opt, $value = NULL ) {
		if( $this->currentPlatform ) {
		if( !isset( $value ) ) {
			$this->fields[$this->FieldID( $field )]['OPTS'][] = $opt;
				} else {
			$this->fields[$this->FieldID( $field )]['OPTS'][] = array( $opt => $value );
		}
	}
	}

	
	function addTableOpt( $opt ) {
		if(isset($this->currentPlatform)) {
			$this->opts[$this->parent->db->databaseType] = $opt;
		}
		return $this->opts;
	}


	
	function create( &$xmls ) {
		$sql = array();

				if( is_array( $legacy_indexes = $xmls->dict->MetaIndexes( $this->name ) ) ) {
			foreach( $legacy_indexes as $index => $index_details ) {
				$sql[] = $xmls->dict->DropIndexSQL( $index, $this->name );
			}
		}

				foreach( $this->drop_field as $field ) {
			unset( $this->fields[$field] );
		}

				if( is_array( $legacy_fields = $xmls->dict->MetaColumns( $this->name ) ) ) {
						if( $this->drop_table ) {
				$sql[] = $xmls->dict->DropTableSQL( $this->name );

				return $sql;
			}

						foreach( $legacy_fields as $field_id => $field ) {
				if( !isset( $this->fields[$field_id] ) ) {
					$sql[] = $xmls->dict->DropColumnSQL( $this->name, $field->name );
				}
			}
				} else {
			if( $this->drop_table ) {
				return $sql;
			}

			$legacy_fields = array();
		}

				$fldarray = array();

		foreach( $this->fields as $field_id => $finfo ) {
						if( !isset( $finfo['SIZE'] ) ) {
				$finfo['SIZE'] = '';
			}

						$fldarray[$field_id] = array(
				'NAME' => $finfo['NAME'],
				'TYPE' => $finfo['TYPE'],
				'SIZE' => $finfo['SIZE']
			);

						if( isset( $finfo['OPTS'] ) ) {
				foreach( $finfo['OPTS'] as $opt ) {
										if( is_array( $opt ) ) {
						$key = key( $opt );
						$value = $opt[key( $opt )];
						@$fldarray[$field_id][$key] .= $value;
										} else {
						$fldarray[$field_id][$opt] = $opt;
					}
				}
			}
		}

		if( empty( $legacy_fields ) ) {
						$sql[] = $xmls->dict->CreateTableSQL( $this->name, $fldarray, $this->opts );
			logMsg( end( $sql ), 'Generated CreateTableSQL' );
		} else {
						logMsg( "Upgrading {$this->name} using '{$xmls->upgrade}'" );
			switch( $xmls->upgrade ) {
								case 'ALTER':
					logMsg( 'Generated ChangeTableSQL (ALTERing table)' );
					$sql[] = $xmls->dict->ChangeTableSQL( $this->name, $fldarray, $this->opts );
					break;
				case 'REPLACE':
					logMsg( 'Doing upgrade REPLACE (testing)' );
					$sql[] = $xmls->dict->DropTableSQL( $this->name );
					$sql[] = $xmls->dict->CreateTableSQL( $this->name, $fldarray, $this->opts );
					break;
								default:
					return array();
			}
		}

		foreach( $this->indexes as $index ) {
			$sql[] = $index->create( $xmls );
		}

		if( isset( $this->data ) ) {
			$sql[] = $this->data->create( $xmls );
		}

		return $sql;
	}

	
	function drop() {
		if( isset( $this->current_field ) ) {
						logMsg( "Dropping field '{$this->current_field}' from table '{$this->name}'" );
						$this->drop_field[$this->current_field] = $this->current_field;
		} else {
						logMsg( "Dropping table '{$this->name}'" );
						$this->drop_table = TRUE;
		}
	}
}


class dbIndex extends dbObject {

	
	var $name;

	
	var $opts = array();

	
	var $columns = array();

	
	var $drop = FALSE;

	
	function __construct( &$parent, $attributes = NULL ) {
		$this->parent = $parent;

		$this->name = $this->prefix ($attributes['NAME']);
	}

	
	function _tag_open( &$parser, $tag, $attributes ) {
		$this->currentElement = strtoupper( $tag );

		switch( $this->currentElement ) {
			case 'DROP':
				$this->drop();
				break;
			case 'CLUSTERED':
			case 'BITMAP':
			case 'UNIQUE':
			case 'FULLTEXT':
			case 'HASH':
								$this->addIndexOpt( $this->currentElement );
				break;
			default:
						}
	}

	
	function _tag_cdata( &$parser, $cdata ) {
		switch( $this->currentElement ) {
						case 'COL':
				$this->addField( $cdata );
				break;
			default:

		}
	}

	
	function _tag_close( &$parser, $tag ) {
		$this->currentElement = '';

		switch( strtoupper( $tag ) ) {
			case 'INDEX':
				xml_set_object( $parser, $this->parent );
				break;
		}
	}

	
	function addField( $name ) {
		$this->columns[$this->FieldID( $name )] = $name;

				return $this->columns;
	}

	
	function addIndexOpt( $opt ) {
		$this->opts[] = $opt;

				return $this->opts;
	}

	
	function create( &$xmls ) {
		if( $this->drop ) {
			return NULL;
		}

				foreach( $this->columns as $id => $col ) {
			if( !isset( $this->parent->fields[$id] ) ) {
				unset( $this->columns[$id] );
			}
		}

		return $xmls->dict->CreateIndexSQL( $this->name, $this->parent->name, $this->columns, $this->opts );
	}

	
	function drop() {
		$this->drop = TRUE;
	}
}


class dbData extends dbObject {

	var $data = array();

	var $row;

	
	function __construct( &$parent, $attributes = NULL ) {
		$this->parent = $parent;
	}

	
	function _tag_open( &$parser, $tag, $attributes ) {
		$this->currentElement = strtoupper( $tag );

		switch( $this->currentElement ) {
			case 'ROW':
				$this->row = count( $this->data );
				$this->data[$this->row] = array();
				break;
			case 'F':
				$this->addField($attributes);
			default:
						}
	}

	
	function _tag_cdata( &$parser, $cdata ) {
		switch( $this->currentElement ) {
						case 'F':
				$this->addData( $cdata );
				break;
			default:

		}
	}

	
	function _tag_close( &$parser, $tag ) {
		$this->currentElement = '';

		switch( strtoupper( $tag ) ) {
			case 'DATA':
				xml_set_object( $parser, $this->parent );
				break;
		}
	}

	
	function addField( $attributes ) {
				if( !isset( $this->row ) || !isset( $this->data[$this->row] ) ) {
			return;
		}

				if( isset( $attributes['NAME'] ) ) {
			$this->current_field = $this->FieldID( $attributes['NAME'] );
		} else {
			$this->current_field = count( $this->data[$this->row] );
		}

				if( !isset( $this->data[$this->row][$this->current_field] ) ) {
			$this->data[$this->row][$this->current_field] = '';
		}
	}

	
	function addData( $cdata ) {
				if ( isset( $this->data[$this->row][$this->current_field] ) ) {
						$this->data[$this->row][$this->current_field] .= $cdata;
		}
	}

	
	function create( &$xmls ) {
		$table = $xmls->dict->TableName($this->parent->name);
		$table_field_count = count($this->parent->fields);
		$tables = $xmls->db->MetaTables();
		$sql = array();

		$ukeys = $xmls->db->MetaPrimaryKeys( $table );
		if( !empty( $this->parent->indexes ) and !empty( $ukeys ) ) {
			foreach( $this->parent->indexes as $indexObj ) {
				if( !in_array( $indexObj->name, $ukeys ) ) $ukeys[] = $indexObj->name;
			}
		}

				foreach( $this->data as $row ) {
			$table_fields = $this->parent->fields;
			$fields = array();
			$rawfields = array(); 
			foreach( $row as $field_id => $field_data ) {
				if( !array_key_exists( $field_id, $table_fields ) ) {
					if( is_numeric( $field_id ) ) {
						$field_id = reset( array_keys( $table_fields ) );
					} else {
						continue;
					}
				}

				$name = $table_fields[$field_id]['NAME'];

				switch( $table_fields[$field_id]['TYPE'] ) {
					case 'I':
					case 'I1':
					case 'I2':
					case 'I4':
					case 'I8':
						$fields[$name] = intval($field_data);
						break;
					case 'C':
					case 'C2':
					case 'X':
					case 'X2':
					default:
						$fields[$name] = $xmls->db->qstr( $field_data );
						$rawfields[$name] = $field_data;
				}

				unset($table_fields[$field_id]);

			}

						if( empty( $fields ) ) {
				continue;
			}

						if( count( $fields ) < $table_field_count ) {
				foreach( $table_fields as $field ) {
					if( isset( $field['OPTS'] ) and ( in_array( 'NOTNULL', $field['OPTS'] ) || in_array( 'KEY', $field['OPTS'] ) ) && !in_array( 'AUTOINCREMENT', $field['OPTS'] ) ) {
							continue(2);
						}
				}
			}

			
			if( !in_array( $table, $tables ) or ( $mode = $xmls->existingData() ) == XMLS_MODE_INSERT ) {
								logMsg( "$table doesn't exist, inserting or mode is INSERT" );
			$sql[] = 'INSERT INTO '. $table .' ('. implode( ',', array_keys( $fields ) ) .') VALUES ('. implode( ',', $fields ) .')';
				continue;
		}

						$mfields = array_merge( $fields, $rawfields );
			$keyFields = array_intersect( $ukeys, array_keys( $mfields ) );

			if( empty( $ukeys ) or count( $keyFields ) == 0 ) {
								logMsg( "Either schema or data has no unique keys, so safe to insert" );
				$sql[] = 'INSERT INTO '. $table .' ('. implode( ',', array_keys( $fields ) ) .') VALUES ('. implode( ',', $fields ) .')';
				continue;
			}

						$where = '';
			foreach( $ukeys as $key ) {
				if( isset( $mfields[$key] ) and $mfields[$key] ) {
					if( $where ) $where .= ' AND ';
					$where .= $key . ' = ' . $xmls->db->qstr( $mfields[$key] );
				}
			}
			$records = $xmls->db->Execute( 'SELECT * FROM ' . $table . ' WHERE ' . $where );
			switch( $records->RecordCount() ) {
				case 0:
										logMsg( "No matching records. Inserting new row with unique data" );
					$sql[] = $xmls->db->GetInsertSQL( $records, $mfields );
					break;
				case 1:
										logMsg( "One matching record..." );
					if( $mode == XMLS_MODE_UPDATE ) {
						logMsg( "...Updating existing row from unique data" );
						$sql[] = $xmls->db->GetUpdateSQL( $records, $mfields );
					}
					break;
				default:
										logMsg( "More than one matching record. Ignoring row." );
			}
		}
		return $sql;
	}
}


class dbQuerySet extends dbObject {

	
	var $queries = array();

	
	var $query;

	
	var $prefixKey = '';

	
	var $prefixMethod = 'AUTO';

	
	function __construct( &$parent, $attributes = NULL ) {
		$this->parent = $parent;

				if( isset( $attributes['KEY'] ) ) {
			$this->prefixKey = $attributes['KEY'];
		}

		$prefixMethod = isset( $attributes['PREFIXMETHOD'] ) ? strtoupper( trim( $attributes['PREFIXMETHOD'] ) ) : '';

				switch( $prefixMethod ) {
			case 'AUTO':
				$this->prefixMethod = 'AUTO';
				break;
			case 'MANUAL':
				$this->prefixMethod = 'MANUAL';
				break;
			case 'NONE':
				$this->prefixMethod = 'NONE';
				break;
		}
	}

	
	function _tag_open( &$parser, $tag, $attributes ) {
		$this->currentElement = strtoupper( $tag );

		switch( $this->currentElement ) {
			case 'QUERY':
																if( !isset( $attributes['PLATFORM'] ) OR $this->supportedPlatform( $attributes['PLATFORM'] ) ) {
					$this->newQuery();
				} else {
					$this->discardQuery();
				}
				break;
			default:
						}
	}

	
	function _tag_cdata( &$parser, $cdata ) {
		switch( $this->currentElement ) {
						case 'QUERY':
				$this->buildQuery( $cdata );
				break;
			default:

		}
	}

	
	function _tag_close( &$parser, $tag ) {
		$this->currentElement = '';

		switch( strtoupper( $tag ) ) {
			case 'QUERY':
								$this->addQuery();
				break;
			case 'SQL':
				$this->parent->addSQL( $this->create( $this->parent ) );
				xml_set_object( $parser, $this->parent );
				$this->destroy();
				break;
			default:

		}
	}

	
	function newQuery() {
		$this->query = '';

		return TRUE;
	}

	
	function discardQuery() {
		unset( $this->query );

		return TRUE;
	}

	
	function buildQuery( $sql = NULL ) {
		if( !isset( $this->query ) OR empty( $sql ) ) {
			return FALSE;
		}

		$this->query .= $sql;

		return $this->query;
	}

	
	function addQuery() {
		if( !isset( $this->query ) ) {
			return FALSE;
		}

		$this->queries[] = $return = trim($this->query);

		unset( $this->query );

		return $return;
	}

	
	function create( &$xmls ) {
		foreach( $this->queries as $id => $query ) {
			switch( $this->prefixMethod ) {
				case 'AUTO':
					
															$query = $this->prefixQuery( '/^\s*((?is)INSERT\s+(INTO\s+)?)((\w+\s*,?\s*)+)(\s.*$)/', $query, $xmls->objectPrefix );
					$query = $this->prefixQuery( '/^\s*((?is)UPDATE\s+(FROM\s+)?)((\w+\s*,?\s*)+)(\s.*$)/', $query, $xmls->objectPrefix );
					$query = $this->prefixQuery( '/^\s*((?is)DELETE\s+(FROM\s+)?)((\w+\s*,?\s*)+)(\s.*$)/', $query, $xmls->objectPrefix );

										
				case 'MANUAL':
															if( isset( $this->prefixKey ) AND( $this->prefixKey !== '' ) ) {
												$query = str_replace( $this->prefixKey, $xmls->objectPrefix, $query );
					} else {
												$query = str_replace( XMLS_PREFIX , $xmls->objectPrefix, $query );
					}
			}

			$this->queries[$id] = trim( $query );
		}

				return $this->queries;
	}

	
	function prefixQuery( $regex, $query, $prefix = NULL ) {
		if( !isset( $prefix ) ) {
			return $query;
		}

		if( preg_match( $regex, $query, $match ) ) {
			$preamble = $match[1];
			$postamble = $match[5];
			$objectList = explode( ',', $match[3] );
			
			$prefixedList = '';

			foreach( $objectList as $object ) {
				if( $prefixedList !== '' ) {
					$prefixedList .= ', ';
				}

				$prefixedList .= $prefix . trim( $object );
			}

			$query = $preamble . ' ' . $prefixedList . ' ' . $postamble;
		}

		return $query;
	}
}


class adoSchema {

	
	var $sqlArray;

	
	var $db;

	
	var $dict;

	
	var $currentElement = '';

	
	var $upgrade = '';

	
	var $objectPrefix = '';

	
	var $mgq;

	
	var $debug;

	
	var $versionRegex = '/<schema.*?( version="([^"]*)")?.*?>/';

	
	var $schemaVersion;

	
	var $success;

	
	var $executeInline;

	
	var $continueOnError;

	
	var $existingData;

	
	function __construct( $db ) {
				$this->mgq = get_magic_quotes_runtime();
				ini_set("magic_quotes_runtime", 0);

		$this->db = $db;
		$this->debug = $this->db->debug;
		$this->dict = NewDataDictionary( $this->db );
		$this->sqlArray = array();
		$this->schemaVersion = XMLS_SCHEMA_VERSION;
		$this->executeInline( XMLS_EXECUTE_INLINE );
		$this->continueOnError( XMLS_CONTINUE_ON_ERROR );
		$this->existingData( XMLS_EXISTING_DATA );
		$this->setUpgradeMethod();
	}

	
	function SetUpgradeMethod( $method = '' ) {
		if( !is_string( $method ) ) {
			return FALSE;
		}

		$method = strtoupper( $method );

				switch( $method ) {
			case 'ALTER':
				$this->upgrade = $method;
				break;
			case 'REPLACE':
				$this->upgrade = $method;
				break;
			case 'BEST':
				$this->upgrade = 'ALTER';
				break;
			case 'NONE':
				$this->upgrade = 'NONE';
				break;
			default:
								$this->upgrade = XMLS_DEFAULT_UPGRADE_METHOD;
		}

		return $this->upgrade;
	}

	
	function ExistingData( $mode = NULL ) {
		if( is_int( $mode ) ) {
			switch( $mode ) {
				case XMLS_MODE_UPDATE:
					$mode = XMLS_MODE_UPDATE;
					break;
				case XMLS_MODE_IGNORE:
					$mode = XMLS_MODE_IGNORE;
					break;
				case XMLS_MODE_INSERT:
					$mode = XMLS_MODE_INSERT;
					break;
				default:
					$mode = XMLS_EXISTING_DATA;
					break;
			}
			$this->existingData = $mode;
		}

		return $this->existingData;
	}

	
	function ExecuteInline( $mode = NULL ) {
		if( is_bool( $mode ) ) {
			$this->executeInline = $mode;
		}

		return $this->executeInline;
	}

	
	function ContinueOnError( $mode = NULL ) {
		if( is_bool( $mode ) ) {
			$this->continueOnError = $mode;
		}

		return $this->continueOnError;
	}

	
	function ParseSchema( $filename, $returnSchema = FALSE ) {
		return $this->ParseSchemaString( $this->ConvertSchemaFile( $filename ), $returnSchema );
	}

	
	function ParseSchemaFile( $filename, $returnSchema = FALSE ) {
				if( !($fp = fopen( $filename, 'r' )) ) {
			logMsg( 'Unable to open file' );
			return FALSE;
		}

				if( $this->SchemaFileVersion( $filename ) != $this->schemaVersion ) {
			logMsg( 'Invalid Schema Version' );
			return FALSE;
		}

		if( $returnSchema ) {
			$xmlstring = '';
			while( $data = fread( $fp, 4096 ) ) {
				$xmlstring .= $data . "\n";
			}
			return $xmlstring;
		}

		$this->success = 2;

		$xmlParser = $this->create_parser();

				while( $data = fread( $fp, 4096 ) ) {
			if( !xml_parse( $xmlParser, $data, feof( $fp ) ) ) {
				die( sprintf(
					"XML error: %s at line %d",
					xml_error_string( xml_get_error_code( $xmlParser) ),
					xml_get_current_line_number( $xmlParser)
				) );
			}
		}

		xml_parser_free( $xmlParser );

		return $this->sqlArray;
	}

	
	function ParseSchemaString( $xmlstring, $returnSchema = FALSE ) {
		if( !is_string( $xmlstring ) OR empty( $xmlstring ) ) {
			logMsg( 'Empty or Invalid Schema' );
			return FALSE;
		}

				if( $this->SchemaStringVersion( $xmlstring ) != $this->schemaVersion ) {
			logMsg( 'Invalid Schema Version' );
			return FALSE;
		}

		if( $returnSchema ) {
			return $xmlstring;
		}

		$this->success = 2;

		$xmlParser = $this->create_parser();

		if( !xml_parse( $xmlParser, $xmlstring, TRUE ) ) {
			die( sprintf(
				"XML error: %s at line %d",
				xml_error_string( xml_get_error_code( $xmlParser) ),
				xml_get_current_line_number( $xmlParser)
			) );
		}

		xml_parser_free( $xmlParser );

		return $this->sqlArray;
	}

	
	function RemoveSchema( $filename, $returnSchema = FALSE ) {
		return $this->RemoveSchemaString( $this->ConvertSchemaFile( $filename ), $returnSchema );
	}

	
	function RemoveSchemaString( $schema, $returnSchema = FALSE ) {

				if( !( $version = $this->SchemaStringVersion( $schema ) ) ) {
			return FALSE;
		}

		return $this->ParseSchemaString( $this->TransformSchema( $schema, 'remove-' . $version), $returnSchema );
	}

	
	function ExecuteSchema( $sqlArray = NULL, $continueOnErr =  NULL ) {
		if( !is_bool( $continueOnErr ) ) {
			$continueOnErr = $this->ContinueOnError();
		}

		if( !isset( $sqlArray ) ) {
			$sqlArray = $this->sqlArray;
		}

		if( !is_array( $sqlArray ) ) {
			$this->success = 0;
		} else {
			$this->success = $this->dict->ExecuteSQLArray( $sqlArray, $continueOnErr );
		}

		return $this->success;
	}

	
	function PrintSQL( $format = 'NONE' ) {
		$sqlArray = null;
		return $this->getSQL( $format, $sqlArray );
	}

	
	function SaveSQL( $filename = './schema.sql' ) {

		if( !isset( $sqlArray ) ) {
			$sqlArray = $this->sqlArray;
		}
		if( !isset( $sqlArray ) ) {
			return FALSE;
		}

		$fp = fopen( $filename, "w" );

		foreach( $sqlArray as $key => $query ) {
			fwrite( $fp, $query . ";\n" );
		}
		fclose( $fp );
	}

	
	function create_parser() {
				$xmlParser = xml_parser_create();
		xml_set_object( $xmlParser, $this );

				xml_set_element_handler( $xmlParser, '_tag_open', '_tag_close' );
		xml_set_character_data_handler( $xmlParser, '_tag_cdata' );

		return $xmlParser;
	}

	
	function _tag_open( &$parser, $tag, $attributes ) {
		switch( strtoupper( $tag ) ) {
			case 'TABLE':
				if( !isset( $attributes['PLATFORM'] ) OR $this->supportedPlatform( $attributes['PLATFORM'] ) ) {
				$this->obj = new dbTable( $this, $attributes );
				xml_set_object( $parser, $this->obj );
				}
				break;
			case 'SQL':
				if( !isset( $attributes['PLATFORM'] ) OR $this->supportedPlatform( $attributes['PLATFORM'] ) ) {
					$this->obj = new dbQuerySet( $this, $attributes );
					xml_set_object( $parser, $this->obj );
				}
				break;
			default:
						}

	}

	
	function _tag_cdata( &$parser, $cdata ) {
	}

	
	function _tag_close( &$parser, $tag ) {

	}

	
	function ConvertSchemaString( $schema, $newVersion = NULL, $newFile = NULL ) {

				if( !( $version = $this->SchemaStringVersion( $schema ) ) ) {
			return FALSE;
		}

		if( !isset ($newVersion) ) {
			$newVersion = $this->schemaVersion;
		}

		if( $version == $newVersion ) {
			$result = $schema;
		} else {
			$result = $this->TransformSchema( $schema, 'convert-' . $version . '-' . $newVersion);
		}

		if( is_string( $result ) AND is_string( $newFile ) AND ( $fp = fopen( $newFile, 'w' ) ) ) {
			fwrite( $fp, $result );
			fclose( $fp );
		}

		return $result;
	}

	

	
	function ConvertSchemaFile( $filename, $newVersion = NULL, $newFile = NULL ) {

				if( !( $version = $this->SchemaFileVersion( $filename ) ) ) {
			return FALSE;
		}

		if( !isset ($newVersion) ) {
			$newVersion = $this->schemaVersion;
		}

		if( $version == $newVersion ) {
			$result = _file_get_contents( $filename );

						if( substr( $result, 0, 3 ) == sprintf( '%c%c%c', 239, 187, 191 ) ) {
				$result = substr( $result, 3 );
			}
		} else {
			$result = $this->TransformSchema( $filename, 'convert-' . $version . '-' . $newVersion, 'file' );
		}

		if( is_string( $result ) AND is_string( $newFile ) AND ( $fp = fopen( $newFile, 'w' ) ) ) {
			fwrite( $fp, $result );
			fclose( $fp );
		}

		return $result;
	}

	function TransformSchema( $schema, $xsl, $schematype='string' )
	{
				if( ! function_exists( 'xslt_create' ) ) {
			return FALSE;
		}

		$xsl_file = dirname( __FILE__ ) . '/xsl/' . $xsl . '.xsl';

				if( !is_readable( $xsl_file ) ) {
			return FALSE;
		}

		switch( $schematype )
		{
			case 'file':
				if( !is_readable( $schema ) ) {
					return FALSE;
				}

				$schema = _file_get_contents( $schema );
				break;
			case 'string':
			default:
				if( !is_string( $schema ) ) {
					return FALSE;
				}
		}

		$arguments = array (
			'/_xml' => $schema,
			'/_xsl' => _file_get_contents( $xsl_file )
		);

				$xh = xslt_create ();

				xslt_set_error_handler ($xh, array (&$this, 'xslt_error_handler'));

				$result = xslt_process ($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);

		xslt_free ($xh);

		return $result;
	}

	
	function xslt_error_handler( $parser, $errno, $level, $fields ) {
		if( is_array( $fields ) ) {
			$msg = array(
				'Message Type' => ucfirst( $fields['msgtype'] ),
				'Message Code' => $fields['code'],
				'Message' => $fields['msg'],
				'Error Number' => $errno,
				'Level' => $level
			);

			switch( $fields['URI'] ) {
				case 'arg:/_xml':
					$msg['Input'] = 'XML';
					break;
				case 'arg:/_xsl':
					$msg['Input'] = 'XSL';
					break;
				default:
					$msg['Input'] = $fields['URI'];
			}

			$msg['Line'] = $fields['line'];
		} else {
			$msg = array(
				'Message Type' => 'Error',
				'Error Number' => $errno,
				'Level' => $level,
				'Fields' => var_export( $fields, TRUE )
			);
		}

		$error_details = $msg['Message Type'] . ' in XSLT Transformation' . "\n"
					   . '<table>' . "\n";

		foreach( $msg as $label => $details ) {
			$error_details .= '<tr><td><b>' . $label . ': </b></td><td>' . htmlentities( $details ) . '</td></tr>' . "\n";
		}

		$error_details .= '</table>';

		trigger_error( $error_details, E_USER_ERROR );
	}

	
	function SchemaFileVersion( $filename ) {
				if( !($fp = fopen( $filename, 'r' )) ) {
						return FALSE;
		}

				while( $data = fread( $fp, 4096 ) ) {
			if( preg_match( $this->versionRegex, $data, $matches ) ) {
				return !empty( $matches[2] ) ? $matches[2] : XMLS_DEFAULT_SCHEMA_VERSION;
			}
		}

		return FALSE;
	}

	
	function SchemaStringVersion( $xmlstring ) {
		if( !is_string( $xmlstring ) OR empty( $xmlstring ) ) {
			return FALSE;
		}

		if( preg_match( $this->versionRegex, $xmlstring, $matches ) ) {
			return !empty( $matches[2] ) ? $matches[2] : XMLS_DEFAULT_SCHEMA_VERSION;
		}

		return FALSE;
	}

	
	function ExtractSchema( $data = FALSE, $indent = '  ', $prefix = '' , $stripprefix=false) {
		$old_mode = $this->db->SetFetchMode( ADODB_FETCH_NUM );

		$schema = '<?xml version="1.0"?>' . "\n"
				. '<schema version="' . $this->schemaVersion . '">' . "\n";
		if( is_array( $tables = $this->db->MetaTables( 'TABLES' ,false ,($prefix) ? str_replace('_','\_',$prefix).'%' : '') ) ) {
			foreach( $tables as $table ) {
				$schema .= $indent
					. '<table name="'
					. htmlentities( $stripprefix ? str_replace($prefix, '', $table) : $table )
					. '">' . "\n";

								$rs = $this->db->Execute( 'SELECT * FROM ' . $table . ' WHERE -1' );
				$fields = $this->db->MetaColumns( $table );
				$indexes = $this->db->MetaIndexes( $table );

				if( is_array( $fields ) ) {
					foreach( $fields as $details ) {
						$extra = '';
						$content = array();

						if( isset($details->max_length) && $details->max_length > 0 ) {
							$extra .= ' size="' . $details->max_length . '"';
						}

						if( isset($details->primary_key) && $details->primary_key ) {
							$content[] = '<KEY/>';
						} elseif( isset($details->not_null) && $details->not_null ) {
							$content[] = '<NOTNULL/>';
						}

						if( isset($details->has_default) && $details->has_default ) {
							$content[] = '<DEFAULT value="' . htmlentities( $details->default_value ) . '"/>';
						}

						if( isset($details->auto_increment) && $details->auto_increment ) {
							$content[] = '<AUTOINCREMENT/>';
						}

						if( isset($details->unsigned) && $details->unsigned ) {
							$content[] = '<UNSIGNED/>';
						}

																		$details->primary_key = 0;
						$type = $rs->MetaType( $details );

						$schema .= str_repeat( $indent, 2 ) . '<field name="' . htmlentities( $details->name ) . '" type="' . $type . '"' . $extra;

						if( !empty( $content ) ) {
							$schema .= ">\n" . str_repeat( $indent, 3 )
									 . implode( "\n" . str_repeat( $indent, 3 ), $content ) . "\n"
									 . str_repeat( $indent, 2 ) . '</field>' . "\n";
						} else {
							$schema .= "/>\n";
						}
					}
				}

				if( is_array( $indexes ) ) {
					foreach( $indexes as $index => $details ) {
						$schema .= str_repeat( $indent, 2 ) . '<index name="' . $index . '">' . "\n";

						if( $details['unique'] ) {
							$schema .= str_repeat( $indent, 3 ) . '<UNIQUE/>' . "\n";
						}

						foreach( $details['columns'] as $column ) {
							$schema .= str_repeat( $indent, 3 ) . '<col>' . htmlentities( $column ) . '</col>' . "\n";
						}

						$schema .= str_repeat( $indent, 2 ) . '</index>' . "\n";
					}
				}

				if( $data ) {
					$rs = $this->db->Execute( 'SELECT * FROM ' . $table );

					if( is_object( $rs ) && !$rs->EOF ) {
						$schema .= str_repeat( $indent, 2 ) . "<data>\n";

						while( $row = $rs->FetchRow() ) {
							foreach( $row as $key => $val ) {
								if ( $val != htmlentities( $val ) ) {
									$row[$key] = '<![CDATA[' . $val . ']]>';
								}
							}

							$schema .= str_repeat( $indent, 3 ) . '<row><f>' . implode( '</f><f>', $row ) . "</f></row>\n";
						}

						$schema .= str_repeat( $indent, 2 ) . "</data>\n";
					}
				}

				$schema .= $indent . "</table>\n";
			}
		}

		$this->db->SetFetchMode( $old_mode );

		$schema .= '</schema>';
		return $schema;
	}

	
	function SetPrefix( $prefix = '', $underscore = TRUE ) {
		switch( TRUE ) {
						case empty( $prefix ):
				logMsg( 'Cleared prefix' );
				$this->objectPrefix = '';
				return TRUE;
						case strlen( $prefix ) > XMLS_PREFIX_MAXLEN:
						case !preg_match( '/^[a-z][a-z0-9_]+$/i', $prefix ):
				logMsg( 'Invalid prefix: ' . $prefix );
				return FALSE;
		}

		if( $underscore AND substr( $prefix, -1 ) != '_' ) {
			$prefix .= '_';
		}

				logMsg( 'Set prefix: ' . $prefix );
		$this->objectPrefix = $prefix;
		return TRUE;
	}

	
	function prefix( $name = '' ) {
				if( !empty( $this->objectPrefix ) ) {
									return preg_replace( '/^(`?)(.+)$/', '$1' . $this->objectPrefix . '$2', $name );
		}

				return $name;
	}

	
	function supportedPlatform( $platform = NULL ) {
		if( !empty( $platform ) ) {
			$regex = '/(^|\|)' . $this->db->databaseType . '(\||$)/i';

			if( preg_match( '/^- /', $platform ) ) {
				if (preg_match ( $regex, substr( $platform, 2 ) ) ) {
					logMsg( 'Platform ' . $platform . ' is NOT supported' );
					return FALSE;
				}
		} else {
				if( !preg_match ( $regex, $platform ) ) {
					logMsg( 'Platform ' . $platform . ' is NOT supported' );
			return FALSE;
		}
	}
		}

		logMsg( 'Platform ' . $platform . ' is supported' );
		return TRUE;
	}

	
	function clearSQL() {
		$this->sqlArray = array();
	}

	
	function addSQL( $sql = NULL ) {
		if( is_array( $sql ) ) {
			foreach( $sql as $line ) {
				$this->addSQL( $line );
			}

			return TRUE;
		}

		if( is_string( $sql ) ) {
			$this->sqlArray[] = $sql;

						if( $this->ExecuteInline() && ( $this->success == 2 || $this->ContinueOnError() ) ) {
				$saved = $this->db->debug;
				$this->db->debug = $this->debug;
				$ok = $this->db->Execute( $sql );
				$this->db->debug = $saved;

				if( !$ok ) {
					if( $this->debug ) {
						ADOConnection::outp( $this->db->ErrorMsg() );
					}

					$this->success = 1;
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	
	function getSQL( $format = NULL, $sqlArray = NULL ) {
		if( !is_array( $sqlArray ) ) {
			$sqlArray = $this->sqlArray;
		}

		if( !is_array( $sqlArray ) ) {
			return FALSE;
		}

		switch( strtolower( $format ) ) {
			case 'string':
			case 'text':
				return !empty( $sqlArray ) ? implode( ";\n\n", $sqlArray ) . ';' : '';
			case'html':
				return !empty( $sqlArray ) ? nl2br( htmlentities( implode( ";\n\n", $sqlArray ) . ';' ) ) : '';
		}

		return $this->sqlArray;
	}

	
	function Destroy() {
		ini_set("magic_quotes_runtime", $this->mgq );
				unset( $this );
	}
}


function logMsg( $msg, $title = NULL, $force = FALSE ) {
	if( XMLS_DEBUG or $force ) {
		echo '<pre>';

		if( isset( $title ) ) {
			echo '<h3>' . htmlentities( $title ) . '</h3>';
		}

		if( @is_object( $this ) ) {
			echo '[' . get_class( $this ) . '] ';
		}

		print_r( $msg );

		echo '</pre>';
	}
}
