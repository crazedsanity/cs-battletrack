//         Set.Create("SET NAME",      "CODE")
var oSet = CreateSet("Ruins of the Wild", "DT4");
/*
oSet.AddTile( count, { Width:w, Height:h, Diagonal:d, Tag:t, TagB:tb, NameA:a, NameB:b, Extension:e } );
count      = number of times the tile occurs
Width      = width of the tile in squares
Height     = height of the tile in squares
Diagonal   = true/false if the tile has a diagonal
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
NameA      = the label for side a
NameB      = the label for side b (optional; without a SideB, the tile can't be flipped)
Extension  = the file type (.jpg, .gif, .png, etc.)
*/
 

oSet.AddTile(1, { Width:128, Height:32, Tag:'1.1', Dimensions:'2x8', NameA:'Stream', NameB:'Crevasse', Extension:'jpg' } );
oSet.AddTile(1, { Width:128, Height:128, Tag:'1.2', Dimensions:'8x8', NameA:'Camp', NameB:'Field', Extension:'jpg' } );
oSet.AddTile(1, { Width:128, Height:32, Tag:'2.1', TagB:'1.1', Dimensions:'2x8', NameA:'Road', NameB:'Crevasse', Extension:'jpg' } );
oSet.AddTile(1, { Width:128, Height:128, Tag:'2.2', Dimensions:'8x8', NameA:'Cabin', NameB:'Field w/Trees', Extension:'jpg' } );
oSet.AddTile(2, { Width:128, Height:32, Tag:'1.1', TagB:'3.1', Dimensions:'2x8', NameA:'Stream', NameB:'Road', Extension:'jpg' } );
oSet.AddTile(1, { Width:128, Height:128, Tag:'3.2', Dimensions:'8x8', NameA:'Stonehenge', NameB:'Field w/Pond', Extension:'jpg' } );
oSet.AddTile(1, { Width:128, Height:128, Tag:'4.2', Dimensions:'8x8', NameA:'Ruined Tower', NameB:'Field w/Trees', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:128, Tag:'5.1', Dimensions:'4x8', NameA:'Field', NameB:'Skeleton', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:32, Tag:'5.2', Dimensions:'2x4', NameA:'Graves', NameB:'Briar', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:64, Tag:'5.3', Dimensions:'4x4', NameA:'Mound', NameB:'Hobbit Hole', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:64, Tag:'5.4', Dimensions:'4x4', NameA:'Hill', NameB:'Stairs Down', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:32, Tag:'5.5', TagB:'5.2', Dimensions:'2x4', NameA:'Ruined Wagon', NameB:'Briar', Extension:'jpg' } );
oSet.AddTile(1, { Width:16, Height:16, Tag:'6.1', Dimensions:'1x1', NameA:'Mushroom Circle', NameB:'Campfire', Extension:'jpg' } );
oSet.AddTile(1, { Width:32, Height:16, Tag:'6.2', Dimensions:'1x2', NameA:'Fallen Statue', NameB:'Mud Puddle', Extension:'jpg' } );
oSet.AddTile(1, { Width:32, Height:16, Tag:'6.3', Dimensions:'1x2', NameA:'Camping Gear', NameB:'Hole in Ground', Extension:'jpg' } );
oSet.AddTile(1, { Width:32, Height:16, Tag:'6.4', Dimensions:'1x2', NameA:'Rock Outcropping', NameB:'Skeleton', Extension:'jpg' } );
oSet.AddTile(1, { Width:16, Height:32, Tag:'6.5', Dimensions:'1x2', NameA:'Log Bridge', NameB:'Treasure Chest', Extension:'jpg' } );
oSet.AddTile(1, { Width:32, Height:32, Tag:'6.6', Dimensions:'2x2', NameA:'Stream Bend', NameB:'Horse', Extension:'jpg' } );
oSet.AddTile(1, { Width:32, Height:32, Tag:'6.7', Dimensions:'2x2', NameA:'Stream Bend', NameB:'Road Bend', Extension:'jpg' } );
oSet.AddTile(1, { Width:32, Height:32, Tag:'6.8', Dimensions:'2x2', NameA:'Horse', NameB:'Road Bend', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:32, Tag:'6.9', Dimensions:'2x4', NameA:'Covered Wagon', NameB:'Field w/Log', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:32, Tag:'6.10', Dimensions:'2x4', NameA:'Field w/ Statue', NameB:'Muddy Pond', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:32, Tag:'6.11', Dimensions:'2x4', NameA:'Field', NameB:'Rock Outcropping', Extension:'jpg' } );
oSet.AddTile(1, { Width:64, Height:32, Tag:'6.12', Dimensions:'2x4', NameA:'Road w/ Steps', NameB:'Fallen Pillar', Extension:'jpg' } );
oSet.AddTile(1, { Width:128, Height:32, Tag:'6.13', Dimensions:'2x8',  NameA:'Field', NameB:'Road', Extension:'jpg' } );


/*
	Maps.Add("Map Export String");
Maps.Add("Dungeon Tiles IV,DT4.0.A.0.16.16.5,DT4.1.A.0.16.48.6,DT4.2.A.0.304.16.7,DT4.0.B.0.160.16.8,DT4.1.B.0.160.48.9,DT4.2.B.0.448.16.10,DT4.3.A.0.304.48.11,DT4.3.B.0.448.48.12,DT4.4.A.0.16.192.13,DT4.4.B.0.160.192.14,DT4.4.A.0.304.192.15,DT4.4.B.0.448.192.16,DT4.5.A.0.16.224.17,DT4.5.B.0.160.224.18,DT4.6.A.0.304.224.19,DT4.6.B.0.448.224.20,DT4.7.A.0.16.368.21,DT4.7.B.0.224.368.22,DT4.8.A.0.16.496.23,DT4.8.B.0.224.496.24,DT4.9.A.0.80.368.25,DT4.9.B.0.160.368.26,DT4.10.A.0.80.432.27,DT4.10.B.0.160.432.28,DT4.11.A.0.80.496.29,DT4.11.B.0.160.496.30,DT4.12.A.0.312.376.31,DT4.12.B.0.552.376.32,DT4.13.A.0.328.376.33,DT4.13.B.0.520.376.34,DT4.14.A.0.360.376.35,DT4.14.B.0.488.376.36,DT4.15.A.0.392.376.37,DT4.15.B.0.456.376.38,DT4.16.A.0.312.392.39,DT4.16.B.0.552.392.40,DT4.17.A.0.328.392.41,DT4.17.B.0.520.392.42,DT4.18.A.0.360.392.43,DT4.18.B.0.488.392.44,DT4.19.A.0.392.392.45,DT4.19.B.0.456.392.46,DT4.20.B.0.512.424.47,DT4.20.A.0.304.424.48,DT4.21.A.0.368.424.49,DT4.21.B.0.448.424.50,DT4.22.A.0.304.456.51,DT4.22.B.0.512.456.52,DT4.23.A.0.368.456.53,DT4.23.B.0.448.456.54,DT4.24.A.0.304.488.55,DT4.24.B.0.448.488.56");
*/
