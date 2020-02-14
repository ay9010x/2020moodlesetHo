<?php




class SimplePie_gzdecode
{
	
	var $compressed_data;

	
	var $compressed_size;

	
	var $min_compressed_size = 18;

	
	var $position = 0;

	
	var $flags;

	
	var $data;

	
	var $MTIME;

	
	var $XFL;

	
	var $OS;

	
	var $SI1;

	
	var $SI2;

	
	var $extra_field;

	
	var $filename;

	
	var $comment;

	
	public function __set($name, $value)
	{
		trigger_error("Cannot write property $name", E_USER_ERROR);
	}

	
	public function __construct($data)
	{
		$this->compressed_data = $data;
		$this->compressed_size = strlen($data);
	}

	
	public function parse()
	{
		if ($this->compressed_size >= $this->min_compressed_size)
		{
						if (substr($this->compressed_data, 0, 3) !== "\x1F\x8B\x08")
			{
				return false;
			}

						$this->flags = ord($this->compressed_data[3]);

						if ($this->flags > 0x1F)
			{
				return false;
			}

						$this->position += 4;

						$mtime = substr($this->compressed_data, $this->position, 4);
						if (current(unpack('S', "\x00\x01")) === 1)
			{
				$mtime = strrev($mtime);
			}
			$this->MTIME = current(unpack('l', $mtime));
			$this->position += 4;

						$this->XFL = ord($this->compressed_data[$this->position++]);

						$this->OS = ord($this->compressed_data[$this->position++]);

						if ($this->flags & 4)
			{
								$this->SI1 = $this->compressed_data[$this->position++];
				$this->SI2 = $this->compressed_data[$this->position++];

								if ($this->SI2 === "\x00")
				{
					return false;
				}

								$len = current(unpack('v', substr($this->compressed_data, $this->position, 2)));
				$this->position += 2;

								$this->min_compressed_size += $len + 4;
				if ($this->compressed_size >= $this->min_compressed_size)
				{
										$this->extra_field = substr($this->compressed_data, $this->position, $len);
					$this->position += $len;
				}
				else
				{
					return false;
				}
			}

						if ($this->flags & 8)
			{
								$len = strcspn($this->compressed_data, "\x00", $this->position);

								$this->min_compressed_size += $len + 1;
				if ($this->compressed_size >= $this->min_compressed_size)
				{
										$this->filename = substr($this->compressed_data, $this->position, $len);
					$this->position += $len + 1;
				}
				else
				{
					return false;
				}
			}

						if ($this->flags & 16)
			{
								$len = strcspn($this->compressed_data, "\x00", $this->position);

								$this->min_compressed_size += $len + 1;
				if ($this->compressed_size >= $this->min_compressed_size)
				{
										$this->comment = substr($this->compressed_data, $this->position, $len);
					$this->position += $len + 1;
				}
				else
				{
					return false;
				}
			}

						if ($this->flags & 2)
			{
								$this->min_compressed_size += $len + 2;
				if ($this->compressed_size >= $this->min_compressed_size)
				{
										$crc = current(unpack('v', substr($this->compressed_data, $this->position, 2)));

										if ((crc32(substr($this->compressed_data, 0, $this->position)) & 0xFFFF) === $crc)
					{
						$this->position += 2;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}

						if (($this->data = gzinflate(substr($this->compressed_data, $this->position, -8))) === false)
			{
				return false;
			}
			else
			{
				$this->position = $this->compressed_size - 8;
			}

						$crc = current(unpack('V', substr($this->compressed_data, $this->position, 4)));
			$this->position += 4;
			

						$isize = current(unpack('V', substr($this->compressed_data, $this->position, 4)));
			$this->position += 4;
			if (sprintf('%u', strlen($this->data) & 0xFFFFFFFF) !== sprintf('%u', $isize))
			{
				return false;
			}

						return true;
		}
		else
		{
			return false;
		}
	}
}
