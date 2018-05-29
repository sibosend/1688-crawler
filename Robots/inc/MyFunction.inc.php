<?php

include_once ALIBB_PATH."Robots/inc/MyTools.php";
include_once ALIBB_PATH."Robots/inc/MyLog.php";

$checks_default = array('name','code','bank_id','portfolio_id','publish_status','status','type','interest_type','currency','min_invest',
    'step_invest','currency_supplement','min_invest_supplement','step_invest_supplement','expected_yield','max_yield','yield_to_maturity','capital_guarantee','pledge','publish_start','publish_end',
    'interest_start', 'interest_end','min_days_hold','fee','fee_extra','addtime','early_termination','early_redemption','region','subscription_rule',
    'investment_scope','interest_extra','risk_extra','risk_level','link');

/** 将一个json对象按规则解析成为insertValue 注:此函数一般不单独使用 请使用processTableForInsert和processJsonForInsert
 * @param string $jsonObject       要解析的json对象
 * @param $type             0;直接取值 1:原样不动(匹配多个key时可以叠加) 2:日期(可以对应多个列) 3:匹配正则返回单值('i') 4:匹配正则返回枚举值('i') 5:百分数(可以对应多个列) 6:钱(去掉千分符,万加0)
 *                           7:执行一个返回单值的函数(使用方法见下)      可以对应多列时，如果找到的数据个数(f)小于想要的数据列数(w)则只赋值前w-f列
 * @param string $colName          数据库列名
 * @param string $jsonKey          json key值
 * @param array $projections_item 解析规则
 * @param array $eachResult       返回结果
 */
function processForInsert($jsonObject,$type,$colName,$jsonKey,$projections_item,&$eachResult)
{
    //$jsonObject[$jsonKey]不存在 直接返回 $type=0除外
    if($type !== 0)
    {
        if(!array_key_exists($jsonKey,$jsonObject))
            return;
        if(is_empty($jsonObject[$jsonKey]))
            return;
    }
    //如果$eachResult[$colName]已经存在 直接返回 $type=1除外 这里如果是0的话认为是不存在
    if($type !== 1)
    {
        if(is_array($colName))
        {
            foreach($colName as $name)
            {
                if(array_key_exists($name,$eachResult) && !empty($eachResult[$name]))
                    return;
            }
        }else{
            if(array_key_exists($colName,$eachResult) && !empty($eachResult[$colName]))
                return;
        }
    }

    switch ($type)
    {
        case 0 :
            if(!is_empty($jsonKey))
                $eachResult[$colName] = $jsonKey;
            break;
        case 1 :
            if(array_key_exists($colName,$eachResult) && is_string($eachResult[$colName]))
                $eachResult[$colName] = $eachResult[$colName]."\n".$jsonObject[$jsonKey];
            else
                $eachResult[$colName] = $jsonObject[$jsonKey];
            break;
        case 2 :    //1970-2038之外的年份返回0
            if(count($projections_item) > 3)
            {
                if($projections_item[3] == 2 || $projections_item[3] == 4)      //两位年份或四位年份
                    $dateArray = parseDate($jsonObject[$jsonKey],$projections_item[3]);
                else
                    $dateArray = parseDate($jsonObject[$jsonKey]);
            }else{
                $dateArray = parseDate($jsonObject[$jsonKey]);
            }

            if(empty($dateArray))
                break;
            if(is_array($colName))
            {
                $count_wanted = count($colName);
                $count_found = count($dateArray);
                if($count_wanted >= $count_found)       //实际数据数量可能小于想要的数量；但如果大于则有可能出现匹配错误，舍弃
                {
                    for($i=0;$i<$count_found;$i++)
                    {
                        if(is_numeric($dateArray[$i]))
//                            $eachResult[$colName[$i]] = date('Y/m/d H/i/s',$dateArray[$i]);
                            $eachResult[$colName[$i]] = $dateArray[$i];
                    }
                }
            }else{
                if(is_numeric($dateArray[0]))
                    $eachResult[$colName] = $dateArray[0];
            }
            break;
        case 3 :
            if(count($projections_item) > 3)
            {
                $reg = $projections_item[3];
                $match = fetchOnce($jsonObject[$jsonKey],$reg,'i');
                if(!is_array($match) && !is_empty($match))
                    $eachResult[$colName] = $match;
            }
            break;
        case 4 :
            if(count($projections_item) > 3)
            {
                $enum = $projections_item[3];
                if(is_array($enum))
                {
                    foreach($enum as $reg => $V)
                    {
                        $match = fetchOnce($jsonObject[$jsonKey],$reg,'i');
                        if(!is_empty($match) && !is_empty($V))
                        {
                            $eachResult[$colName] = $V;
                            break;
                        }
                    }
                }
            }
            break;
        case 5 :
            $percents = parsePercentage($jsonObject[$jsonKey]);
            if(!empty($percents))
            {
                if(is_array($colName))
                {
                    $count_wanted = count($colName);
                    $count_found = count($percents);
                    if($count_wanted >= $count_found)       //实际数据数量可能小于想要的数量；但如果大于则有可能出现匹配错误，舍弃
                    {
                        for($i=0;$i<$count_found;$i++)
                        {
                            if(is_numeric($percents[$i]))
                                $eachResult[$colName[$i]] = $percents[$i];
                        }
                    }
                }else{
                    if(is_numeric($percents[0]))
                        $eachResult[$colName] = $percents[0];
                }
            }
            break;
        case 6 :
            $strip = stripUnitName(stripSeparator($jsonObject[$jsonKey]));
            if(is_numeric($strip))
                $eachResult[$colName] = $strip;
            break;
        case 7 :        //4列对应单形参函数；5列对应多形参
            if(count($projections_item) == 5)       //如果是5列 则第4列为函数名 第5列为参数列表 并把要解析的value添加到参数列表末尾
            {
                $func_name = $projections_item[3];
                $param = $projections_item[4];
                if(is_array($param))
                    $param[] = $jsonObject[$jsonKey];
                else
                    $param = array($param,$jsonObject[$jsonKey]);
                $eachResult[$colName] = call_user_func_array($func_name,$param);
            }elseif(count($projections_item) == 4){     //如果是4列 则第4列为函数名 把要解析的value作为参数
                $func_name = $projections_item[3];
                $eachResult[$colName] = call_user_func_array($func_name,array($jsonObject[$jsonKey]));
            }
            break;

    }
}

