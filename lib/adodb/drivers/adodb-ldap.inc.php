<?php


if (!defined('ADODB_DIR')) die();

if (!defined('LDAP_ASSOC')) {
	define('LDAP_ASSOC',ADODB_FETCH_ASSOC);
	define('LDAP_NUM',ADODB_FETCH_NUM);
	define('LDAP_BOTH',ADODB_FETCH_BOTH);
}

class ADODB_ldap extends ADOConnection {
	var $databaseType = 'ldap';
	var $dataProvider = 'ldap';

		var $username = false;
	var $password = false;

		var $filter;
	var $dn;
	var $version;
	var $port = 389;

		var $LDAP_CONNECT_OPTIONS;

		var $_bind_errmsg = "Binding: %s";

	function __construct()
	{
	}

	
	function _connect( $host, $username, $password, $ldapbase)
	{
		global $LDAP_CONNECT_OPTIONS;

		if ( !function_exists( 'ldap_connect' ) ) return null;

		if (strpos($host,'ldap://') === 0 || strpos($host,'ldaps://') === 0) {
			$this->_connectionID = @ldap_connect($host);
		} else {
			$conn_info = array( $host,$this->port);

			if ( strstr( $host, ':' ) ) {
				$conn_info = explode( ':', $host );
			}

			$this->_connectionID = @ldap_connect( $conn_info[0], $conn_info[1] );
		}
		if (!$this->_connectionID) {
			$e = 'Could not connect to ' . $conn_info[0];
			$this->_errorMsg = $e;
			if ($this->debug) ADOConnection::outp($e);
			return false;
		}
		if( count( $LDAP_CONNECT_OPTIONS ) > 0 ) {
			$this->_inject_bind_options( $LDAP_CONNECT_OPTIONS );
		}

		if ($username) {
			$bind = @ldap_bind( $this->_connectionID, $username, $password );
		} else {
			$username = 'anonymous';
			$bind = @ldap_bind( $this->_connectionID );
		}

		if (!$bind) {
			$e = sprintf($this->_bind_errmsg,ldap_error($this->_connectionID));
			$this->_errorMsg = $e;
			if ($this->debug) ADOConnection::outp($e);
			return false;
		}
		$this->_errorMsg = '';
		$this->database = $ldapbase;
		return $this->_connectionID;
	}



	function _inject_bind_options( $options ) {
		foreach( $options as $option ) {
			ldap_set_option( $this->_connectionID, $option["OPTION_NAME"], $option["OPTION_VALUE"] )
				or die( "Unable to set server option: " . $option["OPTION_NAME"] );
		}
	}

	
	function _query($sql,$inputarr=false)
	{
		$rs = @ldap_search( $this->_connectionID, $this->database, $sql );
		$this->_errorMsg = ($rs) ? '' : 'Search error on '.$sql.': '.ldap_error($this->_connectionID);
		return $rs;
	}

	function ErrorMsg()
	{
		return $this->_errorMsg;
	}

	function ErrorNo()
	{
		return @ldap_errno($this->_connectionID);
	}

	
	function _close()
	{
		@ldap_close( $this->_connectionID );
		$this->_connectionID = false;
	}

	function SelectDB($db) {
		$this->database = $db;
		return true;
	} 
	function ServerInfo()
	{
		if( !empty( $this->version ) ) {
			return $this->version;
		}

		$version = array();
		
		ldap_get_option( $this->_connectionID, LDAP_OPT_DEREF, $version['LDAP_OPT_DEREF'] ) ;
		switch ( $version['LDAP_OPT_DEREF'] ) {
			case 0:
				$version['LDAP_OPT_DEREF'] = 'LDAP_DEREF_NEVER';
			case 1:
				$version['LDAP_OPT_DEREF'] = 'LDAP_DEREF_SEARCHING';
			case 2:
				$version['LDAP_OPT_DEREF'] = 'LDAP_DEREF_FINDING';
			case 3:
				$version['LDAP_OPT_DEREF'] = 'LDAP_DEREF_ALWAYS';
		}

		
		ldap_get_option( $this->_connectionID, LDAP_OPT_SIZELIMIT, $version['LDAP_OPT_SIZELIMIT'] );
		if ( $version['LDAP_OPT_SIZELIMIT'] == 0 ) {
			$version['LDAP_OPT_SIZELIMIT'] = 'LDAP_NO_LIMIT';
		}

		
		ldap_get_option( $this->_connectionID, LDAP_OPT_TIMELIMIT, $version['LDAP_OPT_TIMELIMIT'] );
		if ( $version['LDAP_OPT_TIMELIMIT'] == 0 ) {
			$version['LDAP_OPT_TIMELIMIT'] = 'LDAP_NO_LIMIT';
		}

		
		ldap_get_option( $this->_connectionID, LDAP_OPT_REFERRALS, $version['LDAP_OPT_REFERRALS'] );
		if ( $version['LDAP_OPT_REFERRALS'] == 0 ) {
			$version['LDAP_OPT_REFERRALS'] = 'LDAP_OPT_OFF';
		} else {
			$version['LDAP_OPT_REFERRALS'] = 'LDAP_OPT_ON';
		}

		
		ldap_get_option( $this->_connectionID, LDAP_OPT_RESTART, $version['LDAP_OPT_RESTART'] );
		if ( $version['LDAP_OPT_RESTART'] == 0 ) {
			$version['LDAP_OPT_RESTART'] = 'LDAP_OPT_OFF';
		} else {
			$version['LDAP_OPT_RESTART'] = 'LDAP_OPT_ON';
		}

		
		ldap_get_option( $this->_connectionID, LDAP_OPT_PROTOCOL_VERSION, $version['LDAP_OPT_PROTOCOL_VERSION'] );
		if ( $version['LDAP_OPT_PROTOCOL_VERSION'] == 2 ) {
			$version['LDAP_OPT_PROTOCOL_VERSION'] = 'LDAP_VERSION2';
		} else {
			$version['LDAP_OPT_PROTOCOL_VERSION'] = 'LDAP_VERSION3';
		}

		
		ldap_get_option( $this->_connectionID, LDAP_OPT_HOST_NAME, $version['LDAP_OPT_HOST_NAME'] );
		ldap_get_option( $this->_connectionID, LDAP_OPT_ERROR_NUMBER, $version['LDAP_OPT_ERROR_NUMBER'] );
		ldap_get_option( $this->_connectionID, LDAP_OPT_ERROR_STRING, $version['LDAP_OPT_ERROR_STRING'] );
		ldap_get_option( $this->_connectionID, LDAP_OPT_MATCHED_DN, $version['LDAP_OPT_MATCHED_DN'] );

		return $this->version = $version;
	}
}



