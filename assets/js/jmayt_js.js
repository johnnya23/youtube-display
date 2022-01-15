function jmayt_title_resize() {
    jQuery('.jmayt-list-wrap').each(function() {
        //make all title boxes the same height as the largest boxes
        $list_wrap = jQuery(this);
        var $title_max = Math.max.apply(null, $list_wrap.find('h3').map(function() {
            return jQuery(this).outerHeight();
        }).get());
        $list_wrap.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });
}

function jmayt_toggle() {
    //create the toggle lightbox effect for the youtube items

    jQuery('.jmayt-item').each(function() {
        $this = jQuery(this);
        //apply the toggle effect to the area around the video
        //when it is expanded
        $this.find('.jmayt-video-wrap').on('click', function() {
            if (jQuery(this).hasClass('jmayt-fixed'))
                jmayt_handle_lightbox(jQuery(this));
        }).children().on('click', function(e) {
            return false;
        });


        $this.find('.jmayt-btn').on('click', function() {
            jmayt_handle_lightbox(jQuery(this));
        });
    });
}

function jmayt_handle_lightbox($button) {
    if (!$button.is('button'))
        $button = $button.find('.jmayt-btn');

    $jmayt_item = $button.closest('.jmayt-item');
    jmayt_item_width = $jmayt_item.innerWidth();
    $jmayt_video_wrap = $jmayt_item.find('.jmayt-video-wrap'); //adjust class jmayt-fixed
    $z_index = $jmayt_video_wrap.parents('.jmayt-outer').parents().add($jmayt_video_wrap);
    $video_win = $jmayt_item.find('.jma-responsive-wrap');
    //open the lightbox
    if (!$jmayt_video_wrap.hasClass('jmayt-fixed')) {

        //distance the user has scrolled down the window (dynamic)
        $scroll = jQuery(document).scrollTop();
        //get rid of scroll

        $jmayt_item.css('min-height', $jmayt_item.height() + 'px');
        $button.html('&#xe097;');
        //bring this section of the page to the top
        $z_index.css({
            'z-index': '2147483647',
            'overflow': 'visible'
        });
        jQuery('body').css({
            'overflow-y': 'hidden'
        });
        //first we make it absolute and give it a size
        $jmayt_video_wrap.addClass('jmayt-fixed');
        //x and y coordinates of the div (static)
        $pos = $jmayt_item.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $jmayt_video_wrap.css({
            'width': (jmayt_item_width) + 'px',
            'height': (jmayt_item_width) / 1.7778 + 'px',
            'padding-bottom': 0
        }).animate({ //then we increase it's size while positioning it at the top left of the window
            'top': -($pos_top - $scroll) + 'px',
            'left': -$pos_left + 'px',
            'width': jQuery(window).width() + 'px',
            'height': window.innerHeight + 'px'
        });
        $ratio = 9 / 16;

        $window = jQuery(window);
        if (($window.height() / $window.width()) < $ratio) {
            $video_win.css({
                'width': ((($window.height() / $window.width()) / $ratio) * 100) + '%',
                'padding-bottom': (($window.height() / $window.width()) * 100) + '%'
            });
        }
    } else //close the lightbox
    {
        $button.html('&#xe140;');
        $jmayt_video_wrap.animate({
            'top': 0,
            'left': 0,
            'width': (jmayt_item_width) + 'px',
            'height': (jmayt_item_width) / 1.7778 + 'px'
        }, 300, 'swing', function() {
            $jmayt_video_wrap.removeClass('jmayt-fixed');
            $jmayt_video_wrap.css({
                'top': '',
                'left': '',
                'height': '',
                'width': '',
                'padding-bottom': ''
            });
            $z_index.css({
                'z-index': '',
                'overflow': ''
            });
            $video_win.css({
                'width': '',
                'padding-bottom': ''
            });
        });

        jQuery('body').css({
            'overflow-y': ''
        });
    }
    //for width change and orientation change on mobile
}

