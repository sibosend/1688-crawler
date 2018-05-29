<?php

//小工具类函数
/*-----------------------------------------------------正则-----------------------------------------------------------*/
function fetchBetween($content,$start,$end)
{
    return fetchOnce($content,$start.'.*?'.$end);
}

function fetchTables($content)
{
    $reg_tables = '/<table.*?>.*?<\/table>/is';
    preg_match_all($reg_tables, $content, $tmpTables);
    return $tmpTables[0];
}

function fetchHTMLContent($content)
{
    return trim_nbsp(trim(strip_tags($content)));
}

function replaceString($original,array $replace_array)
{
    if(empty($replace_array))
        return $original;
    $reg_array = array();
    for($i=0;$i<count($replace_array);$i++)
    {
        $reg_array[] = '/<'.$i.'>/';
    }
    return preg_replace($reg_array,$replace_array,$original);
}

function eraseColon($source)
{
    $temp = preg_replace('/:/','',$source);
    return preg_replace('/：/','',$temp);
}

function erase($source,$part)
{
    $reg = '/'.$part.'/i';
    return preg_replace($reg,'',$source);
}

function fetchOnce($content,$reg,$type='is')
{
//    if(!is_string($reg) || !is_string($type))
//        return "";
    $reg = '/'.$reg.'/'.$type;
    preg_match($reg, $content, $tmpResults);
    $num = count($tmpResults);
    if($num == 0)       //没有匹配
        return "";
    elseif($num == 1)   //无分组
        return $tmpResults[0];
    elseif($num == 2)   //有一个分组
        return $tmpResults[1];
    else                //有多个分组
        return $tmpResults;
}

function fetchAll($content,$reg,$type='is')
{
    $reg = '/'.$reg.'/'.$type;
    preg_match_all($reg, $content, $tmpResults);
    $num = count($tmpResults);
    if($num == 0)       //没有匹配
        return array();
    elseif($num == 1)   //无分组
        return $tmpResults[0];
    elseif($num == 2)   //有一个分组
        return $tmpResults[1];
    else                //有多个分组
        return $tmpResults;
}

/** 拼接正则表达式字符串
 * @param string $html      单个HTML标签，如'div'
 * @param string $content   标签中的内容，如.*?
 * @param array $attr       标签内的属性，如arrary('id')
 * @param array $attrValue  各属性对应的值，如arrary('ID_1')，不用加双引号
 * @param int $type         1、<div></div>  2、<div/>或<div>且忽略$content参数  3、单引号
 * @return string           拼接成 '<div[^>]*id="ID_1"[^>]*>.*?<\/div>'
 */
function appendTagsWithAttr($html = 'div',$content = '.*?',$attr=array(),$attrValue=array(),$type = 1)
{
    if(!is_array($attr))
        $attr = array($attr);
    if(!is_array($attrValue))
        $attrValue = array($attrValue);

    $reg = '<'.$html.'[^>]*';
    for($i=0; $i<count($attr);$i++)
    {
        if($type === 3)
            $reg = $reg.$attr[$i]."='".$attrValue[$i]."'[^>]*";
        else
            $reg = $reg.$attr[$i].'[\s]*=[\s]*"'.$attrValue[$i].'"[^>]*';
    }
    if($type === 2)
        $reg = $reg.'\/?>';
    else
        $reg = $reg.'>'.$content.'<\/'.$html.'>';
    return $reg;
}

function appendTags($html,$attr=array(),$attrValue=array())
{
    if(!is_array($attr))
        $attr = array($attr);
    if(!is_array($attrValue))
        $attrValue = array($attrValue);
    return appendTagsWithAttr($html,'',$attr,$attrValue,2);
}

function appendRegType($reg,$type='is')
{
    return '/'.$reg.'/'.$type;
}

