<?php



class Google_Service_Script extends Google_Service
{
  
  const MAIL_GOOGLE_COM =
      "https://mail.google.com/";
  
  const WWW_GOOGLE_COM_CALENDAR_FEEDS =
      "https://www.google.com/calendar/feeds";
  
  const WWW_GOOGLE_COM_M8_FEEDS =
      "https://www.google.com/m8/feeds";
  
  const ADMIN_DIRECTORY_GROUP =
      "https://www.googleapis.com/auth/admin.directory.group";
  
  const ADMIN_DIRECTORY_USER =
      "https://www.googleapis.com/auth/admin.directory.user";
  
  const DRIVE =
      "https://www.googleapis.com/auth/drive";
  
  const FORMS =
      "https://www.googleapis.com/auth/forms";
  
  const FORMS_CURRENTONLY =
      "https://www.googleapis.com/auth/forms.currentonly";
  
  const GROUPS =
      "https://www.googleapis.com/auth/groups";
  
  const USERINFO_EMAIL =
      "https://www.googleapis.com/auth/userinfo.email";

  public $scripts;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://script.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1';
    $this->serviceName = 'script';

    $this->scripts = new Google_Service_Script_Scripts_Resource(
        $this,
        $this->serviceName,
        'scripts',
        array(
          'methods' => array(
            'run' => array(
              'path' => 'v1/scripts/{scriptId}:run',
              'httpMethod' => 'POST',
              'parameters' => array(
                'scriptId' => array(
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



class Google_Service_Script_Scripts_Resource extends Google_Service_Resource
{

  
  public function run($scriptId, Google_Service_Script_ExecutionRequest $postBody, $optParams = array())
  {
    $params = array('scriptId' => $scriptId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('run', array($params), "Google_Service_Script_Operation");
  }
}




class Google_Service_Script_ExecutionError extends Google_Collection
{
  protected $collection_key = 'scriptStackTraceElements';
  protected $internal_gapi_mappings = array(
  );
  public $errorMessage;
  public $errorType;
  protected $scriptStackTraceElementsType = 'Google_Service_Script_ScriptStackTraceElement';
  protected $scriptStackTraceElementsDataType = 'array';


  public function setErrorMessage($errorMessage)
  {
    $this->errorMessage = $errorMessage;
  }
  public function getErrorMessage()
  {
    return $this->errorMessage;
  }
  public function setErrorType($errorType)
  {
    $this->errorType = $errorType;
  }
  public function getErrorType()
  {
    return $this->errorType;
  }
  public function setScriptStackTraceElements($scriptStackTraceElements)
  {
    $this->scriptStackTraceElements = $scriptStackTraceElements;
  }
  public function getScriptStackTraceElements()
  {
    return $this->scriptStackTraceElements;
  }
}

class Google_Service_Script_ExecutionRequest extends Google_Collection
{
  protected $collection_key = 'parameters';
  protected $internal_gapi_mappings = array(
  );
  public $devMode;
  public $function;
  public $parameters;
  public $sessionState;


  public function setDevMode($devMode)
  {
    $this->devMode = $devMode;
  }
  public function getDevMode()
  {
    return $this->devMode;
  }
  public function setFunction($function)
  {
    $this->function = $function;
  }
  public function getFunction()
  {
    return $this->function;
  }
  public function setParameters($parameters)
  {
    $this->parameters = $parameters;
  }
  public function getParameters()
  {
    return $this->parameters;
  }
  public function setSessionState($sessionState)
  {
    $this->sessionState = $sessionState;
  }
  public function getSessionState()
  {
    return $this->sessionState;
  }
}

class Google_Service_Script_ExecutionResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $result;


  public function setResult($result)
  {
    $this->result = $result;
  }
  public function getResult()
  {
    return $this->result;
  }
}

class Google_Service_Script_Operation extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $done;
  protected $errorType = 'Google_Service_Script_Status';
  protected $errorDataType = '';
  public $metadata;
  public $name;
  public $response;


  public function setDone($done)
  {
    $this->done = $done;
  }
  public function getDone()
  {
    return $this->done;
  }
  public function setError(Google_Service_Script_Status $error)
  {
    $this->error = $error;
  }
  public function getError()
  {
    return $this->error;
  }
  public function setMetadata($metadata)
  {
    $this->metadata = $metadata;
  }
  public function getMetadata()
  {
    return $this->metadata;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
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

class Google_Service_Script_OperationMetadata extends Google_Model
{
}

class Google_Service_Script_OperationResponse extends Google_Model
{
}

class Google_Service_Script_ScriptStackTraceElement extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $function;
  public $lineNumber;


  public function setFunction($function)
  {
    $this->function = $function;
  }
  public function getFunction()
  {
    return $this->function;
  }
  public function setLineNumber($lineNumber)
  {
    $this->lineNumber = $lineNumber;
  }
  public function getLineNumber()
  {
    return $this->lineNumber;
  }
}

class Google_Service_Script_Status extends Google_Collection
{
  protected $collection_key = 'details';
  protected $internal_gapi_mappings = array(
  );
  public $code;
  public $details;
  public $message;


  public function setCode($code)
  {
    $this->code = $code;
  }
  public function getCode()
  {
    return $this->code;
  }
  public function setDetails($details)
  {
    $this->details = $details;
  }
  public function getDetails()
  {
    return $this->details;
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

class Google_Service_Script_StatusDetails extends Google_Model
{
}
