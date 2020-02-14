<?php



class Google_Service_YouTubeAnalytics extends Google_Service
{
  
  const YOUTUBE =
      "https://www.googleapis.com/auth/youtube";
  
  const YOUTUBE_READONLY =
      "https://www.googleapis.com/auth/youtube.readonly";
  
  const YOUTUBEPARTNER =
      "https://www.googleapis.com/auth/youtubepartner";
  
  const YT_ANALYTICS_MONETARY_READONLY =
      "https://www.googleapis.com/auth/yt-analytics-monetary.readonly";
  
  const YT_ANALYTICS_READONLY =
      "https://www.googleapis.com/auth/yt-analytics.readonly";

  public $batchReportDefinitions;
  public $batchReports;
  public $groupItems;
  public $groups;
  public $reports;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'youtube/analytics/v1/';
    $this->version = 'v1';
    $this->serviceName = 'youtubeAnalytics';

    $this->batchReportDefinitions = new Google_Service_YouTubeAnalytics_BatchReportDefinitions_Resource(
        $this,
        $this->serviceName,
        'batchReportDefinitions',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'batchReportDefinitions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->batchReports = new Google_Service_YouTubeAnalytics_BatchReports_Resource(
        $this,
        $this->serviceName,
        'batchReports',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'batchReports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'batchReportDefinitionId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->groupItems = new Google_Service_YouTubeAnalytics_GroupItems_Resource(
        $this,
        $this->serviceName,
        'groupItems',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'groupItems',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'groupItems',
              'httpMethod' => 'POST',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'groupItems',
              'httpMethod' => 'GET',
              'parameters' => array(
                'groupId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->groups = new Google_Service_YouTubeAnalytics_Groups_Resource(
        $this,
        $this->serviceName,
        'groups',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'groups',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'groups',
              'httpMethod' => 'POST',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'groups',
              'httpMethod' => 'GET',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'groups',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->reports = new Google_Service_YouTubeAnalytics_Reports_Resource(
        $this,
        $this->serviceName,
        'reports',
        array(
          'methods' => array(
            'query' => array(
              'path' => 'reports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'ids' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'start-date' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'end-date' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'metrics' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'dimensions' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'currency' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'filters' => array(
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



class Google_Service_YouTubeAnalytics_BatchReportDefinitions_Resource extends Google_Service_Resource
{

  
  public function listBatchReportDefinitions($onBehalfOfContentOwner, $optParams = array())
  {
    $params = array('onBehalfOfContentOwner' => $onBehalfOfContentOwner);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeAnalytics_BatchReportDefinitionList");
  }
}


class Google_Service_YouTubeAnalytics_BatchReports_Resource extends Google_Service_Resource
{

  
  public function listBatchReports($batchReportDefinitionId, $onBehalfOfContentOwner, $optParams = array())
  {
    $params = array('batchReportDefinitionId' => $batchReportDefinitionId, 'onBehalfOfContentOwner' => $onBehalfOfContentOwner);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeAnalytics_BatchReportList");
  }
}


class Google_Service_YouTubeAnalytics_GroupItems_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert(Google_Service_YouTubeAnalytics_GroupItem $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTubeAnalytics_GroupItem");
  }

  
  public function listGroupItems($groupId, $optParams = array())
  {
    $params = array('groupId' => $groupId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeAnalytics_GroupItemListResponse");
  }
}


class Google_Service_YouTubeAnalytics_Groups_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert(Google_Service_YouTubeAnalytics_Group $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTubeAnalytics_Group");
  }

  
  public function listGroups($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeAnalytics_GroupListResponse");
  }

  
  public function update(Google_Service_YouTubeAnalytics_Group $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTubeAnalytics_Group");
  }
}


class Google_Service_YouTubeAnalytics_Reports_Resource extends Google_Service_Resource
{

  
  public function query($ids, $startDate, $endDate, $metrics, $optParams = array())
  {
    $params = array('ids' => $ids, 'start-date' => $startDate, 'end-date' => $endDate, 'metrics' => $metrics);
    $params = array_merge($params, $optParams);
    return $this->call('query', array($params), "Google_Service_YouTubeAnalytics_ResultTable");
  }
}




class Google_Service_YouTubeAnalytics_BatchReport extends Google_Collection
{
  protected $collection_key = 'outputs';
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  protected $outputsType = 'Google_Service_YouTubeAnalytics_BatchReportOutputs';
  protected $outputsDataType = 'array';
  public $reportId;
  protected $timeSpanType = 'Google_Service_YouTubeAnalytics_BatchReportTimeSpan';
  protected $timeSpanDataType = '';
  public $timeUpdated;


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
  public function setOutputs($outputs)
  {
    $this->outputs = $outputs;
  }
  public function getOutputs()
  {
    return $this->outputs;
  }
  public function setReportId($reportId)
  {
    $this->reportId = $reportId;
  }
  public function getReportId()
  {
    return $this->reportId;
  }
  public function setTimeSpan(Google_Service_YouTubeAnalytics_BatchReportTimeSpan $timeSpan)
  {
    $this->timeSpan = $timeSpan;
  }
  public function getTimeSpan()
  {
    return $this->timeSpan;
  }
  public function setTimeUpdated($timeUpdated)
  {
    $this->timeUpdated = $timeUpdated;
  }
  public function getTimeUpdated()
  {
    return $this->timeUpdated;
  }
}

class Google_Service_YouTubeAnalytics_BatchReportDefinition extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $name;
  public $status;
  public $type;


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
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
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

class Google_Service_YouTubeAnalytics_BatchReportDefinitionList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_YouTubeAnalytics_BatchReportDefinition';
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

class Google_Service_YouTubeAnalytics_BatchReportList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_YouTubeAnalytics_BatchReport';
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

class Google_Service_YouTubeAnalytics_BatchReportOutputs extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $downloadUrl;
  public $format;
  public $type;


  public function setDownloadUrl($downloadUrl)
  {
    $this->downloadUrl = $downloadUrl;
  }
  public function getDownloadUrl()
  {
    return $this->downloadUrl;
  }
  public function setFormat($format)
  {
    $this->format = $format;
  }
  public function getFormat()
  {
    return $this->format;
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

class Google_Service_YouTubeAnalytics_BatchReportTimeSpan extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $endTime;
  public $startTime;


  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
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

class Google_Service_YouTubeAnalytics_Group extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $contentDetailsType = 'Google_Service_YouTubeAnalytics_GroupContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTubeAnalytics_GroupSnippet';
  protected $snippetDataType = '';


  public function setContentDetails(Google_Service_YouTubeAnalytics_GroupContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
  }
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
  public function setSnippet(Google_Service_YouTubeAnalytics_GroupSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTubeAnalytics_GroupContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $itemCount;
  public $itemType;


  public function setItemCount($itemCount)
  {
    $this->itemCount = $itemCount;
  }
  public function getItemCount()
  {
    return $this->itemCount;
  }
  public function setItemType($itemType)
  {
    $this->itemType = $itemType;
  }
  public function getItemType()
  {
    return $this->itemType;
  }
}

class Google_Service_YouTubeAnalytics_GroupItem extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $groupId;
  public $id;
  public $kind;
  protected $resourceType = 'Google_Service_YouTubeAnalytics_GroupItemResource';
  protected $resourceDataType = '';


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setGroupId($groupId)
  {
    $this->groupId = $groupId;
  }
  public function getGroupId()
  {
    return $this->groupId;
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
  public function setResource(Google_Service_YouTubeAnalytics_GroupItemResource $resource)
  {
    $this->resource = $resource;
  }
  public function getResource()
  {
    return $this->resource;
  }
}

class Google_Service_YouTubeAnalytics_GroupItemListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_YouTubeAnalytics_GroupItem';
  protected $itemsDataType = 'array';
  public $kind;


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
}

class Google_Service_YouTubeAnalytics_GroupItemResource extends Google_Model
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

class Google_Service_YouTubeAnalytics_GroupListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_YouTubeAnalytics_Group';
  protected $itemsDataType = 'array';
  public $kind;


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
}

class Google_Service_YouTubeAnalytics_GroupSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $publishedAt;
  public $title;


  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
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

class Google_Service_YouTubeAnalytics_ResultTable extends Google_Collection
{
  protected $collection_key = 'rows';
  protected $internal_gapi_mappings = array(
  );
  protected $columnHeadersType = 'Google_Service_YouTubeAnalytics_ResultTableColumnHeaders';
  protected $columnHeadersDataType = 'array';
  public $kind;
  public $rows;


  public function setColumnHeaders($columnHeaders)
  {
    $this->columnHeaders = $columnHeaders;
  }
  public function getColumnHeaders()
  {
    return $this->columnHeaders;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
}

class Google_Service_YouTubeAnalytics_ResultTableColumnHeaders extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $columnType;
  public $dataType;
  public $name;


  public function setColumnType($columnType)
  {
    $this->columnType = $columnType;
  }
  public function getColumnType()
  {
    return $this->columnType;
  }
  public function setDataType($dataType)
  {
    $this->dataType = $dataType;
  }
  public function getDataType()
  {
    return $this->dataType;
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