function appendUrlParams($url,$params)
{
    if( !empty(fetchOnce($url,'\w+=')) )
    {
        if(!str_end_with($url,'&'))
            $url .= '&';
        if(strpos($url,'?')===false)
            $url = '?'.$url;
    } else {
        $url = str_cut_tail($url,'/');
        if(!str_end_with($url,'?'))
            $url .= '?';
    }
    foreach($params as $key => $value)
    {
        $url .= $key.'='.$value.'&';
    }
    return str_cut_tail($url,'&');

}

/*----------------------------------------------------字符串----------------------------------------------------------*/
//判断字符串是否以$head开头
function str_start_with($str,$head)
{
    if( !is_string($str) || !is_string($head) )
        return false;
    if( strpos($str,$head) === 0 )
        return true;
    else
        return false;
}

function str_end_with($str,$tail)
{
    if( !is_string($str) || !is_string($tail) )
        return false;
    if( strrpos($str,$tail) === strlen($str)-strlen($tail) )
        return true;
    else
        return false;
}

//判断字符串是否以$head开头，若真则截去$head，返回剩余字符串
function str_cut_head($str,$head)
{
    if( !is_string($str) || !is_string($head) )
        return $str;
    $pos = strpos($str,$head);
    if( $pos !== 0 )
        return $str;
    return substr($str,strlen($head));
}

function str_cut_tail($str,$tail)
{
    if( !is_string($str) || !is_string($tail) )
        return $str;
    $pos = strrpos($str,$tail);
    if( $pos !== strlen($str)-strlen($tail) )
        return $str;
    return substr($str,0,-strlen($tail));
}

/*-----------------------------------------------------解析-----------------------------------------------------------*/

/** 解析日期字符串 只会解析出一个日期 有效年份为1970 到 2038
 * @param $dateString     格式为 20151009 或 2015年10月9日 或者分开的年月日
 * @return int  返回到1970年的毫秒数；1970年以前或2038以后的日期返回0；没有匹配到返回-1；可能返回empty，原因不明
 */
function parseDateToInt($dateString)
{
    preg_match('/(\d{4})-?(\d{2})-?(\d{2})/i', $dateString, $tempDates);
    if (!empty($tempDates))
        return mktime(0, 0, 0, $tempDates[2], $tempDates[3], $tempDates[1]);
    preg_match('/(\d{4})年/i', $dateString, $tempYears);
    preg_match('/(\d{1,2})月/i', $dateString, $tempMonths);
    preg_match('/(\d{1,2})日/i', $dateString, $tempDates);
    if (!empty($tempYears))
    {
        if(!empty($tempMonths))
        {
            if(!empty($tempDates))
                return mktime(0, 0, 0, $tempMonths[1], $tempDates[1], $tempYears[1]);
            else
                return mktime(0, 0, 0, $tempMonths[1], 1, $tempYears[1]);
        }
        return mktime(0, 0, 0, 1, 1, $tempYears[1]);
    }
    return -1;
}

/** 解析多个日期 格式：1、2015年(0)2月(0)3日 (15:10:00) 2、2015-(.\)12-10 (13:40:00) 或 20151210    3、160119-160125
 * @param $dateString
 * @param int $year_type    指定年份是四位数还是两位数(只接受4或2两个数字)
 * @return array
 * 注意 2015119会被解析为2015/11/9而不是2015/1/19，同理2015219解析为2015/21/9 注意来源数据的正确性
 */
