<?php



class Google_Service_Translate extends Google_Service
{


  public $detections;
  public $languages;
  public $translations;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'language/translate/';
    $this->version = 'v2';
    $this->serviceName = 'translate';

    $this->detections = new Google_Service_Translate_Detections_Resource(
        $this,
        $this->serviceName,
        'detections',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v2/detect',
              'httpMethod' => 'GET',
              'parameters' => array(
                'q' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->languages = new Google_Service_Translate_Languages_Resource(
        $this,
        $this->serviceName,
        'languages',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v2/languages',
              'httpMethod' => 'GET',
              'parameters' => array(
                'target' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->translations = new Google_Service_Translate_Translations_Resource(
        $this,
        $this->serviceName,
        'translations',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v2',
              'httpMethod' => 'GET',
              'parameters' => array(
                'q' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                  'required' => true,
                ),
                'target' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'source' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'format' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'cid' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_Translate_Detections_Resource extends Google_Service_Resource
{

  
  public function listDetections($q, $optParams = array())
  {
    $params = array('q' => $q);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Translate_DetectionsListResponse");
  }
}


class Google_Service_Translate_Languages_Resource extends Google_Service_Resource
{

  
  public function listLanguages($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Translate_LanguagesListResponse");
  }
}


class Google_Service_Translate_Translations_Resource extends Google_Service_Resource
{

  
  public function listTranslations($q, $target, $optParams = array())
  {
    $params = array('q' => $q, 'target' => $target);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Translate_TranslationsListResponse");
  }
}




class Google_Service_Translate_DetectionsListResponse extends Google_Collection
{
  protected $collection_key = 'detections';
  protected $internal_gapi_mappings = array(
  );
  protected $detectionsType = 'Google_Service_Translate_DetectionsResourceItems';
  protected $detectionsDataType = 'array';


  public function setDetections($detections)
  {
    $this->detections = $detections;
  }
  public function getDetections()
  {
    return $this->detections;
  }
}

class Google_Service_Translate_DetectionsResourceItems extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $confidence;
  public $isReliable;
  public $language;


  public function setConfidence($confidence)
  {
    $this->confidence = $confidence;
  }
  public function getConfidence()
  {
    return $this->confidence;
  }
  public function setIsReliable($isReliable)
  {
    $this->isReliable = $isReliable;
  }
  public function getIsReliable()
  {
    return $this->isReliable;
  }
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
}

class Google_Service_Translate_LanguagesListResponse extends Google_Collection
{
  protected $collection_key = 'languages';
  protected $internal_gapi_mappings = array(
  );
  protected $languagesType = 'Google_Service_Translate_LanguagesResource';
  protected $languagesDataType = 'array';


  public function setLanguages($languages)
  {
    $this->languages = $languages;
  }
  public function getLanguages()
  {
    return $this->languages;
  }
}

class Google_Service_Translate_LanguagesResource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $language;
  public $name;


  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
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

class Google_Service_Translate_TranslationsListResponse extends Google_Collection
{
  protected $collection_key = 'translations';
  protected $internal_gapi_mappings = array(
  );
  protected $translationsType = 'Google_Service_Translate_TranslationsResource';
  protected $translationsDataType = 'array';


  public function setTranslations($translations)
  {
    $this->translations = $translations;
  }
  public function getTranslations()
  {
    return $this->translations;
  }
}

class Google_Service_Translate_TranslationsResource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $detectedSourceLanguage;
  public $translatedText;


  public function setDetectedSourceLanguage($detectedSourceLanguage)
  {
    $this->detectedSourceLanguage = $detectedSourceLanguage;
  }
  public function getDetectedSourceLanguage()
  {
    return $this->detectedSourceLanguage;
  }
  public function setTranslatedText($translatedText)
  {
    $this->translatedText = $translatedText;
  }
  public function getTranslatedText()
  {
    return $this->translatedText;
  }
}
