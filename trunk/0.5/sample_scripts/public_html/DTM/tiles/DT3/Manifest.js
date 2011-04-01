//         CreateSet("SET NAME",      "CODE")
var oSet = CreateSet("Hidden Crypts", "DT3");
/*
oSet.AddTile( count, { Width:w, Height:h, Tag:t, TagB:tb, Dimensions:d, NameA:a, NameB:b, Extension:e } );
count      = number of times the tile occurs
Width      = width of the image in pixels
Height     = height of the image in pixels
Tag        = first part of the images' filename
TagB       = first part of the images' B-side filename (for use in duplicate backside images)
	All tiles should have 4 images per side:
	Two sided example:
		{Tag}.a.0.jpg
		{Tag}.a.90.jpg
		{Tag}.a.180.jpg
		{Tag}.a.270.jpg
		{Tag}.b.0.jpg
		{Tag}.b.90.jpg
		{Tag}.b.180.jpg
		{Tag}.b.270.jpg
	TagB example:
		{Tag}.a.0.jpg
		{TagB}.b.0.jpg
	Single sided example:
		{Tag}.0.jpg
		{Tag}.90.jpg
		{Tag}.180.jpg
		{Tag}.270.jpg
Dimensions = the width and height in squares on the grid
NameA      = the label for side a
NameB      = the label for side b (optional; without a SideB, the tile can't be flipped)
Extensions = the file type (.jpg, .gif, .png, etc.)
*/
oSet.AddTile(1, {Width:32, Height:64, Tag:'2x4_AcidPit', Dimensions:'2x4', NameA:'Acid Pit', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:128, Height:32, Tag:'8x2_JQ_Alcoves', TagB:'8x2_Stairs', Dimensions:'8x2', NameA:'Alcoves', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:32, Tag:'4x2_JQ_Alcoves', TagB:'4x2_Wall', Dimensions:'4x2', NameA:'Alcoves - Hall', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(2, {Width:32, Height:64, Tag:'2x4_JQ_Alcoves', TagB:'2x4_AcidPit', Dimensions:'2x4', NameA:'Alcoves - Wall', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:128, Height:160, Tag:'8x10_Barn', Dimensions:'8x10', NameA:'Barn', NameB:'Empty Crypt', Extension:'jpg' } )
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2_Cage', TagB:'2x2_Stairs', Dimensions:'2x2', NameA:'Cage', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2_ClawPool', TagB:'2x2_Stairs', Dimensions:'2x2', NameA:'Claw Pool', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:64, Tag:'4x4_Crypt', Dimensions:'4x4', NameA:'Crypt', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(2, {Width:64, Height:64, Tag:'4x4xd_Crypt', Dimensions:'4x4d', NameA:'Crypt', NameB:'Floor', Extension:'gif' } );
oSet.AddTile(1, {Width:64, Height:32, Tag:'4x2_DoubleIronDoors', TagB:'4x2_Wall', Dimensions:'4x2', NameA:'Double Doors - Hall', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2_Grate', Dimensions:'2x2', NameA:'Grate', NameB:'Sink Hole', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:32, Tag:'4x2_HallArch', TagB:'4x2_Wall', Dimensions:'4x2', NameA:'Hall Arch', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:16, Height:16, Tag:'1x1_IronMaiden', Dimensions:'1x1', NameA:'Iron Maiden', NameB:'Skulls', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:32, Tag:'4x2_LargeDoubleDoors', TagB:'4x2_Wall', Dimensions:'4x2', NameA:'Large Double Doors', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:128, Height:160, Tag:'8x10_Mausoleum', Dimensions:'8x10', NameA:'Mausoleum', NameB:'Coffins', Extension:'jpg' } );
oSet.AddTile(1, {Width:32, Height:16, Tag:'2x1_Rack', Dimensions:'2x1', NameA:'Rack', NameB:'Coffin', Extension:'jpg' } );
oSet.AddTile(2, {Width:16, Height:16, Tag:'1x1_Sarcophagus', TagB:'1x1_Wall', Dimensions:'1x1', NameA:'Sarcophagus', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(2, {Width:32, Height:16, Tag:'2x1_Sarcophagus', TagB:'2x1_WoodenDoor', Dimensions:'2x1', NameA:'Sarcophagus', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:64, Tag:'4x4_SkullPile', Dimensions:'4x4', NameA:'Skull Pile', NameB:'Blood Symbol', Extension:'jpg' } );
oSet.AddTile(1, {Width:16, Height:64, Tag:'1x4_Stairs', TagB:'1x4_Wall', Dimensions:'1x4', NameA:'Stairs', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2_Stairs', Dimensions:'2x2', NameA:'Stairs', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:128, Height:32, Tag:'8x2_Stairs', Dimensions:'8x2', NameA:'Stairs', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2_StatueAltar', TagB:'2x2_Stairs', Dimensions:'2x2', NameA:'Statue w/ Altar', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2_StatueFire', TagB:'2x2_Stairs', Dimensions:'2x2', NameA:'Statue w/ Fire', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2xd_StatueShield', TagB:'2x2xd_Wall', Dimensions:'2x2d', NameA:'Statue w/ Shield', NameB:'Floor', Extension:'gif' } );
oSet.AddTile(1, {Width:32, Height:32, Tag:'2x2xd_StatueSpear', TagB:'2x2xd_Wall', Dimensions:'2x2d', NameA:'Statue w/ Spear', NameB:'Floor', Extension:'gif' } );
oSet.AddTile(2, {Width:64, Height:64, Tag:'4x4xd_Steps', TagB:'4x4xd_Crypt', Dimensions:'4x4d', NameA:'Steps', NameB:'Floor', Extension:'gif' } );
oSet.AddTile(2, {Width:16, Height:16, Tag:'1x1_Wall', Dimensions:'1x1', NameA:'Wall', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:16, Height:64, Tag:'1x4_Wall', Dimensions:'1x4', NameA:'Wall', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(2, {Width:32, Height:32, Tag:'2x2xd_Wall', Dimensions:'2x2d', NameA:'Wall', NameB:'Floor', Extension:'gif' } );
oSet.AddTile(2, {Width:64, Height:32, Tag:'4x2_Wall', Dimensions:'4x2', NameA:'Wall', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:16, Height:16, Tag:'1x1_WoodenDoor', TagB:'1x1_Wall', Dimensions:'1x1', NameA:'Wooden Door', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:16, Height:32, Tag:'1x2_WoodenDoor', Dimensions:'1x2', NameA:'Wooden Door', NameB:'Coffin', Extension:'jpg' } );
oSet.AddTile(2, {Width:32, Height:16, Tag:'2x1_WoodenDoor', Dimensions:'2x1', NameA:'Wooden Door', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(2, {Width:32, Height:16, Tag:'2x1_DoubleWoodenDoors', TagB:'2x1_WoodenDoor', Dimensions:'2x1', NameA:'Wooden Double Doors', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:32, Tag:'4x2_WoodenFloor', TagB:'4x2_Wall', Dimensions:'4x2', NameA:'Wooden Floor', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:32, Tag:'4x2_WoodenPlatformSE', TagB:'4x2_Wall', Dimensions:'4x2', NameA:'Wooden Platform', NameB:'Floor', Extension:'jpg' } );
oSet.AddTile(1, {Width:64, Height:32, Tag:'4x2_WoodenPlatformNE', TagB:'4x2_Wall', Dimensions:'4x2', NameA:'Wooden Platform 2', NameB:'Floor', Extension:'jpg' } );