function hold_fixed() {
    //using the class that is added on show_lightbox
    jQuery('.jmayt-fixed').each(function() {
        $jmayt_video_wrap_el = jQuery(this);
        //distance the use has scrolled down the window (dynamic)
        $scroll = jQuery(document).scrollTop();
        $jmayt_item = $jmayt_video_wrap_el.closest('.jmayt-item');
        //x and y coordinates of the div (static)
        $pos = $jmayt_item.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $jmayt_video_wrap_el.css({
            'top': -($pos_top - $scroll) + 'px',
            'left': -$pos_left + 'px',
            'width': jQuery(window).width() + 'px',
            'height': window.innerHeight + 'px'
        });
        $ratio = 9 / 16;
        $video_win = $jmayt_video_wrap_el.find('.jma-responsive-wrap');
        $window = jQuery(window);
        if (($window.height() / $window.width()) < $ratio) { //for short window reduce wrap width
            $video_win.css({
                'width': ((($window.height() / $window.width()) / $ratio) * 100) + '%',
                'padding-bottom': (($window.height() / $window.width()) * 100) + '%'
            });
        }
    });
}

var tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);


var checkYT = setInterval(function() {
    if (typeof YT !== 'undefined') {
        jmayt_setup_onscreen();

        clearInterval(checkYT);
    }
}, 100);


/* load video when it scrolls into screen */

function JmaytUtils() {

}

JmaytUtils.prototype = {
    constructor: JmaytUtils,
    isElementInView: function(element, fullyInView) {
        var pageTop = jQuery(window).scrollTop();
        var pageBottom = pageTop + jQuery(window).height();
        var elementTop = jQuery(element).offset().top;
        var elementBottom = elementTop + jQuery(element).height();

        if (fullyInView === true) {
            return ((pageTop < elementTop) && (pageBottom > elementBottom));
        } else {
            return ((elementTop <= pageBottom) && (elementBottom >= pageTop));
        }
    }
};

var JmaytUtils = new JmaytUtils();

function jmayt_setup_onscreen() {
    jQuery('.jmayt-overlay-button').each(function() {
        $overlayButton = jQuery(this);
        if (JmaytUtils.isElementInView($overlayButton, false) && !$overlayButton.next().is('iframe')) {
            jmayt_setup_video($overlayButton);
        }
    });
}

function jmayt_setup_video($button) {
    // create the global player from the specific iframe (#video) jmayt-overlay-button
    $button_id = $button.data('embedid');
    $start = $button.data('start');
    if (typeof YT !== 'undefined') {
        window.YT.ready(function() {
            $player = new YT.Player('video' + $button_id, {
                videoId: $button_id,
                playerVars: {
                    rel: 0,
                    origin: document.domain,
                    enablejsapi: 1,
                    start: $start
                },
                events: {
                    // call this function when player is ready to use
                    'onReady': jmayt_onPlayerReady
                }
            });
        });
    }
}

function jmayt_onPlayerReady(event) {
    // bind events


    data = event.target.getVideoData();
    var $playButton = jQuery('#video' + data.video_id).parents('.jma-responsive-wrap').find('.jmayt-overlay-button');
    $playButton.addClass('jmayt-ready');
    $playButton.bind("click", function() {
        jQuery(this).css('display', 'none');
        event.target.playVideo();
    });

}


jQuery(window).on('scroll', function() {
    hold_fixed();
    jmayt_setup_onscreen();
    if (jQuery('body').hasClass('wp-admin')) {
        jmayt_title_resize();
    }
});

jQuery(document).ready(function() {
    jmayt_title_resize();

    jQuery(window).on('load', function() {
        jmayt_toggle();
        //only in the edit screen
        if (jQuery('body').hasClass('wp-admin')) {
            jmayt_title_resize();
        }
    });

    jQuery(window).on('resize', function() {
        hold_fixed();
        jmayt_title_resize();
    });
});