/** 解析二维表数据，返回insertValue名值对
 * @param array $keyArray       {0=>产品代码,1=>认购起始日期,2=>投资期限,3=>风险等级}
 * @param array $valueArray    {{0=>RTX0001,1=>2015-09-10,2=>50天,3=>低},
 *                               {0=>RTX0002,1=>2015-09-01,2=>300天,3=>中},
 *                               {0=>RTX0003,1=>2015-10-06,2=>100天,3=>高}}
 * @param array $projections   ( ('code','产品代码',1),
 *                                ('publish_start','认购起始日期',2),
 *                                ('min_days_hold','投资期限',3,'(\d*)天'),
 *                                ('risk_level','风险等级',4,array('低'=>'1','中'=>'2','高'=>'3')) )
 * @return array                ( 0=>( code=>RTX0001,publish_start=>1471039200,min_days_hold=>50,risk_level=>1 ),
 *                                 1=>( code=>RTX0002,publish_start=>1471125600,min_days_hold=>300,risk_level=>2 ),
 *                                 2=>( code=>RTX0003,publish_start=>1471212000,min_days_hold=>100,risk_level=>3 ) )
 *                              如果$valueArray是二维数组,返回的result也是二维数组；否则返回一维数组
 */
function processTableForInsert(array $keyArray,array $valueArray,array $projections)
{
    if(empty($projections) || empty($keyArray) || empty($valueArray))
        return $valueArray;
    $rand_key = array_rand($valueArray);
    if(is_array($valueArray[$rand_key]))    //$valueArray是二维数组,返回的result也是二维数组
    {
        $result = array();
        for($i=0; $i<count($projections); $i++)
        {
            $colName = $projections[$i][0];
            $keyReg = $projections[$i][1];
            $type = $projections[$i][2];

            //轮询$keyArray，如果$keyReg与某个key值匹配，则使用对应下标的value值执行processForInsert(可能与多个key值匹配，如果是字符串则拼接，而其他忽略)
            //如果对应的下标在value数组中不存在，则将key挖去匹配的字段，将剩下的值作为value
            //已经考虑到了$keyArray中数组下标不连续的情况，使用foreach
            foreach ($keyArray as $key_index => $key)
            {
                if($type !== 0)
                {
                    $match = fetchOnce($key,$keyReg,'i');
                    if(!empty($match))
                    {
                        for($k=0; $k<count($valueArray); $k++)      //$valueArray是二维数组
                        {
                            if(!array_key_exists($k,$result))
                                $result[$k] = array();
//                            if(!array_key_exists($key_index,$valueArray))
//                                $valueArray[$key_index] = $key;
                            processForInsert($valueArray[$k],$type,$colName,$key_index,$projections[$i],$result[$k]);
                        }
                    }
                }else{
                    for($k=0; $k<count($valueArray); $k++)
                    {
                        if(!array_key_exists($k,$result))
                            $result[$k] = array();
                        processForInsert($valueArray[$k],$type,$colName,$keyReg,$projections[$i],$result[$k]);
                    }
                }
            }

        }
        return $result;
    }else{
        $result = array();
        for($i=0; $i<count($projections); $i++)
        {
            $colName = $projections[$i][0];
            $keyReg = $projections[$i][1];
            $type = $projections[$i][2];

            //如果$keyReg与某个key值匹配，则使用对应下标的value值执行processForInsert
            foreach ($keyArray as $key_index => $key)
            {
                if($type !== 0)
                {
                    $match = fetchOnce($key,$keyReg,'i');
                    if(!empty($match))
                    {
                        if(!array_key_exists($key_index,$valueArray))
                            $valueArray[$key_index] = $key;
                        processForInsert($valueArray,$type,$colName,$key_index,$projections[$i],$result);
                    }
                }else{
                    processForInsert($valueArray,$type,$colName,$keyReg,$projections[$i],$result);
                }
            }
        }
//        logUtil($valueArray,'$valueArray');
        return $result;
    }
}

