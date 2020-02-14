<?php



class Google_Service_Audit extends Google_Service
{


  public $activities;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->servicePath = 'apps/reporting/audit/v1/';
    $this->version = 'v1';
    $this->serviceName = 'audit';

    $this->activities = new Google_Service_Audit_Activities_Resource(
        $this,
        $this->serviceName,
        'activities',
        array(
          'methods' => array(
            'list' => array(
              'path' => '{customerId}/{applicationId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'customerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'applicationId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'actorEmail' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'actorApplicationId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'actorIpAddress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'caller' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'eventName' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'continuationToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_Audit_Activities_Resource extends Google_Service_Resource
{

  
  public function listActivities($customerId, $applicationId, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'applicationId' => $applicationId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Audit_Activities");
  }
}




class Google_Service_Audit_Activities extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Audit_Activity';
  protected $itemsDataType = 'array';
  public $kind;
  public $next;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNext($next)
  {
    $this->next = $next;
  }
  public function getNext()
  {
    return $this->next;
  }
}

class Google_Service_Audit_Activity extends Google_Collection
{
  protected $collection_key = 'events';
  protected $internal_gapi_mappings = array(
  );
  protected $actorType = 'Google_Service_Audit_ActivityActor';
  protected $actorDataType = '';
  protected $eventsType = 'Google_Service_Audit_ActivityEvents';
  protected $eventsDataType = 'array';
  protected $idType = 'Google_Service_Audit_ActivityId';
  protected $idDataType = '';
  public $ipAddress;
  public $kind;
  public $ownerDomain;


  public function setActor(Google_Service_Audit_ActivityActor $actor)
  {
    $this->actor = $actor;
  }
  public function getActor()
  {
    return $this->actor;
  }
  public function setEvents($events)
  {
    $this->events = $events;
  }
  public function getEvents()
  {
    return $this->events;
  }
  public function setId(Google_Service_Audit_ActivityId $id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIpAddress($ipAddress)
  {
    $this->ipAddress = $ipAddress;
  }
  public function getIpAddress()
  {
    return $this->ipAddress;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setOwnerDomain($ownerDomain)
  {
    $this->ownerDomain = $ownerDomain;
  }
  public function getOwnerDomain()
  {
    return $this->ownerDomain;
  }
}

class Google_Service_Audit_ActivityActor extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $applicationId;
  public $callerType;
  public $email;
  public $key;


  public function setApplicationId($applicationId)
  {
    $this->applicationId = $applicationId;
  }
  public function getApplicationId()
  {
    return $this->applicationId;
  }
  public function setCallerType($callerType)
  {
    $this->callerType = $callerType;
  }
  public function getCallerType()
  {
    return $this->callerType;
  }
  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function getEmail()
  {
    return $this->email;
  }
  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
  }
}

class Google_Service_Audit_ActivityEvents extends Google_Collection
{
  protected $collection_key = 'parameters';
  protected $internal_gapi_mappings = array(
  );
  public $eventType;
  public $name;
  protected $parametersType = 'Google_Service_Audit_ActivityEventsParameters';
  protected $parametersDataType = 'array';


  public function setEventType($eventType)
  {
    $this->eventType = $eventType;
  }
  public function getEventType()
  {
    return $this->eventType;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParameters($parameters)
  {
    $this->parameters = $parameters;
  }
  public function getParameters()
  {
    return $this->parameters;
  }
}

class Google_Service_Audit_ActivityEventsParameters extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $name;
  public $value;


  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
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

class Google_Service_Audit_ActivityId extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $applicationId;
  public $customerId;
  public $time;
  public $uniqQualifier;


  public function setApplicationId($applicationId)
  {
    $this->applicationId = $applicationId;
  }
  public function getApplicationId()
  {
    return $this->applicationId;
  }
  public function setCustomerId($customerId)
  {
    $this->customerId = $customerId;
  }
  public function getCustomerId()
  {
    return $this->customerId;
  }
  public function setTime($time)
  {
    $this->time = $time;
  }
  public function getTime()
  {
    return $this->time;
  }
  public function setUniqQualifier($uniqQualifier)
  {
    $this->uniqQualifier = $uniqQualifier;
  }
  public function getUniqQualifier()
  {
    return $this->uniqQualifier;
  }
}
