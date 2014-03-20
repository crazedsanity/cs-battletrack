ALTER TABLE csbt_character_ability_table ADD CONSTRAINT
	csbt_character_ability_table_character_id_ability_id_uix
	UNIQUE (character_id, ability_id);

ALTER TABLE csbt_character_save_table ADD CONSTRAINT 
	csbt_character_save_table_character_id_save_name_uix 
	UNIQUE (character_id, save_name);
