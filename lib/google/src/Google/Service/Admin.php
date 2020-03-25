<?php



class Google_Service_Admin extends Google_Service
{
  
  const EMAIL_MIGRATION =
      "https://www.googleapis.com/auth/email.migration";

  public $mail;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'email/v2/users/';
    $this->version = 'email_migration_v2';
    $this->serviceName = 'admin';

    $this->mail = new Google_Service_Admin_Mail_Resource(
        $this,
        $this->serviceName,
        'mail',
        array(
          'methods' => array(
            'insert' => array(
              'path' => '{userKey}/mail',
              'httpMethod' => 'POST',
              'parameters' => array(
                'userKey' => array(
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



class Google_Service_Admin_Mail_Resource extends Google_Service_Resource
{

  
  public function insert($userKey, Google_Service_Admin_MailItem $postBody, $optParams = array())
  {
    $params = array('userKey' => $userKey, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params));
  }
}




class Google_Service_Admin_MailItem extends Google_Collection
{
  protected $collection_key = 'labels';
  protected $internal_gapi_mappings = array(
  );
  public $isDeleted;
  public $isDraft;
  public $isInbox;
  public $isSent;
  public $isStarred;
  public $isTrash;
  public $isUnread;
  public $kind;
  public $labels;


  public function setIsDeleted($isDeleted)
  {
    $this->isDeleted = $isDeleted;
  }
  public function getIsDeleted()
  {
    return $this->isDeleted;
  }
  public function setIsDraft($isDraft)
  {
    $this->isDraft = $isDraft;
  }
  public function getIsDraft()
  {
    return $this->isDraft;
  }
  public function setIsInbox($isInbox)
  {
    $this->isInbox = $isInbox;
  }
  public function getIsInbox()
  {
    return $this->isInbox;
  }
  public function setIsSent($isSent)
  {
    $this->isSent = $isSent;
  }
  public function getIsSent()
  {
    return $this->isSent;
  }
  public function setIsStarred($isStarred)
  {
    $this->isStarred = $isStarred;
  }
  public function getIsStarred()
  {
    return $this->isStarred;
  }
  public function setIsTrash($isTrash)
  {
    $this->isTrash = $isTrash;
  }
  public function getIsTrash()
  {
    return $this->isTrash;
  }
  public function setIsUnread($isUnread)
  {
    $this->isUnread = $isUnread;
  }
  public function getIsUnread()
  {
    return $this->isUnread;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
}
