<?php

/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 * Remove all html tags except <div><p><ul><li><ol><span><a><img><table><thead><tbody><tr><th><td>
 * Strip all attributes except img tag's src.
 */
function strip_html_tags( $text )
{
    $text = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
            '@<form[^>]*?.*?</form>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $text );
    $text = strip_tags($text, '<div><p><ul><li><ol><span><img><table><thead><tbody><tr><th><td>');;

    $text = stripAttributes( $text, array("src") );

    $text = str_ireplace( array(
        "mailto:"
    ),
    array(
        ""
    ),
    $text );
    return $text;
}

/**
 * strip all attributes except ones you want
 * @param $s
 * @param array $allowedattr
 * @return mixed
 */
function stripAttributes($s, $allowedattr = array()) {
    if (preg_match_all("/<[^>]*\\s([^>]*)\\/*>/msiU", $s, $res, PREG_SET_ORDER)) {
        foreach ($res as $r) {
            $tag = $r[0];
            $attrs = array();
            preg_match_all("/\\s.*=(['\"]).*\\1/msiU", " " . $r[1], $split, PREG_SET_ORDER);
            foreach ($split as $spl) {
                $attrs[] = $spl[0];
            }
            $newattrs = array();
            foreach ($attrs as $a) {
                $tmp = explode("=", $a);
                if (trim($a) != "" && (!isset($tmp[1]) || (trim($tmp[0]) != "" && !in_array(strtolower(trim($tmp[0])), $allowedattr)))) {

                } else {
                    $newattrs[] = $a;
                }
            }
            $attrs = implode(" ", $newattrs);
            $rpl = str_replace($r[1], $attrs, $tag);
            $s = str_replace($tag, $rpl, $s);
        }
    }
    return $s;
}

/**
 * 创建图片保存目录
 * @param $path
 * @param $mode
 */
function RecursiveMkdir($path, $mode) {
    if( strlen( $path ) < 5 ){
        echo "wrong RecursiveMkdir path: $path \n";
        exit;
    }
    if (!file_exists($path)) {
        RecursiveMkdir(dirname($path), $mode);
        mkdir($path, $mode);
    }
}

/**
 * 下载图片
 *
 */
function download_image($image_url, $image_file, $referer){
    $fp = fopen ($image_file, 'w+');              // open file handle

    $ch = curl_init($image_url);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
    curl_setopt($ch, CURLOPT_FILE, $fp);          // output to file
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);      // some large value to allow curl to run for a long time
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1');
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    // curl_setopt($ch, CURLOPT_VERBOSE, true);   // Enable this line to see debug prints
    curl_exec($ch);

    curl_close($ch);                              // closing curl handle
    fclose($fp);                                  // closing file handle
}



function relative_to_absolute($subject_url,$replace_url) //$subject_url为要转换的页面（包含文件名）
{
    $urls = parse_url($subject_url);
    $pnum = substr_count($replace_url,'../');
    $isabs = strpos($replace_url,'://');
    if($isabs && $isabs<6)
        return $replace_url;
    else if(substr($replace_url,0,1) == '/')
        $replace_url = 'http://'.$urls['host'].$replace_url;
    else if(substr($replace_url,0,2) == './'){
        //$replace_url = dirname($subject_url).substr($replace_url,1);
        $replace_url = $subject_url.substr($replace_url,1);
    }else if($pnum>0)
    {
        for($i=0;$i<($pnum+1);$i++)
        {
            $subject_url = $subject_url;
        }
        $replace_url = str_replace('../','',$replace_url);
        $replace_url = $subject_url.'/'.$replace_url;
    }
    else
        $replace_url = $subject_url.'/'.$replace_url;
    return $replace_url;
}

/**
 * 相对标签 to 绝对标签
 * @param $domain
 * @param $content
 * @return mixed|string
 */
function AbsRelativeTags($domain, $content) {
    $patterns = array("/(<a\s+.*href=[\"|']?)([^>\"'\s]+?)(\s*[^>]*>)/iesU",
        "/(<img\s+.*src=[\"|']?)([^>\"'\s]+?)(\s*[^>]*>)/iesU");
    $replace = "'\$1'.relative_to_absolute('$domain','\$2').'\$3'";
    $new_code = preg_replace($patterns, $replace, $content);
    $new_code = stripslashes($new_code);
    return $new_code;
}