class ADORecordSet_ldap extends ADORecordSet{

	var $databaseType = "ldap";
	var $canSeek = false;
	var $_entryID; 

	function __construct($queryID,$mode=false)
	{
		if ($mode === false) {
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		switch ($mode)
		{
		case ADODB_FETCH_NUM:
			$this->fetchMode = LDAP_NUM;
			break;
		case ADODB_FETCH_ASSOC:
			$this->fetchMode = LDAP_ASSOC;
			break;
		case ADODB_FETCH_DEFAULT:
		case ADODB_FETCH_BOTH:
		default:
			$this->fetchMode = LDAP_BOTH;
			break;
		}

		parent::__construct($queryID);
	}

	function _initrs()
	{
		
		$this->_numOfRows = ldap_count_entries( $this->connection->_connectionID, $this->_queryID );
	}

	
	function GetAssoc($force_array = false, $first2cols = false)
	{
		$records = $this->_numOfRows;
		$results = array();
		for ( $i=0; $i < $records; $i++ ) {
			foreach ( $this->fields as $k=>$v ) {
				if ( is_array( $v ) ) {
					if ( $v['count'] == 1 ) {
						$results[$i][$k] = $v[0];
					} else {
						array_shift( $v );
						$results[$i][$k] = $v;
					}
				}
			}
		}

		return $results;
	}

	function GetRowAssoc($upper = ADODB_ASSOC_CASE)
	{
		$results = array();
		foreach ( $this->fields as $k=>$v ) {
			if ( is_array( $v ) ) {
				if ( $v['count'] == 1 ) {
					$results[$k] = $v[0];
				} else {
					array_shift( $v );
					$results[$k] = $v;
				}
			}
		}

		return $results;
	}

	function GetRowNums()
	{
		$results = array();
		foreach ( $this->fields as $k=>$v ) {
			static $i = 0;
			if (is_array( $v )) {
				if ( $v['count'] == 1 ) {
					$results[$i] = $v[0];
				} else {
					array_shift( $v );
					$results[$i] = $v;
				}
				$i++;
			}
		}
		return $results;
	}

	function _fetch()
	{
		if ( $this->_currentRow >= $this->_numOfRows && $this->_numOfRows >= 0 ) {
			return false;
		}

		if ( $this->_currentRow == 0 ) {
			$this->_entryID = ldap_first_entry( $this->connection->_connectionID, $this->_queryID );
		} else {
			$this->_entryID = ldap_next_entry( $this->connection->_connectionID, $this->_entryID );
		}

		$this->fields = ldap_get_attributes( $this->connection->_connectionID, $this->_entryID );
		$this->_numOfFields = $this->fields['count'];

		switch ( $this->fetchMode ) {

			case LDAP_ASSOC:
				$this->fields = $this->GetRowAssoc();
				break;

			case LDAP_NUM:
				$this->fields = array_merge($this->GetRowNums(),$this->GetRowAssoc());
				break;

			case LDAP_BOTH:
			default:
				$this->fields = $this->GetRowNums();
				break;
		}

		return is_array( $this->fields );
	}

	function _close() {
		@ldap_free_result( $this->_queryID );
		$this->_queryID = false;
	}

}
