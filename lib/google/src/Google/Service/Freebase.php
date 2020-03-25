<?php



class Google_Service_Freebase extends Google_Service
{



  private $base_methods;

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'freebase/v1/';
    $this->version = 'v1';
    $this->serviceName = 'freebase';

    $this->base_methods = new Google_Service_Resource(
        $this,
        $this->serviceName,
        '',
        array(
          'methods' => array(
            'reconcile' => array(
              'path' => 'reconcile',
              'httpMethod' => 'GET',
              'parameters' => array(
                'lang' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'confidence' => array(
                  'location' => 'query',
                  'type' => 'number',
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'kind' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'prop' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'limit' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'search' => array(
              'path' => 'search',
              'httpMethod' => 'GET',
              'parameters' => array(
                'domain' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'help' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'query' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'scoring' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'cursor' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'prefixed' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'exact' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'mid' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'encode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'type' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'as_of_time' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'stemmed' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'format' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'spell' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'with' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'lang' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'indent' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'callback' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'without' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'limit' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'output' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mql_output' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
  }
  
  public function reconcile($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->base_methods->call('reconcile', array($params), "Google_Service_Freebase_ReconcileGet");
  }
  
  public function search($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->base_methods->call('search', array($params));
  }
}





class Google_Service_Freebase_ReconcileCandidate extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $confidence;
  public $lang;
  public $mid;
  public $name;
  protected $notableType = 'Google_Service_Freebase_ReconcileCandidateNotable';
  protected $notableDataType = '';


  public function setConfidence($confidence)
  {
    $this->confidence = $confidence;
  }
  public function getConfidence()
  {
    return $this->confidence;
  }
  public function setLang($lang)
  {
    $this->lang = $lang;
  }
  public function getLang()
  {
    return $this->lang;
  }
  public function setMid($mid)
  {
    $this->mid = $mid;
  }
  public function getMid()
  {
    return $this->mid;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotable(Google_Service_Freebase_ReconcileCandidateNotable $notable)
  {
    $this->notable = $notable;
  }
  public function getNotable()
  {
    return $this->notable;
  }
}

class Google_Service_Freebase_ReconcileCandidateNotable extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $name;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
}

class Google_Service_Freebase_ReconcileGet extends Google_Collection
{
  protected $collection_key = 'warning';
  protected $internal_gapi_mappings = array(
  );
  protected $candidateType = 'Google_Service_Freebase_ReconcileCandidate';
  protected $candidateDataType = 'array';
  protected $costsType = 'Google_Service_Freebase_ReconcileGetCosts';
  protected $costsDataType = '';
  protected $matchType = 'Google_Service_Freebase_ReconcileCandidate';
  protected $matchDataType = '';
  protected $warningType = 'Google_Service_Freebase_ReconcileGetWarning';
  protected $warningDataType = 'array';


  public function setCandidate($candidate)
  {
    $this->candidate = $candidate;
  }
  public function getCandidate()
  {
    return $this->candidate;
  }
  public function setCosts(Google_Service_Freebase_ReconcileGetCosts $costs)
  {
    $this->costs = $costs;
  }
  public function getCosts()
  {
    return $this->costs;
  }
  public function setMatch(Google_Service_Freebase_ReconcileCandidate $match)
  {
    $this->match = $match;
  }
  public function getMatch()
  {
    return $this->match;
  }
  public function setWarning($warning)
  {
    $this->warning = $warning;
  }
  public function getWarning()
  {
    return $this->warning;
  }
}

class Google_Service_Freebase_ReconcileGetCosts extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $hits;
  public $ms;


  public function setHits($hits)
  {
    $this->hits = $hits;
  }
  public function getHits()
  {
    return $this->hits;
  }
  public function setMs($ms)
  {
    $this->ms = $ms;
  }
  public function getMs()
  {
    return $this->ms;
  }
}

class Google_Service_Freebase_ReconcileGetWarning extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $location;
  public $message;
  public $reason;


  public function setLocation($location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setMessage($message)
  {
    $this->message = $message;
  }
  public function getMessage()
  {
    return $this->message;
  }
  public function setReason($reason)
  {
    $this->reason = $reason;
  }
  public function getReason()
  {
    return $this->reason;
  }
}
