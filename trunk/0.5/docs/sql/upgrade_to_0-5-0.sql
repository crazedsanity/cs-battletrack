--
-- SVN INFORMATION:::
-- SVN Signature::::::::: $Id:tables.sql 23 2008-04-18 04:25:47Z crazedsanity $
-- Last Committted Date:: $Date:2008-04-17 23:25:47 -0500 (Thu, 17 Apr 2008) $
-- Last Committed Path::: $HeadURL:https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/docs/sql/tables.sql $
--  

--
-- Contains a list of campaigns.
--
CREATE TABLE csbt_campaign_table (
	campaign_id serial NOT NULL PRIMARY KEY,
	campaign_name varchar(128) NOT NULL UNIQUE,	
	owner_uid integer NOT NULL REFERENCES cs_authentication_table(uid),
	create_date timestamp NOT NULL DEFAULT NOW(),
	is_active bool NOT NULL DEFAULT true
);

ALTER TABLE csbt_character_attribute_table RENAME TO _backup_csbt_ca;

-- 
-- Contains the main character information.
-- 

--CREATE TABLE csbt_character_table (
--	character_id serial NOT NULL PRIMARY KEY,
--	uid integer NOT NULL REFERENCES cs_authentication_table(uid),
--	character_name text,
--	campaign_id integer DEFAULT NULL REFERENCES csbt_campaign_table(campaign_id)
--);

ALTER TABLE csbt_character_table ADD COLUMN ac_misc integer;
ALTER TABLE csbt_character_table ALTER COLUMN ac_misc SET DEFAULT 0;
UPDATE csbt_character_table SET ac_misc=0;
ALTER TABLE csbt_character_table ALTER COLUMN ac_misc SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN ac_size integer;
ALTER TABLE csbt_character_table ALTER COLUMN ac_size SET DEFAULT 0;
UPDATE csbt_character_table SET ac_size=0;
ALTER TABLE csbt_character_table ALTER COLUMN ac_size SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN ac_natural integer;
ALTER TABLE csbt_character_table ALTER COLUMN ac_natural SET DEFAULT 0;
UPDATE csbt_character_table SET ac_natural=0;
ALTER TABLE csbt_character_table ALTER COLUMN ac_natural SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN action_points integer;
ALTER TABLE csbt_character_table ALTER COLUMN action_points SET DEFAULT 0;
UPDATE csbt_character_table SET action_points=0;
ALTER TABLE csbt_character_table ALTER COLUMN action_points SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN character_age integer;
ALTER TABLE csbt_character_table ALTER COLUMN character_age SET DEFAULT 18;
UPDATE csbt_character_table SET character_age=18;
ALTER TABLE csbt_character_table ALTER COLUMN character_age SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN character_level text;
ALTER TABLE csbt_character_table ALTER COLUMN character_level SET DEFAULT '(class/level) EXAMPLE: fighter/1, rogue/3';
UPDATE csbt_character_table SET character_level='(class/level) EXAMPLE: fighter/1, rogue/3';
ALTER TABLE csbt_character_table ALTER COLUMN character_level SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN alignment text;
ALTER TABLE csbt_character_table ALTER COLUMN alignment SET DEFAULT 'Chaotic Neutral';
UPDATE csbt_character_table SET alignment='Chaotic Neutral';
ALTER TABLE csbt_character_table ALTER COLUMN alignment SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN base_attack_bonus integer;
ALTER TABLE csbt_character_table ALTER COLUMN base_attack_bonus SET DEFAULT 1;
UPDATE csbt_character_table SET base_attack_bonus=1;
ALTER TABLE csbt_character_table ALTER COLUMN base_attack_bonus SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN deity text;
ALTER TABLE csbt_character_table ADD COLUMN eye_color text;
ALTER TABLE csbt_character_table ADD COLUMN gender text;
ALTER TABLE csbt_character_table ADD COLUMN hair_color text;
ALTER TABLE csbt_character_table ADD COLUMN height text;

ALTER TABLE csbt_character_table ADD COLUMN hit_points_max integer;
ALTER TABLE csbt_character_table ALTER COLUMN hit_points_max SET DEFAULT 1;
UPDATE csbt_character_table SET hit_points_max=1;
ALTER TABLE csbt_character_table ALTER COLUMN hit_points_max SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN hit_points_current integer;
ALTER TABLE csbt_character_table ALTER COLUMN hit_points_current SET DEFAULT 1;
UPDATE csbt_character_table SET hit_points_current=1;
ALTER TABLE csbt_character_table ALTER COLUMN hit_points_current SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN race text;

