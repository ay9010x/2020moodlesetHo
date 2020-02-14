<?php



class Google_Service_Replicapool extends Google_Service
{
  
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";
  
  const CLOUD_PLATFORM_READ_ONLY =
      "https://www.googleapis.com/auth/cloud-platform.read-only";
  
  const COMPUTE =
      "https://www.googleapis.com/auth/compute";
  
  const COMPUTE_READONLY =
      "https://www.googleapis.com/auth/compute.readonly";

  public $instanceGroupManagers;
  public $zoneOperations;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'replicapool/v1beta2/projects/';
    $this->version = 'v1beta2';
    $this->serviceName = 'replicapool';

    $this->instanceGroupManagers = new Google_Service_Replicapool_InstanceGroupManagers_Resource(
        $this,
        $this->serviceName,
        'instanceGroupManagers',
        array(
          'methods' => array(
            'abandonInstances' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}/abandonInstances',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'deleteInstances' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}/deleteInstances',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'size' => array(
                  'location' => 'query',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filter' => array(
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
              ),
            ),'recreateInstances' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}/recreateInstances',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resize' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}/resize',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'size' => array(
                  'location' => 'query',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),'setInstanceTemplate' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}/setInstanceTemplate',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setTargetPools' => array(
              'path' => '{project}/zones/{zone}/instanceGroupManagers/{instanceGroupManager}/setTargetPools',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'instanceGroupManager' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->zoneOperations = new Google_Service_Replicapool_ZoneOperations_Resource(
        $this,
        $this->serviceName,
        'zoneOperations',
        array(
          'methods' => array(
            'get' => array(
              'path' => '{project}/zones/{zone}/operations/{operation}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'operation' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{project}/zones/{zone}/operations',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'zone' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filter' => array(
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
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_Replicapool_InstanceGroupManagers_Resource extends Google_Service_Resource
{

  
  public function abandonInstances($project, $zone, $instanceGroupManager, Google_Service_Replicapool_InstanceGroupManagersAbandonInstancesRequest $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('abandonInstances', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function delete($project, $zone, $instanceGroupManager, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function deleteInstances($project, $zone, $instanceGroupManager, Google_Service_Replicapool_InstanceGroupManagersDeleteInstancesRequest $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('deleteInstances', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function get($project, $zone, $instanceGroupManager, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Replicapool_InstanceGroupManager");
  }

  
  public function insert($project, $zone, $size, Google_Service_Replicapool_InstanceGroupManager $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'size' => $size, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function listInstanceGroupManagers($project, $zone, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Replicapool_InstanceGroupManagerList");
  }

  
  public function recreateInstances($project, $zone, $instanceGroupManager, Google_Service_Replicapool_InstanceGroupManagersRecreateInstancesRequest $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('recreateInstances', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function resize($project, $zone, $instanceGroupManager, $size, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager, 'size' => $size);
    $params = array_merge($params, $optParams);
    return $this->call('resize', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function setInstanceTemplate($project, $zone, $instanceGroupManager, Google_Service_Replicapool_InstanceGroupManagersSetInstanceTemplateRequest $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setInstanceTemplate', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function setTargetPools($project, $zone, $instanceGroupManager, Google_Service_Replicapool_InstanceGroupManagersSetTargetPoolsRequest $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'instanceGroupManager' => $instanceGroupManager, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setTargetPools', array($params), "Google_Service_Replicapool_Operation");
  }
}


class Google_Service_Replicapool_ZoneOperations_Resource extends Google_Service_Resource
{

  
  public function get($project, $zone, $operation, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone, 'operation' => $operation);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Replicapool_Operation");
  }

  
  public function listZoneOperations($project, $zone, $optParams = array())
  {
    $params = array('project' => $project, 'zone' => $zone);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Replicapool_OperationList");
  }
}




class Google_Service_Replicapool_InstanceGroupManager extends Google_Collection
{
  protected $collection_key = 'targetPools';
  protected $internal_gapi_mappings = array(
  );
  protected $autoHealingPoliciesType = 'Google_Service_Replicapool_ReplicaPoolAutoHealingPolicy';
  protected $autoHealingPoliciesDataType = 'array';
  public $baseInstanceName;
  public $creationTimestamp;
  public $currentSize;
  public $description;
  public $fingerprint;
  public $group;
  public $id;
  public $instanceTemplate;
  public $kind;
  public $name;
  public $selfLink;
  public $targetPools;
  public $targetSize;


  public function setAutoHealingPolicies($autoHealingPolicies)
  {
    $this->autoHealingPolicies = $autoHealingPolicies;
  }
  public function getAutoHealingPolicies()
  {
    return $this->autoHealingPolicies;
  }
  public function setBaseInstanceName($baseInstanceName)
  {
    $this->baseInstanceName = $baseInstanceName;
  }
  public function getBaseInstanceName()
  {
    return $this->baseInstanceName;
  }
  public function setCreationTimestamp($creationTimestamp)
  {
    $this->creationTimestamp = $creationTimestamp;
  }
  public function getCreationTimestamp()
  {
    return $this->creationTimestamp;
  }
  public function setCurrentSize($currentSize)
  {
    $this->currentSize = $currentSize;
  }
  public function getCurrentSize()
  {
    return $this->currentSize;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setGroup($group)
  {
    $this->group = $group;
  }
  public function getGroup()
  {
    return $this->group;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInstanceTemplate($instanceTemplate)
  {
    $this->instanceTemplate = $instanceTemplate;
  }
  public function getInstanceTemplate()
  {
    return $this->instanceTemplate;
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
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTargetPools($targetPools)
  {
    $this->targetPools = $targetPools;
  }
  public function getTargetPools()
  {
    return $this->targetPools;
  }
  public function setTargetSize($targetSize)
  {
    $this->targetSize = $targetSize;
  }
  public function getTargetSize()
  {
    return $this->targetSize;
  }
}

class Google_Service_Replicapool_InstanceGroupManagerList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $id;
  protected $itemsType = 'Google_Service_Replicapool_InstanceGroupManager';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  public $selfLink;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
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
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

class Google_Service_Replicapool_InstanceGroupManagersAbandonInstancesRequest extends Google_Collection
{
  protected $collection_key = 'instances';
  protected $internal_gapi_mappings = array(
  );
  public $instances;


  public function setInstances($instances)
  {
    $this->instances = $instances;
  }
  public function getInstances()
  {
    return $this->instances;
  }
}

class Google_Service_Replicapool_InstanceGroupManagersDeleteInstancesRequest extends Google_Collection
{
  protected $collection_key = 'instances';
  protected $internal_gapi_mappings = array(
  );
  public $instances;


  public function setInstances($instances)
  {
    $this->instances = $instances;
  }
  public function getInstances()
  {
    return $this->instances;
  }
}

class Google_Service_Replicapool_InstanceGroupManagersRecreateInstancesRequest extends Google_Collection
{
  protected $collection_key = 'instances';
  protected $internal_gapi_mappings = array(
  );
  public $instances;


  public function setInstances($instances)
  {
    $this->instances = $instances;
  }
  public function getInstances()
  {
    return $this->instances;
  }
}

class Google_Service_Replicapool_InstanceGroupManagersSetInstanceTemplateRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $instanceTemplate;


  public function setInstanceTemplate($instanceTemplate)
  {
    $this->instanceTemplate = $instanceTemplate;
  }
  public function getInstanceTemplate()
  {
    return $this->instanceTemplate;
  }
}

class Google_Service_Replicapool_InstanceGroupManagersSetTargetPoolsRequest extends Google_Collection
{
  protected $collection_key = 'targetPools';
  protected $internal_gapi_mappings = array(
  );
  public $fingerprint;
  public $targetPools;


  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setTargetPools($targetPools)
  {
    $this->targetPools = $targetPools;
  }
  public function getTargetPools()
  {
    return $this->targetPools;
  }
}

class Google_Service_Replicapool_Operation extends Google_Collection
{
  protected $collection_key = 'warnings';
  protected $internal_gapi_mappings = array(
  );
  public $clientOperationId;
  public $creationTimestamp;
  public $endTime;
  protected $errorType = 'Google_Service_Replicapool_OperationError';
  protected $errorDataType = '';
  public $httpErrorMessage;
  public $httpErrorStatusCode;
  public $id;
  public $insertTime;
  public $kind;
  public $name;
  public $operationType;
  public $progress;
  public $region;
  public $selfLink;
  public $startTime;
  public $status;
  public $statusMessage;
  public $targetId;
  public $targetLink;
  public $user;
  protected $warningsType = 'Google_Service_Replicapool_OperationWarnings';
  protected $warningsDataType = 'array';
  public $zone;


  public function setClientOperationId($clientOperationId)
  {
    $this->clientOperationId = $clientOperationId;
  }
  public function getClientOperationId()
  {
    return $this->clientOperationId;
  }
  public function setCreationTimestamp($creationTimestamp)
  {
    $this->creationTimestamp = $creationTimestamp;
  }
  public function getCreationTimestamp()
  {
    return $this->creationTimestamp;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setError(Google_Service_Replicapool_OperationError $error)
  {
    $this->error = $error;
  }
  public function getError()
  {
    return $this->error;
  }
  public function setHttpErrorMessage($httpErrorMessage)
  {
    $this->httpErrorMessage = $httpErrorMessage;
  }
  public function getHttpErrorMessage()
  {
    return $this->httpErrorMessage;
  }
  public function setHttpErrorStatusCode($httpErrorStatusCode)
  {
    $this->httpErrorStatusCode = $httpErrorStatusCode;
  }
  public function getHttpErrorStatusCode()
  {
    return $this->httpErrorStatusCode;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInsertTime($insertTime)
  {
    $this->insertTime = $insertTime;
  }
  public function getInsertTime()
  {
    return $this->insertTime;
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
  public function setOperationType($operationType)
  {
    $this->operationType = $operationType;
  }
  public function getOperationType()
  {
    return $this->operationType;
  }
  public function setProgress($progress)
  {
    $this->progress = $progress;
  }
  public function getProgress()
  {
    return $this->progress;
  }
  public function setRegion($region)
  {
    $this->region = $region;
  }
  public function getRegion()
  {
    return $this->region;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  public function getStartTime()
  {
    return $this->startTime;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setStatusMessage($statusMessage)
  {
    $this->statusMessage = $statusMessage;
  }
  public function getStatusMessage()
  {
    return $this->statusMessage;
  }
  public function setTargetId($targetId)
  {
    $this->targetId = $targetId;
  }
  public function getTargetId()
  {
    return $this->targetId;
  }
  public function setTargetLink($targetLink)
  {
    $this->targetLink = $targetLink;
  }
  public function getTargetLink()
  {
    return $this->targetLink;
  }
  public function setUser($user)
  {
    $this->user = $user;
  }
  public function getUser()
  {
    return $this->user;
  }
  public function setWarnings($warnings)
  {
    $this->warnings = $warnings;
  }
  public function getWarnings()
  {
    return $this->warnings;
  }
  public function setZone($zone)
  {
    $this->zone = $zone;
  }
  public function getZone()
  {
    return $this->zone;
  }
}

class Google_Service_Replicapool_OperationError extends Google_Collection
{
  protected $collection_key = 'errors';
  protected $internal_gapi_mappings = array(
  );
  protected $errorsType = 'Google_Service_Replicapool_OperationErrorErrors';
  protected $errorsDataType = 'array';


  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  public function getErrors()
  {
    return $this->errors;
  }
}

class Google_Service_Replicapool_OperationErrorErrors extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $code;
  public $location;
  public $message;


  public function setCode($code)
  {
    $this->code = $code;
  }
  public function getCode()
  {
    return $this->code;
  }
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
}

class Google_Service_Replicapool_OperationList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $id;
  protected $itemsType = 'Google_Service_Replicapool_Operation';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  public $selfLink;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
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
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

class Google_Service_Replicapool_OperationWarnings extends Google_Collection
{
  protected $collection_key = 'data';
  protected $internal_gapi_mappings = array(
  );
  public $code;
  protected $dataType = 'Google_Service_Replicapool_OperationWarningsData';
  protected $dataDataType = 'array';
  public $message;


  public function setCode($code)
  {
    $this->code = $code;
  }
  public function getCode()
  {
    return $this->code;
  }
  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $this->data;
  }
  public function setMessage($message)
  {
    $this->message = $message;
  }
  public function getMessage()
  {
    return $this->message;
  }
}

class Google_Service_Replicapool_OperationWarningsData extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $key;
  public $value;


  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
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

class Google_Service_Replicapool_ReplicaPoolAutoHealingPolicy extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $actionType;
  public $healthCheck;


  public function setActionType($actionType)
  {
    $this->actionType = $actionType;
  }
  public function getActionType()
  {
    return $this->actionType;
  }
  public function setHealthCheck($healthCheck)
  {
    $this->healthCheck = $healthCheck;
  }
  public function getHealthCheck()
  {
    return $this->healthCheck;
  }
}
