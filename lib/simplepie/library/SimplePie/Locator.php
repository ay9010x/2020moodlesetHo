<?php



class SimplePie_Locator
{
	var $useragent;
	var $timeout;
	var $file;
	var $local = array();
	var $elsewhere = array();
	var $cached_entities = array();
	var $http_base;
	var $base;
	var $base_location = 0;
	var $checked_feeds = 0;
	var $max_checked_feeds = 10;
	protected $registry;

	public function __construct(SimplePie_File $file, $timeout = 10, $useragent = null, $max_checked_feeds = 10)
	{
		$this->file = $file;
		$this->useragent = $useragent;
		$this->timeout = $timeout;
		$this->max_checked_feeds = $max_checked_feeds;

		if (class_exists('DOMDocument'))
		{
			$this->dom = new DOMDocument();

			set_error_handler(array('SimplePie_Misc', 'silence_errors'));
			$this->dom->loadHTML($this->file->body);
			restore_error_handler();
		}
		else
		{
			$this->dom = null;
		}
	}

	public function set_registry(SimplePie_Registry $registry)
	{
		$this->registry = $registry;
	}

	public function find($type = SIMPLEPIE_LOCATOR_ALL, &$working)
	{
		if ($this->is_feed($this->file))
		{
			return $this->file;
		}

		if ($this->file->method & SIMPLEPIE_FILE_SOURCE_REMOTE)
		{
			$sniffer = $this->registry->create('Content_Type_Sniffer', array($this->file));
			if ($sniffer->get_type() !== 'text/html')
			{
				return null;
			}
		}

		if ($type & ~SIMPLEPIE_LOCATOR_NONE)
		{
			$this->get_base();
		}

		if ($type & SIMPLEPIE_LOCATOR_AUTODISCOVERY && $working = $this->autodiscovery())
		{
			return $working[0];
		}

		if ($type & (SIMPLEPIE_LOCATOR_LOCAL_EXTENSION | SIMPLEPIE_LOCATOR_LOCAL_BODY | SIMPLEPIE_LOCATOR_REMOTE_EXTENSION | SIMPLEPIE_LOCATOR_REMOTE_BODY) && $this->get_links())
		{
			if ($type & SIMPLEPIE_LOCATOR_LOCAL_EXTENSION && $working = $this->extension($this->local))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_LOCAL_BODY && $working = $this->body($this->local))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_REMOTE_EXTENSION && $working = $this->extension($this->elsewhere))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_REMOTE_BODY && $working = $this->body($this->elsewhere))
			{
				return $working;
			}
		}
		return null;
	}

	public function is_feed($file)
	{
		if ($file->method & SIMPLEPIE_FILE_SOURCE_REMOTE)
		{
			$sniffer = $this->registry->create('Content_Type_Sniffer', array($file));
			$sniffed = $sniffer->get_type();
			if (in_array($sniffed, array('application/rss+xml', 'application/rdf+xml', 'text/rdf', 'application/atom+xml', 'text/xml', 'application/xml')))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		elseif ($file->method & SIMPLEPIE_FILE_SOURCE_LOCAL)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function get_base()
	{
		if ($this->dom === null)
		{
			throw new SimplePie_Exception('DOMDocument not found, unable to use locator');
		}
		$this->http_base = $this->file->url;
		$this->base = $this->http_base;
		$elements = $this->dom->getElementsByTagName('base');
		foreach ($elements as $element)
		{
			if ($element->hasAttribute('href'))
			{
				$base = $this->registry->call('Misc', 'absolutize_url', array(trim($element->getAttribute('href')), $this->http_base));
				if ($base === false)
				{
					continue;
				}
				$this->base = $base;
				$this->base_location = method_exists($element, 'getLineNo') ? $element->getLineNo() : 0;
				break;
			}
		}
	}

	public function autodiscovery()
	{
		$done = array();
		$feeds = array();
		$feeds = array_merge($feeds, $this->search_elements_by_tag('link', $done, $feeds));
		$feeds = array_merge($feeds, $this->search_elements_by_tag('a', $done, $feeds));
		$feeds = array_merge($feeds, $this->search_elements_by_tag('area', $done, $feeds));

		if (!empty($feeds))
		{
			return array_values($feeds);
		}
		else
		{
			return null;
		}
	}

	protected function search_elements_by_tag($name, &$done, $feeds)
	{
		if ($this->dom === null)
		{
			throw new SimplePie_Exception('DOMDocument not found, unable to use locator');
		}

		$links = $this->dom->getElementsByTagName($name);
		foreach ($links as $link)
		{
			if ($this->checked_feeds === $this->max_checked_feeds)
			{
				break;
			}
			if ($link->hasAttribute('href') && $link->hasAttribute('rel'))
			{
				$rel = array_unique($this->registry->call('Misc', 'space_seperated_tokens', array(strtolower($link->getAttribute('rel')))));
				$line = method_exists($link, 'getLineNo') ? $link->getLineNo() : 1;

				if ($this->base_location < $line)
				{
					$href = $this->registry->call('Misc', 'absolutize_url', array(trim($link->getAttribute('href')), $this->base));
				}
				else
				{
					$href = $this->registry->call('Misc', 'absolutize_url', array(trim($link->getAttribute('href')), $this->http_base));
				}
				if ($href === false)
				{
					continue;
				}

				if (!in_array($href, $done) && in_array('feed', $rel) || (in_array('alternate', $rel) && !in_array('stylesheet', $rel) && $link->hasAttribute('type') && in_array(strtolower($this->registry->call('Misc', 'parse_mime', array($link->getAttribute('type')))), array('application/rss+xml', 'application/atom+xml'))) && !isset($feeds[$href]))
				{
					$this->checked_feeds++;
					$headers = array(
						'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
					);
					$feed = $this->registry->create('File', array($href, $this->timeout, 5, $headers, $this->useragent));
					if ($feed->success && ($feed->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || $feed->status_code > 206 && $feed->status_code < 300)) && $this->is_feed($feed))
					{
						$feeds[$href] = $feed;
					}
				}
				$done[] = $href;
			}
		}

		return $feeds;
	}

	public function get_links()
	{
		if ($this->dom === null)
		{
			throw new SimplePie_Exception('DOMDocument not found, unable to use locator');
		}

		$links = $this->dom->getElementsByTagName('a');
		foreach ($links as $link)
		{
			if ($link->hasAttribute('href'))
			{
				$href = trim($link->getAttribute('href'));
				$parsed = $this->registry->call('Misc', 'parse_url', array($href));
				if ($parsed['scheme'] === '' || preg_match('/^(http(s)|feed)?$/i', $parsed['scheme']))
				{
					if ($this->base_location < $link->getLineNo())
					{
						$href = $this->registry->call('Misc', 'absolutize_url', array(trim($link->getAttribute('href')), $this->base));
					}
					else
					{
						$href = $this->registry->call('Misc', 'absolutize_url', array(trim($link->getAttribute('href')), $this->http_base));
					}
					if ($href === false)
					{
						continue;
					}

					$current = $this->registry->call('Misc', 'parse_url', array($this->file->url));

					if ($parsed['authority'] === '' || $parsed['authority'] === $current['authority'])
					{
						$this->local[] = $href;
					}
					else
					{
						$this->elsewhere[] = $href;
					}
				}
			}
		}
		$this->local = array_unique($this->local);
		$this->elsewhere = array_unique($this->elsewhere);
		if (!empty($this->local) || !empty($this->elsewhere))
		{
			return true;
		}
		return null;
	}

	public function extension(&$array)
	{
		foreach ($array as $key => $value)
		{
			if ($this->checked_feeds === $this->max_checked_feeds)
			{
				break;
			}
			if (in_array(strtolower(strrchr($value, '.')), array('.rss', '.rdf', '.atom', '.xml')))
			{
				$this->checked_feeds++;

				$headers = array(
					'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
				);
				$feed = $this->registry->create('File', array($value, $this->timeout, 5, $headers, $this->useragent));
				if ($feed->success && ($feed->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || $feed->status_code > 206 && $feed->status_code < 300)) && $this->is_feed($feed))
				{
					return $feed;
				}
				else
				{
					unset($array[$key]);
				}
			}
		}
		return null;
	}

	public function body(&$array)
	{
		foreach ($array as $key => $value)
		{
			if ($this->checked_feeds === $this->max_checked_feeds)
			{
				break;
			}
			if (preg_match('/(rss|rdf|atom|xml)/i', $value))
			{
				$this->checked_feeds++;
				$headers = array(
					'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
				);
				$feed = $this->registry->create('File', array($value, $this->timeout, 5, null, $this->useragent));
				if ($feed->success && ($feed->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || $feed->status_code > 206 && $feed->status_code < 300)) && $this->is_feed($feed))
				{
					return $feed;
				}
				else
				{
					unset($array[$key]);
				}
			}
		}
		return null;
	}
}