function parseDate($dateString,$year_type = 4)
{
    //(?:(\d{4})[-|\.|年]?)?(\d{1,2})[-|\.|月]?(\d{1,2})[日]?(?:[\s]*(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?

    //(?:(\d{4})年)?(\d{1,2})月(\d{1,2})日(?:[\s]*(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?
    //(?:(\d{4})-)?(\d{1,2})-(\d{1,2})(?:[\s]*(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?
    //(?:(\d{4}).)?(\d{1,2}).(\d{1,2})(?:[\s]*(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?
    //(?:(\d{4})[-|\.]?)?(\d{1,2})[-|\.]?(\d{1,2})(?:[\s]*(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?
    if($year_type !== 2 && $year_type !== 4)
        $year_type = 4;
    $dateArray = array();
    //先检查 年月日 型 顺序不能错
    preg_match_all('/(?:(\d{'.$year_type.'})年)?(\d{1,2})月(\d{1,2})日(?:[\s]*(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?/i', $dateString, $tempDates);
    if(empty($tempDates[0]))
        preg_match_all('/(?:(\d{'.$year_type.'})[-|\.|\/]?)?(\d{1,2})[-|\.|\/]?(\d{1,2})(?:[\s]*(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?/i', $dateString, $tempDates);
    $tempYears = $tempDates[1];
    $tempMonths = $tempDates[2];
    $tempDays = $tempDates[3];
    $tempHours = $tempDates[4];
    $tempMinutes = $tempDates[5];
    $tempSeconds = $tempDates[6];
    if(!empty($tempYears))
    {
        for($i=0; $i<count($tempYears); $i++)
        {
            if(empty($tempHours[$i]))
                $tempHours[$i] = 0;
            if(empty($tempMinutes[$i]))
                $tempMinutes[$i] = 0;
            if(empty($tempSeconds[$i]))
                $tempSeconds[$i] = 0;
            if(empty($tempMonths[$i]))
                $tempMonths[$i] = 1;
            if(empty($tempDays[$i]))
                $tempDays[$i] = 1;
            if(empty($tempYears[$i]))
                $tempYears[$i] = date('Y',time());
            $dateArray[] = mktime($tempHours[$i], $tempMinutes[$i], $tempSeconds[$i], $tempMonths[$i], $tempDays[$i], $tempYears[$i]);
        }
    }
    return $dateArray;
    //    $dateArray = array();
//    preg_match_all('/(\d{4})-?(\d{2})-?(\d{2})/i', $dateString, $tempDates);
//    if (empty($tempDates[0]))
//        preg_match_all('/(\d{4})年[\s]*(\d{1,2})月[\s]*(\d{1,2})日/i', $dateString, $tempDates);
//    $tempYears = $tempDates[1];
//    $tempMonths = $tempDates[2];
//    $tempDays = $tempDates[3];
//    if(!empty($tempYears))
//    {
//        for($i=0; $i<count($tempYears); $i++)
//        {
//            $dateArray[] = mktime(0, 0, 0, $tempMonths[$i], $tempDays[$i], $tempYears[$i]);
//        }
//    }
}

/** 解析百分数，只返回%前面的数字，可能返回多个值
 * @param $percentString    整数位为 1-3位
 * @return array    返回数组
 */
function parsePercentage($percentString)
{
    $result = array();
    preg_match_all('/(\d{1,3}(?:\.\d+)?)[%|％]/i', $percentString, $tempPercents);
    if (!empty($tempPercents[0]))
    {
        $percents = $tempPercents[1];
        for($i=0; $i < count($percents); $i++)
        {
            $result[$i] = $percents[$i];
        }
        return $result;
    }

    $result = array();
    preg_match_all('/(\d{1,3}(?:\.\d+)?)[%|％]?/i', $percentString, $tempPercents);
    if (!empty($tempPercents[0]))
    {
        $percents = $tempPercents[1];
        for($i=0; $i < count($percents); $i++)
        {
            $result[$i] = $percents[$i];
        }
        return $result;
    }
    return $result;
}

function convertToDay($source)
{
    $temp = fetchOnce($source,'(\d*)天','i');
    if(!empty($temp))
        return $temp;
    $temp = fetchOnce($source,'(\d*)[个]?月','i');
    if(!empty($temp))
        return $temp*30;
    $temp = fetchOnce($source,'开放式|无期限','i');
    if(!empty($temp))
        return 0;
    return -1;
}

