<?php

define("ALIBB_PATH", dirname(__FILE__)."/../../");
require ALIBB_PATH.'vendor/autoload.php';
include_once '../inc/global.php';
include_once ALIBB_PATH . "Robots/inc/MyDAO.php";

use JonnyW\PhantomJs\Client;
use JonnyW\PhantomJs\Http\Request;


class CustomRequest extends Request
{
    protected $selector;

    public function getPage()
    {
        return $this->selector;
    }

    public function setPage($selector)
    {
        $this->selector = $selector;
    }
}


$domainMappings = array(
    'domain_fake' => 'https://www.1688.com/',
    'domain' => 'https://s.1688.com/selloffer/offer_search.htm?spm=a2604.8117111.iq3gamj1.1.<0>&keywords=%C5%AE%CA%BDT%D0%F4&button_click=top&earseDirect=false&n=y',
    'domain_ajax' => 'https://s.1688.com/selloffer/rpc_async_render.jsonp',
);

$projections_brief = array(
    array('contacts','联系人',1),
    array('phone','电话',3,'([\s\d]+)'),
    array('mobile_phone','移动电话',3,'([\s\d]+)'),
    array('fax','传真',3,'([\s\d]+)'),
    array('address','地址',1),
    array('postcode','邮编',3,'([\s\d]+)'),
    array('website','公司主页|旺铺主页',1),
);

$confusions = array('/page/offerlist.htm','/page/creditdetail.htm','/page/albumlist.htm','/page/merchants.htm');

$server_ip = gethostname();
//logUtil($server_ip,'$server_ip');

