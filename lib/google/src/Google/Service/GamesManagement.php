<?php



class Google_Service_GamesManagement extends Google_Service
{
  
  const GAMES =
      "https://www.googleapis.com/auth/games";
  
  const PLUS_LOGIN =
      "https://www.googleapis.com/auth/plus.login";

  public $achievements;
  public $applications;
  public $events;
  public $players;
  public $quests;
  public $rooms;
  public $scores;
  public $turnBasedMatches;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'games/v1management/';
    $this->version = 'v1management';
    $this->serviceName = 'gamesManagement';

    $this->achievements = new Google_Service_GamesManagement_Achievements_Resource(
        $this,
        $this->serviceName,
        'achievements',
        array(
          'methods' => array(
            'reset' => array(
              'path' => 'achievements/{achievementId}/reset',
              'httpMethod' => 'POST',
              'parameters' => array(
                'achievementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetAll' => array(
              'path' => 'achievements/reset',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetAllForAllPlayers' => array(
              'path' => 'achievements/resetAllForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetForAllPlayers' => array(
              'path' => 'achievements/{achievementId}/resetForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'achievementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetMultipleForAllPlayers' => array(
              'path' => 'achievements/resetMultipleForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->applications = new Google_Service_GamesManagement_Applications_Resource(
        $this,
        $this->serviceName,
        'applications',
        array(
          'methods' => array(
            'listHidden' => array(
              'path' => 'applications/{applicationId}/players/hidden',
              'httpMethod' => 'GET',
              'parameters' => array(
                'applicationId' => array(
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
    $this->events = new Google_Service_GamesManagement_Events_Resource(
        $this,
        $this->serviceName,
        'events',
        array(
          'methods' => array(
            'reset' => array(
              'path' => 'events/{eventId}/reset',
              'httpMethod' => 'POST',
              'parameters' => array(
                'eventId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetAll' => array(
              'path' => 'events/reset',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetAllForAllPlayers' => array(
              'path' => 'events/resetAllForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetForAllPlayers' => array(
              'path' => 'events/{eventId}/resetForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'eventId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetMultipleForAllPlayers' => array(
              'path' => 'events/resetMultipleForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->players = new Google_Service_GamesManagement_Players_Resource(
        $this,
        $this->serviceName,
        'players',
        array(
          'methods' => array(
            'hide' => array(
              'path' => 'applications/{applicationId}/players/hidden/{playerId}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'applicationId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'playerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'unhide' => array(
              'path' => 'applications/{applicationId}/players/hidden/{playerId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'applicationId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'playerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->quests = new Google_Service_GamesManagement_Quests_Resource(
        $this,
        $this->serviceName,
        'quests',
        array(
          'methods' => array(
            'reset' => array(
              'path' => 'quests/{questId}/reset',
              'httpMethod' => 'POST',
              'parameters' => array(
                'questId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetAll' => array(
              'path' => 'quests/reset',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetAllForAllPlayers' => array(
              'path' => 'quests/resetAllForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetForAllPlayers' => array(
              'path' => 'quests/{questId}/resetForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'questId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetMultipleForAllPlayers' => array(
              'path' => 'quests/resetMultipleForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->rooms = new Google_Service_GamesManagement_Rooms_Resource(
        $this,
        $this->serviceName,
        'rooms',
        array(
          'methods' => array(
            'reset' => array(
              'path' => 'rooms/reset',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetForAllPlayers' => array(
              'path' => 'rooms/resetForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->scores = new Google_Service_GamesManagement_Scores_Resource(
        $this,
        $this->serviceName,
        'scores',
        array(
          'methods' => array(
            'reset' => array(
              'path' => 'leaderboards/{leaderboardId}/scores/reset',
              'httpMethod' => 'POST',
              'parameters' => array(
                'leaderboardId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetAll' => array(
              'path' => 'scores/reset',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetAllForAllPlayers' => array(
              'path' => 'scores/resetAllForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetForAllPlayers' => array(
              'path' => 'leaderboards/{leaderboardId}/scores/resetForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'leaderboardId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resetMultipleForAllPlayers' => array(
              'path' => 'scores/resetMultipleForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->turnBasedMatches = new Google_Service_GamesManagement_TurnBasedMatches_Resource(
        $this,
        $this->serviceName,
        'turnBasedMatches',
        array(
          'methods' => array(
            'reset' => array(
              'path' => 'turnbasedmatches/reset',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'resetForAllPlayers' => array(
              'path' => 'turnbasedmatches/resetForAllPlayers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
  }
}



class Google_Service_GamesManagement_Achievements_Resource extends Google_Service_Resource
{

  
  public function reset($achievementId, $optParams = array())
  {
    $params = array('achievementId' => $achievementId);
    $params = array_merge($params, $optParams);
    return $this->call('reset', array($params), "Google_Service_GamesManagement_AchievementResetResponse");
  }

  
  public function resetAll($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAll', array($params), "Google_Service_GamesManagement_AchievementResetAllResponse");
  }

  
  public function resetAllForAllPlayers($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAllForAllPlayers', array($params));
  }

  
  public function resetForAllPlayers($achievementId, $optParams = array())
  {
    $params = array('achievementId' => $achievementId);
    $params = array_merge($params, $optParams);
    return $this->call('resetForAllPlayers', array($params));
  }

  
  public function resetMultipleForAllPlayers(Google_Service_GamesManagement_AchievementResetMultipleForAllRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('resetMultipleForAllPlayers', array($params));
  }
}


class Google_Service_GamesManagement_Applications_Resource extends Google_Service_Resource
{

  
  public function listHidden($applicationId, $optParams = array())
  {
    $params = array('applicationId' => $applicationId);
    $params = array_merge($params, $optParams);
    return $this->call('listHidden', array($params), "Google_Service_GamesManagement_HiddenPlayerList");
  }
}


class Google_Service_GamesManagement_Events_Resource extends Google_Service_Resource
{

  
  public function reset($eventId, $optParams = array())
  {
    $params = array('eventId' => $eventId);
    $params = array_merge($params, $optParams);
    return $this->call('reset', array($params));
  }

  
  public function resetAll($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAll', array($params));
  }

  
  public function resetAllForAllPlayers($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAllForAllPlayers', array($params));
  }

  
  public function resetForAllPlayers($eventId, $optParams = array())
  {
    $params = array('eventId' => $eventId);
    $params = array_merge($params, $optParams);
    return $this->call('resetForAllPlayers', array($params));
  }

  
  public function resetMultipleForAllPlayers(Google_Service_GamesManagement_EventsResetMultipleForAllRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('resetMultipleForAllPlayers', array($params));
  }
}


class Google_Service_GamesManagement_Players_Resource extends Google_Service_Resource
{

  
  public function hide($applicationId, $playerId, $optParams = array())
  {
    $params = array('applicationId' => $applicationId, 'playerId' => $playerId);
    $params = array_merge($params, $optParams);
    return $this->call('hide', array($params));
  }

  
  public function unhide($applicationId, $playerId, $optParams = array())
  {
    $params = array('applicationId' => $applicationId, 'playerId' => $playerId);
    $params = array_merge($params, $optParams);
    return $this->call('unhide', array($params));
  }
}


class Google_Service_GamesManagement_Quests_Resource extends Google_Service_Resource
{

  
  public function reset($questId, $optParams = array())
  {
    $params = array('questId' => $questId);
    $params = array_merge($params, $optParams);
    return $this->call('reset', array($params));
  }

  
  public function resetAll($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAll', array($params));
  }

  
  public function resetAllForAllPlayers($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAllForAllPlayers', array($params));
  }

  
  public function resetForAllPlayers($questId, $optParams = array())
  {
    $params = array('questId' => $questId);
    $params = array_merge($params, $optParams);
    return $this->call('resetForAllPlayers', array($params));
  }

  
  public function resetMultipleForAllPlayers(Google_Service_GamesManagement_QuestsResetMultipleForAllRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('resetMultipleForAllPlayers', array($params));
  }
}


class Google_Service_GamesManagement_Rooms_Resource extends Google_Service_Resource
{

  
  public function reset($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('reset', array($params));
  }

  
  public function resetForAllPlayers($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetForAllPlayers', array($params));
  }
}


class Google_Service_GamesManagement_Scores_Resource extends Google_Service_Resource
{

  
  public function reset($leaderboardId, $optParams = array())
  {
    $params = array('leaderboardId' => $leaderboardId);
    $params = array_merge($params, $optParams);
    return $this->call('reset', array($params), "Google_Service_GamesManagement_PlayerScoreResetResponse");
  }

  
  public function resetAll($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAll', array($params), "Google_Service_GamesManagement_PlayerScoreResetAllResponse");
  }

  
  public function resetAllForAllPlayers($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetAllForAllPlayers', array($params));
  }

  
  public function resetForAllPlayers($leaderboardId, $optParams = array())
  {
    $params = array('leaderboardId' => $leaderboardId);
    $params = array_merge($params, $optParams);
    return $this->call('resetForAllPlayers', array($params));
  }

  
  public function resetMultipleForAllPlayers(Google_Service_GamesManagement_ScoresResetMultipleForAllRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('resetMultipleForAllPlayers', array($params));
  }
}


class Google_Service_GamesManagement_TurnBasedMatches_Resource extends Google_Service_Resource
{

  
  public function reset($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('reset', array($params));
  }

  
  public function resetForAllPlayers($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('resetForAllPlayers', array($params));
  }
}




class Google_Service_GamesManagement_AchievementResetAllResponse extends Google_Collection
{
  protected $collection_key = 'results';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $resultsType = 'Google_Service_GamesManagement_AchievementResetResponse';
  protected $resultsDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setResults($results)
  {
    $this->results = $results;
  }
  public function getResults()
  {
    return $this->results;
  }
}

class Google_Service_GamesManagement_AchievementResetMultipleForAllRequest extends Google_Collection
{
  protected $collection_key = 'achievement_ids';
  protected $internal_gapi_mappings = array(
        "achievementIds" => "achievement_ids",
  );
  public $achievementIds;
  public $kind;


  public function setAchievementIds($achievementIds)
  {
    $this->achievementIds = $achievementIds;
  }
  public function getAchievementIds()
  {
    return $this->achievementIds;
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

class Google_Service_GamesManagement_AchievementResetResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currentState;
  public $definitionId;
  public $kind;
  public $updateOccurred;


  public function setCurrentState($currentState)
  {
    $this->currentState = $currentState;
  }
  public function getCurrentState()
  {
    return $this->currentState;
  }
  public function setDefinitionId($definitionId)
  {
    $this->definitionId = $definitionId;
  }
  public function getDefinitionId()
  {
    return $this->definitionId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setUpdateOccurred($updateOccurred)
  {
    $this->updateOccurred = $updateOccurred;
  }
  public function getUpdateOccurred()
  {
    return $this->updateOccurred;
  }
}

class Google_Service_GamesManagement_EventsResetMultipleForAllRequest extends Google_Collection
{
  protected $collection_key = 'event_ids';
  protected $internal_gapi_mappings = array(
        "eventIds" => "event_ids",
  );
  public $eventIds;
  public $kind;


  public function setEventIds($eventIds)
  {
    $this->eventIds = $eventIds;
  }
  public function getEventIds()
  {
    return $this->eventIds;
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

class Google_Service_GamesManagement_GamesPlayedResource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $autoMatched;
  public $timeMillis;


  public function setAutoMatched($autoMatched)
  {
    $this->autoMatched = $autoMatched;
  }
  public function getAutoMatched()
  {
    return $this->autoMatched;
  }
  public function setTimeMillis($timeMillis)
  {
    $this->timeMillis = $timeMillis;
  }
  public function getTimeMillis()
  {
    return $this->timeMillis;
  }
}

class Google_Service_GamesManagement_GamesPlayerExperienceInfoResource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currentExperiencePoints;
  protected $currentLevelType = 'Google_Service_GamesManagement_GamesPlayerLevelResource';
  protected $currentLevelDataType = '';
  public $lastLevelUpTimestampMillis;
  protected $nextLevelType = 'Google_Service_GamesManagement_GamesPlayerLevelResource';
  protected $nextLevelDataType = '';


  public function setCurrentExperiencePoints($currentExperiencePoints)
  {
    $this->currentExperiencePoints = $currentExperiencePoints;
  }
  public function getCurrentExperiencePoints()
  {
    return $this->currentExperiencePoints;
  }
  public function setCurrentLevel(Google_Service_GamesManagement_GamesPlayerLevelResource $currentLevel)
  {
    $this->currentLevel = $currentLevel;
  }
  public function getCurrentLevel()
  {
    return $this->currentLevel;
  }
  public function setLastLevelUpTimestampMillis($lastLevelUpTimestampMillis)
  {
    $this->lastLevelUpTimestampMillis = $lastLevelUpTimestampMillis;
  }
  public function getLastLevelUpTimestampMillis()
  {
    return $this->lastLevelUpTimestampMillis;
  }
  public function setNextLevel(Google_Service_GamesManagement_GamesPlayerLevelResource $nextLevel)
  {
    $this->nextLevel = $nextLevel;
  }
  public function getNextLevel()
  {
    return $this->nextLevel;
  }
}

class Google_Service_GamesManagement_GamesPlayerLevelResource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $level;
  public $maxExperiencePoints;
  public $minExperiencePoints;


  public function setLevel($level)
  {
    $this->level = $level;
  }
  public function getLevel()
  {
    return $this->level;
  }
  public function setMaxExperiencePoints($maxExperiencePoints)
  {
    $this->maxExperiencePoints = $maxExperiencePoints;
  }
  public function getMaxExperiencePoints()
  {
    return $this->maxExperiencePoints;
  }
  public function setMinExperiencePoints($minExperiencePoints)
  {
    $this->minExperiencePoints = $minExperiencePoints;
  }
  public function getMinExperiencePoints()
  {
    return $this->minExperiencePoints;
  }
}

class Google_Service_GamesManagement_HiddenPlayer extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $hiddenTimeMillis;
  public $kind;
  protected $playerType = 'Google_Service_GamesManagement_Player';
  protected $playerDataType = '';


  public function setHiddenTimeMillis($hiddenTimeMillis)
  {
    $this->hiddenTimeMillis = $hiddenTimeMillis;
  }
  public function getHiddenTimeMillis()
  {
    return $this->hiddenTimeMillis;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPlayer(Google_Service_GamesManagement_Player $player)
  {
    $this->player = $player;
  }
  public function getPlayer()
  {
    return $this->player;
  }
}

class Google_Service_GamesManagement_HiddenPlayerList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_GamesManagement_HiddenPlayer';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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

class Google_Service_GamesManagement_Player extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $avatarImageUrl;
  public $bannerUrlLandscape;
  public $bannerUrlPortrait;
  public $displayName;
  protected $experienceInfoType = 'Google_Service_GamesManagement_GamesPlayerExperienceInfoResource';
  protected $experienceInfoDataType = '';
  public $kind;
  protected $lastPlayedWithType = 'Google_Service_GamesManagement_GamesPlayedResource';
  protected $lastPlayedWithDataType = '';
  protected $nameType = 'Google_Service_GamesManagement_PlayerName';
  protected $nameDataType = '';
  public $playerId;
  public $title;


  public function setAvatarImageUrl($avatarImageUrl)
  {
    $this->avatarImageUrl = $avatarImageUrl;
  }
  public function getAvatarImageUrl()
  {
    return $this->avatarImageUrl;
  }
  public function setBannerUrlLandscape($bannerUrlLandscape)
  {
    $this->bannerUrlLandscape = $bannerUrlLandscape;
  }
  public function getBannerUrlLandscape()
  {
    return $this->bannerUrlLandscape;
  }
  public function setBannerUrlPortrait($bannerUrlPortrait)
  {
    $this->bannerUrlPortrait = $bannerUrlPortrait;
  }
  public function getBannerUrlPortrait()
  {
    return $this->bannerUrlPortrait;
  }
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setExperienceInfo(Google_Service_GamesManagement_GamesPlayerExperienceInfoResource $experienceInfo)
  {
    $this->experienceInfo = $experienceInfo;
  }
  public function getExperienceInfo()
  {
    return $this->experienceInfo;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLastPlayedWith(Google_Service_GamesManagement_GamesPlayedResource $lastPlayedWith)
  {
    $this->lastPlayedWith = $lastPlayedWith;
  }
  public function getLastPlayedWith()
  {
    return $this->lastPlayedWith;
  }
  public function setName(Google_Service_GamesManagement_PlayerName $name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setPlayerId($playerId)
  {
    $this->playerId = $playerId;
  }
  public function getPlayerId()
  {
    return $this->playerId;
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

class Google_Service_GamesManagement_PlayerName extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $familyName;
  public $givenName;


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
}

class Google_Service_GamesManagement_PlayerScoreResetAllResponse extends Google_Collection
{
  protected $collection_key = 'results';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $resultsType = 'Google_Service_GamesManagement_PlayerScoreResetResponse';
  protected $resultsDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setResults($results)
  {
    $this->results = $results;
  }
  public function getResults()
  {
    return $this->results;
  }
}

class Google_Service_GamesManagement_PlayerScoreResetResponse extends Google_Collection
{
  protected $collection_key = 'resetScoreTimeSpans';
  protected $internal_gapi_mappings = array(
  );
  public $definitionId;
  public $kind;
  public $resetScoreTimeSpans;


  public function setDefinitionId($definitionId)
  {
    $this->definitionId = $definitionId;
  }
  public function getDefinitionId()
  {
    return $this->definitionId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setResetScoreTimeSpans($resetScoreTimeSpans)
  {
    $this->resetScoreTimeSpans = $resetScoreTimeSpans;
  }
  public function getResetScoreTimeSpans()
  {
    return $this->resetScoreTimeSpans;
  }
}

class Google_Service_GamesManagement_QuestsResetMultipleForAllRequest extends Google_Collection
{
  protected $collection_key = 'quest_ids';
  protected $internal_gapi_mappings = array(
        "questIds" => "quest_ids",
  );
  public $kind;
  public $questIds;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setQuestIds($questIds)
  {
    $this->questIds = $questIds;
  }
  public function getQuestIds()
  {
    return $this->questIds;
  }
}

class Google_Service_GamesManagement_ScoresResetMultipleForAllRequest extends Google_Collection
{
  protected $collection_key = 'leaderboard_ids';
  protected $internal_gapi_mappings = array(
        "leaderboardIds" => "leaderboard_ids",
  );
  public $kind;
  public $leaderboardIds;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLeaderboardIds($leaderboardIds)
  {
    $this->leaderboardIds = $leaderboardIds;
  }
  public function getLeaderboardIds()
  {
    return $this->leaderboardIds;
  }
}
