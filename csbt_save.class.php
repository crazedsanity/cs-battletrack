<?php 

class csbt_save extends csbt_data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_save_table';
	const tableSeq  = 'csbt_character_save_table_character_save_id_seq';
	const pkeyField = 'character_save_id';
	const sheetIdPrefix = 'saves';
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
		$this->_sheetIdPrefix = self::sheetIdPrefix;
		$this->_useSheetIdSuffix = true;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(cs_phpDB $dbObj, $characterId, $byAbilityId=null) {
		if(!is_null($characterId) && $characterId > 0) { 
			$sql = "SELECT cs.*, ca.* FROM csbt_character_save_table AS cs 
					INNER JOIN csbt_character_ability_table AS ca 
						ON (cs.character_id=ca.character_id 
						AND cs.ability_id=ca.ability_id) 
					INNER JOIN csbt_ability_table AS a 
						ON (cs.ability_id=a.ability_id) 
					WHERE 
						cs.character_id=:id";
			$params = array(
				'id' => $characterId,
			);
			
			if(!is_null($byAbilityId) && is_numeric($byAbilityId)) {
				$sql .= " AND ca.ability_id=:aid";
				$params['aid'] = $byAbilityId;
			}
			
			$sql .= " ORDER BY save_name";
			
			try {
				$dbObj->run_query($sql, $params);
				$retval = $dbObj->farray_fieldnames(self::pkeyField);
			} catch (LogicException $le) {
				throw new ErrorException(__METHOD__ .": failed to derive save modifier... DETAILS: ". $le->getMessage());
			} catch (Exception $e) {
				throw new ErrorException(__METHOD__ . ":: failed to retrieve character saves, DETAILS::: " . $e->getMessage());
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": cannot load without characterId");
		}
		return($retval);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create_character_defaults(cs_phpDB $dbObj) {
		$result = 0;
		
		if(!is_null($this->characterId) && is_numeric($this->characterId) && $this->characterId > 0) {
			$defaults = array(
				'fort'		=> 'con',
				'reflex'	=> 'dex',
				'will'		=> 'wis',
			);
			$x = new csbt_ability();
			$abilityList = $x->get_all_abilities($dbObj);

			foreach($defaults as $k=>$v) {
				if(isset($abilityList[$v]) && is_numeric($abilityList[$v])) {
					$createData = array(
						'character_id'	=> $this->characterId,
						'save_name'		=> $k,
						'ability_id'	=> $abilityList[$v]
					);
					$this->create($dbObj, $createData);
					$result++;
				}
				else {
					throw new LogicException(__METHOD__ .": missing ability '". $v ."' for save (". $k .")");
				}
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": missing characterId (". cs_global::debug_var_dump($this->characterId) .")");
		}
		
		return $result;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function _get_record_extras(array $recordData) {
		if(isset($recordData['ability_score'])) {
			$recordData['ability_mod'] = self::calculate_ability_modifier($recordData['ability_score']);
		}
		$recordData['total'] = self::calculate_total_save_modifier($recordData);
		
		$displayName = strtoupper($recordData['save_name']);
		if($displayName == 'FORT') {
			$displayName = 'FORTITUDE';
		}
		$recordData['display_name'] = $displayName;
		return $recordData;
	}
	//==========================================================================
}