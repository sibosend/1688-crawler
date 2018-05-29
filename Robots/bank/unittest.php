<?php

define("ALIBB_PATH", dirname(__FILE__)."/../../");
include_once '../inc/global.php';
require ALIBB_PATH.'vendor/autoload.php';
include_once ALIBB_PATH . "Robots/inc/MyDAO.php";

use JonnyW\PhantomJs\Client;
use JonnyW\PhantomJs\Http\CaptureRequest;
use JonnyW\PhantomJs\Http\Request;


class CustomRequest1 extends CaptureRequest
{
    protected $page;

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }
}

class CustomRequest2 extends Request
{
    protected $page;

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }
}

class CustomRequest3 extends Request
{
    protected $multi_urls;

    public function getMultiUrls()
    {
        return $this->multi_urls;
    }

    public function setMultiUrls(array $multi_urls)
    {
        $this->multi_urls = $multi_urls;
        return $this;
    }
}

class CustomRequest4 extends CaptureRequest
{
    protected $multi_urls;

    public function getMultiUrls()
    {
        return $this->multi_urls;
    }

    public function setMultiUrls(array $multi_urls)
    {
        $this->multi_urls = $multi_urls;
        return $this;
    }
}

/**

 */



$url = 'https://s.1688.com/selloffer/offer_search.htm?keywords=%C1%AC%D2%C2%C8%B9&button_click=top&earseDirect=false&n=y';
$urls = array('https://www.puhuilicai.com/invest/toInvest.htm','http://www.xiaoniu88.com/','https://www.tuandai.com/','http://jinrong.suning.com/','http://www.we.com/',
    'http://www.zhicheng.com/','http://finance.sina.com.cn/money/','http://finance.qq.com/a/20150728/057468.htm','http://www.southmoney.com/jijin/jijinpaihang/2016/0608/593678.html',
    'http://money.sohu.com/20160908/n467907509.shtml','http://www.southmoney.com/jijin/jijinpaihang/2016/0617/603403.html','http://jinrong.suning.com/','http://www.kuaiji.com/'
    );

$urls1 = array('https://www.puhuilicai.com/invest/toInvest.htm','http://www.xiaoniu88.com/','https://www.tuandai.com/');
$urls2 = array('http://jinrong.suning.com/','http://www.we.com/','http://money.sohu.com/20160908/n467907509.shtml');
$urls4 = array('http://www.zhicheng.com/','http://finance.sina.com.cn/money/','http://finance.qq.com/a/20150728/057468.htm');
$urls3 = array('http://www.southmoney.com/jijin/jijinpaihang/2016/0617/603403.html','http://jinrong.suning.com/','http://www.kuaiji.com/');

$cache_dir = ALIBB_PATH.'/cache';
$exe_dir = ALIBB_PATH . "/bin/phantomjs.exe";

$client = Client::getInstance();
//$client->getProcedureLoader()->addLoader($procedureLoader);
//$client->getEngine()->setPath('D:\phantomjs\phantomjs-2.1.1-windows\bin\phantomjs.exe');
$client->getEngine()->setPath($exe_dir);
$client->getEngine()->addOption('--local-storage-path='.$cache_dir);
//$client->getEngine()->addOption('--cookies-file=cookie_test.txt');
//$client->getEngine()->addOption('--config=config.json');

//logUtil($client->getEngine()->getPath());
//logUtil($client->getEngine()->getOptions());
//logUtil($client->getEngine()->getCommand());

//phantom_custom_capture($client,$url,'D:/phantomjs/php-phantomjs-master/Robots/bank/03.jpg','http_click_1',30);
//phantom_custom_capture($client,$url,'D:/phantomjs/php-phantomjs-master/Robots/bank/02.jpg','http_large',);
//phantom_simple_capture($client,$urls[0],'D:/phantomjs/php-phantomjs-master/Robots/bank/01.jpg');
//phantom_simple_capture($client,$urls[1],'D:/phantomjs/php-phantomjs-master/Robots/bank/02.jpg');
//phantom_simple_capture($client,$urls[2],'D:/phantomjs/php-phantomjs-master/Robots/bank/03.jpg');