/** 解析json对象为InsertValue
 * @param $jsonObject   1、解析后的json对象("key"=>"value",key"=>"value",...) 2、数字下标的数组
 * @param array $projections
 * @return array
 */
function processJsonForInsert(array $jsonObject,array $projections)
{
    if(empty($projections))
        return $jsonObject;
    $eachResult = array();
    for($i=0; $i<count($projections); $i++)
    {
        $colName = $projections[$i][0];
        $jsonKey = $projections[$i][1];
        $type = $projections[$i][2];
        processForInsert($jsonObject,$type,$colName,$jsonKey,$projections[$i],$eachResult);
    }
    return $eachResult;
}

/** 解析json字符串 key和value可以没有双引号 key中不能有冒号 value中可以有冒号（未解决的问题：单引号）
 * @param $jsonString   1、{k:v,k:v,list1:[{},{},{},...],list2:[{},{},{},...]} 或 2、[{},{},{},...] 或 3、{k:v,k:v}
 * @return mixed    返回三维或二维数组，注意情况3也会返回二维数组只不过只有一行
 */
function fuck_json_decode($jsonString)
{
    //取出最两侧的{}之间的内容 排除 ({ k:v,k:v,list:[{},{},{},...] }) 外面有括号或其他字符的情况
    $jsonString = fetchOnce($jsonString,'\{.*\}');
    //但如果是2和3  需要再把[]加回来
    $jsonKVArrayTemp = fetchOnce($jsonString,'(\[[^\]]*\])');
    if(empty($jsonKVArrayTemp))
        $jsonString = '['.$jsonString.']';
    //value没有双引号的时候json_decode依然可以解析，但key没有双引号的时候json_decode是不能解析的，所以要将没有加双引号的key加上双引号
    //考虑到value中可能会有http://..的情况，所以使用([{|,])(\w+):而不是(\w+):
    //然而对于如{result:1,url:http://www.baidu.com,total:Page:页数}这种(value中有冒号又不加引号)丧心病狂的json
    //我们分2次replace搞定它 而这2次replace也能解决key和value有一个没加引号或都没加引号的情况
    if(preg_match('/\w:/', $jsonString))
    {
        //{result:1,url:http://www.baidu.com,total:Page:页数} => {"result":1,"url":http://www.baidu.com,"total":Page:页数}
        $jsonString = preg_replace('/([{|,])(\w+):/is', '$1"$2":', $jsonString);
        //{"result":1,"url":http://www.baidu.com,"total":Page:页数} => {"result":"1","url":"http://www.baidu.com","total":"Page:页数"}
        $jsonString = preg_replace('/":([^"]*?)([,|}])/is', '":"$1"$2', $jsonString);
    }
    //取出所有k:[{},{},{},...]
    $jsonKVArrayTemp = fetchAll($jsonString,'"(\w+)":(\[[^\]]*\])');
    //第2种或第3种 返回二维数组
    if(empty($jsonKVArrayTemp[0]))
    {
        //$jsonKVArrayTemp = [{},{},{},{}]
        $jsonKVArrayTemp = fetchOnce($jsonString,'(\[[^\]]*\])');
        return json_decode($jsonKVArrayTemp,TRUE);
    }else{
        //第1种 返回三维数组
        $jsonArrayKeys = $jsonKVArrayTemp[1];
        $jsonArrays = $jsonKVArrayTemp[2];
        $result_array = array();
        //轮询并解析每组k:[{},{},{},...]
        for($i=0; $i<count($jsonArrayKeys); $i++)
        {
            $result_array[$jsonArrayKeys[$i]] = json_decode($jsonArrays[$i],TRUE);
        }
        //去除所有k:[{},{},{},...] 解析剩余的json
        $jsonString = preg_replace('/,"\w+":\[[^\]]*\]/',"",$jsonString);
        $result_strip = json_decode($jsonString,TRUE);
        return array_merge($result_array,$result_strip);
    }
}

