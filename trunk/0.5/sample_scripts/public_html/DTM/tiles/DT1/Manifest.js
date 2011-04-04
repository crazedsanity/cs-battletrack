//         CreateSet("SET NAME",      "CODE")
var oSet = CreateSet("Dungeon Tiles", "DT1");
/*
oSet.AddTile(count, width, height, filename prefix, dimensions, side a, side b);
count           = number of times the tile occurs
width           = width of the image in pixels
height          = height of the image in pixels
filename prefix = first part of the images' filename
	All tiles should have 4 images per side (two sided example):
		{prefix}.a.0.jpg
		{prefix}.a.90.jpg
		{prefix}.a.180.jpg
		{prefix}.a.270.jpg
		{prefix}.b.0.jpg
		{prefix}.b.90.jpg
		{prefix}.b.180.jpg
		{prefix}.b.270.jpg
	Single sided example:
		{prefix}.0.jpg
		{prefix}.90.jpg
		{prefix}.180.jpg
		{prefix}.270.jpg
dimensions       = the width and height in squares on the grid
side a           = the label for side a
side b           = the label for side b (optional; without a SideB, the tile can't be flipped)
*/
oSet.AddTile(2, 32, 16, "2x1_Bars", "2x1", "Bars", "Rubble");
oSet.AddTile(1, 64, 128, "4x8_Cave", "4x8", "Cave", "Floor");
oSet.AddTile(1, 64, 32, "4x2_Crevasse", "4x2", "Crevasse", "Floor");
oSet.AddTile(1, 128, 32, "8x2_Crevasse", "8x2", "Crevasse", "Floor");
oSet.AddTile(2, 32, 16, "2x1_DoubleDoors", "2x1", "Double Doors", "Rubble");
oSet.AddTile(1, 128, 32, "8x2_DragonStatues", "8x2", "Dragon Statues", "Floor");
oSet.AddTile(2, 32, 32, "2x2_Ground", "2x2", "Ground", "Floor");
oSet.AddTile(1, 32, 32, "2x2_Obelisk", "2x2", "Obelisk", "Floor");
oSet.AddTile(1, 32, 32, "2x2_Pit", "2x2", "Pit", "Floor");
oSet.AddTile(1, 64, 64, "4x4_Pit", "4x4", "Pit", "Floor");
oSet.AddTile(1, 64, 64, "4x4_Pool", "4x4", "Pool", "Floor");
oSet.AddTile(1, 64, 128, "4x8_Ruins", "4x8", "Ruins", "Floor");
oSet.AddTile(1, 32, 32, "2x2_Rune", "2x2", "Rune", "Floor");
oSet.AddTile(1, 32, 32, "2x2_SpiralStairs", "2x2", "Spiral Stairs", "Floor");
oSet.AddTile(2, 64, 32, "4x2_Stairs", "4x2", "Stairs", "Floor");
oSet.AddTile(1, 64, 32, "4x2_StairsLanding", "4x2", "Stairs Landing", "Floor");
oSet.AddTile(1, 128, 160, "8x10_Shop", "8x10", "Shop", "Floor");
oSet.AddTile(4, 32, 16, "2x1_SingleDoor", "2x1", "Single Door", "Floor");
oSet.AddTile(1, 16, 16, "1x1_Statue", "1x1", "Statue", "Floor");
oSet.AddTile(1, 128, 160, "8x10_Tavern", "8x10", "Tavern", "Floor");
oSet.AddTile(1, 16, 16, "1x1_TrapDoor", "1x1", "Trap Door", "Floor");
oSet.AddTile(1, 64, 64, "4x4_Treasure", "4x4", "Treasure", "Platform");
oSet.AddTile(8, 64, 32, "4x2_Wall", "4x2", "Wall", "Floor");
oSet.AddTile(1, 64, 64, "4x4_WaterFountain", "4x4", "Water Fountain", "Magic Circle");
