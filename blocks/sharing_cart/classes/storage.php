<?php

namespace sharing_cart;


class storage
{
	const COMPONENT = 'user';
	const FILEAREA  = 'backup';
	const ITEMID    = 0;
	const FILEPATH  = '/';
	
	
	private $storage;
	
	private $context;
	
	
	public function __construct($userid = null)
	{
		$this->storage = \get_file_storage();
		$this->context = \context_user::instance($userid ?: $GLOBALS['USER']->id);
	}
	
	
	public function copy_from(\stored_file $file)
	{
		$filerecord = (object)array(
			'contextid' => $this->context->id,
			'component' => self::COMPONENT,
			'filearea'  => self::FILEAREA,
			'itemid'    => self::ITEMID,
			'filepath'  => self::FILEPATH,
			);
		$this->storage->create_file_from_storedfile($filerecord, $file);
	}
	
	
	public function get($filename)
	{
		return $this->storage->get_file($this->context->id,
			self::COMPONENT, self::FILEAREA, self::ITEMID, self::FILEPATH,
			$filename);
	}
	
	
	public function delete($filename)
	{
		$file = $this->get($filename);
		return $file && $file->delete();
	}
}