function processKVTable($table,$tr,$token = '：')
{
    $results = array();
    $results[0] = array();
    $results[1] = array();
    $tempResults = fetchAll($table,appendTagsWithAttr($tr,'(.*?)'.$token.'(.*?)'));
    if(!empty($tempResults[0]))
    {
        for($i=0;$i<count($tempResults[0]);$i++)
        {
            $results[0][$i] = $tempResults[1][$i];
            $results[1][$i] = fetchHTMLContent($tempResults[2][$i]);
        }
    }
    return $results;
}

/**
 * 1、抽取某一列数据中的HTML元素中的数据。例如抽取<a>中的herf中的某些数据
 * 2、剥离$array中所有数据的HTML元素
 * @param array $array   被抽取的二维数组
 * @param string $reg    使用正则表达式抽取HTML元素
 * @param int $col      抽取的是哪一列
 * @param bool|true $singleResult   返回结果中value值只包含一个
 * @return array        返回一维数组，$result[key]对应$array[key][$col]中的HTML元素
 */
function processHTML(&$array,$reg,$col,$singleResult = true)
{
    $result = array();
    if($col>=0)
    {
        for($i = 0; $i < count($array); $i++)
        {
            preg_match_all($reg, $array[$i][$col], $tmpHTML);
            $HTML = $tmpHTML[1];
            for($j = 0; $j < count($array[$i]); $j++)
            {
                $array[$i][$j] = trim_nbsp(trim(strip_tags($array[$i][$j])));
            }
            if(empty($HTML))
            {
                $result[$i] = '';
                continue;
            }
            if($singleResult)
                $result[$i] = $HTML[0];
            else
                $result[$i] = $HTML;
        }
    }
    return $result;
}

//见下process2DTableWithAttr
function fetchAttr($content,$tag)
{
    $reg = '<'.$tag.'(.*?)>(.*?)<\/'.$tag.'>';
    $tmpAlls = fetchOnce($content,$reg);
    $KVArray = array();
    if(!empty($tmpAlls[0]))
    {
        $all = $tmpAlls[1];
        $KVArray['value'] = fetchHTMLContent($tmpAlls[2]);
        preg_match_all('/([\w|-]+)="(.*?)"/is',$all,$tmpAttrs);

        if(!empty($tmpAttrs[0]))
        {
            for($i=0;$i<count($tmpAttrs[1]);$i++)
            {
                $KVArray[$tmpAttrs[1][$i]] = $tmpAttrs[2][$i];
            }
        }
    }
    return $KVArray;
}

/** 处理二维表，将td标签中的属性也作为每行的值，例如：($tr=li  $td=a)
 *  <li><a href="/mh/311605.html" title="3月的狮子(三月的狮子)第115话"><span class="red">第115话</span></a></li>
 *  返回一行的数据为('href'=>'/mh/311605.html','title'=>'3月的狮子(三月的狮子)第115话','value'=>'第115话')
 * @param string $table     要处理的二维表
 * @param string $tr        行的HTML标签
 * @param string $td        列的HTML标签
 * @param string $type      正则匹配模式
 * @return array            返回二维数组，注意每行只解析第一个td
 * 参见fetchAttr函数
 */
