<?php 

namespace battletrack\character;


use crazedsanity\database\Database;

use battletrack\character\Ability;

use LogicException;
use ErrorException;
use Exception;
use InvalidArgumentException;

class Ability extends \battletrack\basic\Data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_ability_table';
	const tableSeq  = 'csbt_character_ability_table_character_ability_id_seq';
	const pkeyField = 'character_ability_id';
	
	const sheetIdPrefix = 'characterAbility';
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
		$this->_sheetIdPrefix = self::sheetIdPrefix;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_modifier() {
		return self::calculate_ability_modifier($this->_data['ability_score']);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_temp_modifier() {
		return self::calculate_ability_modifier($this->_data['temporary_score']);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all_abilities(Database $dbObj, $byId=false) {
		$sql = "SELECT * FROM csbt_ability_table";
		
		try {
			$numrows = $dbObj->run_query($sql);
			
			if($numrows > 0) {
				if($byId) {
					$data = $dbObj->farray_nvp('ability_id', 'ability_name');
				}
				else {
					$data = $dbObj->farray_nvp('ability_name', 'ability_id');
				}
			}
			else {
				throw new LogicException(__METHOD__ .": no data available");
			}
		} catch (Exception $ex) {
			throw new ErrorException(__METHOD__ .": failed to retrieve cache, DETAILS::: ". $ex->getMessage());
		}
		
		return $data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(Database $dbObj, $characterId) {
		if(!is_null($characterId) && $characterId > 0) {

			$sql = "SELECT ca.*, a.ability_name, a.display_name "
					. "FROM csbt_character_ability_table "
					. "AS ca INNER JOIN csbt_ability_table AS a USING (ability_id) "
					. "WHERE ca.character_id=:id "
					. "ORDER BY a.display_order";
			$params = array('id'=>$characterId);
			try {
				$dbObj->run_query($sql, $params);
				$data = $dbObj->farray_fieldnames('ability_name');
			} catch (Exception $ex) {
				throw new ErrorException(__METHOD__ .": failed to retrieve cache, DETAILS::: ". $ex->getMessage());
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": characterId required");
		}
		
		return $data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create_defaults(Database $db, $minScore = 6, $maxScore = 18) {
		$retval = 0;
		if (!is_null($this->characterId) && $this->characterId > 0) {
			if (!is_numeric($minScore) || $minScore < 1) {
				throw new InvalidArgumentException(__METHOD__ . ": invalid minimum score (" . $minScore . ")");
			}
			if (!is_numeric($maxScore) || $maxScore < 1) {
				throw new InvalidArgumentException(__METHOD__ . ": invalid max score (" . $maxScore . ")");
			}
			$abilityList = $this->get_all_abilities($db);
			
			foreach ($abilityList as $n => $v) {
				$data = array(
					'character_id'	=> $this->characterId,
					'ability_id'	=> $v,
					'ability_score'	=> rand($minScore, $maxScore),
				);
				$res = $this->create($db, $data);
				$retval++;
			}
		} else {
			throw new ErrorException(__METHOD__ . ": characterId required");
		}
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function _get_record_extras(array $myData) {
		$myData['ability_modifier'] = self::calculate_ability_modifier($myData['ability_score']);
		$myData['temporary_modifier'] = self::calculate_ability_modifier($myData['temporary_score']);	
		
		return $myData;
	}
	//==========================================================================
}