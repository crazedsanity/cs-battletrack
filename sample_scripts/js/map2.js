var animateMe = function(targetElement, speed){
    //move back to start if out of bounds
    var position = $(targetElement).position();
    if(position.left >= $('.container').width()){
        $(targetElement).css({left:'-200px'});
    };
    //animate
    $(targetElement).animate(
        {
        'left': $('.container').width()
        },
        {
        duration: speed,
        complete: function(){
            animateMe(this, speed);
            }
        }
    );
};

//hover (start / stop)
//note this can also be duplicated by .mouseover(function(){}).mouseout(function(){});
$('.container').hover(function(){
console.debug("starting...");
    animateMe($('#token_1'), 5000);
    animateMe($('#token_2'), 3000);
},
function(){
    $('#object1,#object2').stop();
});

