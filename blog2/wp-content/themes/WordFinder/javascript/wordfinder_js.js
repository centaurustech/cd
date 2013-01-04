// When the document loads do everything inside here ...
jQuery(document).ready(function(){

					if (jQuery("#wrap_container").hasClass("nofixed")) {
						jQuery("#wrap_container").css({position:"absolute"});
					} else {
						jQuery("#wrap_container").css({position:"fixed"});
					}

// Hover for Titles and div in index	
jQuery(".third-post, #four-post, #sidebar h2 a, #post h1, #archive-post h1").hover(function() {
jQuery(this).animate({ backgroundColor: '#ddd'},600);
},function() {
jQuery(this).animate({ backgroundColor: '#eee'},600);
});

jQuery(" .second-post, #first-post").hover(function() {
jQuery(this).animate({ backgroundColor: '#eee'},600);
},function() {
jQuery(this).animate({ backgroundColor: '#fff'},600);
});

// Hover for images
jQuery("#first-post-thumb, .postthumb, img , .sideadvert_250x250, .headadvert_468x60 ").hover(function() {
jQuery(this).animate({borderColor: '#bbb', backgroundColor: '#ddd'},600);
},function() {
jQuery(this).animate({borderColor: '#ddd' , backgroundColor: '#f7f7f7'},600);
});


// Hover for link on Wrap Categories
jQuery(".moretext").hover(function() {
jQuery(this).animate({ backgroundColor: '#fff'},600);
},function() {
jQuery(this).animate({ backgroundColor: '#eee'},600);
});

// Hover for link on Wrap Categories
jQuery(".prevleft,.nextright").hover(function() {
jQuery(this).animate({ backgroundColor: '#ddd', borderColor: '#ccc'},600);
},function() {
jQuery(this).animate({ backgroundColor: '#fff', borderColor: '#ddd'},600);
});


jQuery("#wrap .main-menu li, #wrap li").hover(function(){ 
jQuery(this).find('ul:first').css({visibility: "visible",display: "none"}).slideDown('fast').show();
},function(){ 
jQuery(this).find('ul:first').css({}).slideUp('fast').show();
});

jQuery("#wrap .main-menu ul.sub-menu, #wrap ul.sub-menu").parent().prepend("<span></span>"); 


});