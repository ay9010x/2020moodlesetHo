<?php

namespace sharing_cart;


class renderer
{
	
	public static function render_tree(array & $tree)
	{
		return '<ul class="tree list" style="font-size:90%;">'
		     . self::render_node($tree, '/')
		     . '</ul>';
	}

	
	private static function render_node(array & $node, $path)
	{
		$html = '';
		foreach ($node as $name => & $leaf) {
			if ($name !== '') {
				$next = rtrim($path, '/') . '/' . $name;
				$html .= self::render_dir_open($next);
				$html .= self::render_node($leaf, $next);
				$html .= self::render_dir_close();
			} else {
				foreach ($leaf as $item)
					$html .= self::render_item($path, $item);
			}
		}
		return $html;
	}
	
	private static function render_dir_open($path)
	{
		global $OUTPUT;

		$components = explode('/', trim($path, '/'));
		$depth = count($components) - 1;
		return '
		<li class="directory">
			<div class="sc-indent-' . $depth . '" title="' . s($path) . '">
				<img class="activityicon iconsmall" src="' . s($OUTPUT->pix_url('f/folder')) . '" alt="" />
				<span class="instancename">' . format_string(end($components)) . '</span>
			</div>
			<ul class="list" style="display:none;">';
	}
	
	private static function render_item($path, $item)
	{
		$components = array_filter(explode('/', trim($path, '/')), 'strlen');
		$depth = count($components);
		$class = $item->modname . ' ' . "modtype_{$item->modname}";

		if ($item->modname == 'label') {
			$item->modtext = self::render_label($item->modtext);
		}

		return '
				<li class="activity ' . $class . '" id="block_sharing_cart-item-' . $item->id . '">
					<div class="sc-indent-' . $depth . '">
						' . self::render_modicon($item) . '
						<span class="instancename">' . format_string($item->modtext) . '</span>
						<span class="commands"></span>
					</div>
				</li>';
	}
	
	private static function render_dir_close()
	{
		return '
			</ul>
		</li>';
	}

	
	public static function render_modicon($item)
	{
		global $OUTPUT;

		if ($item->modname === 'label')
			return '';
		$src = $OUTPUT->pix_url('icon', $item->modname);
		if (!empty($item->modicon)) {
						if (strncmp($item->modicon, 'mod/', 4) == 0) {
				list ($modname, $iconname) = explode('/', substr($item->modicon, 4), 2);
				$src = $OUTPUT->pix_url($iconname, $modname);
			} else {
				$src = $OUTPUT->pix_url($item->modicon);
			}
		}
		return '<img class="activityicon iconsmall" src="' . s($src) . '" alt="" />';
	}

	public static function render_label($modtext)
	{
		preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $modtext, $result);
		$img_src = array_pop($result);

		if (!empty($img_src)) {
			$path_parts = pathinfo($img_src);
			$modtext = urldecode($path_parts['filename']);
		}

		return $modtext;
	}
}