ALTER TABLE csbt_character_table ADD COLUMN size text;
ALTER TABLE csbt_character_table ALTER COLUMN size SET DEFAULT 'Medium';
UPDATE csbt_character_table SET size='Medium';
ALTER TABLE csbt_character_table ALTER COLUMN size SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN weight integer;
ALTER TABLE csbt_character_table ALTER COLUMN weight SET DEFAULT 180;
UPDATE csbt_character_table SET weight=180;
ALTER TABLE csbt_character_table ALTER COLUMN weight SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN initiative_misc integer;
ALTER TABLE csbt_character_table ALTER COLUMN initiative_misc SET DEFAULT 0;
UPDATE csbt_character_table SET initiative_misc=0;
ALTER TABLE csbt_character_table ALTER COLUMN initiative_misc SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN nonlethal_damage integer;
ALTER TABLE csbt_character_table ALTER COLUMN nonlethal_damage SET DEFAULT 0;
UPDATE csbt_character_table SET nonlethal_damage=0;
ALTER TABLE csbt_character_table ALTER COLUMN nonlethal_damage SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN hit_dice integer;
ALTER TABLE csbt_character_table ALTER COLUMN hit_dice SET DEFAULT 0;
UPDATE csbt_character_table SET hit_dice=0;
ALTER TABLE csbt_character_table ALTER COLUMN hit_dice SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN damage_reduction text;

ALTER TABLE csbt_character_table ADD COLUMN melee_misc integer;
ALTER TABLE csbt_character_table ALTER COLUMN melee_misc SET DEFAULT 0;
UPDATE csbt_character_table SET melee_misc=0;
ALTER TABLE csbt_character_table ALTER COLUMN melee_misc SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN melee_size integer;
ALTER TABLE csbt_character_table ALTER COLUMN melee_size SET DEFAULT 0;
UPDATE csbt_character_table SET melee_size=0;
ALTER TABLE csbt_character_table ALTER COLUMN melee_size SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN melee_temp integer;
ALTER TABLE csbt_character_table ALTER COLUMN melee_temp SET DEFAULT 0;
UPDATE csbt_character_table SET melee_temp=0;
ALTER TABLE csbt_character_table ALTER COLUMN melee_temp SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN ranged_misc integer;
ALTER TABLE csbt_character_table ALTER COLUMN ranged_misc SET DEFAULT 0;
UPDATE csbt_character_table SET ranged_misc=0;
ALTER TABLE csbt_character_table ALTER COLUMN ranged_misc SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN ranged_size integer;
ALTER TABLE csbt_character_table ALTER COLUMN ranged_size SET DEFAULT 0;
UPDATE csbt_character_table SET ranged_size=0;
ALTER TABLE csbt_character_table ALTER COLUMN ranged_size SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN ranged_temp integer;
ALTER TABLE csbt_character_table ALTER COLUMN ranged_temp SET DEFAULT 0;
UPDATE csbt_character_table SET ranged_temp=0;
ALTER TABLE csbt_character_table ALTER COLUMN ranged_temp SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN skills_max integer;
ALTER TABLE csbt_character_table ALTER COLUMN skills_max SET DEFAULT 10;
UPDATE csbt_character_table SET skills_max=0;
ALTER TABLE csbt_character_table ALTER COLUMN skills_max SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN speed integer;
ALTER TABLE csbt_character_table ALTER COLUMN speed SET DEFAULT 0;
UPDATE csbt_character_table SET speed=0;
ALTER TABLE csbt_character_table ALTER COLUMN speed SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN xp_current integer;
ALTER TABLE csbt_character_table ALTER COLUMN xp_current SET DEFAULT 0;
UPDATE csbt_character_table SET xp_current=0;
ALTER TABLE csbt_character_table ALTER COLUMN xp_current SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN xp_next integer;
ALTER TABLE csbt_character_table ALTER COLUMN xp_next SET DEFAULT 0;
UPDATE csbt_character_table SET xp_next=0;
ALTER TABLE csbt_character_table ALTER COLUMN xp_next SET NOT NULL;

ALTER TABLE csbt_character_table ADD COLUMN notes text;


CREATE TABLE csbt_attribute_table (
	attribute_id serial NOT NULL PRIMARY KEY,
	attribute text NOT NULL UNIQUE
);



