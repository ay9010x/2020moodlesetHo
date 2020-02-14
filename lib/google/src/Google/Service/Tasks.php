<?php



class Google_Service_Tasks extends Google_Service
{
  
  const TASKS =
      "https://www.googleapis.com/auth/tasks";
  
  const TASKS_READONLY =
      "https://www.googleapis.com/auth/tasks.readonly";

  public $tasklists;
  public $tasks;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'tasks/v1/';
    $this->version = 'v1';
    $this->serviceName = 'tasks';

    $this->tasklists = new Google_Service_Tasks_Tasklists_Resource(
        $this,
        $this->serviceName,
        'tasklists',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'users/@me/lists/{tasklist}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'users/@me/lists/{tasklist}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'users/@me/lists',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'list' => array(
              'path' => 'users/@me/lists',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'users/@me/lists/{tasklist}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'users/@me/lists/{tasklist}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->tasks = new Google_Service_Tasks_Tasks_Resource(
        $this,
        $this->serviceName,
        'tasks',
        array(
          'methods' => array(
            'clear' => array(
              'path' => 'lists/{tasklist}/clear',
              'httpMethod' => 'POST',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'lists/{tasklist}/tasks/{task}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'tasklist' => array(
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
              'path' => 'lists/{tasklist}/tasks/{task}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'tasklist' => array(
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
              'path' => 'lists/{tasklist}/tasks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'parent' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'previous' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'lists/{tasklist}/tasks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dueMax' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'showDeleted' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'updatedMin' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'completedMin' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'showCompleted' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'completedMax' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'showHidden' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'dueMin' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'move' => array(
              'path' => 'lists/{tasklist}/tasks/{task}/move',
              'httpMethod' => 'POST',
              'parameters' => array(
                'tasklist' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'task' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'parent' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'previous' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'lists/{tasklist}/tasks/{task}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'tasklist' => array(
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
            ),'update' => array(
              'path' => 'lists/{tasklist}/tasks/{task}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'tasklist' => array(
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
            ),
          )
        )
    );
  }
}



class Google_Service_Tasks_Tasklists_Resource extends Google_Service_Resource
{

  
  public function delete($tasklist, $optParams = array())
  {
    $params = array('tasklist' => $tasklist);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($tasklist, $optParams = array())
  {
    $params = array('tasklist' => $tasklist);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Tasks_TaskList");
  }

  
  public function insert(Google_Service_Tasks_TaskList $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Tasks_TaskList");
  }

  
  public function listTasklists($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Tasks_TaskLists");
  }

  
  public function patch($tasklist, Google_Service_Tasks_TaskList $postBody, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Tasks_TaskList");
  }

  
  public function update($tasklist, Google_Service_Tasks_TaskList $postBody, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Tasks_TaskList");
  }
}


class Google_Service_Tasks_Tasks_Resource extends Google_Service_Resource
{

  
  public function clear($tasklist, $optParams = array())
  {
    $params = array('tasklist' => $tasklist);
    $params = array_merge($params, $optParams);
    return $this->call('clear', array($params));
  }

  
  public function delete($tasklist, $task, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'task' => $task);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($tasklist, $task, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'task' => $task);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Tasks_Task");
  }

  
  public function insert($tasklist, Google_Service_Tasks_Task $postBody, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Tasks_Task");
  }

  
  public function listTasks($tasklist, $optParams = array())
  {
    $params = array('tasklist' => $tasklist);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Tasks_Tasks");
  }

  
  public function move($tasklist, $task, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'task' => $task);
    $params = array_merge($params, $optParams);
    return $this->call('move', array($params), "Google_Service_Tasks_Task");
  }

  
  public function patch($tasklist, $task, Google_Service_Tasks_Task $postBody, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'task' => $task, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Tasks_Task");
  }

  
  public function update($tasklist, $task, Google_Service_Tasks_Task $postBody, $optParams = array())
  {
    $params = array('tasklist' => $tasklist, 'task' => $task, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Tasks_Task");
  }
}




class Google_Service_Tasks_Task extends Google_Collection
{
  protected $collection_key = 'links';
  protected $internal_gapi_mappings = array(
  );
  public $completed;
  public $deleted;
  public $due;
  public $etag;
  public $hidden;
  public $id;
  public $kind;
  protected $linksType = 'Google_Service_Tasks_TaskLinks';
  protected $linksDataType = 'array';
  public $notes;
  public $parent;
  public $position;
  public $selfLink;
  public $status;
  public $title;
  public $updated;


  public function setCompleted($completed)
  {
    $this->completed = $completed;
  }
  public function getCompleted()
  {
    return $this->completed;
  }
  public function setDeleted($deleted)
  {
    $this->deleted = $deleted;
  }
  public function getDeleted()
  {
    return $this->deleted;
  }
  public function setDue($due)
  {
    $this->due = $due;
  }
  public function getDue()
  {
    return $this->due;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setHidden($hidden)
  {
    $this->hidden = $hidden;
  }
  public function getHidden()
  {
    return $this->hidden;
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
  public function setLinks($links)
  {
    $this->links = $links;
  }
  public function getLinks()
  {
    return $this->links;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setParent($parent)
  {
    $this->parent = $parent;
  }
  public function getParent()
  {
    return $this->parent;
  }
  public function setPosition($position)
  {
    $this->position = $position;
  }
  public function getPosition()
  {
    return $this->position;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
}

class Google_Service_Tasks_TaskLinks extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $link;
  public $type;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLink($link)
  {
    $this->link = $link;
  }
  public function getLink()
  {
    return $this->link;
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

class Google_Service_Tasks_TaskList extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  public $selfLink;
  public $title;
  public $updated;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
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
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
}

class Google_Service_Tasks_TaskLists extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_Tasks_TaskList';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
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
}

class Google_Service_Tasks_Tasks extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_Tasks_Task';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
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
}
