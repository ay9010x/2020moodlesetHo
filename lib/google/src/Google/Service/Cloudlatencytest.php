<?php



class Google_Service_Cloudlatencytest extends Google_Service
{
  
  const MONITORING_READONLY =
      "https://www.googleapis.com/auth/monitoring.readonly";

  public $statscollection;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://cloudlatencytest-pa.googleapis.com/';
    $this->servicePath = 'v2/statscollection/';
    $this->version = 'v2';
    $this->serviceName = 'cloudlatencytest';

    $this->statscollection = new Google_Service_Cloudlatencytest_Statscollection_Resource(
        $this,
        $this->serviceName,
        'statscollection',
        array(
          'methods' => array(
            'updateaggregatedstats' => array(
              'path' => 'updateaggregatedstats',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'updatestats' => array(
              'path' => 'updatestats',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
  }
}



class Google_Service_Cloudlatencytest_Statscollection_Resource extends Google_Service_Resource
{

  
  public function updateaggregatedstats(Google_Service_Cloudlatencytest_AggregatedStats $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updateaggregatedstats', array($params), "Google_Service_Cloudlatencytest_AggregatedStatsReply");
  }

  
  public function updatestats(Google_Service_Cloudlatencytest_Stats $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updatestats', array($params), "Google_Service_Cloudlatencytest_StatsReply");
  }
}




class Google_Service_Cloudlatencytest_AggregatedStats extends Google_Collection
{
  protected $collection_key = 'stats';
  protected $internal_gapi_mappings = array(
  );
  protected $statsType = 'Google_Service_Cloudlatencytest_Stats';
  protected $statsDataType = 'array';


  public function setStats($stats)
  {
    $this->stats = $stats;
  }
  public function getStats()
  {
    return $this->stats;
  }
}

class Google_Service_Cloudlatencytest_AggregatedStatsReply extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $testValue;


  public function setTestValue($testValue)
  {
    $this->testValue = $testValue;
  }
  public function getTestValue()
  {
    return $this->testValue;
  }
}

class Google_Service_Cloudlatencytest_DoubleValue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $label;
  public $value;


  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Google_Service_Cloudlatencytest_IntValue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $label;
  public $value;


  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Google_Service_Cloudlatencytest_Stats extends Google_Collection
{
  protected $collection_key = 'stringValues';
  protected $internal_gapi_mappings = array(
  );
  protected $doubleValuesType = 'Google_Service_Cloudlatencytest_DoubleValue';
  protected $doubleValuesDataType = 'array';
  protected $intValuesType = 'Google_Service_Cloudlatencytest_IntValue';
  protected $intValuesDataType = 'array';
  protected $stringValuesType = 'Google_Service_Cloudlatencytest_StringValue';
  protected $stringValuesDataType = 'array';
  public $time;


  public function setDoubleValues($doubleValues)
  {
    $this->doubleValues = $doubleValues;
  }
  public function getDoubleValues()
  {
    return $this->doubleValues;
  }
  public function setIntValues($intValues)
  {
    $this->intValues = $intValues;
  }
  public function getIntValues()
  {
    return $this->intValues;
  }
  public function setStringValues($stringValues)
  {
    $this->stringValues = $stringValues;
  }
  public function getStringValues()
  {
    return $this->stringValues;
  }
  public function setTime($time)
  {
    $this->time = $time;
  }
  public function getTime()
  {
    return $this->time;
  }
}

class Google_Service_Cloudlatencytest_StatsReply extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $testValue;


  public function setTestValue($testValue)
  {
    $this->testValue = $testValue;
  }
  public function getTestValue()
  {
    return $this->testValue;
  }
}

class Google_Service_Cloudlatencytest_StringValue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $label;
  public $value;


  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}
