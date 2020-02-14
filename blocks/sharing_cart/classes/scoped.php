<?php

namespace sharing_cart;


class scoped
{
	
	private $callback;
	
	
	public function __construct( $callback)
	{
		$this->callback = $callback;
	}
	
	
	public function __destruct()
	{
		call_user_func($this->callback);
	}
}