main_process();
function main_process()
{
    global $domainMappings, $server_ip;

    $cache_dir = ALIBB_PATH.'/cache';
    //win
    $exe_dir = ALIBB_PATH . "/bin/phantomjs.exe";
    //linux
//    $exe_dir = ALIBB_PATH . "/bin/phantomjs";

    $client = Client::getInstance();

    $client->getEngine()->setPath($exe_dir);

    $client->getEngine()->addOption('--cookies-file=cookie.txt');
    $client->getEngine()->addOption('--local-storage-path='.$cache_dir);
//    $client->getEngine()->addOption('--local-storage-quota=10');
//    $client->getEngine()->addOption('--offline-storage-quota=10');
    //1688��ҳ

    $content = phantom_simple_request($client,$domainMappings['domain_fake']);

    $index_nav_content = fetchOnce( $content,appendTagsWithAttr('ul','.*?',array('id'),array('nav-sub')) );
    $index_nav = process3DTableWithAllAttr($index_nav_content,'li','a');

    for($p=0;$p<count($index_nav);$p++)
    {
        for($q=0;$q<count($index_nav[$p]);$q++)
        {
            //ĳ�г�
            $market_name = $index_nav[$p][$q]['value'];
            $market_page = createHttpsLink($index_nav[$p][$q]['href']);
            $content = phantom_simple_request($client,$market_page);
            logUtil(createHttpsLink($index_nav[$p][$q]['href']),'$market_page');

            $content = fetchOnce( $content,appendTagsWithAttr('div','.*',array('class'),array('ch-menu-body')) );

            $carts = fetchAll( $content,appendTagsWithAttr('ul','.*?',array('class'),array('fd-clr')) );
            foreach($carts as $cart)
            {
                $cart_types = process2DTableWithAttr($cart,'li','a');
                foreach($cart_types as $item)
                {
                    if( !array_key_exists('href',$item) || empty($item['href']))
                        continue;
                    //��������ҳ
                    $exception_time = 0;
                    $search_page = $item['href'];
                    $market_nav = $item['value'];
                    $search_page = trim_amp($search_page);
                    logUtil('search_page='.$search_page.'  time='.get_current_time());
//                    $search_page = 'https://s.1688.com/selloffer/offer_search.htm?keywords=%C1%AC%D2%C2%C8%B9&button_click=top&earseDirect=false&n=y';

                    //��������ҳ ÿҳ from 1 to 100
                    for($j=1;$j<=100;$j++)
                    {
                        //����ҳ�Ƿ���ȡ��
                        $posValue = array(
                            'index_nav'         => $market_name,
                            'index_nav_link'    => $market_page,
                            'market_nav'        => $market_nav,
                            'market_nav_link'   => $search_page,
                            'search_page_index' => $j,
                        );
                        $result = check_position($posValue);
                        if($result===false)
                        {
//                            logUtil('skip page '.$j,'',3);
                            continue;
                        }
                        if($result===-1)
                        {
                            $posValue['status'] = 0;
                            $posValue['distribute_ip'] = $server_ip;
                            $insert_id = insert_curl_pos($posValue);
                        } else {
                            $insert_id = $result;
                        }

                        //��ʼ��ȡ
                        logUtil('processing page '.$j.'  time='.get_current_time(), '', 2 );
                        if($j==1)
                        {
                            $content = phantom_simple_request($client,$search_page);
                        } else {
                            $content = phantom_custom_request($client,$search_page,'http_click',$j);
                        }

                        if($content==='Exception')
                        {
                            $exception_time++;
                            if($exception_time>1)
                            {
                                $exception_time = 0;
                                logUtil('sleep for 10 min');
                                sleep(10*60);
                            }
                            continue;
                        }


                        $reg = appendTags('ul','id','sm-offer-list').'.*?'.appendTags('div','id','sm-maindata-script');
                        $content_1 = fetchOnce( $content, $reg );

                        if(empty($content_1)) {
                            $content_1 = fetchOnce( $content,appendTags('ul','id','sm-offer-list').'.*?'.appendTags('div','class','s-module-individuation.*?') );
                        }

                        if(empty($content_1))
                        {
                            logUtil(' $content_1 null!!!! ');
                            $exception_time++;
                            if($exception_time>2)
                            {
                                $exception_time = 0;
                                logUtil('sleep for 10 min');
                                sleep(10*60);
                            }
                        }


                        $result = process_page($client,$content_1);

                        //��ȡ���
                        update_curl_pos($insert_id,$result);
                    }
                    //�������
                    deleteFiles($cache_dir,'localstorage');
                    sleep(5);
                }
                sleep(5);
            }
            //�г�����
        }
    }

}

function get_current_time()
{
    return date ( "Y-m-d H:i:s" );
}

function is_redirect($content)
{
    $content1 = fetchOnce($content,'<div class="w952" id="masthead-v4">');
    $content2 = fetchOnce($content,'<div id="loginchina-wrapper" class="signin">');
    if( !empty($content1)
        ||  !empty($content2) )
        return true;
    else
        return false;
}

