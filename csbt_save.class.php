<?php 

class csbt_save extends csbt_basicRecord {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_save_table';
	const tableSeq  = 'csbt_character_save_table_character_save_id_seq';
	const pkeyField = 'character_save_id';
	
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_all_character_saves() {
		if(!is_null($this->characterId) && $this->characterId > 0 && !is_null($this->id) && $this->id > 0) { 
			$sql = "SELECT cs.*, ca.* FROM csbt_character_save_table AS cs 
					INNER JOIN csbt_character_ability_table AS ca 
						ON (cs.character_id=ca.character_id 
						AND cs.ability_id=ca.ability_id) 
					INNER JOIN csbt_character_ability_table AS a 
						ON (cs.ability_id=a.ability_id) 
					WHERE 
						cs.character_id=:id";
			$params = array(
				'id' => $this->characterId,
			);
			
			$sql .= " ORDER BY save_name";
			
			try {
				$this->dbObj->run_query($sql, $params);
				$retval = $this->dbObj->farray_fieldnames($this->pkeyField);
				foreach($retval as $i=>$data) {
					$data['ability_mod'] = $this->calculate_ability_modifier($data['ability_score']);
					$data['total_mod'] = $this->calculate_total_save_modifier($data);
					$retval[$i] = $data;
				}
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
	public function create_character_defaults() {
		$result = 0;
		
		$defaults = array(
			'fort'		=> 'con',
			'reflex'	=> 'dex',
			'will'		=> 'wis',
		);
		$x = new csbt_ability($this->dbObj);
		$abilityList = $x->get_all_abilities();
		
		foreach($defaults as $k=>$v) {
			if(isset($abilityList[$v]) && is_numeric($abilityList[$v])) {
				$createData = array(
					'character_id'	=> $this->characterId,
					'save_name'		=> $k,
					'ability_id'	=> $abilityList[$v]
				);
				$this->create($createData);
				$result++;
			}
			else {
				throw new LogicException(__METHOD__ .": missing ability '". $v ."' for save (". $k .")");
			}
		}
		
		return $result;
	}
	//==========================================================================

}