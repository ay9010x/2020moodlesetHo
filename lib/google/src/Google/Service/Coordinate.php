<?php



class Google_Service_Coordinate extends Google_Service
{
  
  const COORDINATE =
      "https://www.googleapis.com/auth/coordinate";
  
  const COORDINATE_READONLY =
      "https://www.googleapis.com/auth/coordinate.readonly";

  public $customFieldDef;
  public $jobs;
  public $location;
  public $schedule;
  public $team;
  public $worker;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'coordinate/v1/';
    $this->version = 'v1';
    $this->serviceName = 'coordinate';

    $this->customFieldDef = new Google_Service_Coordinate_CustomFieldDef_Resource(
        $this,
        $this->serviceName,
        'customFieldDef',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'teams/{teamId}/custom_fields',
              'httpMethod' => 'GET',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->jobs = new Google_Service_Coordinate_Jobs_Resource(
        $this,
        $this->serviceName,
        'jobs',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'teams/{teamId}/jobs/{jobId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'teams/{teamId}/jobs',
              'httpMethod' => 'POST',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'address' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'lat' => array(
                  'location' => 'query',
                  'type' => 'number',
                  'required' => true,
                ),
                'lng' => array(
                  'location' => 'query',
                  'type' => 'number',
                  'required' => true,
                ),
                'title' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'customerName' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'note' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'assignee' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'customerPhoneNumber' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'customField' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'teams/{teamId}/jobs',
              'httpMethod' => 'GET',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'minModifiedTimestampMs' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'omitJobChanges' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'patch' => array(
              'path' => 'teams/{teamId}/jobs/{jobId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customerName' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'title' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'note' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'assignee' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'customerPhoneNumber' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'address' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'lat' => array(
                  'location' => 'query',
                  'type' => 'number',
                ),
                'progress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'lng' => array(
                  'location' => 'query',
                  'type' => 'number',
                ),
                'customField' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'teams/{teamId}/jobs/{jobId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customerName' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'title' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'note' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'assignee' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'customerPhoneNumber' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'address' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'lat' => array(
                  'location' => 'query',
                  'type' => 'number',
                ),
                'progress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'lng' => array(
                  'location' => 'query',
                  'type' => 'number',
                ),
                'customField' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->location = new Google_Service_Coordinate_Location_Resource(
        $this,
        $this->serviceName,
        'location',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'teams/{teamId}/workers/{workerEmail}/locations',
              'httpMethod' => 'GET',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'workerEmail' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'startTimestampMs' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->schedule = new Google_Service_Coordinate_Schedule_Resource(
        $this,
        $this->serviceName,
        'schedule',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'teams/{teamId}/jobs/{jobId}/schedule',
              'httpMethod' => 'GET',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'teams/{teamId}/jobs/{jobId}/schedule',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'allDay' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'startTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'duration' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'teams/{teamId}/jobs/{jobId}/schedule',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'allDay' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'startTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'duration' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->team = new Google_Service_Coordinate_Team_Resource(
        $this,
        $this->serviceName,
        'team',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'teams',
              'httpMethod' => 'GET',
              'parameters' => array(
                'admin' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'worker' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'dispatcher' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->worker = new Google_Service_Coordinate_Worker_Resource(
        $this,
        $this->serviceName,
        'worker',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'teams/{teamId}/workers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'teamId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_Coordinate_CustomFieldDef_Resource extends Google_Service_Resource
{

  
  public function listCustomFieldDef($teamId, $optParams = array())
  {
    $params = array('teamId' => $teamId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Coordinate_CustomFieldDefListResponse");
  }
}


class Google_Service_Coordinate_Jobs_Resource extends Google_Service_Resource
{

  
  public function get($teamId, $jobId, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'jobId' => $jobId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Coordinate_Job");
  }

  
  public function insert($teamId, $address, $lat, $lng, $title, Google_Service_Coordinate_Job $postBody, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'address' => $address, 'lat' => $lat, 'lng' => $lng, 'title' => $title, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Coordinate_Job");
  }

  
  public function listJobs($teamId, $optParams = array())
  {
    $params = array('teamId' => $teamId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Coordinate_JobListResponse");
  }

  
  public function patch($teamId, $jobId, Google_Service_Coordinate_Job $postBody, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'jobId' => $jobId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Coordinate_Job");
  }

  
  public function update($teamId, $jobId, Google_Service_Coordinate_Job $postBody, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'jobId' => $jobId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Coordinate_Job");
  }
}


class Google_Service_Coordinate_Location_Resource extends Google_Service_Resource
{

  
  public function listLocation($teamId, $workerEmail, $startTimestampMs, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'workerEmail' => $workerEmail, 'startTimestampMs' => $startTimestampMs);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Coordinate_LocationListResponse");
  }
}


class Google_Service_Coordinate_Schedule_Resource extends Google_Service_Resource
{

  
  public function get($teamId, $jobId, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'jobId' => $jobId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Coordinate_Schedule");
  }

  
  public function patch($teamId, $jobId, Google_Service_Coordinate_Schedule $postBody, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'jobId' => $jobId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Coordinate_Schedule");
  }

  
  public function update($teamId, $jobId, Google_Service_Coordinate_Schedule $postBody, $optParams = array())
  {
    $params = array('teamId' => $teamId, 'jobId' => $jobId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Coordinate_Schedule");
  }
}


class Google_Service_Coordinate_Team_Resource extends Google_Service_Resource
{

  
  public function listTeam($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Coordinate_TeamListResponse");
  }
}


class Google_Service_Coordinate_Worker_Resource extends Google_Service_Resource
{

  
  public function listWorker($teamId, $optParams = array())
  {
    $params = array('teamId' => $teamId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Coordinate_WorkerListResponse");
  }
}




class Google_Service_Coordinate_CustomField extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $customFieldId;
  public $kind;
  public $value;


  public function setCustomFieldId($customFieldId)
  {
    $this->customFieldId = $customFieldId;
  }
  public function getCustomFieldId()
  {
    return $this->customFieldId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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

class Google_Service_Coordinate_CustomFieldDef extends Google_Collection
{
  protected $collection_key = 'enumitems';
  protected $internal_gapi_mappings = array(
  );
  public $enabled;
  protected $enumitemsType = 'Google_Service_Coordinate_EnumItemDef';
  protected $enumitemsDataType = 'array';
  public $id;
  public $kind;
  public $name;
  public $requiredForCheckout;
  public $type;


  public function setEnabled($enabled)
  {
    $this->enabled = $enabled;
  }
  public function getEnabled()
  {
    return $this->enabled;
  }
  public function setEnumitems($enumitems)
  {
    $this->enumitems = $enumitems;
  }
  public function getEnumitems()
  {
    return $this->enumitems;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setRequiredForCheckout($requiredForCheckout)
  {
    $this->requiredForCheckout = $requiredForCheckout;
  }
  public function getRequiredForCheckout()
  {
    return $this->requiredForCheckout;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}

class Google_Service_Coordinate_CustomFieldDefListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Coordinate_CustomFieldDef';
  protected $itemsDataType = 'array';
  public $kind;


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
}

class Google_Service_Coordinate_CustomFields extends Google_Collection
{
  protected $collection_key = 'customField';
  protected $internal_gapi_mappings = array(
  );
  protected $customFieldType = 'Google_Service_Coordinate_CustomField';
  protected $customFieldDataType = 'array';
  public $kind;


  public function setCustomField($customField)
  {
    $this->customField = $customField;
  }
  public function getCustomField()
  {
    return $this->customField;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}

class Google_Service_Coordinate_EnumItemDef extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $active;
  public $kind;
  public $value;


  public function setActive($active)
  {
    $this->active = $active;
  }
  public function getActive()
  {
    return $this->active;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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

class Google_Service_Coordinate_Job extends Google_Collection
{
  protected $collection_key = 'jobChange';
  protected $internal_gapi_mappings = array(
  );
  public $id;
  protected $jobChangeType = 'Google_Service_Coordinate_JobChange';
  protected $jobChangeDataType = 'array';
  public $kind;
  protected $stateType = 'Google_Service_Coordinate_JobState';
  protected $stateDataType = '';


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setJobChange($jobChange)
  {
    $this->jobChange = $jobChange;
  }
  public function getJobChange()
  {
    return $this->jobChange;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setState(Google_Service_Coordinate_JobState $state)
  {
    $this->state = $state;
  }
  public function getState()
  {
    return $this->state;
  }
}

class Google_Service_Coordinate_JobChange extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $stateType = 'Google_Service_Coordinate_JobState';
  protected $stateDataType = '';
  public $timestamp;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setState(Google_Service_Coordinate_JobState $state)
  {
    $this->state = $state;
  }
  public function getState()
  {
    return $this->state;
  }
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
  }
  public function getTimestamp()
  {
    return $this->timestamp;
  }
}

class Google_Service_Coordinate_JobListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Coordinate_Job';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_Coordinate_JobState extends Google_Collection
{
  protected $collection_key = 'note';
  protected $internal_gapi_mappings = array(
  );
  public $assignee;
  protected $customFieldsType = 'Google_Service_Coordinate_CustomFields';
  protected $customFieldsDataType = '';
  public $customerName;
  public $customerPhoneNumber;
  public $kind;
  protected $locationType = 'Google_Service_Coordinate_Location';
  protected $locationDataType = '';
  public $note;
  public $progress;
  public $title;


  public function setAssignee($assignee)
  {
    $this->assignee = $assignee;
  }
  public function getAssignee()
  {
    return $this->assignee;
  }
  public function setCustomFields(Google_Service_Coordinate_CustomFields $customFields)
  {
    $this->customFields = $customFields;
  }
  public function getCustomFields()
  {
    return $this->customFields;
  }
  public function setCustomerName($customerName)
  {
    $this->customerName = $customerName;
  }
  public function getCustomerName()
  {
    return $this->customerName;
  }
  public function setCustomerPhoneNumber($customerPhoneNumber)
  {
    $this->customerPhoneNumber = $customerPhoneNumber;
  }
  public function getCustomerPhoneNumber()
  {
    return $this->customerPhoneNumber;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLocation(Google_Service_Coordinate_Location $location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setNote($note)
  {
    $this->note = $note;
  }
  public function getNote()
  {
    return $this->note;
  }
  public function setProgress($progress)
  {
    $this->progress = $progress;
  }
  public function getProgress()
  {
    return $this->progress;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_Coordinate_Location extends Google_Collection
{
  protected $collection_key = 'addressLine';
  protected $internal_gapi_mappings = array(
  );
  public $addressLine;
  public $kind;
  public $lat;
  public $lng;


  public function setAddressLine($addressLine)
  {
    $this->addressLine = $addressLine;
  }
  public function getAddressLine()
  {
    return $this->addressLine;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLat($lat)
  {
    $this->lat = $lat;
  }
  public function getLat()
  {
    return $this->lat;
  }
  public function setLng($lng)
  {
    $this->lng = $lng;
  }
  public function getLng()
  {
    return $this->lng;
  }
}

class Google_Service_Coordinate_LocationListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Coordinate_LocationRecord';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $tokenPaginationType = 'Google_Service_Coordinate_TokenPagination';
  protected $tokenPaginationDataType = '';


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setTokenPagination(Google_Service_Coordinate_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
}

class Google_Service_Coordinate_LocationRecord extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $collectionTime;
  public $confidenceRadius;
  public $kind;
  public $latitude;
  public $longitude;


  public function setCollectionTime($collectionTime)
  {
    $this->collectionTime = $collectionTime;
  }
  public function getCollectionTime()
  {
    return $this->collectionTime;
  }
  public function setConfidenceRadius($confidenceRadius)
  {
    $this->confidenceRadius = $confidenceRadius;
  }
  public function getConfidenceRadius()
  {
    return $this->confidenceRadius;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLatitude($latitude)
  {
    $this->latitude = $latitude;
  }
  public function getLatitude()
  {
    return $this->latitude;
  }
  public function setLongitude($longitude)
  {
    $this->longitude = $longitude;
  }
  public function getLongitude()
  {
    return $this->longitude;
  }
}

class Google_Service_Coordinate_Schedule extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $allDay;
  public $duration;
  public $endTime;
  public $kind;
  public $startTime;


  public function setAllDay($allDay)
  {
    $this->allDay = $allDay;
  }
  public function getAllDay()
  {
    return $this->allDay;
  }
  public function setDuration($duration)
  {
    $this->duration = $duration;
  }
  public function getDuration()
  {
    return $this->duration;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  public function getStartTime()
  {
    return $this->startTime;
  }
}

class Google_Service_Coordinate_Team extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $name;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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

class Google_Service_Coordinate_TeamListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Coordinate_Team';
  protected $itemsDataType = 'array';
  public $kind;


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
}

class Google_Service_Coordinate_TokenPagination extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $nextPageToken;
  public $previousPageToken;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setPreviousPageToken($previousPageToken)
  {
    $this->previousPageToken = $previousPageToken;
  }
  public function getPreviousPageToken()
  {
    return $this->previousPageToken;
  }
}

class Google_Service_Coordinate_Worker extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}

class Google_Service_Coordinate_WorkerListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Coordinate_Worker';
  protected $itemsDataType = 'array';
  public $kind;


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
}