/** 解析请求Url的参数部分
 * @param string $url  https://s.1688.com/selloffer/offer_search.htm?keywords=%C1%AC%D2%C2%C8%B9&button_click=top&earseDirect=false&n=y
 * @return array    返回 一维数组 ('keywords'=>'%C1%AC%D2%C2%C8%B9','button_click'='top','earseDirect'='false','n'='y')
 */
function parseUrlParams($url)
{
    $url_params = fetchOnce($url,'\?(.*)');
    $result = array();
    if(empty($url_params))
        return $result;
    $params = fetchAll($url_params,'(\w+)=(.*?)&');
    if(!empty($params))
    {
        $keys = $params[1];
        $values = $params[2];
        for($i=0;$i<count($keys);$i++)
        {
            $result[trim($keys[$i])] = trim($values[$i]);
        }
        $url_params = fetchOnce($url_params,'(?:[\w]+=.*?&)+(.*)');
    }
    $param = fetchOnce($url_params,'(\w)+=(.*)');
    if(!empty($param))
    {
        $result[trim($param[1])] = trim($param[2]);
    }
    return $result;
}

/*----------------------------------------------------文件流----------------------------------------------------------*/
function saveToFile($content,$file_name='temp_file.txt')
{
    $out_file = fopen($file_name, "w") or die("Unable to open file!");
    fwrite($out_file,$content);
    fclose($out_file);
}

function readFromFile($file_name)
{
    $in_file = fopen($file_name, "r");
    if($in_file===false)
        return "";
    return fread($in_file, filesize ($file_name));
}

function deleteFiles($d,$ext)
{
    if($od=opendir($d))   //$d是目录名
    {
        while(($file=readdir($od))!==false)  // 读取目录内文件
        {
            if(get_extension($file) === $ext)
            {
                unlink($d.'/'.$file);  //$file是文件名
            }
        }
    }
}

function get_extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

/*-----------------------------------------------------数组-----------------------------------------------------------*/
// 用unset删除数组中值为空的元素，用is_empty判断是否为空
function unset_null_value($arr)
{
    if(!is_array($arr))
        return $arr;
    foreach($arr as $key => $item)
    {
        if(is_empty($item))
            unset($arr[$key]);
    }
    return $arr;
}

// 区分empty函数 ,此函数对 0  0.00 '0'也视做非空
function is_empty($var)
{
    if(is_numeric($var))
        return false;
    elseif(!empty($var))
        return false;
    else
        return true;
}

function is_empty_array($array)
{
    $empty_size = 0;
    foreach($array as $element)
    {
        if(is_empty($element))
            $empty_size++;
    }
    if($empty_size === count($array))
        return true;
    else
        return false;
}

/** (递归，深优先)计算一个数组的最大维度
 * @param $array            要检测的数组
 * @param int $max_dimen    上一层的最大维度，此值为递归调用时的中间值，不需要指定
 * @return int|string       本层最大维度
 */
function array_dimension($array,$max_dimen=0)
{
    if($max_dimen===0 || !is_numeric($max_dimen))      //首次调用
    {
        if(is_array($array))
            $max_dimen = 1;
        else
            return 0;
    }
    $origin_dimen = $max_dimen;     //记录这一层原来的维度
    foreach($array as $element)
    {
        if(is_array($element))
        {
            if($origin_dimen == $max_dimen)     //每一层，维度只能+1
            {
                $max_dimen++;
//                logUtil($max_dimen,'==before');
                $max_dimen = array_dimension($element,$max_dimen);
//                logUtil($max_dimen,'==after');
            }elseif($origin_dimen < $max_dimen){
//                logUtil($max_dimen,'<before');
                $last_dimen = array_dimension($element,$origin_dimen+1);    //既不是$max_dimen，也不是$origin_dimen，而是$origin_dimen+1
                if($last_dimen>$max_dimen)      //返回值比$max_dimen大再替换
                    $max_dimen = $last_dimen;
//                logUtil($max_dimen,'<after');
            }
        }
    }
    return $max_dimen;
}

