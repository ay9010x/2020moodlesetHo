<?php



class Google_Service_Taskqueue extends Google_Service
{
  
  const TASKQUEUE =
      "https://www.googleapis.com/auth/taskqueue";
  
  const TASKQUEUE_CONSUMER =
      "https://www.googleapis.com/auth/taskqueue.consumer";

  public $taskqueues;
  public $tasks;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'taskqueue/v1beta2/projects/';
    $this->version = 'v1beta2';
    $this->serviceName = 'taskqueue';

    $this->taskqueues = new Google_Service_Taskqueue_Taskqueues_Resource(
        $this,
        $this->serviceName,
        'taskqueues',
        array(
          'methods' => array(
            'get' => array(
              'path' => '{project}/taskqueues/{taskqueue}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'getStats' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->tasks = new Google_Service_Taskqueue_Tasks_Resource(
        $this,
        $this->serviceName,
        'tasks',
        array(
          'methods' => array(
            'delete' => array(
              'path' => '{project}/taskqueues/{taskqueue}/tasks/{task}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'task' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{project}/taskqueues/{taskqueue}/tasks/{task}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'task' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => '{project}/taskqueues/{taskqueue}/tasks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'lease' => array(
              'path' => '{project}/taskqueues/{taskqueue}/tasks/lease',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'numTasks' => array(
                  'location' => 'query',
                  'type' => 'integer',
                  'required' => true,
                ),
                'leaseSecs' => array(
                  'location' => 'query',
                  'type' => 'integer',
                  'required' => true,
                ),
                'groupByTag' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'tag' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => '{project}/taskqueues/{taskqueue}/tasks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{project}/taskqueues/{taskqueue}/tasks/{task}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'task' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'newLeaseSeconds' => array(
                  'location' => 'query',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{project}/taskqueues/{taskqueue}/tasks/{task}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'taskqueue' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'task' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'newLeaseSeconds' => array(
                  'location' => 'query',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_Taskqueue_Taskqueues_Resource extends Google_Service_Resource
{

  
  public function get($project, $taskqueue, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Taskqueue_TaskQueue");
  }
}


class Google_Service_Taskqueue_Tasks_Resource extends Google_Service_Resource
{

  
  public function delete($project, $taskqueue, $task, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue, 'task' => $task);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($project, $taskqueue, $task, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue, 'task' => $task);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Taskqueue_Task");
  }

  
  public function insert($project, $taskqueue, Google_Service_Taskqueue_Task $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Taskqueue_Task");
  }

  
  public function lease($project, $taskqueue, $numTasks, $leaseSecs, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue, 'numTasks' => $numTasks, 'leaseSecs' => $leaseSecs);
    $params = array_merge($params, $optParams);
    return $this->call('lease', array($params), "Google_Service_Taskqueue_Tasks");
  }

  
  public function listTasks($project, $taskqueue, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Taskqueue_Tasks2");
  }

  
  public function patch($project, $taskqueue, $task, $newLeaseSeconds, Google_Service_Taskqueue_Task $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue, 'task' => $task, 'newLeaseSeconds' => $newLeaseSeconds, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Taskqueue_Task");
  }

  
  public function update($project, $taskqueue, $task, $newLeaseSeconds, Google_Service_Taskqueue_Task $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'taskqueue' => $taskqueue, 'task' => $task, 'newLeaseSeconds' => $newLeaseSeconds, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Taskqueue_Task");
  }
}




class Google_Service_Taskqueue_Task extends Google_Model
{
  protected $internal_gapi_mappings = array(
        "retryCount" => "retry_count",
  );
  public $enqueueTimestamp;
  public $id;
  public $kind;
  public $leaseTimestamp;
  public $payloadBase64;
  public $queueName;
  public $retryCount;
  public $tag;


  public function setEnqueueTimestamp($enqueueTimestamp)
  {
    $this->enqueueTimestamp = $enqueueTimestamp;
  }
  public function getEnqueueTimestamp()
  {
    return $this->enqueueTimestamp;
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
  public function setLeaseTimestamp($leaseTimestamp)
  {
    $this->leaseTimestamp = $leaseTimestamp;
  }
  public function getLeaseTimestamp()
  {
    return $this->leaseTimestamp;
  }
  public function setPayloadBase64($payloadBase64)
  {
    $this->payloadBase64 = $payloadBase64;
  }
  public function getPayloadBase64()
  {
    return $this->payloadBase64;
  }
  public function setQueueName($queueName)
  {
    $this->queueName = $queueName;
  }
  public function getQueueName()
  {
    return $this->queueName;
  }
  public function setRetryCount($retryCount)
  {
    $this->retryCount = $retryCount;
  }
  public function getRetryCount()
  {
    return $this->retryCount;
  }
  public function setTag($tag)
  {
    $this->tag = $tag;
  }
  public function getTag()
  {
    return $this->tag;
  }
}

class Google_Service_Taskqueue_TaskQueue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $aclType = 'Google_Service_Taskqueue_TaskQueueAcl';
  protected $aclDataType = '';
  public $id;
  public $kind;
  public $maxLeases;
  protected $statsType = 'Google_Service_Taskqueue_TaskQueueStats';
  protected $statsDataType = '';


  public function setAcl(Google_Service_Taskqueue_TaskQueueAcl $acl)
  {
    $this->acl = $acl;
  }
  public function getAcl()
  {
    return $this->acl;
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
  public function setMaxLeases($maxLeases)
  {
    $this->maxLeases = $maxLeases;
  }
  public function getMaxLeases()
  {
    return $this->maxLeases;
  }
  public function setStats(Google_Service_Taskqueue_TaskQueueStats $stats)
  {
    $this->stats = $stats;
  }
  public function getStats()
  {
    return $this->stats;
  }
}

class Google_Service_Taskqueue_TaskQueueAcl extends Google_Collection
{
  protected $collection_key = 'producerEmails';
  protected $internal_gapi_mappings = array(
  );
  public $adminEmails;
  public $consumerEmails;
  public $producerEmails;


  public function setAdminEmails($adminEmails)
  {
    $this->adminEmails = $adminEmails;
  }
  public function getAdminEmails()
  {
    return $this->adminEmails;
  }
  public function setConsumerEmails($consumerEmails)
  {
    $this->consumerEmails = $consumerEmails;
  }
  public function getConsumerEmails()
  {
    return $this->consumerEmails;
  }
  public function setProducerEmails($producerEmails)
  {
    $this->producerEmails = $producerEmails;
  }
  public function getProducerEmails()
  {
    return $this->producerEmails;
  }
}

class Google_Service_Taskqueue_TaskQueueStats extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $leasedLastHour;
  public $leasedLastMinute;
  public $oldestTask;
  public $totalTasks;


  public function setLeasedLastHour($leasedLastHour)
  {
    $this->leasedLastHour = $leasedLastHour;
  }
  public function getLeasedLastHour()
  {
    return $this->leasedLastHour;
  }
  public function setLeasedLastMinute($leasedLastMinute)
  {
    $this->leasedLastMinute = $leasedLastMinute;
  }
  public function getLeasedLastMinute()
  {
    return $this->leasedLastMinute;
  }
  public function setOldestTask($oldestTask)
  {
    $this->oldestTask = $oldestTask;
  }
  public function getOldestTask()
  {
    return $this->oldestTask;
  }
  public function setTotalTasks($totalTasks)
  {
    $this->totalTasks = $totalTasks;
  }
  public function getTotalTasks()
  {
    return $this->totalTasks;
  }
}

class Google_Service_Taskqueue_Tasks extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Taskqueue_Task';
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

class Google_Service_Taskqueue_Tasks2 extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Taskqueue_Task';
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
