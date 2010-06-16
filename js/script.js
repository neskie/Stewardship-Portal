var theImages = new Array() 

theImages[0] = 'images/Image1.jpg'
theImages[1] = 'images/Image2.jpg'
theImages[2] = 'images/Image3.jpg'
theImages[3] = 'images/Image4.jpg'

var j = 0
var p = theImages.length;
var preBuffer = new Array()
for (i = 0; i < p; i++){
   preBuffer[i] = new Image()
   preBuffer[i].src = theImages[i]
}
var whichImage = Math.round(Math.random()*(p-1));
function showImage(){
document.write('<img width="100%" src="'+theImages[whichImage]+'">');
}