/** (深优先)递归遍历数组，找出key对应的value。当key值有冲突时，返回优先遍历到的
 * @param string $key      要找的key，为数字时会有冲突
 * @param array $array    多维数组
 * @return null     返回key对应的value；不存在该key时返回null
 */
function array_find_recursive($key,$array)
{
    foreach($array as $K => $V)
    {
        if($key === $K)
            return $V;
        if(is_array($V))
        {
            $find = array_find_recursive($key,$V);
            if(!empty($find))
                return $find;
        }
    }
    return null;
}

/*-----------------------------------------------------其他-----------------------------------------------------------*/
function trim_nbsp($source)
{
    return preg_replace('/&nbsp;/','',$source);
}

function trim_amp($source)
{
    return preg_replace('/&amp;/','&',$source);
}

function trim_blank($source,$with="\n")
{
    $source = trim($source);
    return preg_replace('/[\s]+/',$with,$source);
}

function stripUnitName($source)
{
    $source = preg_replace("/元|欧元|美元|日元|韩元|澳元/","",$source);    //(\d*(?:万)?(?:欧|美|日|韩|澳)?元)
    if(!empty(fetchOnce($source,'万')))
    {
        $source = preg_replace("/万/","",$source);
        if(!is_numeric($source))
        {
            $strip = fetchOnce($source,'\d*\.\d+','i');
            if(!is_empty($strip))
                return floatval($strip)*10000;
            else{
                $strip = fetchOnce($source,'\d*','i');
                return floatval($strip)*10000;
            }
        }else{
            if(!empty(fetchOnce($source,'\.')))     //float
                return floatval($source)*10000;
            else        //int
                return intval($source)*10000;
        }
    }else{
        $strip = fetchOnce($source,'\d*(?:\.\d+)?','i');
        if(is_empty($strip))
            return $source;
        else
            return $strip;
    }
}

function stripSeparator($source)
{
    return preg_replace("/,/","",$source);
}

function func_during($func_name,$params)
{
    $start_time = microtime(true);
    call_user_func_array($func_name,$params);
    $end_time = microtime(true);
    logUtil($end_time-$start_time,'"'.$func_name.'" during');
}

function fuck_convert_encoding($message,$from_encoding='UTF-8')
{
    if(is_string($message))
        return mb_convert_encoding($message,"GBK",$from_encoding);
    if(is_array($message))
    {
        foreach($message as $K => $V)
        {
            if(is_string($V))
                $message[$K] = mb_convert_encoding($V,"GBK",$from_encoding);
        }
    }
    return $message;
}

function createRandStr($length)
{
    $returnStr='';
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    for($i = 0; $i < $length; $i ++) {
        $returnStr .= $pattern {mt_rand ( 0, 61 )};
    }
    return $returnStr;

}

function createHttpLink($url)
{
    if( strpos($url,'http:') !== false || strpos($url,'https:') !== false )
        return $url;
    if( strpos($url,'//') === 0 )
        return 'http:'.$url;
    elseif( strpos($url,'/') === 0 )
        return 'http:/'.$url;
    else
        return 'http://'.$url;
}

function createHttpsLink($url)
{
    if( strpos($url,'http:') !== false || strpos($url,'https:') !== false )
        return $url;
    if( strpos($url,'//') === 0 )
        return 'https:'.$url;
    elseif( strpos($url,'/') === 0 )
        return 'https:/'.$url;
    else
        return 'https://'.$url;
}

