<?php
class JMAYtList extends JMAYtVideo
{

    /*
     * function jma_get_yt_loop()
     * returns the youotube api array for a list
     * using $yt_list_id and  $this->api
     * */
    public function yt_loop($yt_list_id, $offset=0, $max=10000)
    {
        $return = array();
        $next = '';

        if ($offset) {
            $offsetarray = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&maxResults=' . $offset . '&playlistId=' . $yt_list_id . '&key=' . $this->api;
            $curl_array = JMAYtList::curl($offsetarray);
            $next =isset($curl_array['nextPageToken'])? '&pageToken=' . $curl_array['nextPageToken']:'';
        }
        do {
            $maxval = $max > 50? 50: $max;
            $max =$max - 50;
            $youtube_array = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=' . $maxval . $next . '&playlistId=' . $yt_list_id . '&key=' . $this->api;
            $curl_array = JMAYtList::curl($youtube_array);
            $next =isset($curl_array['nextPageToken'])? '&pageToken=' . $curl_array['nextPageToken']:'';
            if ($curl_array) {
                $return = array_merge($return, $curl_array['items']);
            }
        } while ($next && $max > 0);
        
        return $return;
    }

    /*
     * function markup() creates transient id, checks for transient - if needed and sets up the column div
     * and gets the video items array using JMAYtList::yt_loop($this->id) calls single_html() as it
     * loops through the $yt_loop_items
     *
     * @param array $res_cols will have indexes 'sm' 'lg' etc for break point
     * and values indicating the number of cols at each breakpoint (from 1 to 6)
     * @uses $this->id, $this->trans_atts_id, $this->col_space (from JMAYtVideo::process_display_atts()
     * @global $jmayt_options_array - for cache period
     * returns $return - video list html
     *
    * */
    public function markup($res_cols = array(), $offset, $max)
    {
        global $jmayt_options_array;
        $col_class = '';
        $trans_id = 'jmaytvideolist';
        foreach ($res_cols as $break => $res_col) {
            $trans_id .= $break . $res_col;
        }

        $trans_id .= $this->id . $this->trans_atts_id . $offset . $max;
        $return = get_transient($trans_id);
        if (false === $return || !$jmayt_options_array['cache']) {//if cache at 0
            $yt_api_array = JMAYtList::yt_loop($this->id, $offset, $max);

            //kick errors to error handler here
            if (is_string($yt_api_array)) {
                return JMAYtVideo::error_handler($yt_api_array);
            }

            $yt_loop_items = $yt_api_array;
            $count = count($yt_loop_items);
            $i = 0;
            if ($count > 0) {
                foreach ($res_cols as $break => $res_col) {
                    //if there are not enough items to fill the row we
                    //increase the size of the items
                    $res_col = $res_col < $count? $res_col: $count;
                    //invert columns for bootstrap grid values
                    if ($res_col != 5) {
                        $val = 12/$res_col;
                    } else {
                        $val = '020';
                    }

                    $col_class .= ' jmayt-col-' . $break . '-' . $val;
                }
                $return = '';
                foreach ($yt_loop_items as  $yt_loop_item) {
                    // add bootstrap column classses for each screen size
                    $br_cl = '';
                    foreach ($res_cols as $break => $res_col) {
                        //add clearing class for each screen size to left column items
                        if ((!($i % $res_col)) && ($res_col != 1)) {
                            $br_cl .= ' ' . "{$break}" . '-break';
                        }
                    }
                    $yt_snippet = $yt_loop_item['snippet'];
                    if ($yt_snippet['thumbnails']) {//only show videos with thumbnails in grid
                        $yt_id = $yt_snippet['resourceId']['videoId'];
                        $return .= '<div class="jmayt-outer jmayt-list-item ' . $col_class . $br_cl .'"' . $this->col_space . '>';
                        $return .= JMAYtList::single_html($yt_id, true);
                        $return .= '</div>';
                        $i++;
                    }
                }
                set_transient($trans_id, $return, $jmayt_options_array['cache']);
            }
        }
        return $return;
    }
}
