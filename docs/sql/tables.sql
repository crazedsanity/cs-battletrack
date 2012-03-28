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
	campaign_name varchar(128) NOT NULL,	
	description text,
	owner_uid integer NOT NULL REFERENCES cs_authentication_table(uid),
	create_date timestamp NOT NULL DEFAULT NOW(),
	is_active bool NOT NULL DEFAULT true
);


-- 
-- Contains the main character information.
-- 

CREATE TABLE csbt_character_table (
	character_id serial NOT NULL PRIMARY KEY,
	uid integer NOT NULL REFERENCES cs_authentication_table(uid),
	character_name text,
	campaign_id integer DEFAULT NULL REFERENCES csbt_campaign_table(campaign_id),
	ac_misc integer NOT NULL DEFAULT 0,
	ac_size integer NOT NULL DEFAULT 0,
	ac_natural integer NOT NULL DEFAULT 0,
	action_points integer NOT NULL DEFAULT 0,
	character_age integer NOT NULL DEFAULT 18,
	character_level text NOT NULL DEFAULT '(class/level) EXAMPLE: fighter/1, rogue/3',
	alignment text NOT NULL DEFAULT 'Chaotic Neutral',
	base_attack_bonus integer NOT NULL DEFAULT 1,
	deity text,
	eye_color text,
	gender text,
	hair_color text,
	height text,
	hit_points_max integer NOT NULL DEFAULT 1,
	hit_points_current integer NOT NULL DEFAULT 1,
	race text,
	size text NOT NULL DEFAULT 'Medium',
	weight integer NOT NULL DEFAULT 180,
	initiative_misc integer NOT NULL DEFAULT 0,
	nonlethal_damage integer NOT NULL DEFAULT 0,
	hit_dice text NOT NULL DEFAULT 'd6',
	damage_reduction text,
	melee_misc integer NOT NULL DEFAULT 0,
	melee_size integer NOT NULL DEFAULT 0,
	melee_temp integer NOT NULL DEFAULT 0,
	ranged_misc integer NOT NULL DEFAULT 0,
	ranged_size integer NOT NULL DEFAULT 0,
	ranged_temp integer NOT NULL DEFAULT 0,
	skills_max integer NOT NULL DEFAULT 10,
	speed integer NOT NULL DEFAULT 30,
	xp_current integer NOT NULL DEFAULT 0,
	xp_next integer NOT NULL DEFAULT 0,
	notes text
);

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


CREATE TABLE csbt_map_table (
	map_id serial NOT NULL PRIMARY KEY,
	campaign_id integer REFERENCES csbt_campaign_table(campaign_id),
	map_name text NOT NULL DEFAULT 'map',
	map_image_url text,
	creator_uid integer NOT NULL REFERENCES cs_authentication_table(uid),
	width integer NOT NULL DEFAULT 10,
	height integer NOT NULL DEFAULT 10,
	offset_left integer NOT NULL DEFAULT 20,
	offset_top integer NOT NULL DEFAULT 20,
	cell_size integer NOT NULL DEFAULT 32,
	toolbox_offset_left integer NOT NULL default 200,
	toolbox_offset_top integer NOT NULL DEFAULT 50,
	grid_shown boolean NOT NULL DEFAULT false
);


CREATE TABLE csbt_map_token_table (
	map_token_id serial NOT NULL PRIMARY KEY,
	map_id integer NOT NULL REFERENCES csbt_map_table(map_id),
	token_name text NOT NULL DEFAULT 'not set...',
	token_img text,
	location text,
	movement text
);
