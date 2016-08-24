<?php 

namespace battletrack\character;

use battletrack\character\Ability;

use crazedsanity\database\Database;
use crazedsanity\core\ToolBox;

use ErrorException;

class Skill extends \battletrack\basic\Data {
	
	
	const tableName = 'csbt_character_skill_table';
	const tableSeq  = 'csbt_character_skill_table_character_skill_id_seq';
	const pkeyField = 'character_skill_id';
	const sheetIdPrefix = 'skills';
	
	public $booleanFields = array('is_class_skill');
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
		$this->_sheetIdPrefix = self::sheetIdPrefix;
		$this->_useSheetIdSuffix = true;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(Database $dbObj, $characterId, $basedOnAbilityId=null) {
		$sql = 'SELECT 
					cs.*, a.ability_name, ca.ability_score, 
					ca.temporary_score 
				FROM csbt_character_skill_table AS cs 
					INNER JOIN csbt_ability_table AS a 
						ON (cs.ability_id=a.ability_id) 
					INNER JOIN csbt_character_ability_table AS ca 
						ON (cs.character_id=ca.character_id AND a.ability_id=ca.ability_id) 
				WHERE 
					cs.character_id=:id';
		
		$params = array(
			'id'	=> $characterId,
		);
		if(!is_null($basedOnAbilityId) && is_numeric($basedOnAbilityId)) {
			$sql .= ' AND cs.ability_id=:aid ';
			$params['aid'] = $basedOnAbilityId;
		}
		
		$sql .= ' ORDER BY cs.skill_name';
		
		try {
			$dbObj->run_query($sql, $params);
			$retval = $dbObj->farray_fieldnames(self::pkeyField);
		}
		catch(Exception $e) {
			throw new ErrorException(__METHOD__ .":: failed to retrieve character skills, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function _get_record_extras(array $recordData) {
		
		if(isset($recordData['ability_score'])) {
			$recordData['ability_mod'] = Ability::calculate_ability_modifier($recordData['ability_score']);
		}
		$recordData['skill_mod'] = self::calculate_skill_modifier($recordData);
		$recordData['is_class_skill_checked'] = ToolBox::interpret_bool($recordData['is_class_skill'], array('', 'checked="checked"'));
		$recordData['is_checked_checkbox'] = ToolBox::interpret_bool($recordData['is_class_skill'], array("", "checked"));
		
		return $recordData;
	}
	//==========================================================================
}
	