/*-----------------------------------------------------排序-----------------------------------------------------------*/
/*    对整个数组排序请使用sort_*() 对数组部分元素排序请使用 *_sort()    */
function sort_quick(&$array)
{
    quick_sort_optimized($array,0,count($array)-1);
}
function sort_insert(&$array)
{
    insert_sort($array,0,count($array)-1);
}
function sort_bubble(&$array)
{
    $n = count($array);
    for ($i = 0; $i < $n; $i++)
    {
        for ($j = 1; $j < $n - $i; $j++)
        {
            if ($array[$j - 1] > $array[$j])
            {
                $temp = $array[$j - 1];
                $array[$j - 1] = $array[$j];
                $array[$j] = $temp;
            }
        }
    }
}

//保证最大堆性质
function max_heapify(&$array,$x,$heap_size)
{
//    $l = $x<<1+1;
//    $r = $x<<1+2;
    $l = $x*2+1;
    $r = $x*2+2;
    $largest = $x;
    if($l<$heap_size && $array[$l]>$array[$x])
        $largest = $l;
    if($r<$heap_size && $array[$r]>$array[$largest])
        $largest = $r;
    if($largest != $x)
    {
        swap($array[$largest],$array[$x]);
        max_heapify($array,$largest,$heap_size);
    }
}

//function binsearch($k,$r,$a,$array)
//{
//    $j = ( log($k,2)-log($r,2)+1 )/2;
//    $m = $k/pow(2,$j);
//    if($a<$array[$m])
//        return binsearch($k/pow(2,$j+1),$r,$a,$array);
//    elseif($a>$array[$m])
//        return binsearch($k,$k/pow(2,$j-1),$a,$array);
//    else
//        return $m;
//}

//function search($a,$root,$bottom)
//{
////    var $height,$length,$current,$k;
//    $height=log($bottom)-log($root);
//    if($height==1)
//    {
//        $search=$root;$k=$root;
//        if($bottom>$k)
//        {
//            if($a[$k]<$a[$k+1])
//                $k++;
//            if($a[$bottom]<$a[$k])
//                $search=$k;
//        }
//    }else
//    {
//        $length=$height-log($height);
//        $height=$height-$length;
//        $current=$root;
//        for($k=1;$k<count($a);$k++)
//        {
////            $current=$current;
//            if($a[$current]<$a[$current+1])
//                $current++;
//            if($a[$bottom]<$a[$current]
//                $search=search($a,$current,$bottom);
////            else
////                $search=binary($root,$current,$bottom);
//        }
//    }
//}


//堆排序
function heap_sort(&$array)
{
    $heap_size = count($array);
    for($i=ceil((count($array)-1)/2);$i>=0;$i--)
    {
        max_heapify($array,$i,$heap_size);
    }
    for($i=count($array)-1;$i>0;$i--)
    {
        swap($array[$i],$array[0]);
        $heap_size--;
        max_heapify($array,0,$heap_size);
    }
}

//插入排序
function insert_sort(&$arr,$low,$high)
{
    if($low >= $high)
        return;
    //假定第一个元素被放到了正确的位置上
    for ($i = $low+1; $i < $high+1; $i++)
    {
        $j = $i;
        $target = $arr[$i];
        while ($j > $low && $target < $arr[$j - 1])
        {
            $arr[$j] = $arr[$j - 1];
            $j--;
        }
        $arr[$j] = $target;
    }
}

//没有优化的快排
function quick_sort(&$array,$l,$r)
{
    if($l >= $r)
        return;
    $i = $l;
    $j = $r;
    $x = $array[$l];
    while ($i < $j)
    {
        while($i < $j && $array[$j] >= $x) // 从右向左找第一个小于x的数
            $j--;
        if($i < $j)
            $array[$i++] = $array[$j];

        while($i < $j && $array[$i] < $x) // 从左向右找第一个大于等于x的数
            $i++;
        if($i < $j)
            $array[$j--] = $array[$i];
    }
    $array[$i] = $x;
    quick_sort($array, $l, $i - 1);
    quick_sort($array, $i + 1, $r);
}

