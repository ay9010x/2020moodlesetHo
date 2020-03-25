<?php


class Less_Visitor_processExtends extends Less_Visitor{

	public $allExtendsStack;

	
	public function run( $root ){
		$extendFinder = new Less_Visitor_extendFinder();
		$extendFinder->run( $root );
		if( !$extendFinder->foundExtends){
			return $root;
		}

		$root->allExtends = $this->doExtendChaining( $root->allExtends, $root->allExtends);

		$this->allExtendsStack = array();
		$this->allExtendsStack[] = &$root->allExtends;

		return $this->visitObj( $root );
	}

	private function doExtendChaining( $extendsList, $extendsListTarget, $iterationCount = 0){
																
		$extendsToAdd = array();


														for( $extendIndex = 0, $extendsList_len = count($extendsList); $extendIndex < $extendsList_len; $extendIndex++ ){
			for( $targetExtendIndex = 0; $targetExtendIndex < count($extendsListTarget); $targetExtendIndex++ ){

				$extend = $extendsList[$extendIndex];
				$targetExtend = $extendsListTarget[$targetExtendIndex];

								if( in_array($targetExtend->object_id, $extend->parent_ids,true) ){
					continue;
				}

								$selectorPath = array( $targetExtend->selfSelectors[0] );
				$matches = $this->findMatch( $extend, $selectorPath);


				if( $matches ){

										foreach($extend->selfSelectors as $selfSelector ){


												$newSelector = $this->extendSelector( $matches, $selectorPath, $selfSelector);

												$newExtend = new Less_Tree_Extend( $targetExtend->selector, $targetExtend->option, 0);
						$newExtend->selfSelectors = $newSelector;

												end($newSelector)->extendList = array($newExtend);
						
												$extendsToAdd[] = $newExtend;
						$newExtend->ruleset = $targetExtend->ruleset;

												$newExtend->parent_ids = array_merge($newExtend->parent_ids,$targetExtend->parent_ids,$extend->parent_ids);

																								if( $targetExtend->firstExtendOnThisSelectorPath ){
							$newExtend->firstExtendOnThisSelectorPath = true;
							$targetExtend->ruleset->paths[] = $newSelector;
						}
					}
				}
			}
		}

		if( $extendsToAdd ){
									if( $iterationCount > 100) {

				try{
					$selectorOne = $extendsToAdd[0]->selfSelectors[0]->toCSS();
					$selectorTwo = $extendsToAdd[0]->selector->toCSS();
				}catch(Exception $e){
					$selectorOne = "{unable to calculate}";
					$selectorTwo = "{unable to calculate}";
				}

				throw new Less_Exception_Parser("extend circular reference detected. One of the circular extends is currently:" . $selectorOne . ":extend(" . $selectorTwo . ")");
			}

						$extendsToAdd = $this->doExtendChaining( $extendsToAdd, $extendsListTarget, $iterationCount+1);
		}

		return array_merge($extendsList, $extendsToAdd);
	}


	protected function visitRule( $ruleNode, &$visitDeeper ){
		$visitDeeper = false;
	}

	protected function visitMixinDefinition( $mixinDefinitionNode, &$visitDeeper ){
		$visitDeeper = false;
	}

	protected function visitSelector( $selectorNode, &$visitDeeper ){
		$visitDeeper = false;
	}

	protected function visitRuleset($rulesetNode){


		if( $rulesetNode->root ){
			return;
		}

		$allExtends	= end($this->allExtendsStack);
		$paths_len = count($rulesetNode->paths);

				foreach($allExtends as $allExtend){
			for($pathIndex = 0; $pathIndex < $paths_len; $pathIndex++ ){

								if( isset($rulesetNode->extendOnEveryPath) && $rulesetNode->extendOnEveryPath ){
					continue;
				}

				$selectorPath = $rulesetNode->paths[$pathIndex];

				if( end($selectorPath)->extendList ){
					continue;
				}

				$this->ExtendMatch( $rulesetNode, $allExtend, $selectorPath);

			}
		}
	}


	private function ExtendMatch( $rulesetNode, $extend, $selectorPath ){
		$matches = $this->findMatch($extend, $selectorPath);

		if( $matches ){
			foreach($extend->selfSelectors as $selfSelector ){
				$rulesetNode->paths[] = $this->extendSelector($matches, $selectorPath, $selfSelector);
			}
		}
	}