function process_page(Client $client,$content_1)
{
    global $projections_brief,$confusions;

    if(empty($content_1))
        return "0/0";

    $rows = fetchAll( $content_1,appendTagsWithAttr('li','.*?','t-rank','.*?') );

    $curl_count = 0;
    $item_num = count($rows);
//    logUtil($item_num,'count');
    $real_request = 0;
    for($i=0;$i<$item_num;$i++)
    {
//        if($real_request==5)
//        {
//            logUtil('clear cookies.');
//            saveToFile('','cookie.txt');
//            $real_request = 0;
//        }
//        else

        $reg = appendTagsWithAttr('a','.*?',array('class'),array('sm-offer-companyName.*?'));
        $linkHtml = fetchOnce( $rows[$i], $reg);
        $attrs = fetchAttr($linkHtml,'a');
        if( !array_key_exists('title',$attrs) || !array_key_exists('href',$attrs) || empty($attrs['title']) || empty($attrs['href']) )
            continue;
        if( is_company_exist($attrs['title']) )
        {
//            logUtil("company exists. $i/$item_num");
            continue;
        }

//        $real_request++;

        sleep(5);

        logUtil("processing company page $i/$item_num");
        $company_page = $attrs['href'];

        //��˾��ҳ
        $content = phantom_simple_request($client,$company_page);

        $contact_desc_1 = fetchOnce( $content,'<h3>��ϵ��ʽ<\/h3>.*?'.appendTags('div','class','m-footer') );

        sleep(3);

        //��˾��ϵ��ʽҳ
        if($i%3==2)
        {
            $company_page = createHttpsLink(parse_url($company_page)['host']).$confusions[rand(0,3)];
            $content = phantom_simple_request($client,$company_page);
            logUtil("confusion: $company_page");
            continue;
        } else {
            $company_page = createHttpsLink(parse_url($company_page)['host']).'/page/contactinfo.htm';
            $content = phantom_simple_request($client,$company_page);
            logUtil("processing: $company_page");
        }

//        $start_time = time();
        $credit_point = fetchOnce( $content,appendTagsWithAttr('span','(\d)+','class','tp-year') );

        $contact_desc = fetchOnce( $content,appendTags('div','class','contcat-desc').'.*?'.appendTags('div','class','map-container') );

        $contact_info = fetchOnce( $content,appendTagsWithAttr('div','.*?','class','contact-info') );
        $contact_temp = fetchOnce( $contact_info,appendTagsWithAttr('dl') );
        $contact = fetchOnce( $contact_temp,appendTagsWithAttr('a','(.*?)','class','membername').'(.*?)<a' );
        if( !empty($contact) && count($contact)==3 )
        {
            $contact_name = trim_nbsp(trim($contact[1])).trim_nbsp(trim($contact[2]));
            $contact_name = str_cut_tail($contact_name,'������ϵ');
            $contact_name = str_cut_tail($contact_name,'��������');
            $contact_name = trim($contact_name);
        }

        $company_detail = process2DTable($contact_desc,'dl','d[t,d]',false);
        if( empty($company_detail) || count($company_detail)<2 )
        {
            $msg = 'NULL INFO';
            if(is_redirect($content))
            {
                $msg .= ' redirect';
                logUtil($company_page,$msg);
                logUtil('sleep for 10 min');
                sleep(10*60);
            } else {
                logUtil($company_page,$msg);
            }
            continue;
        }

        $insert_detail = processTableForInsert($company_detail[0],$company_detail[1],$projections_brief);
        $insert_detail['company_name'] = $attrs['title'];

        //���ż���
        if(!empty($credit_point))
        {
            $insert_detail['credit'] = intval($credit_point);
        }

        //��ϵ��
        if(!array_key_exists('contacts',$insert_detail) || empty($insert_detail['contacts']))
        {
            if(!empty($contact_name))
                $insert_detail['contacts'] = $contact_name;
        }

        //�ƶ��绰
        if(!array_key_exists('mobile_phone',$insert_detail) || empty($insert_detail['mobile_phone']))
        {
            $mobile = fetchOnce( $contact_desc_1,appendTagsWithAttr('dl','.*?',array('class','data-no'),array('.*?mobilephone.*?','([\d]+)'),2) );
            if(!empty($mobile))
                $insert_detail['mobile_phone'] = $mobile;
        }
        if(array_key_exists('website',$insert_detail))
        {
            $insert_detail['website'] = trim_blank($insert_detail['website']);
        }
        $insert_detail['curl'] = $company_page;

        insert_company_info($insert_detail);
        $curl_count++;
//        logTime($start_time,0);
        $cols = count($insert_detail);
        logUtil("total cols $cols");

    }

    return "$curl_count/$item_num";
}