//优化后的快排  使用三数取中 聚拢相等元素 和 混合排序 对快排进行优化
function quick_sort_optimized(&$arr,$low,$high)
{
    /*  $key 每次递归调用 用来比较的基准值
        $low 从左向右 $high 从右向左
        $first $low原值 $last $high原值 一次递归调用中不变
        $first到$left 与$key相等  $right到$last 与$key相等
        $leftLen $rightLen 左右与$key相等值的个数
    */
    if($low >= $high)
        return;
    $first = $low;
    $last = $high;

    $left = $low;
    $right = $high;

    $leftLen = 0;
    $rightLen = 0;

    if ($high - $low + 1 < 10)
    {
        insert_sort($arr,$low,$high);
        return;
    }

    //一次分割
    SelectPivotMedianOfThree($arr,$low,$high); //使用三数取中法选择枢轴
    $key = $arr[$low];

    while($low < $high)
    {
        while($low < $high && $arr[$high] >= $key)
        {
            if ($arr[$high] == $key)    //处理相等元素
            {
//                swap($arr[$right],$arr[$high]);
                $temp = $arr[$right];
                $arr[$right] = $arr[$high];
                $arr[$high] = $temp;
                $right--;
                $rightLen++;
            }
            $high--;
        }
        if($low < $high)
            $arr[$low++] = $arr[$high];
        while($low < $high && $arr[$low] <= $key)
        {
            if ($arr[$low] == $key)
            {
//                swap($arr[$left],$arr[$low]);
                $temp = $arr[$left];
                $arr[$left] = $arr[$low];
                $arr[$low] = $temp;
                $left++;
                $leftLen++;
            }
            $low++;
        }
        if($low < $high)
            $arr[$high--] = $arr[$low];
    }
    $arr[$low] = $key;

    //一次快排结束
    //把与枢轴key相同的元素移到枢轴最终位置周围
    $i = $low - 1;
    $j = $first;
    while($j < $left && $arr[$i] != $key)
    {
//        swap($arr[$i],$arr[$j]);
        $temp = $arr[$i];
        $arr[$i] = $arr[$j];
        $arr[$j] = $temp;
        $i--;
        $j++;
    }
    $i = $low + 1;
    $j = $last;
    while($j > $right && $arr[$i] != $key)
    {
//        swap($arr[$i],$arr[$j]);
        $temp = $arr[$i];
        $arr[$i] = $arr[$j];
        $arr[$j] = $temp;
        $i++;
        $j--;
    }
    quick_sort_optimized($arr,$first,$low - 1 - $leftLen);
    quick_sort_optimized($arr,$low + 1 + $rightLen,$last);
}

function swap(&$a,&$b)
{
    $temp = $a;
    $a = $b;
    $b = $temp;
}

function SelectPivotMedianOfThree(&$arr,$low,$high)
{
    //算法1
    $mid = $low + (($high - $low) >> 1);//计算数组中间的元素的下标
    //使用三数取中法选择枢轴
    if ($arr[$mid] > $arr[$high])//目标: arr[mid] <= arr[high]
    {
        swap($arr[$mid],$arr[$high]);
//        $temp = $arr[$mid];
//        $arr[$mid] = $arr[$high];
//        $arr[$high] = $temp;
    }
    if ($arr[$low] > $arr[$high])//目标: arr[low] <= arr[high]
    {
        swap($arr[$low],$arr[$high]);
//        $temp = $arr[$low];
//        $arr[$low] = $arr[$high];
//        $arr[$high] = $temp;
    }
    if ($arr[$mid] > $arr[$low]) //目标: arr[low] >= arr[mid]
    {
        swap($arr[$mid],$arr[$low]);
//        $temp = $arr[$mid];
//        $arr[$mid] = $arr[$low];
//        $arr[$low] = $temp;
    }
    //此时，arr[mid] <= arr[low] <= arr[high]
    //low的位置上保存这三个位置中间的值
    //分割时可以直接使用low位置的元素作为枢轴，而不用改变分割函数了

    //算法2
//    $mid = $low + (($high - $low) >> 1);
//    if($arr[$low]<$arr[$mid]){
//        $min = $low;
//        $max = $mid;
//    }else{
//        $max = $low;
//        $min = $mid;
//    }
//    if($arr[$min]>$arr[$high])
//        $middle = $min;
//    else
//        $middle=($arr[$max]<$arr[$high])?$max:$high;
//
//    if($middle != $low)
//    {
//        $temp=$arr[$middle];
//        $arr[$middle]=$arr[$low];
//        $arr[$low]=$temp;
//    }
}

