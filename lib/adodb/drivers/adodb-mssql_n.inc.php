<?php





if (!defined('ADODB_DIR')) die();

if (!defined('SINGLEQUOTE')) define('SINGLEQUOTE', "'");

include_once(ADODB_DIR.'/drivers/adodb-mssql.inc.php');

class ADODB_mssql_n extends ADODB_mssql {
	var $databaseType = "mssql_n";

	function _query($sql,$inputarr=false)
	{
        $sql = $this->_appendN($sql);
		return ADODB_mssql::_query($sql,$inputarr);
	}

         
    function _appendN($inboundData) {

        $inboundIsArray  = false;
       
        if (is_array($inboundData))
        {
            $inboundIsArray = true;
            $inboundArray   = $inboundData;
        } else
            $inboundArray = (array)$inboundData;
        
        
        $outboundArray = $inboundArray;
        
        foreach($inboundArray as $inboundKey=>$inboundValue)
        {
        
            if (is_resource($inboundValue))
            {
                
                if ($this->debug)
                    ADOConnection::outp("{$this->databaseType} index $inboundKey value is resource, continue");

                continue;
            }
           
            if (strpos($inboundValue, SINGLEQUOTE) === false)
            {
                
                if ($this->debug)
                    ADOConnection::outp("{$this->databaseType} index $inboundKey value $inboundValue has no single quotes, continue");
                continue;
            }

            
            if ((substr_count($inboundValue, SINGLEQUOTE) & 1)) 
            {
                if ($this->debug)
                    ADOConnection::outp("{$this->databaseType} internal transformation: not converted. Wrong number of quotes (odd)");
               
                break;
            }

            
            $regexp = '/(\\\\' . SINGLEQUOTE . '[^' . SINGLEQUOTE . '])/';
            if (preg_match($regexp, $inboundValue))
            {
                if ($this->debug) 
                    ADOConnection::outp("{$this->databaseType} internal transformation: not converted. Found bad use of backslash + single quote");
                
                break;
            }

            
            $pairs = array();
            $regexp = '/(' . SINGLEQUOTE . SINGLEQUOTE . ')/';
            preg_match_all($regexp, $inboundValue, $list_of_pairs);
            
            if ($list_of_pairs)
            {
                foreach (array_unique($list_of_pairs[0]) as $key=>$value)
                    $pairs['<@#@#@PAIR-'.$key.'@#@#@>'] = $value;
                
                
                if (!empty($pairs))
                    $inboundValue = str_replace($pairs, array_keys($pairs), $inboundValue);
                
            }

            
            $literals = array();
            $regexp = '/(N?' . SINGLEQUOTE . '.*?' . SINGLEQUOTE . ')/is';
            preg_match_all($regexp, $inboundValue, $list_of_literals);
           
           if ($list_of_literals)
           {
                foreach (array_unique($list_of_literals[0]) as $key=>$value)
                    $literals['<#@#@#LITERAL-'.$key.'#@#@#>'] = $value;
                
               
                if (!empty($literals))
                    $inboundValue = str_replace($literals, array_keys($literals), $inboundValue);
            }

            
            if (!empty($literals))
            {
                foreach ($literals as $key=>$value) {
                    if (!is_numeric(trim($value, SINGLEQUOTE)))
                        
                        $literals[$key] = 'N' . trim($value, 'N'); 
                    
                }
            }

            
            if (!empty($literals))
                $inboundValue = str_replace(array_keys($literals), $literals, $inboundValue);
            

            
            $inboundValue = preg_replace("/((<@#@#@PAIR-(\d+)@#@#@>)+)N'/", "N'$1", $inboundValue);

            
            if (!empty($pairs))
                $inboundValue = str_replace(array_keys($pairs), $pairs, $inboundValue);
            

            
            if (strcmp($inboundValue,$inboundArray[$inboundKey]) <> 0 && $this->debug)
                ADOConnection::outp("{$this->databaseType} internal transformation: {$inboundArray[$inboundKey]} to {$inboundValue}");
            
            if (strcmp($inboundValue,$inboundArray[$inboundKey]) <> 0)
                
                $outboundArray[$inboundKey] = $inboundValue;
        }
        
        
        if ($inboundIsArray)
            return $outboundArray;
        
        
        return $outboundArray[0];
        
    }

}

class ADORecordset_mssql_n extends ADORecordset_mssql {
	var $databaseType = "mssql_n";
	function __construct($id,$mode=false)
	{
		parent::__construct($id,$mode);
	}
}