$urls9 = array(
    "https://shop1372438875492.1688.com/page/contactinfo.htm",
    "https://huashengvip.1688.com/page/contactinfo.htm",
    "https://shop1457456456149.1688.com/page/contactinfo.htm",
    "https://shop1457628837640.1688.com/page/contactinfo.htm",
    "https://baihe1873.1688.com/page/contactinfo.htm",

    "https://huashengvip.1688.com/page/contactinfo.htm",
    "https://shop1457456456149.1688.com/page/contactinfo.htm",
    "https://shop1457628837640.1688.com/page/contactinfo.htm",
    "https://baihe1873.1688.com/page/contactinfo.htm",
    "https://livagirl.1688.com/page/contactinfo.htm",

    "https://shop1457456456149.1688.com/page/contactinfo.htm",
    "https://shop1457628837640.1688.com/page/contactinfo.htm",
    "https://baihe1873.1688.com/page/contactinfo.htm",
    "https://livagirl.1688.com/page/contactinfo.htm",
    "https://shop1407948272385.1688.com/page/contactinfo.htm",

    "https://shop1457628837640.1688.com/page/contactinfo.htm",
    "https://baihe1873.1688.com/page/contactinfo.htm",
    "https://livagirl.1688.com/page/contactinfo.htm",
    "https://shop1407948272385.1688.com/page/creditdetail.htm",
    "https://keyingyi.1688.com/page/contactinfo.htm",

    "https://baihe1873.1688.com/page/contactinfo.htm",
    "https://livagirl.1688.com/page/contactinfo.htm",
    "https://shop1407948272385.1688.com/page/contactinfo.htm",
    "https://keyingyi.1688.com/page/contactinfo.htm",
    "https://kafu88.1688.com/page/contactinfo.htm",

    "https://livagirl.1688.com/page/contactinfo.htm",
    "https://shop1407948272385.1688.com/page/contactinfo.htm",
    "https://keyingyi.1688.com/page/contactinfo.htm",
    "https://kafu88.1688.com/page/contactinfo.htm",
    "https://shop1426857134135.1688.com/page/contactinfo.htm"
);


$confusions = array('/page/offerlist.htm','/page/creditdetail.htm','/page/albumlist.htm','/page/merchants.htm');



function processPer3()
{
    global $urls9,$confusions,$client;

    $num = count($urls9);

    for($i=0;$i<$num;$i++)
    {
        logUtil("processing $i/$num");
        $contact = $urls9[$i];
        $main = parse_url($contact)['host'];
        $conf = createHttpsLink($main).$confusions[rand(0,3)];
        logUtil($contact,'main page');

        phantom_custom_request($client,array($main,$conf,$contact),'http_multi');

        for($j=1;$j<=3;$j++)
        {
            $content = readFromFile('comp_info_'.$j.'.txt');
            logUtil(strlen($content),'$content');
            if(is_redirect($content))
            {
                logUtil($j.' redirect!!!');
            }
        }

        sleep(5);
    }
}

function processPer9()
{
    global $urls9,$confusions,$client;

    for($i=0;$i<10;$i++)
    {
        $comps = array();

        $contact = $urls9[3*$i];
        $main = parse_url($contact)['host'];
        $conf = createHttpsLink($main).$confusions[rand(0,3)];

        $comps[] = $main;
        $comps[] = $conf;
        $comps[] = $contact;

        $contact = $urls9[3*$i+1];
        $main = parse_url($contact)['host'];
        $conf = createHttpsLink($main).$confusions[rand(0,3)];

        $comps[] = $main;
        $comps[] = $conf;
        $comps[] = $contact;

        $contact = $urls9[3*$i+2];
        $main = parse_url($contact)['host'];
        $conf = createHttpsLink($main).$confusions[rand(0,3)];

        $comps[] = $main;
        $comps[] = $conf;
        $comps[] = $contact;

//    logUtil($comps);

        phantom_custom_request($client,$comps,'http_multi');

        for($i=1;$i<=9;$i++)
        {
            $content = readFromFile('comp_info_'.$i.'.txt');
            logUtil(strlen($content),'$content');
            if(is_redirect($content))
            {
                logUtil($i.' redirect!!!');
            }
        }

        sleep(5);
    }
}

//phantom_custom_request($client,array_merge($urls1,$urls2),'http_multi');

