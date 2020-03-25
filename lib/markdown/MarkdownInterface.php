<?php
namespace Michelf;



interface MarkdownInterface {

          public static function defaultTransform($text);

          public function transform($text);

}