function phantom_custom_request(Client $client,$url,$proc,$page)
{
    try{
        //    $start_time = time();
        $client->setProcedure($proc);
        //    $request = $client->getMessageFactory()->createRequest($url);
        $response = $client->getMessageFactory()->createResponse();
        $request  = new CustomRequest();
        $request->setMethod('GET');
        $request->setUrl($url);
        $request->setPage($page);
        $client->send($request, $response);
        $content = $response->getContent();
        //    logTime($start_time,$proc);
        return $content;
    } catch(Exception $e) {
        logUtil('Exception='.$e->getMessage().'  time='.get_current_time());
        return 'Exception';
    }
}

function phantom_simple_request(Client $client,$url)
{
    try{
        //    $start_time = time();
        $client->setProcedure('http_default');
        $request = $client->getMessageFactory()->createRequest($url);
        $response = $client->getMessageFactory()->createResponse();
        $client->send($request, $response);
        $content = $response->getContent();
        //    logTime($start_time,'http_default');
        return $content;
    } catch(Exception $e) {
        logUtil('Exception='.$e->getMessage().'  time='.get_current_time());
        return 'Exception';
    }
}

function insert_company_info($insertValue)
{
    global $mysqli;
    $insertValue = unset_null_value($insertValue);
    if(!array_key_exists('company_name',$insertValue))
        return;
    $company_info = $mysqli->rawQueryOne('select id from company_info where company_name=?', Array($insertValue['company_name']));
    if(empty($company_info)) {
        $result = $mysqli->insert('company_info',$insertValue);
        if(!$result)
            echo $mysqli->getLastError();
    } else {
        $mysqli->where('id', $company_info['id']);
        $mysqli->update('company_info',$insertValue);
    }
}


function insert_curl_pos($posValue)
{
    global $mysqli;
    $result = $mysqli->insert('company_curl_position',$posValue);
    if($result===false)
    {
        echo $mysqli->getLastError();
        return 0;
    } else {
        return $result;
    }
}


function update_curl_pos($pos_id,$code)
{
    global $mysqli;
    $mysqli->where('id', $pos_id);
    $result = $mysqli->update('company_curl_position',array('status'=>1,'position_code'=>$code));
    if($result && $mysqli->count>0)
        return true;
    else
        return false;
}

//�ж��Ƿ�������ҳ ����ֵ��
//1��false   ����Ҫ�ٴ�ץȡ
//2��-1      �κλ�������û����������Ҫ��ȡ
//3��>0      ��������һ�������жϣ���Ҫ��ȡ������ֵΪ��Ӧ����id
function check_position($posValue)
{
    global $mysqli,$server_ip;

    $infos = $mysqli->rawQuery('select * from company_curl_position where index_nav_link=? AND market_nav_link=? AND search_page_index=?',
        Array($posValue['index_nav_link'],$posValue['market_nav_link'],$posValue['search_page_index']));
    $pos_id = -1;
    if(!empty($infos))
    {
        foreach($infos as $pos_info)        //����ɻ�û�з���������ķ���false����Ҫ�ٴ�ץȡ����
        {
            if( $pos_info['status']==1 )
                return false;
            if( !empty($pos_info['distribute_ip']) && $pos_info['distribute_ip']!=$server_ip )
                return false;
            if( $pos_info['distribute_ip']==$server_ip )
                $pos_id = $pos_info['id'];
        }
    }
    return $pos_id;
}

function is_company_exist($company_name)
{
    global $mysqli;
    $company_info = $mysqli->rawQueryOne('select curl from company_info where company_name=?', Array($company_name));
    if(empty($company_info))
        return false;
    else
        return true;
}

function logTime($start_time,$type)
{
    if($type===0)
        logUtil( 'process content during '.(time()-$start_time).'s' );
    elseif($type==='http_default')
        logUtil( 'phantom_simple_request during '.(time()-$start_time).'s' );
    elseif($type==='http_click')
        logUtil( 'phantom_custom_click during '.(time()-$start_time).'s' );
    elseif($type==='http_large')
        logUtil( 'phantom_custom_large during '.(time()-$start_time).'s' );
    else
        logUtil( 'procedure '.$type.' during '.(time()-$start_time).'s' );
}