	private function findMatch($extend, $haystackSelectorPath ){


		if( !$this->HasMatches($extend, $haystackSelectorPath) ){
			return false;
		}


										$needleElements = $extend->selector->elements;
		$potentialMatches = array();
		$potentialMatches_len = 0;
		$potentialMatch = null;
		$matches = array();



				$haystack_path_len = count($haystackSelectorPath);
		for($haystackSelectorIndex = 0; $haystackSelectorIndex < $haystack_path_len; $haystackSelectorIndex++ ){
			$hackstackSelector = $haystackSelectorPath[$haystackSelectorIndex];

			$haystack_elements_len = count($hackstackSelector->elements);
			for($hackstackElementIndex = 0; $hackstackElementIndex < $haystack_elements_len; $hackstackElementIndex++ ){

				$haystackElement = $hackstackSelector->elements[$hackstackElementIndex];

								if( $extend->allowBefore || ($haystackSelectorIndex === 0 && $hackstackElementIndex === 0) ){
					$potentialMatches[] = array('pathIndex'=> $haystackSelectorIndex, 'index'=> $hackstackElementIndex, 'matched'=> 0, 'initialCombinator'=> $haystackElement->combinator);
					$potentialMatches_len++;
				}

				for($i = 0; $i < $potentialMatches_len; $i++ ){

					$potentialMatch = &$potentialMatches[$i];
					$potentialMatch = $this->PotentialMatch( $potentialMatch, $needleElements, $haystackElement, $hackstackElementIndex );


										if( $potentialMatch && $potentialMatch['matched'] === $extend->selector->elements_len ){
						$potentialMatch['finished'] = true;

						if( !$extend->allowAfter && ($hackstackElementIndex+1 < $haystack_elements_len || $haystackSelectorIndex+1 < $haystack_path_len) ){
							$potentialMatch = null;
						}
					}

										if( $potentialMatch ){
						if( $potentialMatch['finished'] ){
							$potentialMatch['length'] = $extend->selector->elements_len;
							$potentialMatch['endPathIndex'] = $haystackSelectorIndex;
							$potentialMatch['endPathElementIndex'] = $hackstackElementIndex + 1; 							$potentialMatches = array(); 							$potentialMatches_len = 0;
							$matches[] = $potentialMatch;
						}
						continue;
					}

					array_splice($potentialMatches, $i, 1);
					$potentialMatches_len--;
					$i--;
				}
			}
		}

		return $matches;
	}


			private function HasMatches($extend, $haystackSelectorPath){

		if( !$extend->selector->cacheable ){
			return true;
		}

		$first_el = $extend->selector->_oelements[0];

		foreach($haystackSelectorPath as $hackstackSelector){
			if( !$hackstackSelector->cacheable ){
				return true;
			}

			if( in_array($first_el, $hackstackSelector->_oelements) ){
				return true;
			}
		}

		return false;
	}


	
	private function PotentialMatch( $potentialMatch, $needleElements, $haystackElement, $hackstackElementIndex ){


		if( $potentialMatch['matched'] > 0 ){

												$targetCombinator = $haystackElement->combinator;
			if( $targetCombinator === '' && $hackstackElementIndex === 0 ){
				$targetCombinator = ' ';
			}

			if( $needleElements[ $potentialMatch['matched'] ]->combinator !== $targetCombinator ){
				return null;
			}
		}

				if( !$this->isElementValuesEqual( $needleElements[$potentialMatch['matched'] ]->value, $haystackElement->value) ){
			return null;
		}

		$potentialMatch['finished'] = false;
		$potentialMatch['matched']++;

		return $potentialMatch;
	}


