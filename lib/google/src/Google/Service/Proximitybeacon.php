<?php



class Google_Service_Proximitybeacon extends Google_Service
{


  public $beaconinfo;
  public $beacons;
  public $beacons_attachments;
  public $beacons_diagnostics;
  public $namespaces;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://proximitybeacon.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1beta1';
    $this->serviceName = 'proximitybeacon';

    $this->beaconinfo = new Google_Service_Proximitybeacon_Beaconinfo_Resource(
        $this,
        $this->serviceName,
        'beaconinfo',
        array(
          'methods' => array(
            'getforobserved' => array(
              'path' => 'v1beta1/beaconinfo:getforobserved',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->beacons = new Google_Service_Proximitybeacon_Beacons_Resource(
        $this,
        $this->serviceName,
        'beacons',
        array(
          'methods' => array(
            'activate' => array(
              'path' => 'v1beta1/{+beaconName}:activate',
              'httpMethod' => 'POST',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'deactivate' => array(
              'path' => 'v1beta1/{+beaconName}:deactivate',
              'httpMethod' => 'POST',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'decommission' => array(
              'path' => 'v1beta1/{+beaconName}:decommission',
              'httpMethod' => 'POST',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1beta1/{+beaconName}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1beta1/beacons',
              'httpMethod' => 'GET',
              'parameters' => array(
                'q' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'register' => array(
              'path' => 'v1beta1/beacons:register',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'update' => array(
              'path' => 'v1beta1/{+beaconName}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->beacons_attachments = new Google_Service_Proximitybeacon_BeaconsAttachments_Resource(
        $this,
        $this->serviceName,
        'attachments',
        array(
          'methods' => array(
            'batchDelete' => array(
              'path' => 'v1beta1/{+beaconName}/attachments:batchDelete',
              'httpMethod' => 'POST',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'namespacedType' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'create' => array(
              'path' => 'v1beta1/{+beaconName}/attachments',
              'httpMethod' => 'POST',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1beta1/{+attachmentName}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'attachmentName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1beta1/{+beaconName}/attachments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'namespacedType' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->beacons_diagnostics = new Google_Service_Proximitybeacon_BeaconsDiagnostics_Resource(
        $this,
        $this->serviceName,
        'diagnostics',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1beta1/{+beaconName}/diagnostics',
              'httpMethod' => 'GET',
              'parameters' => array(
                'beaconName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'alertFilter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->namespaces = new Google_Service_Proximitybeacon_Namespaces_Resource(
        $this,
        $this->serviceName,
        'namespaces',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1beta1/namespaces',
              'httpMethod' => 'GET',
              'parameters' => array(),
            ),
          )
        )
    );
  }
}



class Google_Service_Proximitybeacon_Beaconinfo_Resource extends Google_Service_Resource
{

  
  public function getforobserved(Google_Service_Proximitybeacon_GetInfoForObservedBeaconsRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getforobserved', array($params), "Google_Service_Proximitybeacon_GetInfoForObservedBeaconsResponse");
  }
}


class Google_Service_Proximitybeacon_Beacons_Resource extends Google_Service_Resource
{

  
  public function activate($beaconName, $optParams = array())
  {
    $params = array('beaconName' => $beaconName);
    $params = array_merge($params, $optParams);
    return $this->call('activate', array($params), "Google_Service_Proximitybeacon_Empty");
  }

  
  public function deactivate($beaconName, $optParams = array())
  {
    $params = array('beaconName' => $beaconName);
    $params = array_merge($params, $optParams);
    return $this->call('deactivate', array($params), "Google_Service_Proximitybeacon_Empty");
  }

  
  public function decommission($beaconName, $optParams = array())
  {
    $params = array('beaconName' => $beaconName);
    $params = array_merge($params, $optParams);
    return $this->call('decommission', array($params), "Google_Service_Proximitybeacon_Empty");
  }

  
  public function get($beaconName, $optParams = array())
  {
    $params = array('beaconName' => $beaconName);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Proximitybeacon_Beacon");
  }

  
  public function listBeacons($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Proximitybeacon_ListBeaconsResponse");
  }

  
  public function register(Google_Service_Proximitybeacon_Beacon $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('register', array($params), "Google_Service_Proximitybeacon_Beacon");
  }

  
  public function update($beaconName, Google_Service_Proximitybeacon_Beacon $postBody, $optParams = array())
  {
    $params = array('beaconName' => $beaconName, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Proximitybeacon_Beacon");
  }
}


class Google_Service_Proximitybeacon_BeaconsAttachments_Resource extends Google_Service_Resource
{

  
  public function batchDelete($beaconName, $optParams = array())
  {
    $params = array('beaconName' => $beaconName);
    $params = array_merge($params, $optParams);
    return $this->call('batchDelete', array($params), "Google_Service_Proximitybeacon_DeleteAttachmentsResponse");
  }

  
  public function create($beaconName, Google_Service_Proximitybeacon_BeaconAttachment $postBody, $optParams = array())
  {
    $params = array('beaconName' => $beaconName, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Proximitybeacon_BeaconAttachment");
  }

  
  public function delete($attachmentName, $optParams = array())
  {
    $params = array('attachmentName' => $attachmentName);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Proximitybeacon_Empty");
  }

  
  public function listBeaconsAttachments($beaconName, $optParams = array())
  {
    $params = array('beaconName' => $beaconName);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Proximitybeacon_ListBeaconAttachmentsResponse");
  }
}

class Google_Service_Proximitybeacon_BeaconsDiagnostics_Resource extends Google_Service_Resource
{

  
  public function listBeaconsDiagnostics($beaconName, $optParams = array())
  {
    $params = array('beaconName' => $beaconName);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Proximitybeacon_ListDiagnosticsResponse");
  }
}


class Google_Service_Proximitybeacon_Namespaces_Resource extends Google_Service_Resource
{

  
  public function listNamespaces($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Proximitybeacon_ListNamespacesResponse");
  }
}




class Google_Service_Proximitybeacon_AdvertisedId extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $type;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
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

class Google_Service_Proximitybeacon_AttachmentInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $data;
  public $namespacedType;


  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $this->data;
  }
  public function setNamespacedType($namespacedType)
  {
    $this->namespacedType = $namespacedType;
  }
  public function getNamespacedType()
  {
    return $this->namespacedType;
  }
}

class Google_Service_Proximitybeacon_Beacon extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $advertisedIdType = 'Google_Service_Proximitybeacon_AdvertisedId';
  protected $advertisedIdDataType = '';
  public $beaconName;
  public $description;
  public $expectedStability;
  protected $indoorLevelType = 'Google_Service_Proximitybeacon_IndoorLevel';
  protected $indoorLevelDataType = '';
  protected $latLngType = 'Google_Service_Proximitybeacon_LatLng';
  protected $latLngDataType = '';
  public $placeId;
  public $properties;
  public $status;


  public function setAdvertisedId(Google_Service_Proximitybeacon_AdvertisedId $advertisedId)
  {
    $this->advertisedId = $advertisedId;
  }
  public function getAdvertisedId()
  {
    return $this->advertisedId;
  }
  public function setBeaconName($beaconName)
  {
    $this->beaconName = $beaconName;
  }
  public function getBeaconName()
  {
    return $this->beaconName;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setExpectedStability($expectedStability)
  {
    $this->expectedStability = $expectedStability;
  }
  public function getExpectedStability()
  {
    return $this->expectedStability;
  }
  public function setIndoorLevel(Google_Service_Proximitybeacon_IndoorLevel $indoorLevel)
  {
    $this->indoorLevel = $indoorLevel;
  }
  public function getIndoorLevel()
  {
    return $this->indoorLevel;
  }
  public function setLatLng(Google_Service_Proximitybeacon_LatLng $latLng)
  {
    $this->latLng = $latLng;
  }
  public function getLatLng()
  {
    return $this->latLng;
  }
  public function setPlaceId($placeId)
  {
    $this->placeId = $placeId;
  }
  public function getPlaceId()
  {
    return $this->placeId;
  }
  public function setProperties($properties)
  {
    $this->properties = $properties;
  }
  public function getProperties()
  {
    return $this->properties;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}

class Google_Service_Proximitybeacon_BeaconAttachment extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $attachmentName;
  public $data;
  public $namespacedType;


  public function setAttachmentName($attachmentName)
  {
    $this->attachmentName = $attachmentName;
  }
  public function getAttachmentName()
  {
    return $this->attachmentName;
  }
  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $this->data;
  }
  public function setNamespacedType($namespacedType)
  {
    $this->namespacedType = $namespacedType;
  }
  public function getNamespacedType()
  {
    return $this->namespacedType;
  }
}

class Google_Service_Proximitybeacon_BeaconInfo extends Google_Collection
{
  protected $collection_key = 'attachments';
  protected $internal_gapi_mappings = array(
  );
  protected $advertisedIdType = 'Google_Service_Proximitybeacon_AdvertisedId';
  protected $advertisedIdDataType = '';
  protected $attachmentsType = 'Google_Service_Proximitybeacon_AttachmentInfo';
  protected $attachmentsDataType = 'array';
  public $beaconName;
  public $description;


  public function setAdvertisedId(Google_Service_Proximitybeacon_AdvertisedId $advertisedId)
  {
    $this->advertisedId = $advertisedId;
  }
  public function getAdvertisedId()
  {
    return $this->advertisedId;
  }
  public function setAttachments($attachments)
  {
    $this->attachments = $attachments;
  }
  public function getAttachments()
  {
    return $this->attachments;
  }
  public function setBeaconName($beaconName)
  {
    $this->beaconName = $beaconName;
  }
  public function getBeaconName()
  {
    return $this->beaconName;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
}

class Google_Service_Proximitybeacon_BeaconProperties extends Google_Model
{
}

class Google_Service_Proximitybeacon_Date extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $day;
  public $month;
  public $year;


  public function setDay($day)
  {
    $this->day = $day;
  }
  public function getDay()
  {
    return $this->day;
  }
  public function setMonth($month)
  {
    $this->month = $month;
  }
  public function getMonth()
  {
    return $this->month;
  }
  public function setYear($year)
  {
    $this->year = $year;
  }
  public function getYear()
  {
    return $this->year;
  }
}

class Google_Service_Proximitybeacon_DeleteAttachmentsResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $numDeleted;


  public function setNumDeleted($numDeleted)
  {
    $this->numDeleted = $numDeleted;
  }
  public function getNumDeleted()
  {
    return $this->numDeleted;
  }
}

class Google_Service_Proximitybeacon_Diagnostics extends Google_Collection
{
  protected $collection_key = 'alerts';
  protected $internal_gapi_mappings = array(
  );
  public $alerts;
  public $beaconName;
  protected $estimatedLowBatteryDateType = 'Google_Service_Proximitybeacon_Date';
  protected $estimatedLowBatteryDateDataType = '';


  public function setAlerts($alerts)
  {
    $this->alerts = $alerts;
  }
  public function getAlerts()
  {
    return $this->alerts;
  }
  public function setBeaconName($beaconName)
  {
    $this->beaconName = $beaconName;
  }
  public function getBeaconName()
  {
    return $this->beaconName;
  }
  public function setEstimatedLowBatteryDate(Google_Service_Proximitybeacon_Date $estimatedLowBatteryDate)
  {
    $this->estimatedLowBatteryDate = $estimatedLowBatteryDate;
  }
  public function getEstimatedLowBatteryDate()
  {
    return $this->estimatedLowBatteryDate;
  }
}

class Google_Service_Proximitybeacon_Empty extends Google_Model
{
}

class Google_Service_Proximitybeacon_GetInfoForObservedBeaconsRequest extends Google_Collection
{
  protected $collection_key = 'observations';
  protected $internal_gapi_mappings = array(
  );
  public $namespacedTypes;
  protected $observationsType = 'Google_Service_Proximitybeacon_Observation';
  protected $observationsDataType = 'array';


  public function setNamespacedTypes($namespacedTypes)
  {
    $this->namespacedTypes = $namespacedTypes;
  }
  public function getNamespacedTypes()
  {
    return $this->namespacedTypes;
  }
  public function setObservations($observations)
  {
    $this->observations = $observations;
  }
  public function getObservations()
  {
    return $this->observations;
  }
}

class Google_Service_Proximitybeacon_GetInfoForObservedBeaconsResponse extends Google_Collection
{
  protected $collection_key = 'beacons';
  protected $internal_gapi_mappings = array(
  );
  protected $beaconsType = 'Google_Service_Proximitybeacon_BeaconInfo';
  protected $beaconsDataType = 'array';


  public function setBeacons($beacons)
  {
    $this->beacons = $beacons;
  }
  public function getBeacons()
  {
    return $this->beacons;
  }
}

class Google_Service_Proximitybeacon_IndoorLevel extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $name;


  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
}

class Google_Service_Proximitybeacon_LatLng extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $latitude;
  public $longitude;


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

class Google_Service_Proximitybeacon_ListBeaconAttachmentsResponse extends Google_Collection
{
  protected $collection_key = 'attachments';
  protected $internal_gapi_mappings = array(
  );
  protected $attachmentsType = 'Google_Service_Proximitybeacon_BeaconAttachment';
  protected $attachmentsDataType = 'array';


  public function setAttachments($attachments)
  {
    $this->attachments = $attachments;
  }
  public function getAttachments()
  {
    return $this->attachments;
  }
}

class Google_Service_Proximitybeacon_ListBeaconsResponse extends Google_Collection
{
  protected $collection_key = 'beacons';
  protected $internal_gapi_mappings = array(
  );
  protected $beaconsType = 'Google_Service_Proximitybeacon_Beacon';
  protected $beaconsDataType = 'array';
  public $nextPageToken;
  public $totalCount;


  public function setBeacons($beacons)
  {
    $this->beacons = $beacons;
  }
  public function getBeacons()
  {
    return $this->beacons;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setTotalCount($totalCount)
  {
    $this->totalCount = $totalCount;
  }
  public function getTotalCount()
  {
    return $this->totalCount;
  }
}

class Google_Service_Proximitybeacon_ListDiagnosticsResponse extends Google_Collection
{
  protected $collection_key = 'diagnostics';
  protected $internal_gapi_mappings = array(
  );
  protected $diagnosticsType = 'Google_Service_Proximitybeacon_Diagnostics';
  protected $diagnosticsDataType = 'array';
  public $nextPageToken;


  public function setDiagnostics($diagnostics)
  {
    $this->diagnostics = $diagnostics;
  }
  public function getDiagnostics()
  {
    return $this->diagnostics;
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

class Google_Service_Proximitybeacon_ListNamespacesResponse extends Google_Collection
{
  protected $collection_key = 'namespaces';
  protected $internal_gapi_mappings = array(
  );
  protected $namespacesType = 'Google_Service_Proximitybeacon_ProximitybeaconNamespace';
  protected $namespacesDataType = 'array';


  public function setNamespaces($namespaces)
  {
    $this->namespaces = $namespaces;
  }
  public function getNamespaces()
  {
    return $this->namespaces;
  }
}

class Google_Service_Proximitybeacon_Observation extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $advertisedIdType = 'Google_Service_Proximitybeacon_AdvertisedId';
  protected $advertisedIdDataType = '';
  public $telemetry;
  public $timestampMs;


  public function setAdvertisedId(Google_Service_Proximitybeacon_AdvertisedId $advertisedId)
  {
    $this->advertisedId = $advertisedId;
  }
  public function getAdvertisedId()
  {
    return $this->advertisedId;
  }
  public function setTelemetry($telemetry)
  {
    $this->telemetry = $telemetry;
  }
  public function getTelemetry()
  {
    return $this->telemetry;
  }
  public function setTimestampMs($timestampMs)
  {
    $this->timestampMs = $timestampMs;
  }
  public function getTimestampMs()
  {
    return $this->timestampMs;
  }
}

class Google_Service_Proximitybeacon_ProximitybeaconNamespace extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $namespaceName;
  public $servingVisibility;


  public function setNamespaceName($namespaceName)
  {
    $this->namespaceName = $namespaceName;
  }
  public function getNamespaceName()
  {
    return $this->namespaceName;
  }
  public function setServingVisibility($servingVisibility)
  {
    $this->servingVisibility = $servingVisibility;
  }
  public function getServingVisibility()
  {
    return $this->servingVisibility;
  }
}