//test_process2DTable();
logUtil(appendTags('div','class','contcat-desc').'.*?'.appendTags('div','class','map-container'));
function test_process2DTable()
{
    $contact_desc = <<<EOT
    <div class="contcat-desc" data-spm-protocol="i">
                        <dl>
                <dt>��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;����</dt>
                <dd>86 1896 2370691</dd>
            </dl>
                                                <dl>
                <dt>��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�棺</dt>
                <dd>86  </dd>
            </dl>
                        <dl>
                <dt>��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ַ��</dt>


                                                                                                                                                                                                                                <dd class="address">�й� ���� ������ Ԫ������16��203
                </dd>
            </dl>
                            <dl>
                    <dt>��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�ࣺ</dt>
                    <dd>251200</dd>
                </dl>
						            <dl>
                <dt>������ҳ��</dt>
                <dd>
                                                                                                        <div>
                            <a href="https://shop1392223962302.1688.com/?asker=ATC" class="outsite" target="_blank">https://shop1392223962302.1688.com/?asker=ATC</a>
                        </div>
EOT;
    $projections_brief = array(
        array('contacts','��ϵ��',1),
        array('phone','�绰',3,'([\s\d]+)'),
        array('mobile_phone','�ƶ��绰',3,'([\s\d]+)'),
        array('fax','����',3,'([\s\d]+)'),
        array('address','��ַ',1),
        array('postcode','�ʱ�',3,'([\s\d]+)'),
        array('website','��˾��ҳ|������ҳ',1),
    );
    $temp_detail = process2DTable($contact_desc,'dl','d[t,d]',false);
    $temp_insert = processTableForInsert($temp_detail[0],$temp_detail[1],$projections_brief);
    if(array_key_exists('website',$temp_insert))
    {
        $temp_insert['website'] = trim_blank($temp_insert['website']);
    }
    logUtil($temp_detail);
    logUtil($temp_insert);
}

function phantom_custom_capture(Client $client,$url,$proc)
{
    $start_time = time();
    $client->setProcedure($proc);
    $request  = new CustomRequest4();
    $request->setMethod('GET');
    $request->setMultiUrls($url);
    $response = $client->getMessageFactory()->createResponse();
    $client->send($request, $response);
    $multi = $response->getMultiContent();
    logUtil(count($multi),'count');
    logTime($start_time,$proc);
}

function phantom_custom_request(Client $client,$url,$proc)
{
    $start_time = time();
    $client->setProcedure($proc);
    $request  = new CustomRequest3();
    $request->setMethod('GET');
    $request->setMultiUrls($url);
    $response = $client->getMessageFactory()->createResponse();
    $client->send($request, $response);

//    $multi = $response->getMultiContent();
//    logUtil(count($multi),'count');

//    $content = $response->getContent();
    logTime($start_time,$proc);
//    logUtil(strlen($response->getIndex()),'index');
//    logUtil(strlen($content),'content');



//    $redirect_num = 0;
//    for($i=0;$i<count($multi);$i++)
//    {
//        if(is_redirect($multi[$i]))
//        {
//            logUtil($i.' redirect!!!');
//            $redirect_num++;
//        }
//    }
//    logUtil($redirect_num,'$redirect_num');

}

function get10lines($offset)
{
    global $mysqli;
    $company_info = $mysqli->rawQuery('select curl from company_info ORDER BY id DESC limit 5 offset ?',array($offset));
    $list = array();
    foreach($company_info as $info)
    {
        $list[] = $info['curl'];
    }
    return $list;
}


function phantom_simple_request(Client $client,$url)
{
    $start_time = time();
    $client->setProcedure('http_default');
    $request = $client->getMessageFactory()->createRequest($url);
    $response = $client->getMessageFactory()->createResponse();
    $client->send($request, $response);
    $content = $response->getContent();
    logTime($start_time,'http_default');
//    saveToFile($content);
    return $content;
}

function clear_storage(Client $client)
{
    $start_time = time();
    $client->setProcedure('clear_storage');
    $request = $client->getMessageFactory()->createRequest();
    $response = $client->getMessageFactory()->createResponse();
    $client->send($request, $response);
    logTime($start_time,'clear_storage');
}


function phantom_simple_capture(Client $client,$url,$file)
{
    //1688��ҳ
    $start_time = time();
    $client->setProcedure('http_default');
    $request = $client->getMessageFactory()->createCaptureRequest($url);
    $response = $client->getMessageFactory()->createResponse();
    $request->setOutputFile($file);
    $client->send($request, $response);
    logTime($start_time,'http_default');
}

function logTime($start_time,$type)
{
    if($type===0)
        logUtil( 'process content during '.(time()-$start_time).'s' );
    else
        logUtil( 'procedure '.$type.' during '.(time()-$start_time).'s' );
}


function is_redirect($content)
{
    if( !empty(fetchOnce($content,'<div class="w952" id="masthead-v4">'))
        ||  !empty(fetchOnce($content,'<div id="loginchina-wrapper" class="signin">')) )
        return true;
    else
        return false;
}
