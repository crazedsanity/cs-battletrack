begin;
CREATE TABLE csbt_save_table (
		   save_id serial NOT NULL PRIMARY KEY,
		   save_name text NOT NULL UNIQUE,
		   ability_id integer REFERENCES csbt_ability_table(ability_id)
);

ALTER TABLE csbt_character_save_table ADD COLUMN save_id integer;
ALTER TABLE csbt_character_save_table ADD CONSTRAINT csbt_character_save_table_save_id_fkey FOREIGN KEY (save_id) REFERENCES csbt_save_table (save_id);
INSERT INTO csbt_save_table (save_name, ability_id)
    SELECT distinct save_name, ability_id FROM csbt_character_save_table;

UPDATE csbt_character_save_table AS x SET save_id=(select save_id FROM csbt_save_table WHERE save_name=x.save_name);


ALTER TABLE csbt_character_save_table DROP COLUMN save_name;
ALTER TABLE csbt_character_save_table DROP COLUMN ability_id;

ALTER TABLE csbt_save_table ALTER COLUMN ability_id SET NOT NULL;
ALTER TABLE csbt_character_save_table ALTER COLUMN save_id SET NOT NULL;
