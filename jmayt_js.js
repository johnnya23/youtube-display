function jmayt_title_resize() {
    jQuery('.jmayt-list-wrap').each(function() {
        //make all title boxes the same height as the largest boxes
        $title = jQuery(this);
        var $title_max = Math.max.apply(null, $title.find('h3').map(function() {
            return jQuery(this).outerHeight();
        }).get());
        $title.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });
}

function jmayt_toggle() {
    //create the toggle lightbox effect for the youtube items
    jQuery('.jmayt-video-wrap').click(function() {
        if (jQuery(this).hasClass('jmayt-fixed'))
            jmayt_hide_lightbox();
    }).children().click(function(e) {
        return false;
    });

    jQuery('.jmayt-btn').each(function() {
        jQuery(this).toggle(jmayt_show_lightbox, jmayt_hide_lightbox);
    });

    function jmayt_show_lightbox() {
        if (jQuery(this).is('button')) //keep $this if toggle is backwards
            $this = jQuery(this);
        $fixed = $this.parents('.jmayt-video-wrap');
        if (!$fixed.hasClass('jmayt-fixed')) { //make sure toggle is not backwards
            //distance the user has scrolled down the window (dynamic)
            $scroll = jQuery(document).scrollTop();
            //get rid of scroll
            $parent = $this.parents('.jmayt-item');
            $parent_width = $parent.innerWidth();
            $button = $this;
            $z_index = $fixed.parents('.jmayt-outer').parents().add($fixed);
            $parent.css('min-height', $parent.height() + 'px');
            $this.html('&#xe097;');
            //bring this section of the page to the top
            $z_index.css({
                'z-index': '2147483647',
                'overflow': 'visible'
            });
            jQuery('body').css({
                'overflow-y': 'hidden'
            });
            //first we make it absolute and give it a size
            $fixed.addClass('jmayt-fixed');
            //x and y coordinates of the div (static)
            $pos = $parent.offset();
            $pos_top = $pos.top;
            $pos_left = $pos.left;
            $fixed.css({
                'width': ($parent_width) + 'px',
                'height': ($parent_width) / 1.7778 + 'px',
                'padding-bottom': 0
            }).animate({ //then we increase it's size while positioning it at the top left of the window
                'top': -($pos_top - $scroll) + 'px',
                'left': -$pos_left + 'px',
                'width': jQuery(window).width() + 'px',
                'height': window.innerHeight + 'px'
            });
            $ratio = 9 / 16;
            $video_win = $this.parents('.jma-responsive-wrap');
            $window = jQuery(window);
            if (($window.height() / $window.width()) < $ratio) {
                $video_win.css({
                    'width': ((($window.height() / $window.width()) / $ratio) * 100) + '%',
                    'padding-bottom': (($window.height() / $window.width()) * 100) + '%'
                });
            }
        } else { //adjust if toggle is backwards
            jmayt_hide_lightbox()
        }
    }

    function jmayt_hide_lightbox() {
        if ($fixed.hasClass('jmayt-fixed')) { //adjust if toggle is backwards
            $this.html('&#xe140;');
            $fixed.animate({
                'top': 0,
                'left': 0,
                'width': ($parent_width) + 'px',
                'height': ($parent_width) / 1.7778 + 'px'
            }, 300, 'swing', function() {
                $fixed.removeClass('jmayt-fixed');
                $fixed.css({
                    'top': '',
                    'left': '',
                    'height': '',
                    'width': '',
                    'padding-bottom': ''
                });
                $parent.css('min-height', '');
                $z_index.css({
                    'z-index': '',
                    'overflow': ''
                });
            });
            $video_win.css({
                'width': '',
                'padding-bottom': ''
            });
            jQuery('body').css({
                'overflow-y': ''
            });
        } else {
            $this = jQuery(this); //redefine $this if toggle is backwards
            jmayt_show_lightbox()
        }
    }
    //for width change and orientation change on mobile
}

function hold_fixed() {
    //using the class that is added on show_lightbox
    jQuery('.jmayt-fixed').each(function() {
        $fixed_el = jQuery(this);
        //distance the use has scrolled down the window (dynamic)
        $scroll = jQuery(document).scrollTop();
        $parent = $fixed_el.closest('.jmayt-item');
        //x and y coordinates of the div (static)
        $pos = $parent.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $fixed_el.css({
            'top': -($pos_top - $scroll) + 'px',
            'left': -$pos_left + 'px',
            'width': jQuery(window).width() + 'px',
            'height': window.innerHeight + 'px'
        });
        $ratio = 9 / 16;
        $video_win = $fixed_el.find('.jma-responsive-wrap');
        $window = jQuery(window);
        if (($window.height() / $window.width()) < $ratio) { //for short window reduce wrap width
            $video_win.css({
                'width': ((($window.height() / $window.width()) / $ratio) * 100) + '%',
                'padding-bottom': (($window.height() / $window.width()) * 100) + '%'
            });
        }
    });
}
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


function onYouTubePlayerAPIReady() {
    jQuery('body').addClass('jmayt_loaded');
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
    $player = new YT.Player('video' + $button_id, {
        videoId: $button_id,
        playerVars: {
            rel: 0,
            enablejsapi: 1
        },
        events: {
            // call this function when player is ready to use
            'onReady': jmayt_onPlayerReady
        }
    });
}

function jmayt_onPlayerReady(event) {

    // bind events
    $iframe = event.target.a;
    var $playButton = jQuery($iframe).prev();
    $playButton.bind("click", function() {
        jQuery(this).css('display', 'none');
        event.target.playVideo();
    });

}


jQuery(window).scroll(function() {
    hold_fixed();
    if (jQuery('body').hasClass('jmayt_loaded'))
        onYouTubePlayerAPIReady()
});

jQuery(document).ready(function() {
    jmayt_title_resize();
    jmayt_toggle();

});

jQuery(window).load(function() {
    onYouTubePlayerAPIReady();

});

jQuery(window).resize(function() {
    hold_fixed();
    jmayt_title_resize();
});