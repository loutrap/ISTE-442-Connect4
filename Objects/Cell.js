//////////////////////////////////////////////////////
// Class: Cell										//
// Description:  This will create a cell object		// 
// (board square) that you can reference from the 	//
// game. 											//
// Arguments:										//
//		size - tell the object it's width & height	//
//		??
//		??
//		??
//		??
//////////////////////////////////////////////////////
//Cell constructor()
function Cell(parent,id,size,row,col){
	this.parent=parent;
	this.id=id;
	this.size=size;
	this.row=row;
	this.col=col;
	this.occupied=''; //hold the id of the piece
	this.y=this.size*this.row;
	this.x=this.size*this.col;
	this.object=this.create();
	this.object2=this.addCircle();
	this.parent.appendChild(this.object);
	this.parent.appendChild(this.object2);
	this.myBBox = this.getMyBBox();
}


//////////////////////////////////////////////////////
// Cell : Methods									//
// Description:  All of the methods for the			// 
// Cell Class (remember WHY we want these to be		//
// seperate from the object constructor!)			//
//////////////////////////////////////////////////////
Cell.prototype={
	create:function(){
		var rectEle=document.createElementNS(svgns,'rect');
		rectEle.setAttributeNS(null,'x',this.x+'px');
		rectEle.setAttributeNS(null,'y',this.y+'px');
		rectEle.setAttributeNS(null,'width',this.size+'px');
		rectEle.setAttributeNS(null,'height',this.size+'px');
		rectEle.setAttributeNS(null,'class','cell-rect');
		rectEle.setAttributeNS(null,'id',this.id);
		return rectEle;
	},
	addCircle:function(){
		var cirEle=document.createElementNS(svgns,'circle');
		cirEle.setAttributeNS(null,'r','45px');
        cirEle.setAttributeNS(null, 'cx',(this.x+50)+'px');
        cirEle.setAttributeNS(null, 'cy',(this.y+50)+'px');
        cirEle.setAttributeNS(null,'fill','white');
        cirEle.setAttributeNS(null,'stroke','red');
		cirEle.setAttributeNS(null,'stroke-width','1px');
		cirEle.setAttributeNS(null,'id',this.id);
		return cirEle
	},
	//get my bbox
	getMyBBox:function(){
		return this.object.getBBox();
	},
	//get CenterX
	getCenterX:function(){
		return (BOARDX+this.x+(this.size/2) );
	},
	//get CenterY
	getCenterY:function(){
		return (BOARDY+this.y+(this.size/2) );
	},
	//set a cell to occupied
	isOccupied:function(pieceId){
		this.occupied=pieceId;
	},
	//set cell to empty
	notOccupied:function(){
		this.occupied='';
	},
}