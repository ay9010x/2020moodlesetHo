<?php


class Less_Output_Mapped extends Less_Output {

	
	protected $generator;

	
	protected $lineNumber = 0;

	
	protected $column = 0;

	
	protected $contentsMap = array();

	
	public function __construct(array $contentsMap, $generator){
		$this->contentsMap = $contentsMap;
		$this->generator = $generator;
	}

	
	public function add($chunk, $fileInfo = null, $index = 0, $mapLines = null){

				if( $chunk === '' ){
			return;
		}


		$sourceLines = array();
		$sourceColumns = ' ';


		if( $fileInfo ){

			$url = $fileInfo['currentUri'];

			if( isset($this->contentsMap[$url]) ){
				$inputSource = substr($this->contentsMap[$url], 0, $index);
				$sourceLines = explode("\n", $inputSource);
				$sourceColumns = end($sourceLines);
			}else{
				throw new Exception('Filename '.$url.' not in contentsMap');
			}

		}

		$lines = explode("\n", $chunk);
		$columns = end($lines);

		if($fileInfo){

			if(!$mapLines){
				$this->generator->addMapping(
						$this->lineNumber + 1,											$this->column,													count($sourceLines),											strlen($sourceColumns),											$fileInfo
				);
			}else{
				for($i = 0, $count = count($lines); $i < $count; $i++){
					$this->generator->addMapping(
						$this->lineNumber + $i + 1,										$i === 0 ? $this->column : 0,									count($sourceLines) + $i,										$i === 0 ? strlen($sourceColumns) : 0, 							$fileInfo
					);
				}
			}
		}

		if(count($lines) === 1){
			$this->column += strlen($columns);
		}else{
			$this->lineNumber += count($lines) - 1;
			$this->column = strlen($columns);
		}

				parent::add($chunk);
	}

}