function process2DTableWithAttr($table,$tr,$td,$type='is')
{
    $reg_trs = appendRegType(appendTagsWithAttr($tr),$type);

    preg_match_all($reg_trs, $table, $tmpTrs);
    $trs = $tmpTrs[0];
    if(empty($trs))
        return array();

    $info = array();
    for($i = 0; $i < count($trs); $i++)
    {
        $info[] = fetchAttr($trs[$i],$td);
    }
    return $info;
}

function fetchAllAttr($content,$tag)
{
    $reg = '<'.$tag.'(.*?)>(.*?)<\/'.$tag.'>';
    $tmpAlls = fetchAll($content,$reg);
    $KVArray = array();
    if(!empty($tmpAlls[0]))
    {
        $allAttrs = $tmpAlls[1];
        $allValues = $tmpAlls[2];

        for($j=0;$j<count($allAttrs);$j++)
        {
            $KVArray[$j]['value'] = fetchHTMLContent($allValues[$j]);
            preg_match_all('/([\w|-]+)="(.*?)"/is',$allAttrs[$j],$tmpAttrs);
            if(!empty($tmpAttrs[0]))
            {
                for($i=0;$i<count($tmpAttrs[1]);$i++)
                {
                    $KVArray[$j][$tmpAttrs[1][$i]] = $tmpAttrs[2][$i];
                }
            }
        }
    }
    return $KVArray;
}

/**处理二维表中包括属性的所有信息 返回三维数组
 * <ul>
        <li class="fd-clr subnav_fzny" data-vm="subnav_content_fzny"><i class="iconfont"></i>
            <a href="//fuzhuang.1688.com/nvzhuang?spm=a260k.635.1998214976.1.Qgyx8Q" data-spm-anchor-id="a260k.635.1998214976.1">女装</a><span>/</span>
            <a href="//fuzhuang.1688.com/nanzhuang?spm=a260k.635.1998214976.2.Qgyx8Q" data-spm-anchor-id="a260k.635.1998214976.2">男装</a><span>/</span>
        </li>
        <li class="fd-clr subnav_xbps" data-vm="subnav_content_xbps"><i class="iconfont"></i>
            <a href="//fuzhuang.1688.com/xie?spm=a260k.635.1998214976.4.Qgyx8Q" data-spm-anchor-id="a260k.635.1998214976.4">鞋靴</a><span>/</span>
            <a href="//fuzhuang.1688.com/xiangbao?spm=a260k.635.1998214976.5.Qgyx8Q" data-spm-anchor-id="a260k.635.1998214976.5">箱包</a><span>/</span>
        </li>
    </ul>
 * 输出Array
(
    [0] => Array
    (
        [0] => Array
        (
            [value] => 女装
            [href] => //fuzhuang.1688.com/nvzhuang?spm=a260k.635.1998214976.1.Qgyx8Q
            [id] => a260k.635.1998214976.1
        )
        [1] => Array
        (
            [value] => 男装
            [href] => //fuzhuang.1688.com/nanzhuang?spm=a260k.635.1998214976.2.Qgyx8Q
            [id] => a260k.635.1998214976.2
        )
    )
    [1] => Array
    (
        [0] => Array
        (
            [value] => 鞋靴
            [href] => //fuzhuang.1688.com/xie?spm=a260k.635.1998214976.4.Qgyx8Q
            [id] => a260k.635.1998214976.4
        )
        [1] => Array
        (
            [value] => 箱包
            [href] => //fuzhuang.1688.com/xiangbao?spm=a260k.635.1998214976.5.Qgyx8Q
            [id] => a260k.635.1998214976.5
        )
    )
)
 * @param $table
 * @param $tr
 * @param $td
 * @param string $type
 * @return array
 */
function process3DTableWithAllAttr($table,$tr,$td,$type='is')
{
    $reg_trs = appendRegType(appendTagsWithAttr($tr),$type);

    preg_match_all($reg_trs, $table, $tmpTrs);
    $trs = $tmpTrs[0];
    if(empty($trs))
        return array();

    $info = array();
    for($i = 0; $i < count($trs); $i++)
    {
        $info[] = fetchAllAttr($trs[$i],$td);
    }
    return $info;
}

/** 处理一个二维表的数据,默认第一行或第一列为表头.
 * @param string $table        要处理的table数据
 * @param string $tr           行的HTML标签
 * @param string $td           列的HTML标签
 * @param string $type
 * @param bool|true $vertical   纵向表（true）；横向表（false）
 * @param string $remain        <td>中保留哪些HTML元素，例如<p><a>，保留p和a元素
 * @return array        返回一个二维数组，包含所有表中的数据
 */