function quick_sort_new(&$array,$left,$right)
{
    if(!is_array($array))
        return;
    if($left<$right)
    {
        $p = $left;
        $q = $right;
        $x = $array[$right];
        for($r = $p-1;$p<$q;$p++)
        {
            if($array[$p] <= $x)
            {
                $r++;
                $temp = $array[$p];
                $array[$p] = $array[$r];
                $array[$r] = $temp;
            }
        }
        $r++;
        $temp = $array[$q];
        $array[$q] = $array[$r];
        $array[$r] = $temp;
        quick_sort_new($array,$left,$r-1);
        quick_sort_new($array,$r+1,$right);
    }
}

/*-------------------------------------------------------IP地址--------------------------------------------------------*/
function is_ip_in_mask($client_ip,array $ip_list)
{
    if(empty($client_ip) || empty($ip_list))
        return false;
    $is_ip_in = false;
    $D=ip_binary($client_ip);    //客户端ip
    logUtil($D,'client_ip');
    foreach($ip_list as $ips)
    {
        if(!is_array($ips) || count($ips)!=2 )
        {
            echo 'error';
            continue;
        }
        $server_ip = $ips[0];       //地址段
        $mask_ip = $ips[1];         //子网掩码

        $M = ip_binary($mask_ip);
        $temp = $D & $M;
        $N = ip_binary($server_ip);
        if( $temp == $N ){
            $is_ip_in = true;
            break;
        }
    }
    return $is_ip_in;
}

function ip_binary($ip)
{
    $ip_array = explode(".",$ip);
    $t = '';
    foreach($ip_array as $v)
    {
        $tmp = decbin($v);
        $tmp_len = strlen(decbin($v));
        if($tmp_len < 8)
            $t .= str_repeat( "0",(8 - $tmp_len) ).$tmp;
        else
            $t .= $tmp;
    }
    return $t;
}

function is_ip_in_zone($client_ip,$ip_list)
{
    if(empty($client_ip) || empty($ip_list))
        return false;
    $is_ip_in = false;
    $ip_wanted = sprintf("%u",ip2long($client_ip));
    if(!$ip_wanted)
        return false;
    foreach($ip_list as $ips)
    {
        if(!is_array($ips) || count($ips)!=2 )
            continue;
        $ip_min = sprintf("%u",ip2long($ips[0]));
        $ip_max = sprintf("%u",ip2long($ips[1]));
        if(!$ip_min || !$ip_max)
            continue;

        if($ip_wanted >= $ip_min && $ip_wanted <= $ip_max)
        {
            $is_ip_in = true;
            break;
        }
    }
    return $is_ip_in;
}

//global => UTF-8
//function convert_encoding_from($source)
//{
//    global $encoding;
//    //    $from = mb_detect_encoding($source, array('ASCII','GB2312','GBK','UTF-8'));
//    if(!empty($encoding) && $encoding!='UTF-8')
//        return mb_convert_encoding($source,'UTF-8',$encoding);
//    return $source;
//}
//UTF-8 => global
//function convert_encoding_to($source)
//{
//    global $encoding;
//    //    $from = mb_detect_encoding($source, array('ASCII','GB2312','GBK','UTF-8'));
//    if(!empty($encoding) && $encoding!='UTF-8')
//        return mb_convert_encoding($source,$encoding,'UTF-8');
//    return $source;
//}