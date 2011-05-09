--
-- SVN INFORMATION:::
-- SVN Signature::::::::: $Id:tables.sql 23 2008-04-18 04:25:47Z crazedsanity $
-- Last Committted Date:: $Date:2008-04-17 23:25:47 -0500 (Thu, 17 Apr 2008) $
-- Last Committed Path::: $HeadURL:https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/docs/sql/tables.sql $
--  


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


ALTER TABLE csbt_campaign_table ADD COLUMN description text;
