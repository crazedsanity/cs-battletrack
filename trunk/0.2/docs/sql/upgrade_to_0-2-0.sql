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
	skill_mod integer NOT NULL default 0,
	ability_mod integer NOT NULL default 0,
	ranks integer NOT NULL default 0,
	misc_mod integer NOT NULL default 0
);

CREATE TABLE csbt_character_weapon_table (
	character_weapon_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	weapon_name text NOT NULL,
	total_attack_bonus integer NOT NULL,
	damage text NOT NULL,
	critical text NOT NULL,
	range text NOT NULL DEFAULT 'melee',
	special text,
	ammunition text,
	weight text,
	size text NOT NULL DEFAULT 'medium',
	weapon_type text NOT NULL,
	in_use boolean NOT NULL DEFAULT true
);

CREATE TABLE csbt_character_armor_table (
	character_armor_id serial NOT NULL PRIMARY KEY,
	character_id integer NOT NULL REFERENCES csbt_character_table(character_id),
	armor_name text NOT NULL,
	armor_type text NOT NULL,
	ac_bonus integer NOT NULL,
	check_penalty integer NOT NULL DEFAULT 0,
	max_dex integer NOT NULL,
	special text,
	weight text,
	spell_fail integer NOT NULL DEFAULT 50,
	max_speed integer NOT NULL DEFAULT 30,
	is_worn boolean NOT NULL DEFAULT true
);

CREATE TABLE csbt_character_feat_ability_table (
	character_feat_ability_id serial NOT NULL PRIMARY KEY,
	feat_name text NOT NULL,
	description text
);

CREATE TABLE csbt_character_gear_table (
	character_gear_id serial NOT NULL PRIMARY KEY,
	gear_name text NOT NULL,
	weight integer NOT NULL DEFAULT 1,
	location text
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