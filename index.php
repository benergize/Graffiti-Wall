<!DOCTYPE html>
<html>
	<head>
	
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<title>Graffiti</title>
		<script type="text/JavaScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js?ver=1.4.2"></script>
		
		<style>
			@font-face {
				font-family:'graffiti';
				src:url("Empires.otf");
			}
			* {
				margin: 0;
				padding: 0;
			}

			body, html {
				height: 100%; 
				width:100%;
				font-family:"Courier New";
			}
			h1 {
				
				font-family: graffiti;
				font-size: 4rem;
				margin-bottom: 13px;
			}
			h2 {
				font-size:20px;
			}
			#canvas {
				cursor: crosshair;
				position: relative;
				display:block;
				width:128px;
				height:128px;
				margin-left:2rem;

				border:1px solid black;
			}
			#canvas-holder {
				display:flex;flex-flow:row nowrap;justify-content:flex-start;align-items:center;width:200px;margin:0 auto;
			}
			#canvas-holder h2 {
			
				position: absolute;
				z-index: 20;
				margin-left: 4.35rem;
				pointer-events: none;
				transition: opacity 1s ease;
				opacity: 1;
			}
			
			details {
				width:600px; margin:0 auto; max-width:90%;margin-top:2rem;
			}
			summary {cursor:pointer;}
			details p {
				margin-left:1em;
			}
			
			button {
			  padding:12px;
			  border:1px solid gray;
			}
			button.active {border:1px solid black; box-shadow:0px 0px 4px black;}
			
		</style>
	</head>
	<body>  
		<br/>
		<div style = 'text-align:center'>
			<h1>Leave Some Graffiti</h1>
			<a href = 'wall.php'>or look at the wall</a><br/>
			<Br/> 
			
			<label>Brush Size: <input type = 'number' style = 'width:2rem;' id = 'brush-size' value = '2'></label> &nbsp; 
			<label>Active Color: <input type = 'color' id = 'color-picker'></label>
			<br/><br/><br/>
			<div id = 'canvas-holder'>
				<div id = 'palette'>
					<button value = '#D72525'></button><br/>
					<button value = '#459E22'></button><br/>
					<button value = '#2060F8'></button><br/>
					<button value = '#FFBE2D'></button><br/>
					<button value = '#F47C0C'></button><br/>
					<button value = '#56419B'></button><br/>
					<button value = '#E57CDF'></button><br/>
					<button value = '#000000'></button><br/>
					<button value = '#ffffff'></button>
				</div>
				<canvas id="canvas" width = 128 height = 128>
					Sorry, your browser does not support HTML5 canvas technology.
				</canvas>
				<h2>Draw<br>Something<br>Here</h2>
				
			</div>
		
			<button id = 'spray-it'>Finish</button>
			
		</div>
		
		
	  
		<details>
			<summary>What is this?</summary>

			<p>This site is an ever growing digital graffiti wall. As more people contribute art, the wall continues to grow. Make a contribution! Draw some graffiti in the box above and hit Finish to add it to the wall.</p>
		
		</details>
		<details>
			<summary>Credits</summary>
			
			<p>
				<b>Graffitti:</b><br/>
				C. Ben Ehrlich 2023, a B.I.G. project. Colors provided by Isabel Stewart.<br/>
				<b>WHITEBOARD:</b><br/>
				Based on https://codepen.io/michaelsboost/pen/kQmwyq (Copyright (c) 2023 by Michael Schwartz, MIT License)<br/>
				Heavily modified by Ben Ehrlich --- MIT License
			</p>
		
		</details>
		
		<script>
		
			//Hide the instructions text after 1 second
			setTimeout(fn=>{document.querySelector("#canvas-holder h2").style.opacity = 0;},1000);
		
		
			//Get brush size and color
			function brushSize() { return Number.parseInt(document.querySelector("#brush-size").value); }
			function brushColor() { return document.querySelector("#color-picker").value; }
		
			document.querySelectorAll("#palette button").forEach(button=>{ button.style.background = button.value; });
		
			//Submit art
			document.querySelector("#spray-it").onclick = function() {
				this.style.display="none";
				var payload = {
					image: document.querySelector("#canvas").toDataURL('image/png')
				};
				console.log(payload);

				fetch("addArt.php",
				{
					method: "POST",
					body: JSON.stringify(payload), 
					headers: {
					  'Content-Type': 'application/json'
					},
				})
				.then(function(res){ return res.json(); })
				.then(function(data){ console.log(data); window.location = "wall.php"; })

			}
			
			//Select active color
			document.querySelectorAll("#palette button").forEach(el=>{
				
				
				el.onclick = fn=>{
					
					document.querySelector("#color-picker").value = el.value
				}
			});
		
			//Establish canvas and draw context
			canvas = document.getElementById("canvas");
			ctx = canvas.getContext("2d");
				
			// Set Background Color
			ctx.fillStyle = "white";
			ctx.fillRect(0,0,canvas.width,canvas.height);
			
			// Mouse Event Handlers
			ctx.lineWidth = brushSize();
			
			//Canvas logic
			graff = {
				mouse_x:0.0,
				mouse_y:0.0,
				mouseDown:false,
				
				//Everything the user has drawn is stored in this variable. This is used for undoing.
				lines: [],
				
				//Redo array
				redoTrail:[],
				
				//Mouse or finger down
				penDown: function(e, recordLine=true) {
					
					let mobile = String(e.type).indexOf("touch")!=-1;
					
					//Track the current mouse coordinates
					graff.setMouseCoords(e, mobile);
					
					//If the mouse is over the canvas and we're clicking OR if this penDown is called by undo/redo
					if((graff.getMouseInBounds() && (e.buttons == 1 || mobile)) || !recordLine) {
					
						//Set the pen state to down
						graff.mouseDown = true;
						
						//Start the path
						ctx.beginPath();
						
						//Move the path to the mouse position.
						ctx.moveTo(graff.mouse_x,graff.mouse_y);
						
						//If we're recording this line (meaning this isn't an undo/redo action)
						if(recordLine) { 
						
							//Clear our redo path by setting it to the current drawing history
							graff.redoTrail = graff.lines;
							
							//Add the line we're making to the line history
							graff.lines.push({path:[[graff.mouse_x,graff.mouse_y]],size:brushSize(),color:brushColor()}); 
							
							
						}
					}
				},
				
				//Mouse or finger up
				penUp: function(e,colorOverride=false,sizeOverride=false) {
					
					//If we've been drawing a line
					if(graff.mouseDown) {
						
						//Track the current mouse coordinates
						graff.setMouseCoords(e, String(e.type).indexOf("touch")!=-1);
						
						//Move the current line position to our mouse coordinates
						ctx.lineTo(graff.mouse_x, graff.mouse_y);
						
						//Set the width/stroke of our line
						ctx.lineWidth = sizeOverride||brushSize();
						ctx.strokeStyle = colorOverride||brushColor();
						
						//And draw it to the canvas
						ctx.stroke();
						
						//Life the mouse
						graff.mouseDown = false;
					}
				},
				
				//Mouse move
				penMove: function(e,colorOverride=false,sizeOverride=false, recordLine=true) {
					
					graff.setMouseCoords(e, String(e.type).indexOf("touch")!=-1);
					
					//If we're tracking the pen down
					if(graff.mouseDown) {
						
						//Move the current line position to our mouse coordinates
						ctx.lineTo(graff.mouse_x, graff.mouse_y);
						
						//Set the width/stroke of our line
						ctx.lineWidth = sizeOverride||brushSize();
						ctx.strokeStyle = colorOverride||brushColor();
						
						//And draw it to the canvas
						ctx.stroke();
						
						//If this isn't being called by undo/redo, add this motion step to the current path
						if(recordLine) { graff.lines[graff.lines.length-1].path.push([graff.mouse_x,graff.mouse_y]); }
					}
					
					//If the mouse isn't pressed (meaning either the user isn't clicking or they started clicking off canvas
					else {
						
						//If the mouse is inside the canvas bounds (so we've moved in bounds from out of bounds with the mouse held)
						if(graff.getMouseInBounds()) {
							
							//Treat this as a pen down event
							graff.penDown(e);
						}
					}
				},
				
				//Put the mouse/finger coordinates into variables
				setMouseCoords: function(e, mobile=false) {
					
					
					if(mobile && e.touches.length == 0) { return false; }
					
					graff.mouse_x = (mobile ? e.touches[0].pageX : e.pageX) - canvas.offsetLeft;
					graff.mouse_y = (mobile ? e.touches[0].pageY : e.pageY) - canvas.offsetTop;
				},
				
				//Check if the mouse is over the canvas
				getMouseInBounds:function() {
					
					return graff.mouse_x > 0 && graff.mouse_y > 0 && graff.mouse_x < canvas.width && graff.mouse_y < canvas.height;
				},
				
				//Run through everything in the stroke history
				strokeHistory: function() {
					
					//Clear the canvas
					ctx.fillStyle = "white";
					ctx.fillRect(0,0,canvas.width,canvas.height);
					
					//If there's anything to draw
					if(graff.lines.length > 0) {
						
						//Go through each line
						graff.lines.forEach((line)=>{
							
							//Go through each line's path
							line.path.forEach((path,i)=>{
							
								//Simulate a touch event
								let fakeEvent = {touches:[],pageX:path[0]+canvas.offsetLeft,pageY:path[1]+canvas.offsetTop};
								
								//Pass the touch event to one of the pen functions, depending on what part of the path we're on
								if(i == 0) { graff.penDown(fakeEvent,false); }
								else if(i == line.path.length-1) { graff.penUp(fakeEvent, line.color, line.size,false) ;}
								else { graff.penMove(fakeEvent, line.color, line.size,false); }
							
							});
						});
					}
				},
				
				//Remove last stroke
				undo: function() {
					
					//If there's anything in the stroke path list
					if(graff.lines.length > 0) {
						
						//Drop the last line off
						graff.lines = graff.lines.slice(0,-1);
						
						//Repaint the history without that last stroke
						graff.strokeHistory();
					}
				},
				
				//Undo an undo
				redo: function() {
					
					//If there's anything to redo/we haven't done anything to break the redo history chain
					if(graff.redoTrail.length > 0) {
						
						//If the redo chain is longer than the current path list
						if(graff.redoTrail.length > graff.lines.length) { 
						
							//Add 1 part of the redo trail to the path history
							graff.lines.push(graff.redoTrail[graff.lines.length]);
							
							//Draw the stroke history
							graff.strokeHistory();
						}
					}
				},
				
				//Set color from current mouse position
				colorFromPos:function(e) {
					
					graff.setMouseCoords(e);
					
					e.preventDefault();
					
					let rgb = ctx.getImageData(graff.mouse_x,graff.mouse_y,1,1).data;
					let hex = "#" + rgb[0].toString(16).padStart(2,0) + rgb[1].toString(16).padStart(2,0) + rgb[2].toString(16).padStart(2,0);
					document.querySelector("#color-picker").value = hex;
					
				},
				
				//Bind events and initialize whiteboard
				init: function() {
					
					// Touch Events					
					canvas.addEventListener('touchstart', graff.penDown, {passive:false});
					canvas.addEventListener('touchend', graff.penUp, {passive:false});
					canvas.addEventListener('touchmove', graff.penMove, {passive:false});
					
					//Mouse events
					window.addEventListener("mousedown", graff.penDown, {passive:false});
					window.addEventListener("mousemove", graff.penMove, {passive:false});
					window.addEventListener("mouseup", graff.penUp, {passive:false});
					
					//Get color at position when you right click
					canvas.addEventListener("contextmenu", graff.colorFromPos);
					
					//Change brush size when scrolling over brush size selector
					document.querySelector("#brush-size").addEventListener("mousewheel", function(e) {
						let bss = document.querySelector("#brush-size");
						console.log(e);
						bss.value = parseInt(bss.value) + (e.deltaY == 0 ? 0 : -1*(e.deltaY/Math.abs(e.deltaY)));
					});
					
					window.addEventListener("keydown", function(e){
						console.log(e);
						if(e.ctrlKey && e.shiftKey && String(e.key).toLowerCase() == "z") { e.preventDefault(); graff.redo(); }
						else if(e.ctrlKey && e.key == "z") { e.preventDefault(); graff.undo(); }
						else if(e.ctrlKey && e.key == "y") { e.preventDefault(); graff.redo(); }
					});
					
					//Prevent selecting things when dragging pen outside canvas
					document.querySelector("#canvas-holder").addEventListener("mousedown",function(e){ e.preventDefault(); });
				
					// Disable Page Move
					document.body.addEventListener('touchmove',function(e){ e.preventDefault(); }, {passive:false});
				}
			}
			
			graff.init();
			

			
		</script>
	</body>
</html>
