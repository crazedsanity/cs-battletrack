var oSet = CreateSet("Arcane Corridors", "DT2");
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
oSet.AddTile(1, 16, 32, "1x2_Altar", "1x2", "Altar", "Floor");
oSet.AddTile(1, 64, 64, "4x4_BloodMist", "4x4", "Blood Mist", "Floor");
oSet.AddTile(1, 64, 64, "4x4_BloodSymbol", "4x4", "Blood Symbol", "Floor");
oSet.AddTile(1, 32, 16, "2x1_BluePortal", "2x1", "Blue Portal", "Rubble");
oSet.AddTile(1, 64, 32, "4x2_BrokenIronDoors", "4x2", "Broken Doors", "Floor");
oSet.AddTile(1, 128, 32, "8x2_Couches", "8x2", "Couches", "Floor");
oSet.AddTile(2, 32, 16, "2x1_DoubleDoors", "2x1", "Double Doors", "Floor");
oSet.AddTile(2, 32, 32, "2x2_FireBowl", "2x2", "Fire Bowl", "Floor");
oSet.AddTile(1, 64, 64, "4x4_FireVortex", "4x4", "Fire Vortex", "Runes");
oSet.AddTile(2, 64, 32, "4x2_FlameGout", "4x2", "Flame Blast", "Floor");
oSet.AddTile(1, 64, 128, "4x8_Fog", "4x8", "Fog", "Floor");
oSet.AddTile(2, 64, 32, "4x2_IronDoubleDoors", "4x2", "Iron Doors", "Floor");
oSet.AddTile(1, 128, 32, "8x2_Lightning", "8x2", "Lightning", "Floor");
oSet.AddTile(1, 64, 32, "4x2_BlueMagicWall", "4x2", "Magic Wall", "Floor");
oSet.AddTile(1, 64, 64, "4x4_NaturalPit", "4x4", "Natural Pit", "Desk");
oSet.AddTile(1, 64, 128, "4x8_Pool", "4x8", "Pool", "Hall w/ Statues");
oSet.AddTile(1, 16, 32, "1x2_PurplePortal", "1x2", "Purple Portal", "Rubble");
oSet.AddTile(3, 32, 16, "2x1_SingleDoor", "2x1", "Single Door", "Floor");
oSet.AddTile(1, 64, 32, "4x2_DragonSkeleton", "4x2", "Skeleton", "Floor");
oSet.AddTile(1, 16, 16, "1x1_SkullPile", "1x1", "Skull Pile", "Floor");
oSet.AddTile(1, 32, 32, "2x2_SpikedPit", "2x2", "Spiked Pit", "Floor");
oSet.AddTile(1, 64, 32, "4x2_Stairs2", "4x2", "Stairs", "Bookshelf");
oSet.AddTile(1, 64, 32, "4x2_Stairs", "4x2", "Stairs", "Floor");
oSet.AddTile(1, 32, 32, "2x2_ToothyMaw", "2x2", "Toothy Maw", "Floor");
oSet.AddTile(1, 128, 160, "8x10_TowerBase", "8x10", "Tower Base", "Floor");
oSet.AddTile(1, 128, 160, "8x10_TowerTop", "8x10", "Tower Top", "Magic Lab");
oSet.AddTile(1, 16, 16, "1x1_Wall", "1x1", "Wall", "Floor");
oSet.AddTile(1, 64, 32, "4x2_Wall", "4x2", "Wall", "Bookshelf");
oSet.AddTile(2, 64, 32, "4x2_Wall2", "4x2", "Wall", "Floor");
oSet.AddTile(1, 32, 32, "2x2_Web", "2x2", "Web", "Floor");
oSet.AddTile(1, 32, 32, "2x2_Web2", "2x2", "Web", "Runes");
