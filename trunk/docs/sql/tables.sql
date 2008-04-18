--
-- SVN INFORMATION:::
-- SVN Signature::::::::: $Id$
-- Last Committted Date:: $Date$
-- Last Committed Path::: $HeadURL$
--


-- Table for authentication... Uses tokens (think OpenID) to authenticate against an external system
CREATE TABLE csbt_user_table (
	user_id serial NOT NULL PRIMARY KEY,
	username varchar(128) NOT NULL,
	token_value text NOT NULL
);

-- Table that lists all possible status values.
CREATE TABLE csbt_status_table (
	status_id serial NOT NULL PRIMARY KEY,
	status text NOT NULL
);

-- Main table for holding actions, chat, etc.
-- For validating things in a different order: 
-- 1.) user inserts action (status is "pending")
-- 2.) admin looks at it, meanwhile somebody else inserts something
-- 3.) admin decides where it should be inserted; original record marked as "deleted"
-- 4.) 
CREATE TABLE csbt_main_table (
	main_id serial NOT NULL PRIMARY KEY,
	user_id integer NOT NULL REFERENCES csbt_user_table(user_id),
	is_action bool NOT NULL DEFAULT FALSE,
	status_id integer NOT NULL REFERENCES csbt_status_table(status_id),
	main_data text NOT NULL,
	creation_time timestamp NOT NULL DEFAULT NOW()
);


-- Chat table
CREATE TABLE csbt_chat_table (
	chat_id serial NOT NULL PRIMARY KEY,
	user_id integer NOT NULL REFERENCES csbt_user_table(user_id),
	chat_data text NOT NULL,
	creation_time timestamp NOT NULL DEFAULT NOW()
);
