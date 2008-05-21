--
-- SVN INFORMATION:::
-- SVN Signature::::::::: $Id:tables.sql 23 2008-04-18 04:25:47Z crazedsanity $
-- Last Committted Date:: $Date:2008-04-17 23:25:47 -0500 (Thu, 17 Apr 2008) $
-- Last Committed Path::: $HeadURL:https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/docs/sql/tables.sql $
--


-- Table for authentication... should eventually use tokens (think "OpenID") instead of storing local authentication data.
CREATE TABLE csbt_user_table (
	user_id serial NOT NULL PRIMARY KEY,
	username varchar(128) NOT NULL,
	password varchar(32) NOT NULL,
	is_active boolean DEFAULT TRUE
);

-- Table that lists all possible status values.
CREATE TABLE csbt_status_table (
	status_id serial NOT NULL PRIMARY KEY,
	status text NOT NULL
);


-- Table containing a list of session names and various other data.
CREATE TABLE csbt_battle_table (
	battle_id serial NOT NULL PRIMARY KEY,
	battle_name text NOT NULL,
	date_created timestamp NOT NULL DEFAULT NOW(),
	creator_uid integer NOT NULL REFERENCES csbt_user_table(user_id),
	is_active boolean NOT NULL DEFAULT TRUE
);



-- Main table for holding actions, chat, etc.
-- For validating things in a different order: 
-- 1.) user inserts action (status is "pending")
-- 2.) admin looks at it, meanwhile somebody else inserts something
-- 3.) admin decides where it should be inserted; original record marked as "deleted"
-- 4.) admin inserts new record
CREATE TABLE csbt_main_table (
	main_id serial NOT NULL PRIMARY KEY,
	user_id integer NOT NULL REFERENCES csbt_user_table(user_id),
	is_action bool NOT NULL DEFAULT FALSE,
	status_id integer NOT NULL REFERENCES csbt_status_table(status_id),
	main_data text NOT NULL,
	creation_time timestamp NOT NULL DEFAULT NOW()
);


-- Table that lists all properties
CREATE TABLE csbt_property_table (
	property_id serial NOT NULL PRIMARY KEY,
	property_name text NOT NULL,
	description text NOT NULL
);


-- Linker table: links main to property.
CREATE TABLE csbt_main_property_table (
	main_property_id serial NOT NULL PRIMARY KEY,
	main_id integer NOT NULL REFERENCES csbt_main_table(main_id),
	property_id integer NOT NULL REFERENCES csbt_property_table(property_id),
	main_property_text text,
	main_property_num integer
);

-- Table to link properties to battles.
CREATE TABLE csbt_battle_property_table (
	battle_property_id serial NOT NULL PRIMARY KEY,
	battle_id integer NOT NULL REFERENCES csbt_battle_table(battle_id),
	property_id integer NOT NULL REFERENCES csbt_property_table(property_id),
	battle_property_text text,
	battle_property_num integer
);