function process2DTable($table,$tr,$td,$vertical = true,$type='is',$remain='')
{
    $reg_trs = appendRegType(appendTagsWithAttr($tr),$type);
    $reg_tds = appendRegType(appendTagsWithAttr($td),$type);

    preg_match_all($reg_trs, $table, $tmpTrs);
    $trs = $tmpTrs[0];
    if(empty($trs))
        return array();
    if($vertical)
    {
        $info = array();
        //处理表头 表头使用td或th
        $titleSize = 0;
        //处理每一行
        for($i = 0; $i < count($trs); $i++)
        {
            preg_match_all($reg_tds, $trs[$i], $tmpTds);
            $tds = $tmpTds[0];
            if($titleSize === 0)
                $titleSize = count($tds);
            if(count($tds) >= $titleSize)       //列数不够的行直接舍弃
            {
                for($j = 0; $j < $titleSize; $j++)
                {
                    $tds[$j] = trim_nbsp(trim(strip_tags($tds[$j],$remain)));
                }
                $info[$i] = $tds;
            }
        }
        return $info;
    }else{
        $info = array(array());
        //处理每一行
        preg_match_all($reg_tds, $trs[0], $tmpFirst);
        $first = $tmpFirst[0];
        $wantedSize = count($first);
        for($i = 0; $i < count($trs); $i++)
        {
            preg_match_all($reg_tds, $trs[$i], $tmpTds);
            $tds = $tmpTds[0];
            if($wantedSize===0)
                $wantedSize = count($tds);
            if(count($tds) == $wantedSize)
            {
                for ($j = 0; $j < $wantedSize; $j++)
                {
                    $tds[$j] = trim_nbsp(trim(strip_tags($tds[$j],$remain)));
                    $info[$j][$i] = $tds[$j];
                }
            }
        }
        return $info;
    }
}

/** 处理一个<table>的数据。可以处理列数不规则的表。注意返回的列号可能不连续
 * @param string $table                要处理的table数据
 * @param string $remain        <td>中保留哪些HTML元素，例如<p><a>，保留p和a元素
 * @param bool|true $vertical   纵向表（true），table第一行为表头；横向表（false），table第一列为表头
 * @return array                返回一个二维数组，包含所有表中的数据；纵向表第一行为表头；横向表一行表头一行值
 */
function processTable($table,$remain='',$vertical = true)
{
    $reg_trs = '/<tr.*?>.*?<\/tr>/is';
    $reg_tds = '/<t[d|h].*?>.*?<\/t[d|h]>/is';
    /*$reg_ths = '/<th.*?>.*?<\/th>/is';*/

    preg_match_all($reg_trs, $table, $tmpTrs);
    $trs = $tmpTrs[0];
    if(empty($trs))
        return array();
    if($vertical)
    {
        $info = array();
        //处理表头 表头使用td或th
//        preg_match_all($reg_tds, $trs[0], $tmpTitles);
//        $title = $tmpTitles[0];
////        $titleSize = count($title);
//        for($j = 0; $j < count($title); $j++)
//        {
//            $title[$j] = trim_nbsp(trim(strip_tags($title[$j])));
//        }
//        $info[0] = $title;
        //处理每一行
        for($i = 0; $i < count($trs); $i++)
        {
            preg_match_all($reg_tds, $trs[$i], $tmpTds);
            $tds = $tmpTds[0];
            for($j = 0; $j < count($tds); $j++)
            {
                $tds[$j] = trim_nbsp(trim(strip_tags($tds[$j],$remain)));
            }
            if(!is_empty_array($tds))
                $info[] = $tds;
        }
        return $info;
    }else{
        $info = array(array());
        //处理每一行
//        preg_match_all($reg_tds, $trs[0], $tmpFirst);
//        $first = $tmpFirst[0];
//        $wantedSize = count($first);
        for($i = 0; $i < count($trs); $i++)
        {
            preg_match_all($reg_tds, $trs[$i], $tmpTds);
            $tds = $tmpTds[0];
            for ($j = 0; $j < count($tds); $j++)
            {
                $tds[$j] = trim_nbsp(trim(strip_tags($tds[$j], $remain)));
                if(!is_empty($tds[$j]))
                    $info[$j][$i] = $tds[$j];       //行作为列，列作为行，列号可能不连续
            }
        }
        return $info;
    }
}
