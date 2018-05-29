<?php

//本地服务器
$mysqli = new MysqliDb (
    Array (
        'host' 			=> 'xxx',
        'username' 		=> 'xxx',
        'password' 		=> '1234',
        'db'			=> 'xxx',
        'charset' 		=> 'utf8'
    )
);

$mysqli->connect();

/*-----------------------------------------------insert or update-----------------------------------------------------*/
function insert_bank_products($insertValue)
{
    global $mysqli;
    $bank_product = $mysqli->rawQueryOne('select id from bank_products where code=? and bank_id=?', Array($insertValue['code'],$insertValue['bank_id']));
    if(empty($bank_product))
    {
        $region_array = array();
        if(array_key_exists('region',$insertValue))
        {
            if(is_array($insertValue['region']))
            {
                $region_array = $insertValue['region'];
                $insertValue['region'] = '0';
            }
        }
        $result = $mysqli->insert('bank_products',$insertValue);
        if($result)
        {
//            echo $mysqli->getLastQuery();
            if(!empty($region_array))
            {
                foreach($region_array as $region_id)
                {
                    $region_insertValue = array(
                        'product_id' => $result,
                        'linkage_id' => $region_id,
                    );
                    insert_bank_product_region($region_insertValue);
                }
            }
            return $result;
        }else{
            echo $mysqli->getLastError();
            return -1;
        }
    }else{
        if(array_key_exists('region',$insertValue))
        {
            if(is_array($insertValue['region']))
            {
                $region_array = $insertValue['region'];
                if(!empty($region_array))
                {
                    foreach($region_array as $region_id)
                    {
                        $region_insertValue = array(
                            'product_id' => $bank_product['id'],
                            'linkage_id' => $region_id,
                        );
                        insert_bank_product_region($region_insertValue);
                    }
                }
                $insertValue['region'] = '0';
            }
        }
        $mysqli->where('id', $bank_product['id']);
        $mysqli->update('bank_products',$insertValue);
        return $bank_product['id'];
    }
}

function insert_bank_product_region($insertValue)
{
    global $mysqli;
    if(!array_key_exists('product_id',$insertValue) || !array_key_exists('linkage_id',$insertValue))
        return -1;
    if(!is_numeric($insertValue['product_id']) || !is_numeric($insertValue['linkage_id']))
        return -1;
    if($insertValue['product_id'] <= 0 || $insertValue['linkage_id'] <= 0)
        return -1;
    $product_region = $mysqli->rawQueryOne('select id from bank_product_region where product_id=? and linkage_id=?', Array($insertValue['product_id'],$insertValue['linkage_id']));
    if(empty($product_region))
    {
        $result = $mysqli->insert('bank_product_region',$insertValue);
        if($result)
        {
//            echo $mysqli->getLastQuery();
            return $result;
        }else{
            echo $mysqli->getLastError();
            return -1;
        }
    }
}

function insert_bank_product_worth($insertValue)
{
    global $mysqli;
    $bank_product_worth = $mysqli->rawQueryOne('select id from bank_product_worth where prod_id=? and log_date_start=?', Array($insertValue['prod_id'],$insertValue['log_date_start']) );
    if(empty($bank_product_worth))
    {
        $result = $mysqli->insert('bank_product_worth',$insertValue);
        if($result)
        {
//            echo $mysqli->getLastQuery();
            return $result;
        }else {
            echo $mysqli->getLastError();
            return -1;
        }
    }else{
        $mysqli->where('id', $bank_product_worth['id']);
        $mysqli->update('bank_product_worth',$insertValue);
        return $bank_product_worth['id'];
    }
}