--
-- NOTE::: this table (and 'csbt_attribute_table') is a remnant of the old, one-table system; it should eventually 
--    be completely superceded by specific tables.
-- 
CREATE TABLE csbt_character_attribute_table (
	character_attribute_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	attribute_id integer NOT NULL REFERENCES csbt_attribute_table(attribute_id),
	attribute_value text NOT NULL
);

CREATE TABLE csbt_ability_table (
	ability_id serial NOT NULL PRIMARY KEY,
	ability_name varchar(3) NOT NULL UNIQUE
);
INSERT INTO csbt_ability_table (ability_name) VALUES ('str');
INSERT INTO csbt_ability_table (ability_name) VALUES ('con');
INSERT INTO csbt_ability_table (ability_name) VALUES ('dex');
INSERT INTO csbt_ability_table (ability_name) VALUES ('int');
INSERT INTO csbt_ability_table (ability_name) VALUES ('wis');
INSERT INTO csbt_ability_table (ability_name) VALUES ('cha');

-- 
-- Each character should have 6 records (str, con, dex, int, wis, cha), and should be UNIQUE.
-- 
CREATE TABLE csbt_character_ability_table (
	character_ability_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	ability_id integer NOT NULL REFERENCES csbt_ability_table(ability_id),
	ability_score integer NOT NULL DEFAULT 10,
	temporary_score integer DEFAULT NULL
);

CREATE TABLE csbt_character_skill_table (
	character_skill_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	skill_name text NOT NULL,
	ability_id integer NOT NULL REFERENCES csbt_ability_table (ability_id),
	is_class_skill bool NOT NULL DEFAULT false,
	ranks integer NOT NULL default 0,
	misc_mod integer NOT NULL default 0
);

CREATE TABLE csbt_character_weapon_table (
	character_weapon_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	weapon_name text NOT NULL,
	total_attack_bonus integer NOT NULL DEFAULT 0,
	damage text NOT NULL DEFAULT 0,
	critical text NOT NULL DEFAULT '20 x 2',
	range text NOT NULL DEFAULT 'melee',
	special text,
	ammunition text,
	weight text,
	size text NOT NULL DEFAULT 'medium',
	weapon_type text NOT NULL DEFAULT 'slashing',
	in_use boolean NOT NULL DEFAULT true
);

CREATE TABLE csbt_character_armor_table (
	character_armor_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	armor_name text NOT NULL,
	armor_type text NOT NULL DEFAULT 'update me',
	ac_bonus integer NOT NULL DEFAULT 0,
	check_penalty integer NOT NULL DEFAULT 0,
	max_dex integer NOT NULL DEFAULT 5,
	special text,
	weight text,
	spell_fail integer NOT NULL DEFAULT 0,
	max_speed integer NOT NULL DEFAULT 30,
	is_worn boolean NOT NULL DEFAULT true
);

CREATE TABLE csbt_character_sa_table (
	character_sa_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	special_ability_name text NOT NULL,
	description text,
	book_reference text
);

CREATE TABLE csbt_character_gear_table (
	character_gear_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	gear_name text NOT NULL,
	weight decimal(10,1) NOT NULL DEFAULT 1,
	quantity integer NOT NULL DEFAULT 1,
	location text
);

CREATE TABLE csbt_character_save_table (
	character_save_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	save_name text NOT NULL,
	ability_id integer NOT NULL REFERENCES csbt_ability_table(ability_id),
	base_mod integer NOT NULL DEFAULT 0,
	magic_mod integer NOT NULL DEFAULT 0,
	misc_mod integer NOT NULL DEFAULT 0,
	temp_mod integer NOT NULL DEFAULT 0
);

-- Create a unique set of attributes.
INSERT INTO csbt_attribute_table (attribute) 
	SELECT 
		DISTINCT
			trim(trailing '-' FROM (attribute_type || '-' || attribute_subtype || '-' || attribute_name)) AS attribute
	FROM
		_backup_csbt_ca;


-- Populate the new character attribute table with previous values.
INSERT INTO csbt_character_attribute_table (character_id, attribute_id, attribute_value)
	SELECT 
		bak.character_id,
		(
			SELECT 
				attribute_id 
			FROM 
				csbt_attribute_table 
			WHERE 
				attribute = trim(trailing '-' FROM (attribute_type || '-' || attribute_subtype || '-' || attribute_name))
		),
		bak.attribute_value
	FROM
		_backup_csbt_ca AS bak;

DROP TABLE _backup_csbt_ca;
