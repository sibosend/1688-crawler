<?php

$error_log = array();
//日志类函数
/** 输出 key = value 根据type加换行符
 * @param mixed $message      key
 * @param string $name  value
 * @param int $type     1：结尾加回车 2：两头都加回车 3：两头都不加回车
 */
function logUtil($message,$name='',$type=1)
{
    if(is_bool($message))
    {
        if($message)
            $message = 'TRUE';
        else
            $message = 'FALSE';
    }

    if($type === 2)
        br();
    $flag = true;
    if(!empty($name))
    {
        if(is_array($message))
        {
            if(is_array($name)){
                print_r($name);
                echo ' = ';
            }else{
                echo $name.' = ';
            }
            print_r($message);
            $flag = false;
        }elseif(is_array($name)){
            print_r($name);
            echo " = $message ";
        }else{
            echo "$name = $message ";
        }
    }else{
        if(is_array($message)){
            print_r($message);
            $flag = false;
        }else{
            echo $message;
        }
    }
    if($flag)
    {
        if($type === 1 || $type === 2)
            br();
    }
}

function log_util($message,$name='',$type=1)
{
    $message = fuck_convert_encoding($message);
    if($type === 2)
        br();
    $flag = true;
    if(!empty($name))
    {
        if(is_array($message))
        {
            if(is_array($name)){
                print_r($name);
                echo ' = ';
            }else{
                echo $name.' = ';
            }
            print_r($message);
            $flag = false;
        }elseif(is_array($name)){
            print_r($name);
            echo " = $message ";
        }else{
            echo "$name = $message ";
        }
    }else{
        if(is_array($message)){
            print_r($message);
            $flag = false;
        }else{
            echo $message;
        }
    }
    if($flag)
    {
        if($type === 1 || $type === 2)
            br();
    }
}

function log_array($array,$outer_flag = true)
{
    if(!is_array($array))
        return;

    foreach($array as $K => $V)
    {
        if($K == 'publish_start'||$K == 'publish_end'||$K == 'interest_start'||$K == 'interest_end')
            $array[$K] = date('Y/m/d H/i/s',$V);
        if($K == 'capital_guarantee')      //'非保本'=>'0','保证收益|保本'=>'1'
        {
            switch($V)
            {
                case '0':
                    $array[$K] = '非保本';
                    break;
                case '1':
                    $array[$K] = '保本';
                    break;
            }
        }
        if($K == 'interest_type')       //'保证收益'=>'301','浮动收益'=>'300'
        {
            switch($V)
            {
                case '301':
                    $array[$K] = '固定收益';
                    break;
                case '300':
                    $array[$K] = '浮动收益';
                    break;
            }
        }
        if($K == 'risk_level')      //'中等'=>'3','较低'=>'2','较高'=>'4','低'=>'1','高'=>'5'
        {
            switch($V)
            {
                case '1':
                    $array[$K] = '低风险';
                    break;
                case '2':
                    $array[$K] = '较低风险';
                    break;
                case '3':
                    $array[$K] = '中等风险';
                    break;
                case '4':
                    $array[$K] = '较高风险';
                    break;
                case '5':
                    $array[$K] = '高风险';
                    break;
            }
        }
        if($K == 'publish_status')       //'即将发行'=>'0','正在发行'=>'1','交易期'=>'2'
        {
            switch($V)
            {
                case '0':
                    $array[$K] = '预售';
                    break;
                case '1':
                    $array[$K] = '在售';
                    break;
                case '2':
                    $array[$K] = '停售';
                    break;
            }
        }
        if($K == 'currency')
        {
            $array[$K] = get_linkage($V);
        }
        if($K == 'region')
        {
            if(is_array($V))
            {
                foreach($V as $key => $value)
                {
                    $array[$K][$key] = get_linkage($value);
                }
            }else{
                $array[$K] = get_linkage($V);
            }
        }
//        if(is_array($V))
//            log_array($V,false);
//        else
        $array[$K] = fuck_convert_encoding($array[$K]);
    }
    if($outer_flag)
        print_r($array);
}

function log_partial_array($array,$name='',$range=0)
{
    if(is_array($array) && is_int($range) && $range>0 && $range<=count($array))
        array_splice($array,$range);
    logUtil($array,$name);
}

function br()
{
    echo "\n";
}
function logTableNotFound()
{
    echo "Wanted table NOT FOUND!\n";
}
function logWorthInfoNotFound()
{
    echo "Worth information NOT FOUND!\n";
}
function logProductInfoNotFound($type,$message='',$product_info = array())
{
    global $error_log;
    if($type === 1){
        echo "Product information NOT FOUND:BRIEF\n";
        $message = 'BRIEF';
    }elseif($type === 2){
        echo "Product information NOT FOUND:DETAILS\n";
        $message = 'DETAILS';
    }elseif($type === 3){
        if(!empty($message))
            echo "Product information NOT FOUND:$message\n";
    }elseif($type === 4){

    }
    if(!empty($product_info))
    {
        $product_info[] = $message;
        $error_log[] = $product_info;
    }
}
function logErrorInfo()
{
    global $error_log;
    if(empty($error_log))
        return;
    logUtil('ERROR LOG:');
    for($i=0;$i<count($error_log);$i++)
    {
        $error = '';
        for($j=0;$j<count($error_log[$i]);$j++)
        {
            $error = $error.$error_log[$i][$j].'   ';
        }
        logUtil($error);
    }
}
function logProcessWorthStart($productCode)
{
    echo "\nProcessing worth information of bank product: $productCode,\n";
}
function logProcessWorthFinish($affected,$total)
{
    echo "Processing finished.Worth information of $affected products has been updated(in all $total products).";
}
function logProcessProductStart($productCode)
{
    echo "\nProcessing bank product: $productCode,\n";
}
function logProcessProductFinish($productId)
{
    echo "Processing SUCCESS.ProductID=$productId\n";
}
function logProcessBankStart($message)
{
    echo "Processing bank: $message \n";
}
function logProcessBankFinish($affected)
{
    echo "\nProcessing finished.$affected products has been insert or updated.\n";
}

function checkUnavailableAttr($results,$checks)
{
    echo "Unavailable info: ";
    $total = 0;
    foreach($checks as $check)
    {
        if(!array_key_exists($check,$results))
        {
            echo "$check,";
            $total++;
        }
    }
    echo "  total=$total\n";
    return $total;
}

function checkAvailableAttr($results,$checks)
{
    echo "Available info: ";
    $total = 0;
    foreach($checks as $check)
    {
        if(array_key_exists($check,$results))
        {
            echo "$check,";
            $total++;
        }
    }
    echo "  total=$total\n";
    return $total;
}