function insert_bank_portfolio($bank_id,$name,$code='')
{
    global $mysqli;
    if(!empty($code))
        $row = $mysqli->rawQueryOne('select * from bank_bank_portfolio p where bank_id=? and p.name=? and code=?', Array($bank_id,$name,$code));
    else
        $row = $mysqli->rawQueryOne('select * from bank_bank_portfolio p where bank_id=? and p.name=?', Array($bank_id,$name));
    if(empty($row))
    {
        $insertValue = array(
            'bank_id' => $bank_id,
            'name' => $name,
            'code' => $code,
        );
        $result = $mysqli->insert('bank_bank_portfolio',$insertValue);
        return $result;
    }else
        return $row['id'];
}

//function insert_bank_portfolio1($insertValue)
//{
//    global $mysqli;
//    if(array_key_exists('code',$insertValue))
//        $row = $mysqli->rawQueryOne('select * from bank_bank_portfolio p where bank_id=? and p.name=? and code=?', Array($insertValue['bank_id'],$insertValue['name'],$insertValue['code']));
//    else
//        $row = $mysqli->rawQueryOne('select * from bank_bank_portfolio p where bank_id=? and p.name=?', Array($insertValue['bank_id'],$insertValue['name']));
//    if(empty($row))
//    {
//        $result = $mysqli->insert('bank_bank_portfolio',$insertValue);
//        if($result)
//        {
////        echo $mysqli->getLastQuery();
//            return $result;
//        }else {
//            echo $mysqli->getLastError();
//            return -1;
//        }
//    }else{
//        return $row['id'];
//    }
//}

/*-----------------------------------------------------query-----------------------------------------------------------*/
function get_bank_portfolio($bank_id,$id)
{
    global $mysqli;
    return $mysqli->rawQueryOne('select * from bank_bank_portfolio where bank_id=? and id=?', Array($bank_id,$id));
}

function get_bank_products($bank_id)
{
    global $mysqli;
    return $mysqli->rawQuery('select id,p.name,code,portfolio_id,min_invest from bank_products p where bank_id=?', Array($bank_id));
}

function get_bank_product($bank_id,$product_code)
{
    global $mysqli;
    return $mysqli->rawQueryOne('select * from bank_products where bank_id=? and code=?', Array($bank_id,$product_code));
}

function get_linkage_currency()
{
    global $mysqli;
    $results = array();
    $rows = $mysqli->rawQuery('select * from licai_linkage where id>=1 and id<=100');
    foreach ($rows as $row)
    {
        $results[$row['name']] = $row['id'];
    }
    return $results;
}

function get_linkage_region1()
{
    global $mysqli;
    $results = array();
    $rows = $mysqli->rawQuery('select * from licai_linkage l where id=500');
    foreach ($rows as $row)
    {
        $results[$row['name']] = $row['id'];
    }
    return $results;
}

function get_linkage($id)
{
    global $mysqli;
    $region = $mysqli->rawQueryOne('select * from licai_linkage where id=?', Array($id));
    if(!empty($region))
        return $region['name'];
    else
        return $id;
}

function query_linkage_region($region_name,$default_id = 0)
{
    global $mysqli;
    if(empty($region_name))
        return $default_id;
    $region_name = '%'.$region_name.'%';
    $region = $mysqli->rawQueryOne('select * from licai_linkage where name like ?', Array($region_name));
    if(!empty($region))
        return $region['id'];
    else
        return $default_id;
}

function get_bank_id($bank_name,$default_id)
{
    global $mysqli;
    $bank = $mysqli->rawQueryOne('select id from bank_banks b where b.name=?',array($bank_name));
    if(empty($bank))
        $bank_id = $default_id;
    else
        $bank_id = $bank['id'];
    return $bank_id;
}

/*----------------------------------------------------update----------------------------------------------------------*/
//将id为$bank_id且不在$products_code列表里的银行产品的publish_status设置为停售
function update_bank_products_publish_status($bank_id,$products_code,$new_status=2)
{
    global $mysqli;
    $mysqli->where('bank_id', $bank_id);
    $mysqli->where('code',$products_code, 'NOT IN');
    $mysqli->update('bank_products',array('publish_status' => $new_status));
//    $query = $mysqli->getLastQuery();
//    logUtil($query,'update_bank_products_publish_status QUERY');
}