=== YouTube Playlists with Schema ===
Contributors: johnnya23
Tags: youtube, schema, youtube gallery, youtube playlist, youtube embed, youtube seo, youtube channel, responsive youtube
Requires at least: 4.0
Tested up to: 5.0.2
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Blocks or shortcode for embed of responsive single videos and grids from YouTube video playlists, which include schema.org markup as recommended by google.

== Description ==

Creates styled grids in WordPress pages/post/sidebars from YouTube Playlists. Also, displays single YouTube videos from video id. All videos displayed through plugin block or shortcode (grids and single) include schema.org metadata markup as recommended by google.

Requires YouTube Data API key &#8211; Instructions for getting the api can be found in the installation tab.
Block display for WordPress version 5+

Shortcode examples:
**single videos**
[yt_video_wrap width=&#8221;100%&#8221; alignment=&#8221;none&#8221;]**url here**[/yt_video_wrap]
[yt_video video_id=&#8221;your_yt_video_id&#8221; width=&#8221;100%&#8221; alignment=&#8221;none&#8221;]
notice the optional width and alignment attributes above &#8211; just used to make simple positioning easier.
**lists**
[yt_grid yt_list_id=&#8221;your_yt_list_id&#8221;]
Display attributes can be used to overwrite the plugin settings on a list by list basis (^indicates can be applied to grids only).
item_font_color &#8211; the color of the h3 title
item_font_size &#8211; title size in px
item_font_alignment &#8211; left, right, center
item_font_length &#8211; number of characters from title to display (will round down to nearest word)
item_bg &#8211; background color
item_border &#8211; border color
^query_max &#8211; maximum number of videos to display
^query_offset &#8211; offset query at beginning by this value
^item_gutter &#8211; horizontal spacing (even # between 0 and 30 recommended)
^item_spacing &#8211; vertical spacing
button_font &#8211; for expansion buttons on upper left of items (the arrow color)
button_bg &#8211; for expansion buttons on upper left of items (bg)
^Columns &#8211; if you use and column attribute in the shortcode ALL plugin settings will be ignored and just shortcode values will be used. This was done on the assumption that if you want to take control of the column display of a particular list you should take complete control.
lg_cols &#8211; 1200+
md_cols &#8211; 992+
sm_cols &#8211; 768+
xs_cols &#8211; 768-
additionally the id, class and style attributes are also available and will have the same syntax and behavior as in html.

== Installation ==


1.Upload <code>youtube-playlists-with-schema</code> folder to the <code>/wp-content/plugins/</code> directory
1.Activate the plugin through the &#8216;Plugins&#8217; menu in WordPress

**The YouTube data api key**
[Go Here for more detailed instructions](https://cleansupersites.com/jma-youtube-playlists-with-schema/)
This may be the most complicated part of the whole process.
FIRST log into the google account you plan to use for the api key. You can use any account for this it doesn&#8217;t have to be the account where the videos are housed. Once you have the api key you can use it to get any public YouTube video or list.

ONCE YOU ARE LOGGED IN [click here](https://console.developers.google.com/projectselector/apis/dashboard?organizationId=0)
You should see (or maybe with an additional &#8216;select&#8217; box):
click &#8216;create&#8217;
![slide1](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide1.jpg)

You should see this screen &#8211; name your project and click &#8216;create&#8217;
![slide2](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide2.jpg)

Now you see this &#8211; notice your project name at the top and you have no api&#8217;s enabled. we need to enable one from the &#8216;Library&#8217; so click on &#8216;Library&#8217;
![slide3](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide3.jpg)

From the Library we just need a simple &#8216;YouTube Data API&#8217; &#8211; so click on that item
![slide4](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide4.jpg)

You get some information about the api, but the objective here is to enable the api by clicking &#8216;ENABLE&#8217;. You should see a little working icon and enable should switch to disable.
![slide5](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide5.jpg)

As Google suggests we are going to click &#8216;create credentials&#8217; next
![slide6](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide6.jpg)

Here we select &#8216;website (javascript)&#8217; from the second dropdown and the &#8216;Public data&#8217; radio button then hit the big blue &#8216;what credentials..&#8217; button (SPOILER ALERT &#8211; you need a simple api key, in case you are not seeing this exact sequence)
![slide7](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide7.jpg)

We may want to wait until we go live to restrict the key. Key restrictions are more about resource allocation than security. Like I said, you can use any key to get the information we are using for this. If someone where to use your key for another purpose (which could only be to get this already public data) it would only impact the number of video data calls you can make with the key.

In any case you can click &#8216;Credentials&#8217; in the sidebar to see your key. Use the pencil if you want to edit the name and /or restrict the key.
![slide8](http://cleansupersites.com/cleansupersites/wp-content/uploads/2017/05/slide8.jpg)

Note &#8211; You can use this &#8216;project&#8217; on other sites. Just come back to this page (you may want to bookmark) make sure you are in the correct project go directly to credentials and create another api key by clicking the big &#8216;create credentials &#8216; button and choosing &#8216;api key&#8217; (top option). The new key will do the same thing and have a separate usage allocation.

== Frequently Asked Questions ==

= Does this update support blocks? =

Yes, in the common category "List YouTube Responsive Videos" or "Single YouTube Responsive Video"

= What schema values does the plugin provide? =

meta itemprop="name" content=""
meta itemprop="publisher" content=""
meta itemprop="description" content=""
meta itemprop="thumbnailUrl" content=""
meta itemprop="embedURL" content=""
meta itemprop="uploadDate" content=""
meta itemprop="interactionCount" content=""
meta itemprop="duration" content=""
With content values pulled from the YouTube API.

= My lists and videos are showing, but the display is off. What gives? =

The plugin detects the existence of it&#8217;s shortcode in the page (post) content. If you or your theme are adding the shortcode to a meta box, the plugin will not detect the shortcode and will not call the css and jQuery necessary for proper display. You can overcome this by selecting the "Universal" option in the plugin settings.
`

= How can I improve the list display when using shortcode within tabs and accordions? =

Javascript is used to determine the height of the title boxes so that all boxes are the same height. If a tab/accordion is closed on page load the a height of zero will be assigned to tiles in lists. To overcome this we need to call the javascript after the tab/accordion is clicked. Like this:
`
function jmayt_footer(){ ?&gt;
    &lt;script type="text/javascript"&gt;
    jQuery('.jma-tabbed').find('li').click(function(){//.jam-tabbed and li will have to be changes based on your markup
        setTimeout(function() {
            jmayt_title_resize();
        }, 200);
    });
    &lt;/script&gt;

&lt;?php }


function jma_template_redirect(){
    if(is_page(array(123, 1866, 321) || is_single(array(987, 456)))//optional page number to apply the function to
        add_action('wp_footer', 'jmayt_footer');
}
add_action('template_redirect', 'jma_template_redirect');
`

= Will the shortcode work on ajax generated pages? =

It should. You need to add:
`
jmayt_toggle();jmayt_title_resize();onYouTubePlayerAPIReady();
`
to the end of your success() function

== Screenshots ==

1. The plugin settings page
2. Video id - use the value after v=
3. List id - use the value after list=
4. The grid
5. Single video (aligned right at 50%)
6. Shortcut keys can be found in the text editor only

== Changelog ==

= 1.0 =
* Original

= 1.0.1 =
* Add universalize script option

= 1.1 =
* Sperctrum color picker replaces farbtastic
https://bgrins.github.io/spectrum/

= 1.2 =
* add overlays
* add error handling

= 1.2.1 =
* overlay fixes

= 1.2.2 =
* overlay fixes

= 1.2.3 =
* overlay fixes

= 1.2.4 =
* overlay fixes

= 1.3 =
* add query_offset and query_max values
* allow 50+ results to be returned

= 1.3.1 =
* fix array issues in settings class
* delay play icons until videos are ready on cached images setup

= 1.3.2 =
* add publisher and logo and interaction count and duration to schema

= 2.0 =
* improve Schema
* add content blocks for single videos and lists
* replace colorpicker
* display single videos with overlays
* reorganize files
