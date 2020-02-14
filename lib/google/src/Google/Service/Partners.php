<?php



class Google_Service_Partners extends Google_Service
{


  public $clientMessages;
  public $companies;
  public $companies_leads;
  public $userEvents;
  public $userStates;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://partners.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v2';
    $this->serviceName = 'partners';

    $this->clientMessages = new Google_Service_Partners_ClientMessages_Resource(
        $this,
        $this->serviceName,
        'clientMessages',
        array(
          'methods' => array(
            'log' => array(
              'path' => 'v2/clientMessages:log',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->companies = new Google_Service_Partners_Companies_Resource(
        $this,
        $this->serviceName,
        'companies',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v2/companies/{companyId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'companyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.userOverrides.userId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.userOverrides.ipAddress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.partnersSessionId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.trafficSource.trafficSubId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'address' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.experimentIds' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'currencyCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.trafficSource.trafficSourceId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v2/companies',
              'httpMethod' => 'GET',
              'parameters' => array(
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'requestMetadata.partnersSessionId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxMonthlyBudget.currencyCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxMonthlyBudget.nanos' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'languageCodes' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'minMonthlyBudget.nanos' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'requestMetadata.trafficSource.trafficSubId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'industries' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'minMonthlyBudget.currencyCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'minMonthlyBudget.units' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.trafficSource.trafficSourceId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'companyName' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'address' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'services' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'requestMetadata.experimentIds' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'gpsMotivations' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'requestMetadata.userOverrides.ipAddress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'websiteUrl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.userOverrides.userId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxMonthlyBudget.units' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->companies_leads = new Google_Service_Partners_CompaniesLeads_Resource(
        $this,
        $this->serviceName,
        'leads',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v2/companies/{companyId}/leads',
              'httpMethod' => 'POST',
              'parameters' => array(
                'companyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->userEvents = new Google_Service_Partners_UserEvents_Resource(
        $this,
        $this->serviceName,
        'userEvents',
        array(
          'methods' => array(
            'log' => array(
              'path' => 'v2/userEvents:log',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->userStates = new Google_Service_Partners_UserStates_Resource(
        $this,
        $this->serviceName,
        'userStates',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v2/userStates',
              'httpMethod' => 'GET',
              'parameters' => array(
                'requestMetadata.userOverrides.userId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.userOverrides.ipAddress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.partnersSessionId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.trafficSource.trafficSubId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.experimentIds' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'requestMetadata.trafficSource.trafficSourceId' => array(
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



class Google_Service_Partners_ClientMessages_Resource extends Google_Service_Resource
{

  
  public function log(Google_Service_Partners_LogMessageRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('log', array($params), "Google_Service_Partners_LogMessageResponse");
  }
}


class Google_Service_Partners_Companies_Resource extends Google_Service_Resource
{

  
  public function get($companyId, $optParams = array())
  {
    $params = array('companyId' => $companyId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Partners_GetCompanyResponse");
  }

  
  public function listCompanies($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Partners_ListCompaniesResponse");
  }
}


class Google_Service_Partners_CompaniesLeads_Resource extends Google_Service_Resource
{

  
  public function create($companyId, Google_Service_Partners_CreateLeadRequest $postBody, $optParams = array())
  {
    $params = array('companyId' => $companyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Partners_CreateLeadResponse");
  }
}


class Google_Service_Partners_UserEvents_Resource extends Google_Service_Resource
{

  
  public function log(Google_Service_Partners_LogUserEventRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('log', array($params), "Google_Service_Partners_LogUserEventResponse");
  }
}


class Google_Service_Partners_UserStates_Resource extends Google_Service_Resource
{

  
  public function listUserStates($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Partners_ListUserStatesResponse");
  }
}




class Google_Service_Partners_CertificationExamStatus extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $numberUsersPass;
  public $type;


  public function setNumberUsersPass($numberUsersPass)
  {
    $this->numberUsersPass = $numberUsersPass;
  }
  public function getNumberUsersPass()
  {
    return $this->numberUsersPass;
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

class Google_Service_Partners_CertificationStatus extends Google_Collection
{
  protected $collection_key = 'examStatuses';
  protected $internal_gapi_mappings = array(
  );
  protected $examStatusesType = 'Google_Service_Partners_CertificationExamStatus';
  protected $examStatusesDataType = 'array';
  public $isCertified;
  public $type;


  public function setExamStatuses($examStatuses)
  {
    $this->examStatuses = $examStatuses;
  }
  public function getExamStatuses()
  {
    return $this->examStatuses;
  }
  public function setIsCertified($isCertified)
  {
    $this->isCertified = $isCertified;
  }
  public function getIsCertified()
  {
    return $this->isCertified;
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

class Google_Service_Partners_Company extends Google_Collection
{
  protected $collection_key = 'services';
  protected $internal_gapi_mappings = array(
  );
  protected $certificationStatusesType = 'Google_Service_Partners_CertificationStatus';
  protected $certificationStatusesDataType = 'array';
  protected $convertedMinMonthlyBudgetType = 'Google_Service_Partners_Money';
  protected $convertedMinMonthlyBudgetDataType = '';
  public $id;
  public $industries;
  protected $localizedInfosType = 'Google_Service_Partners_LocalizedCompanyInfo';
  protected $localizedInfosDataType = 'array';
  protected $locationsType = 'Google_Service_Partners_Location';
  protected $locationsDataType = 'array';
  public $name;
  protected $originalMinMonthlyBudgetType = 'Google_Service_Partners_Money';
  protected $originalMinMonthlyBudgetDataType = '';
  protected $publicProfileType = 'Google_Service_Partners_PublicProfile';
  protected $publicProfileDataType = '';
  protected $ranksType = 'Google_Service_Partners_Rank';
  protected $ranksDataType = 'array';
  public $services;
  public $websiteUrl;


  public function setCertificationStatuses($certificationStatuses)
  {
    $this->certificationStatuses = $certificationStatuses;
  }
  public function getCertificationStatuses()
  {
    return $this->certificationStatuses;
  }
  public function setConvertedMinMonthlyBudget(Google_Service_Partners_Money $convertedMinMonthlyBudget)
  {
    $this->convertedMinMonthlyBudget = $convertedMinMonthlyBudget;
  }
  public function getConvertedMinMonthlyBudget()
  {
    return $this->convertedMinMonthlyBudget;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIndustries($industries)
  {
    $this->industries = $industries;
  }
  public function getIndustries()
  {
    return $this->industries;
  }
  public function setLocalizedInfos($localizedInfos)
  {
    $this->localizedInfos = $localizedInfos;
  }
  public function getLocalizedInfos()
  {
    return $this->localizedInfos;
  }
  public function setLocations($locations)
  {
    $this->locations = $locations;
  }
  public function getLocations()
  {
    return $this->locations;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOriginalMinMonthlyBudget(Google_Service_Partners_Money $originalMinMonthlyBudget)
  {
    $this->originalMinMonthlyBudget = $originalMinMonthlyBudget;
  }
  public function getOriginalMinMonthlyBudget()
  {
    return $this->originalMinMonthlyBudget;
  }
  public function setPublicProfile(Google_Service_Partners_PublicProfile $publicProfile)
  {
    $this->publicProfile = $publicProfile;
  }
  public function getPublicProfile()
  {
    return $this->publicProfile;
  }
  public function setRanks($ranks)
  {
    $this->ranks = $ranks;
  }
  public function getRanks()
  {
    return $this->ranks;
  }
  public function setServices($services)
  {
    $this->services = $services;
  }
  public function getServices()
  {
    return $this->services;
  }
  public function setWebsiteUrl($websiteUrl)
  {
    $this->websiteUrl = $websiteUrl;
  }
  public function getWebsiteUrl()
  {
    return $this->websiteUrl;
  }
}

class Google_Service_Partners_CreateLeadRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $leadType = 'Google_Service_Partners_Lead';
  protected $leadDataType = '';
  protected $recaptchaChallengeType = 'Google_Service_Partners_RecaptchaChallenge';
  protected $recaptchaChallengeDataType = '';
  protected $requestMetadataType = 'Google_Service_Partners_RequestMetadata';
  protected $requestMetadataDataType = '';


  public function setLead(Google_Service_Partners_Lead $lead)
  {
    $this->lead = $lead;
  }
  public function getLead()
  {
    return $this->lead;
  }
  public function setRecaptchaChallenge(Google_Service_Partners_RecaptchaChallenge $recaptchaChallenge)
  {
    $this->recaptchaChallenge = $recaptchaChallenge;
  }
  public function getRecaptchaChallenge()
  {
    return $this->recaptchaChallenge;
  }
  public function setRequestMetadata(Google_Service_Partners_RequestMetadata $requestMetadata)
  {
    $this->requestMetadata = $requestMetadata;
  }
  public function getRequestMetadata()
  {
    return $this->requestMetadata;
  }
}

class Google_Service_Partners_CreateLeadResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $leadType = 'Google_Service_Partners_Lead';
  protected $leadDataType = '';
  public $recaptchaStatus;
  protected $responseMetadataType = 'Google_Service_Partners_ResponseMetadata';
  protected $responseMetadataDataType = '';


  public function setLead(Google_Service_Partners_Lead $lead)
  {
    $this->lead = $lead;
  }
  public function getLead()
  {
    return $this->lead;
  }
  public function setRecaptchaStatus($recaptchaStatus)
  {
    $this->recaptchaStatus = $recaptchaStatus;
  }
  public function getRecaptchaStatus()
  {
    return $this->recaptchaStatus;
  }
  public function setResponseMetadata(Google_Service_Partners_ResponseMetadata $responseMetadata)
  {
    $this->responseMetadata = $responseMetadata;
  }
  public function getResponseMetadata()
  {
    return $this->responseMetadata;
  }
}

class Google_Service_Partners_DebugInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $serverInfo;
  public $serverTraceInfo;
  public $serviceUrl;


  public function setServerInfo($serverInfo)
  {
    $this->serverInfo = $serverInfo;
  }
  public function getServerInfo()
  {
    return $this->serverInfo;
  }
  public function setServerTraceInfo($serverTraceInfo)
  {
    $this->serverTraceInfo = $serverTraceInfo;
  }
  public function getServerTraceInfo()
  {
    return $this->serverTraceInfo;
  }
  public function setServiceUrl($serviceUrl)
  {
    $this->serviceUrl = $serviceUrl;
  }
  public function getServiceUrl()
  {
    return $this->serviceUrl;
  }
}

class Google_Service_Partners_EventData extends Google_Collection
{
  protected $collection_key = 'values';
  protected $internal_gapi_mappings = array(
  );
  public $key;
  public $values;


  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
  }
  public function setValues($values)
  {
    $this->values = $values;
  }
  public function getValues()
  {
    return $this->values;
  }
}

class Google_Service_Partners_GetCompanyResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $companyType = 'Google_Service_Partners_Company';
  protected $companyDataType = '';
  protected $responseMetadataType = 'Google_Service_Partners_ResponseMetadata';
  protected $responseMetadataDataType = '';


  public function setCompany(Google_Service_Partners_Company $company)
  {
    $this->company = $company;
  }
  public function getCompany()
  {
    return $this->company;
  }
  public function setResponseMetadata(Google_Service_Partners_ResponseMetadata $responseMetadata)
  {
    $this->responseMetadata = $responseMetadata;
  }
  public function getResponseMetadata()
  {
    return $this->responseMetadata;
  }
}

class Google_Service_Partners_LatLng extends Google_Model
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

class Google_Service_Partners_Lead extends Google_Collection
{
  protected $collection_key = 'gpsMotivations';
  protected $internal_gapi_mappings = array(
  );
  public $comments;
  public $email;
  public $familyName;
  public $givenName;
  public $gpsMotivations;
  public $id;
  protected $minMonthlyBudgetType = 'Google_Service_Partners_Money';
  protected $minMonthlyBudgetDataType = '';
  public $phoneNumber;
  public $type;
  public $websiteUrl;


  public function setComments($comments)
  {
    $this->comments = $comments;
  }
  public function getComments()
  {
    return $this->comments;
  }
  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function getEmail()
  {
    return $this->email;
  }
  public function setFamilyName($familyName)
  {
    $this->familyName = $familyName;
  }
  public function getFamilyName()
  {
    return $this->familyName;
  }
  public function setGivenName($givenName)
  {
    $this->givenName = $givenName;
  }
  public function getGivenName()
  {
    return $this->givenName;
  }
  public function setGpsMotivations($gpsMotivations)
  {
    $this->gpsMotivations = $gpsMotivations;
  }
  public function getGpsMotivations()
  {
    return $this->gpsMotivations;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setMinMonthlyBudget(Google_Service_Partners_Money $minMonthlyBudget)
  {
    $this->minMonthlyBudget = $minMonthlyBudget;
  }
  public function getMinMonthlyBudget()
  {
    return $this->minMonthlyBudget;
  }
  public function setPhoneNumber($phoneNumber)
  {
    $this->phoneNumber = $phoneNumber;
  }
  public function getPhoneNumber()
  {
    return $this->phoneNumber;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setWebsiteUrl($websiteUrl)
  {
    $this->websiteUrl = $websiteUrl;
  }
  public function getWebsiteUrl()
  {
    return $this->websiteUrl;
  }
}

class Google_Service_Partners_ListCompaniesResponse extends Google_Collection
{
  protected $collection_key = 'companies';
  protected $internal_gapi_mappings = array(
  );
  protected $companiesType = 'Google_Service_Partners_Company';
  protected $companiesDataType = 'array';
  public $nextPageToken;
  protected $responseMetadataType = 'Google_Service_Partners_ResponseMetadata';
  protected $responseMetadataDataType = '';


  public function setCompanies($companies)
  {
    $this->companies = $companies;
  }
  public function getCompanies()
  {
    return $this->companies;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setResponseMetadata(Google_Service_Partners_ResponseMetadata $responseMetadata)
  {
    $this->responseMetadata = $responseMetadata;
  }
  public function getResponseMetadata()
  {
    return $this->responseMetadata;
  }
}

class Google_Service_Partners_ListUserStatesResponse extends Google_Collection
{
  protected $collection_key = 'userStates';
  protected $internal_gapi_mappings = array(
  );
  protected $responseMetadataType = 'Google_Service_Partners_ResponseMetadata';
  protected $responseMetadataDataType = '';
  public $userStates;


  public function setResponseMetadata(Google_Service_Partners_ResponseMetadata $responseMetadata)
  {
    $this->responseMetadata = $responseMetadata;
  }
  public function getResponseMetadata()
  {
    return $this->responseMetadata;
  }
  public function setUserStates($userStates)
  {
    $this->userStates = $userStates;
  }
  public function getUserStates()
  {
    return $this->userStates;
  }
}

class Google_Service_Partners_LocalizedCompanyInfo extends Google_Collection
{
  protected $collection_key = 'countryCodes';
  protected $internal_gapi_mappings = array(
  );
  public $countryCodes;
  public $displayName;
  public $languageCode;
  public $overview;


  public function setCountryCodes($countryCodes)
  {
    $this->countryCodes = $countryCodes;
  }
  public function getCountryCodes()
  {
    return $this->countryCodes;
  }
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setLanguageCode($languageCode)
  {
    $this->languageCode = $languageCode;
  }
  public function getLanguageCode()
  {
    return $this->languageCode;
  }
  public function setOverview($overview)
  {
    $this->overview = $overview;
  }
  public function getOverview()
  {
    return $this->overview;
  }
}

class Google_Service_Partners_Location extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $address;
  protected $latLngType = 'Google_Service_Partners_LatLng';
  protected $latLngDataType = '';


  public function setAddress($address)
  {
    $this->address = $address;
  }
  public function getAddress()
  {
    return $this->address;
  }
  public function setLatLng(Google_Service_Partners_LatLng $latLng)
  {
    $this->latLng = $latLng;
  }
  public function getLatLng()
  {
    return $this->latLng;
  }
}

class Google_Service_Partners_LogMessageRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $clientInfo;
  public $details;
  public $level;
  protected $requestMetadataType = 'Google_Service_Partners_RequestMetadata';
  protected $requestMetadataDataType = '';


  public function setClientInfo($clientInfo)
  {
    $this->clientInfo = $clientInfo;
  }
  public function getClientInfo()
  {
    return $this->clientInfo;
  }
  public function setDetails($details)
  {
    $this->details = $details;
  }
  public function getDetails()
  {
    return $this->details;
  }
  public function setLevel($level)
  {
    $this->level = $level;
  }
  public function getLevel()
  {
    return $this->level;
  }
  public function setRequestMetadata(Google_Service_Partners_RequestMetadata $requestMetadata)
  {
    $this->requestMetadata = $requestMetadata;
  }
  public function getRequestMetadata()
  {
    return $this->requestMetadata;
  }
}

class Google_Service_Partners_LogMessageRequestClientInfo extends Google_Model
{
}

class Google_Service_Partners_LogMessageResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $responseMetadataType = 'Google_Service_Partners_ResponseMetadata';
  protected $responseMetadataDataType = '';


  public function setResponseMetadata(Google_Service_Partners_ResponseMetadata $responseMetadata)
  {
    $this->responseMetadata = $responseMetadata;
  }
  public function getResponseMetadata()
  {
    return $this->responseMetadata;
  }
}

class Google_Service_Partners_LogUserEventRequest extends Google_Collection
{
  protected $collection_key = 'eventDatas';
  protected $internal_gapi_mappings = array(
  );
  public $eventAction;
  public $eventCategory;
  protected $eventDatasType = 'Google_Service_Partners_EventData';
  protected $eventDatasDataType = 'array';
  public $eventScope;
  protected $leadType = 'Google_Service_Partners_Lead';
  protected $leadDataType = '';
  protected $requestMetadataType = 'Google_Service_Partners_RequestMetadata';
  protected $requestMetadataDataType = '';
  public $url;


  public function setEventAction($eventAction)
  {
    $this->eventAction = $eventAction;
  }
  public function getEventAction()
  {
    return $this->eventAction;
  }
  public function setEventCategory($eventCategory)
  {
    $this->eventCategory = $eventCategory;
  }
  public function getEventCategory()
  {
    return $this->eventCategory;
  }
  public function setEventDatas($eventDatas)
  {
    $this->eventDatas = $eventDatas;
  }
  public function getEventDatas()
  {
    return $this->eventDatas;
  }
  public function setEventScope($eventScope)
  {
    $this->eventScope = $eventScope;
  }
  public function getEventScope()
  {
    return $this->eventScope;
  }
  public function setLead(Google_Service_Partners_Lead $lead)
  {
    $this->lead = $lead;
  }
  public function getLead()
  {
    return $this->lead;
  }
  public function setRequestMetadata(Google_Service_Partners_RequestMetadata $requestMetadata)
  {
    $this->requestMetadata = $requestMetadata;
  }
  public function getRequestMetadata()
  {
    return $this->requestMetadata;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Partners_LogUserEventResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $responseMetadataType = 'Google_Service_Partners_ResponseMetadata';
  protected $responseMetadataDataType = '';


  public function setResponseMetadata(Google_Service_Partners_ResponseMetadata $responseMetadata)
  {
    $this->responseMetadata = $responseMetadata;
  }
  public function getResponseMetadata()
  {
    return $this->responseMetadata;
  }
}

class Google_Service_Partners_Money extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currencyCode;
  public $nanos;
  public $units;


  public function setCurrencyCode($currencyCode)
  {
    $this->currencyCode = $currencyCode;
  }
  public function getCurrencyCode()
  {
    return $this->currencyCode;
  }
  public function setNanos($nanos)
  {
    $this->nanos = $nanos;
  }
  public function getNanos()
  {
    return $this->nanos;
  }
  public function setUnits($units)
  {
    $this->units = $units;
  }
  public function getUnits()
  {
    return $this->units;
  }
}

class Google_Service_Partners_PublicProfile extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $displayImageUrl;
  public $displayName;
  public $id;
  public $url;


  public function setDisplayImageUrl($displayImageUrl)
  {
    $this->displayImageUrl = $displayImageUrl;
  }
  public function getDisplayImageUrl()
  {
    return $this->displayImageUrl;
  }
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Partners_Rank extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $type;
  public $value;


  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
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

class Google_Service_Partners_RecaptchaChallenge extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $response;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setResponse($response)
  {
    $this->response = $response;
  }
  public function getResponse()
  {
    return $this->response;
  }
}

class Google_Service_Partners_RequestMetadata extends Google_Collection
{
  protected $collection_key = 'experimentIds';
  protected $internal_gapi_mappings = array(
  );
  public $experimentIds;
  public $locale;
  public $partnersSessionId;
  protected $trafficSourceType = 'Google_Service_Partners_TrafficSource';
  protected $trafficSourceDataType = '';
  protected $userOverridesType = 'Google_Service_Partners_UserOverrides';
  protected $userOverridesDataType = '';


  public function setExperimentIds($experimentIds)
  {
    $this->experimentIds = $experimentIds;
  }
  public function getExperimentIds()
  {
    return $this->experimentIds;
  }
  public function setLocale($locale)
  {
    $this->locale = $locale;
  }
  public function getLocale()
  {
    return $this->locale;
  }
  public function setPartnersSessionId($partnersSessionId)
  {
    $this->partnersSessionId = $partnersSessionId;
  }
  public function getPartnersSessionId()
  {
    return $this->partnersSessionId;
  }
  public function setTrafficSource(Google_Service_Partners_TrafficSource $trafficSource)
  {
    $this->trafficSource = $trafficSource;
  }
  public function getTrafficSource()
  {
    return $this->trafficSource;
  }
  public function setUserOverrides(Google_Service_Partners_UserOverrides $userOverrides)
  {
    $this->userOverrides = $userOverrides;
  }
  public function getUserOverrides()
  {
    return $this->userOverrides;
  }
}

class Google_Service_Partners_ResponseMetadata extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $debugInfoType = 'Google_Service_Partners_DebugInfo';
  protected $debugInfoDataType = '';


  public function setDebugInfo(Google_Service_Partners_DebugInfo $debugInfo)
  {
    $this->debugInfo = $debugInfo;
  }
  public function getDebugInfo()
  {
    return $this->debugInfo;
  }
}

class Google_Service_Partners_TrafficSource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $trafficSourceId;
  public $trafficSubId;


  public function setTrafficSourceId($trafficSourceId)
  {
    $this->trafficSourceId = $trafficSourceId;
  }
  public function getTrafficSourceId()
  {
    return $this->trafficSourceId;
  }
  public function setTrafficSubId($trafficSubId)
  {
    $this->trafficSubId = $trafficSubId;
  }
  public function getTrafficSubId()
  {
    return $this->trafficSubId;
  }
}

class Google_Service_Partners_UserOverrides extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $ipAddress;
  public $userId;


  public function setIpAddress($ipAddress)
  {
    $this->ipAddress = $ipAddress;
  }
  public function getIpAddress()
  {
    return $this->ipAddress;
  }
  public function setUserId($userId)
  {
    $this->userId = $userId;
  }
  public function getUserId()
  {
    return $this->userId;
  }
}
