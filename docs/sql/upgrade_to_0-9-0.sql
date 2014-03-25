ALTER TABLE csbt_character_ability_table ADD CONSTRAINT
	csbt_character_ability_table_character_id_ability_id_uix
	UNIQUE (character_id, ability_id);

ALTER TABLE csbt_character_save_table ADD CONSTRAINT 
	csbt_character_save_table_character_id_save_name_uix 
	UNIQUE (character_id, save_name);

ALTER TABLE csbt_ability_table ADD COLUMN display_order INT;
ALTER TABLE csbt_ability_table ADD COLUMN display_name text;
UPDATE csbt_ability_table SET display_order=1, display_name='Strength'		WHERE ability_name='str';
UPDATE csbt_ability_table SET display_order=2, display_name='Dexterity'		WHERE ability_name='dex';
UPDATE csbt_ability_table SET display_order=3, display_name='Constitution'	WHERE ability_name='con';
UPDATE csbt_ability_table SET display_order=4, display_name='Intelligence'	WHERE ability_name='int';
UPDATE csbt_ability_table SET display_order=5, display_name='Wisdom'		WHERE ability_name='wis';
UPDATE csbt_ability_table SET display_order=6, display_name='Charisma'		WHERE ability_name='cha';
ALTER TABLE csbt_ability_table ADD CONSTRAINT
	csbt_ability_table_display_order_uix
	UNIQUE (display_order);