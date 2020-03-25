<?php



class Google_Service_AdExchangeSeller extends Google_Service
{
  
  const ADEXCHANGE_SELLER =
      "https://www.googleapis.com/auth/adexchange.seller";
  
  const ADEXCHANGE_SELLER_READONLY =
      "https://www.googleapis.com/auth/adexchange.seller.readonly";

  public $accounts;
  public $accounts_adclients;
  public $accounts_alerts;
  public $accounts_customchannels;
  public $accounts_metadata_dimensions;
  public $accounts_metadata_metrics;
  public $accounts_preferreddeals;
  public $accounts_reports;
  public $accounts_reports_saved;
  public $accounts_urlchannels;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'adexchangeseller/v2.0/';
    $this->version = 'v2.0';
    $this->serviceName = 'adexchangeseller';

    $this->accounts = new Google_Service_AdExchangeSeller_Accounts_Resource(
        $this,
        $this->serviceName,
        'accounts',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts',
              'httpMethod' => 'GET',
              'parameters' => array(
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
    $this->accounts_adclients = new Google_Service_AdExchangeSeller_AccountsAdclients_Resource(
        $this,
        $this->serviceName,
        'adclients',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/adclients',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
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
    $this->accounts_alerts = new Google_Service_AdExchangeSeller_AccountsAlerts_Resource(
        $this,
        $this->serviceName,
        'alerts',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/alerts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_customchannels = new Google_Service_AdExchangeSeller_AccountsCustomchannels_Resource(
        $this,
        $this->serviceName,
        'customchannels',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/customchannels/{customChannelId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customChannelId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/customchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
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
    $this->accounts_metadata_dimensions = new Google_Service_AdExchangeSeller_AccountsMetadataDimensions_Resource(
        $this,
        $this->serviceName,
        'dimensions',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/metadata/dimensions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_metadata_metrics = new Google_Service_AdExchangeSeller_AccountsMetadataMetrics_Resource(
        $this,
        $this->serviceName,
        'metrics',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/metadata/metrics',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_preferreddeals = new Google_Service_AdExchangeSeller_AccountsPreferreddeals_Resource(
        $this,
        $this->serviceName,
        'preferreddeals',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}/preferreddeals/{dealId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dealId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/preferreddeals',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_reports = new Google_Service_AdExchangeSeller_AccountsReports_Resource(
        $this,
        $this->serviceName,
        'reports',
        array(
          'methods' => array(
            'generate' => array(
              'path' => 'accounts/{accountId}/reports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'startDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'endDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'metric' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'startIndex' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'dimension' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_reports_saved = new Google_Service_AdExchangeSeller_AccountsReportsSaved_Resource(
        $this,
        $this->serviceName,
        'saved',
        array(
          'methods' => array(
            'generate' => array(
              'path' => 'accounts/{accountId}/reports/{savedReportId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'savedReportId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startIndex' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/reports/saved',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
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
    $this->accounts_urlchannels = new Google_Service_AdExchangeSeller_AccountsUrlchannels_Resource(
        $this,
        $this->serviceName,
        'urlchannels',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/urlchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
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
  }
}



class Google_Service_AdExchangeSeller_Accounts_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdExchangeSeller_Account");
  }

  
  public function listAccounts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_Accounts");
  }
}


class Google_Service_AdExchangeSeller_AccountsAdclients_Resource extends Google_Service_Resource
{

  
  public function listAccountsAdclients($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_AdClients");
  }
}

class Google_Service_AdExchangeSeller_AccountsAlerts_Resource extends Google_Service_Resource
{

  
  public function listAccountsAlerts($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_Alerts");
  }
}

class Google_Service_AdExchangeSeller_AccountsCustomchannels_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $adClientId, $customChannelId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId, 'customChannelId' => $customChannelId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdExchangeSeller_CustomChannel");
  }

  
  public function listAccountsCustomchannels($accountId, $adClientId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_CustomChannels");
  }
}

class Google_Service_AdExchangeSeller_AccountsMetadata_Resource extends Google_Service_Resource
{
}


class Google_Service_AdExchangeSeller_AccountsMetadataDimensions_Resource extends Google_Service_Resource
{

  
  public function listAccountsMetadataDimensions($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_Metadata");
  }
}

class Google_Service_AdExchangeSeller_AccountsMetadataMetrics_Resource extends Google_Service_Resource
{

  
  public function listAccountsMetadataMetrics($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_Metadata");
  }
}

class Google_Service_AdExchangeSeller_AccountsPreferreddeals_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $dealId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'dealId' => $dealId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdExchangeSeller_PreferredDeal");
  }

  
  public function listAccountsPreferreddeals($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_PreferredDeals");
  }
}

class Google_Service_AdExchangeSeller_AccountsReports_Resource extends Google_Service_Resource
{

  
  public function generate($accountId, $startDate, $endDate, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'startDate' => $startDate, 'endDate' => $endDate);
    $params = array_merge($params, $optParams);
    return $this->call('generate', array($params), "Google_Service_AdExchangeSeller_Report");
  }
}


class Google_Service_AdExchangeSeller_AccountsReportsSaved_Resource extends Google_Service_Resource
{

  
  public function generate($accountId, $savedReportId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'savedReportId' => $savedReportId);
    $params = array_merge($params, $optParams);
    return $this->call('generate', array($params), "Google_Service_AdExchangeSeller_Report");
  }

  
  public function listAccountsReportsSaved($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_SavedReports");
  }
}

class Google_Service_AdExchangeSeller_AccountsUrlchannels_Resource extends Google_Service_Resource
{

  
  public function listAccountsUrlchannels($accountId, $adClientId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeSeller_UrlChannels");
  }
}




class Google_Service_AdExchangeSeller_Account extends Google_Model
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

class Google_Service_AdExchangeSeller_Accounts extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdExchangeSeller_Account';
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

class Google_Service_AdExchangeSeller_AdClient extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $arcOptIn;
  public $id;
  public $kind;
  public $productCode;
  public $supportsReporting;


  public function setArcOptIn($arcOptIn)
  {
    $this->arcOptIn = $arcOptIn;
  }
  public function getArcOptIn()
  {
    return $this->arcOptIn;
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
  public function setProductCode($productCode)
  {
    $this->productCode = $productCode;
  }
  public function getProductCode()
  {
    return $this->productCode;
  }
  public function setSupportsReporting($supportsReporting)
  {
    $this->supportsReporting = $supportsReporting;
  }
  public function getSupportsReporting()
  {
    return $this->supportsReporting;
  }
}

class Google_Service_AdExchangeSeller_AdClients extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdExchangeSeller_AdClient';
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

class Google_Service_AdExchangeSeller_Alert extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $message;
  public $severity;
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
  public function setMessage($message)
  {
    $this->message = $message;
  }
  public function getMessage()
  {
    return $this->message;
  }
  public function setSeverity($severity)
  {
    $this->severity = $severity;
  }
  public function getSeverity()
  {
    return $this->severity;
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

class Google_Service_AdExchangeSeller_Alerts extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_AdExchangeSeller_Alert';
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

class Google_Service_AdExchangeSeller_CustomChannel extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $code;
  public $id;
  public $kind;
  public $name;
  protected $targetingInfoType = 'Google_Service_AdExchangeSeller_CustomChannelTargetingInfo';
  protected $targetingInfoDataType = '';


  public function setCode($code)
  {
    $this->code = $code;
  }
  public function getCode()
  {
    return $this->code;
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
  public function setTargetingInfo(Google_Service_AdExchangeSeller_CustomChannelTargetingInfo $targetingInfo)
  {
    $this->targetingInfo = $targetingInfo;
  }
  public function getTargetingInfo()
  {
    return $this->targetingInfo;
  }
}

class Google_Service_AdExchangeSeller_CustomChannelTargetingInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $adsAppearOn;
  public $description;
  public $location;
  public $siteLanguage;


  public function setAdsAppearOn($adsAppearOn)
  {
    $this->adsAppearOn = $adsAppearOn;
  }
  public function getAdsAppearOn()
  {
    return $this->adsAppearOn;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLocation($location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setSiteLanguage($siteLanguage)
  {
    $this->siteLanguage = $siteLanguage;
  }
  public function getSiteLanguage()
  {
    return $this->siteLanguage;
  }
}

class Google_Service_AdExchangeSeller_CustomChannels extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdExchangeSeller_CustomChannel';
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

class Google_Service_AdExchangeSeller_Metadata extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_AdExchangeSeller_ReportingMetadataEntry';
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

class Google_Service_AdExchangeSeller_PreferredDeal extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $advertiserName;
  public $buyerNetworkName;
  public $currencyCode;
  public $endTime;
  public $fixedCpm;
  public $id;
  public $kind;
  public $startTime;


  public function setAdvertiserName($advertiserName)
  {
    $this->advertiserName = $advertiserName;
  }
  public function getAdvertiserName()
  {
    return $this->advertiserName;
  }
  public function setBuyerNetworkName($buyerNetworkName)
  {
    $this->buyerNetworkName = $buyerNetworkName;
  }
  public function getBuyerNetworkName()
  {
    return $this->buyerNetworkName;
  }
  public function setCurrencyCode($currencyCode)
  {
    $this->currencyCode = $currencyCode;
  }
  public function getCurrencyCode()
  {
    return $this->currencyCode;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setFixedCpm($fixedCpm)
  {
    $this->fixedCpm = $fixedCpm;
  }
  public function getFixedCpm()
  {
    return $this->fixedCpm;
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
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  public function getStartTime()
  {
    return $this->startTime;
  }
}

class Google_Service_AdExchangeSeller_PreferredDeals extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_AdExchangeSeller_PreferredDeal';
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

class Google_Service_AdExchangeSeller_Report extends Google_Collection
{
  protected $collection_key = 'warnings';
  protected $internal_gapi_mappings = array(
  );
  public $averages;
  protected $headersType = 'Google_Service_AdExchangeSeller_ReportHeaders';
  protected $headersDataType = 'array';
  public $kind;
  public $rows;
  public $totalMatchedRows;
  public $totals;
  public $warnings;


  public function setAverages($averages)
  {
    $this->averages = $averages;
  }
  public function getAverages()
  {
    return $this->averages;
  }
  public function setHeaders($headers)
  {
    $this->headers = $headers;
  }
  public function getHeaders()
  {
    return $this->headers;
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
  public function setTotalMatchedRows($totalMatchedRows)
  {
    $this->totalMatchedRows = $totalMatchedRows;
  }
  public function getTotalMatchedRows()
  {
    return $this->totalMatchedRows;
  }
  public function setTotals($totals)
  {
    $this->totals = $totals;
  }
  public function getTotals()
  {
    return $this->totals;
  }
  public function setWarnings($warnings)
  {
    $this->warnings = $warnings;
  }
  public function getWarnings()
  {
    return $this->warnings;
  }
}

class Google_Service_AdExchangeSeller_ReportHeaders extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currency;
  public $name;
  public $type;


  public function setCurrency($currency)
  {
    $this->currency = $currency;
  }
  public function getCurrency()
  {
    return $this->currency;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
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

class Google_Service_AdExchangeSeller_ReportingMetadataEntry extends Google_Collection
{
  protected $collection_key = 'supportedProducts';
  protected $internal_gapi_mappings = array(
  );
  public $compatibleDimensions;
  public $compatibleMetrics;
  public $id;
  public $kind;
  public $requiredDimensions;
  public $requiredMetrics;
  public $supportedProducts;


  public function setCompatibleDimensions($compatibleDimensions)
  {
    $this->compatibleDimensions = $compatibleDimensions;
  }
  public function getCompatibleDimensions()
  {
    return $this->compatibleDimensions;
  }
  public function setCompatibleMetrics($compatibleMetrics)
  {
    $this->compatibleMetrics = $compatibleMetrics;
  }
  public function getCompatibleMetrics()
  {
    return $this->compatibleMetrics;
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
  public function setRequiredDimensions($requiredDimensions)
  {
    $this->requiredDimensions = $requiredDimensions;
  }
  public function getRequiredDimensions()
  {
    return $this->requiredDimensions;
  }
  public function setRequiredMetrics($requiredMetrics)
  {
    $this->requiredMetrics = $requiredMetrics;
  }
  public function getRequiredMetrics()
  {
    return $this->requiredMetrics;
  }
  public function setSupportedProducts($supportedProducts)
  {
    $this->supportedProducts = $supportedProducts;
  }
  public function getSupportedProducts()
  {
    return $this->supportedProducts;
  }
}

class Google_Service_AdExchangeSeller_SavedReport extends Google_Model
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

class Google_Service_AdExchangeSeller_SavedReports extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdExchangeSeller_SavedReport';
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

class Google_Service_AdExchangeSeller_UrlChannel extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $urlPattern;


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
  public function setUrlPattern($urlPattern)
  {
    $this->urlPattern = $urlPattern;
  }
  public function getUrlPattern()
  {
    return $this->urlPattern;
  }
}

class Google_Service_AdExchangeSeller_UrlChannels extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdExchangeSeller_UrlChannel';
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