	private function isElementValuesEqual( $elementValue1, $elementValue2 ){

		if( $elementValue1 === $elementValue2 ){
			return true;
		}

		if( is_string($elementValue1) || is_string($elementValue2) ) {
			return false;
		}

		if( $elementValue1 instanceof Less_Tree_Attribute ){
			return $this->isAttributeValuesEqual( $elementValue1, $elementValue2 );
		}

		$elementValue1 = $elementValue1->value;
		if( $elementValue1 instanceof Less_Tree_Selector ){
			return $this->isSelectorValuesEqual( $elementValue1, $elementValue2 );
		}

		return false;
	}


	
	private function isSelectorValuesEqual( $elementValue1, $elementValue2 ){

		$elementValue2 = $elementValue2->value;
		if( !($elementValue2 instanceof Less_Tree_Selector) || $elementValue1->elements_len !== $elementValue2->elements_len ){
			return false;
		}

		for( $i = 0; $i < $elementValue1->elements_len; $i++ ){

			if( $elementValue1->elements[$i]->combinator !== $elementValue2->elements[$i]->combinator ){
				if( $i !== 0 || ($elementValue1->elements[$i]->combinator || ' ') !== ($elementValue2->elements[$i]->combinator || ' ') ){
					return false;
				}
			}

			if( !$this->isElementValuesEqual($elementValue1->elements[$i]->value, $elementValue2->elements[$i]->value) ){
				return false;
			}
		}

		return true;
	}


	
	private function isAttributeValuesEqual( $elementValue1, $elementValue2 ){

		if( $elementValue1->op !== $elementValue2->op || $elementValue1->key !== $elementValue2->key ){
			return false;
		}

		if( !$elementValue1->value || !$elementValue2->value ){
			if( $elementValue1->value || $elementValue2->value ) {
				return false;
			}
			return true;
		}

		$elementValue1 = ($elementValue1->value->value ? $elementValue1->value->value : $elementValue1->value );
		$elementValue2 = ($elementValue2->value->value ? $elementValue2->value->value : $elementValue2->value );

		return $elementValue1 === $elementValue2;
	}


	private function extendSelector($matches, $selectorPath, $replacementSelector){

		
		$currentSelectorPathIndex = 0;
		$currentSelectorPathElementIndex = 0;
		$path = array();
		$selectorPath_len = count($selectorPath);

		for($matchIndex = 0, $matches_len = count($matches); $matchIndex < $matches_len; $matchIndex++ ){


			$match = $matches[$matchIndex];
			$selector = $selectorPath[ $match['pathIndex'] ];

			$firstElement = new Less_Tree_Element(
				$match['initialCombinator'],
				$replacementSelector->elements[0]->value,
				$replacementSelector->elements[0]->index,
				$replacementSelector->elements[0]->currentFileInfo
			);

			if( $match['pathIndex'] > $currentSelectorPathIndex && $currentSelectorPathElementIndex > 0 ){
				$last_path = end($path);
				$last_path->elements = array_merge( $last_path->elements, array_slice( $selectorPath[$currentSelectorPathIndex]->elements, $currentSelectorPathElementIndex));
				$currentSelectorPathElementIndex = 0;
				$currentSelectorPathIndex++;
			}

			$newElements = array_merge(
				array_slice($selector->elements, $currentSelectorPathElementIndex, ($match['index'] - $currentSelectorPathElementIndex) ) 				, array($firstElement)
				, array_slice($replacementSelector->elements,1)
				);

			if( $currentSelectorPathIndex === $match['pathIndex'] && $matchIndex > 0 ){
				$last_key = count($path)-1;
				$path[$last_key]->elements = array_merge($path[$last_key]->elements,$newElements);
			}else{
				$path = array_merge( $path, array_slice( $selectorPath, $currentSelectorPathIndex, $match['pathIndex'] ));
				$path[] = new Less_Tree_Selector( $newElements );
			}

			$currentSelectorPathIndex = $match['endPathIndex'];
			$currentSelectorPathElementIndex = $match['endPathElementIndex'];
			if( $currentSelectorPathElementIndex >= count($selectorPath[$currentSelectorPathIndex]->elements) ){
				$currentSelectorPathElementIndex = 0;
				$currentSelectorPathIndex++;
			}
		}

		if( $currentSelectorPathIndex < $selectorPath_len && $currentSelectorPathElementIndex > 0 ){
			$last_path = end($path);
			$last_path->elements = array_merge( $last_path->elements, array_slice($selectorPath[$currentSelectorPathIndex]->elements, $currentSelectorPathElementIndex));
			$currentSelectorPathIndex++;
		}

		$slice_len = $selectorPath_len - $currentSelectorPathIndex;
		$path = array_merge($path, array_slice($selectorPath, $currentSelectorPathIndex, $slice_len));

		return $path;
	}


	protected function visitMedia( $mediaNode ){
		$newAllExtends = array_merge( $mediaNode->allExtends, end($this->allExtendsStack) );
		$this->allExtendsStack[] = $this->doExtendChaining($newAllExtends, $mediaNode->allExtends);
	}

	protected function visitMediaOut(){
		array_pop( $this->allExtendsStack );
	}

	protected function visitDirective( $directiveNode ){
		$newAllExtends = array_merge( $directiveNode->allExtends, end($this->allExtendsStack) );
		$this->allExtendsStack[] = $this->doExtendChaining($newAllExtends, $directiveNode->allExtends);
	}

	protected function visitDirectiveOut(){
		array_pop($this->allExtendsStack);
	}

}