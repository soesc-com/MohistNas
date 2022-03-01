<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;


/* ======  自定义函数  ====== */
function Chk_Authenticate($cUsername='', $cPassword='') {
    // 检查用户名密码
    $cUsername=trim($cUsername); $cPassword=trim($cPassword);
    if (!preg_match('/^[a-zA-Z]([-_a-zA-Z0-9-_]{2,30})$/',  $cUsername ) ) { return [False,'','','Username Err !']; /* 用户名检测不通过 */ }
    if (!preg_match('/^[a-zA-Z]([-_a-zA-Z0-9-_]{2,30})$/',  $cPassword ) ) { return [False,'','','Password Err! ']; /* 用户名检测不通过 */ }
    $Users=posix_getgrnam("MohistNas");//获取指定用户组用户列表
    if (in_array($cUsername, $Users['members'])){
        $xU=trim($cUsername);
        $xUser_authenticate_1=`sudo cat /etc/shadow | grep "$xU" | tr -d "\n" ;`;//获取加密的密码;
        $xUser_authenticate_1Key=explode(":",$xUser_authenticate_1)[1]; $xUser_authenticate_1Pass=explode("$",$xUser_authenticate_1Key)[2]; //获取系统中的用户加密密码
        $xUser_authenticate_2Key=exec('sudo mkpasswd -m sha-512 -S "'.$xUser_authenticate_1Pass.'" "'.$cPassword.'" ;');//计算传入的加密的秘钥;
        if ( trim($xUser_authenticate_1Key)==trim($xUser_authenticate_2Key) ) {
            $zP=''; $zP=strtoupper(hash('sha512', 'MohistNas_Session_Password='.$xUser_authenticate_2Key, $zP));
            return [True,$xU,$zP,'Username and password verification succeeded !']; //用户存在，密码匹配
        } else { return [False,trim($cUsername),'','Username and password verification failed !']; /* 用户存在，密码不匹配 */ }
    } else { return [False,'','','Username does not exist !']; /* 用户不存在 */ }
}

function Chk_Authenticate_Session($cUsername='', $cSession_Password='') {
    // 检查来自Session的用户名密码
    $cUsername=trim($cUsername); $cSession_Password=trim($cSession_Password);
    if (!preg_match('/^[a-zA-Z]([-_a-zA-Z0-9-_]{2,30})$/',  $cUsername ) ) { return [False,'','','Username Err !']; /* 用户名检测不通过 */ }
    if ( $cSession_Password=='' ) { return [False,'','','Password Err! ']; /* 用户名检测不通过 */ }
    $Users=posix_getgrnam("MohistNas");//获取指定用户组用户列表
    if (in_array($cUsername, $Users['members'])){
        $xU=trim($cUsername);
        $xUser_authenticate_1=`sudo cat /etc/shadow | grep "$xU" | tr -d "\n" ;`;//获取加密的密码;
        $xUser_authenticate_1Key=explode(":",$xUser_authenticate_1)[1]; $xUser_authenticate_1Pass=explode("$",$xUser_authenticate_1Key)[2]; //获取系统中的用户加密密码
        $zP=''; $zP=strtoupper(hash('sha512', 'MohistNas_Session_Password='.$xUser_authenticate_1Key, $zP));//计算系统中的用户加密密码的加密的秘钥;
        if ( trim($zP)==trim($cSession_Password) ) {
            return [True,$xU,$zP,'Username and password verification succeeded !']; //用户存在，密码匹配
        } else { return [False,trim($cUsername),'','Username and password verification failed !']; /* 用户存在，密码不匹配 */ }
    } else { return [False,'','','Username does not exist !']; /* 用户不存在 */ }
}

function Get_UrlData($inurl){
    // 获取指定Url中的信息
    $MN_GetUrl = curl_init();
    curl_setopt($MN_GetUrl, CURLOPT_URL,$inurl);
    curl_setopt($MN_GetUrl, CURLOPT_RETURNTRANSFER,1); //相当关键，这句话是让curl_exec($MN_GetUrl)返回的结果可以进行赋值给其他的变量进行，json的数据操作，如果没有这句话，则curl返回的数据不可以进行人为的去操作（如json_decode等格式操作）
    curl_setopt($MN_GetUrl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($MN_GetUrl, CURLOPT_SSL_VERIFYHOST, false);
    return curl_exec($MN_GetUrl);
}

function Get_phpSysinfo($hosturl='https://localhost:6888/',$decode=true) {
    // 通过phpsysinfo组件获取硬件信息
    // $HW=Get_Hardware_phpsysinfo($hosturl=URL::secureAsset(''),$decode=true);
    $HW=Get_UrlData($hosturl."phpsysinfo/xml.php?plugin=complete&json");
    if ($HW==true) { $HW=json_decode($HW); }
    return $HW;
}

function Get_HWSysinfo() {
    // 获取所有系统信息
    $HWS['Sys']=Get_info_System();
    $HWS['Cpu']=Get_info_CPU();
    $HWS['Mem']=Get_info_Mem();
    $HWS['Net']=Get_info_Net();
    $HWS['Time']=Get_info_Time();
    $HWS['Status']=Get_info_Status();
    return $HWS; // 返回所有系统信息
}

function Get_info_System() {
    // 获取系统信息
    $HWS['sys-name']=`cat /etc/issue`; // Linux发行版名称;
        $HWS['sys-name']=trim(str_replace(array("\r\n", "\r", "\n","\\n","\\l"), "", $HWS['sys-name'] ));
    $HWS['host-name']=trim(`cat /etc/hostname`).' ('.$_SERVER['SERVER_ADDR'].')'; // 服务器名称;
    $HWS['kernel']=`uname -s -r -m`; // Linux kernel 信息;
        $HWS['kernel']=trim(str_replace(array("\r\n", "\r", "\n","\\n","\\l"), "", $HWS['kernel'] ));
    $HWS['virtualizer']=`systemd-detect-virt`; // Linux kernel 信息;
        $HWS['virtualizer']=trim(str_replace(array("\r\n", "\r", "\n","\\n","\\l"), "", $HWS['virtualizer'] ));
    $HWS['processes']=trim(`ps -ef  | wc -l`); // 获取当前进程数
        $HWS['processes']=trim(str_replace(array("\r\n", "\r", "\n","\\n","\\l"), "", $HWS['processes'] ));
        $HWS['processes']=$HWS['processes']-1;
    $HWS['NICs']=trim(`lspci | grep Ethernet | wc -l`); // 获取系统网卡数量
        $HWS['NICs']=trim(str_replace(array("\r\n", "\r", "\n","\\n","\\l"), "", $HWS['NICs'] ));
    return $HWS; // 返回系统信息
}

function Get_info_Status() {
    // 获取CPU使用率
    $xTemp=`mpstat  -o JSON -P ALL 1 1 `; // CPU名称;
    $xTemp=json_decode($xTemp,TRUE );
    $HWS['cpu-cores']=$xTemp["sysstat"]['hosts'][0]['number-of-cpus'];
    $xTempAll=$xTemp["sysstat"]['hosts'][0]['statistics'][0]['cpu-load'];
    for ($x=0; $x<=$HWS['cpu-cores']; $x++) {
        if ($x==0) { $xCpuName='all';} else { $xCpuName=$x-1;}
        if ( round(100-$xTempAll[$x]['idle'],1)<0 ) { $HWS['cpu-'.$xCpuName]=0; } else { $HWS['cpu-'.$xCpuName]=round(100-$xTempAll[$x]['idle'],1); }
    } 
    // 获取内存使用率
    $HWS['memtotal']=trim(`cat /proc/meminfo | grep 'MemTotal' | uniq`); // 获取内存总数kB
        $HWS['memtotal']=trim(str_replace(array("MemTotal",":",'kB'), "", $HWS['memtotal'] ));
        $xMt=$HWS['memtotal'];
        unset($HWS['memtotal']);
    $HWS['memfree']=trim(`cat /proc/meminfo | grep 'MemFree' | uniq`); // 获取剩余内存总数kB
        $HWS['memfree']=trim(str_replace(array("MemFree",":",'kB'), "", $HWS['memfree'] ));
        $xMf=$HWS['memfree'];
        unset($HWS['memfree']);
    $xMu=$xMt-$xMf;
    $HWS['mem-used']=$xMu/$xMt*100;
        $HWS['mem-used']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['mem-used']), 0, -2));
    return $HWS; // 返回系统信息
}

function Get_info_CPU() {
    // 获取CPU相关信息
    $HWS['cpu-name']=`cat /proc/cpuinfo | grep 'model name' |uniq`; // CPU名称;
        $HWS['cpu-name']=rtrim(str_replace(array("model name",":"), "", $HWS['cpu-name'] ));
        $HWS['cpu-name']=trim( preg_replace("/\s(?=\s)/","\\1",$HWS['cpu-name']) );
    $HWS['cpu-cores']=trim(`grep 'core id' /proc/cpuinfo | sort -u |wc -l`); // CPU核心数;
    $HWS['cpu-processor']=trim(`grep 'processor' /proc/cpuinfo | sort -u | wc -l`); // CPU逻辑核心数;
    $HWS['cpu-text']=$HWS['cpu-name'].' , '. $HWS['cpu-processor'].' cores';// CPU详细描述
    //====================
    return $HWS; // 返回CPU相关信息
}

function Get_info_Mem() {
    // 获取内存相关信息
    $HWS['memtotal']=trim(`cat /proc/meminfo | grep 'MemTotal' | uniq`); // 获取内存总数kB
        $HWS['memtotal']=trim(str_replace(array("MemTotal",":",'kB'), "", $HWS['memtotal'] ));
        $xMt=$HWS['memtotal'];
        if ($HWS['memtotal']/1024>=1) { $xmemstr='MiB'; $HWS['memtotal']=$HWS['memtotal']/1024; }
        if ($HWS['memtotal']/1024>=1) { $xmemstr='GiB'; $HWS['memtotal']=$HWS['memtotal']/1024; }
        $HWS['memtotal']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['memtotal']), 0, -2));
        $HWS['memtotal']=$HWS['memtotal'].' '.$xmemstr;
    $HWS['cached']=trim(`cat /proc/meminfo | grep 'Cached' | grep -v 'SwapCached' | uniq`); // 获取内存Cached kB
        $HWS['cached']=trim(str_replace(array("Cached",":",'kB'), "", $HWS['cached'] ));
        $xTmp=trim(`cat /proc/meminfo | grep 'Buffers' | uniq`); // 获取内存Buffers kB
        $xTmp=trim(str_replace(array("Buffers",":",'kB'), "", $xTmp ));
        $HWS['cached']=$HWS['cached']+$xTmp;
        if ($HWS['cached']/1024>=1) { $xmemstr='MiB'; $HWS['cached']=$HWS['cached']/1024; }
        if ($HWS['cached']/1024>=1) { $xmemstr='GiB'; $HWS['cached']=$HWS['cached']/1024; }
        $HWS['cached']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['cached']), 0, -2));
        $HWS['cached']=$HWS['cached'].' '.$xmemstr;
    $HWS['memfree']=trim(`cat /proc/meminfo | grep 'MemFree' | uniq`); // 获取剩余内存总数kB
        $HWS['memfree']=trim(str_replace(array("MemFree",":",'kB'), "", $HWS['memfree'] ));
        $xMf=$HWS['memfree'];
        if ($HWS['memfree']/1024>=1) { $xmemstr='MiB'; $HWS['memfree']=$HWS['memfree']/1024; }
        if ($HWS['memfree']/1024>=1) { $xmemstr='GiB'; $HWS['memfree']=$HWS['memfree']/1024; }
        $HWS['memfree']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['memfree']), 0, -2));
        $HWS['memfree']=$HWS['memfree'].' '.$xmemstr;
    $HWS['memused']=$xMt-$xMf;
        if ($HWS['memused']/1024>=1) { $xmemstr='MiB'; $HWS['memused']=$HWS['memused']/1024; }
        if ($HWS['memused']/1024>=1) { $xmemstr='GiB'; $HWS['memused']=$HWS['memused']/1024; }
        $HWS['memused']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['memused']), 0, -2));
        $HWS['memused']=$HWS['memused'].' '.$xmemstr;
    //虚拟内存相关
    $HWS['swaptotal']=trim(`cat /proc/meminfo | grep 'SwapTotal' | uniq`); // 获取虚拟内存总数kB
        $HWS['swaptotal']=trim(str_replace(array("SwapTotal",":",'kB'), "", $HWS['swaptotal'] ));
        $xSt=$HWS['swaptotal'];
        if ($HWS['swaptotal']/1024>=1) { $xmemstr='MiB'; $HWS['swaptotal']=$HWS['swaptotal']/1024; }
        if ($HWS['swaptotal']/1024>=1) { $xmemstr='GiB'; $HWS['swaptotal']=$HWS['swaptotal']/1024; }
        $HWS['swaptotal']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['swaptotal']), 0, -2));
        $HWS['swaptotal']=$HWS['swaptotal'].' '.$xmemstr;
    $HWS['swapfree']=trim(`cat /proc/meminfo | grep 'SwapFree' | uniq`); // 获取剩余虚拟内存总数kB
        $HWS['swapfree']=trim(str_replace(array("SwapFree",":",'kB'), "", $HWS['swapfree'] ));
        $xSf=$HWS['swapfree'];
        if ($HWS['swapfree']/1024>=1) { $xmemstr='MiB'; $HWS['swapfree']=$HWS['swapfree']/1024; }
        if ($HWS['swapfree']/1024>=1) { $xmemstr='GiB'; $HWS['swapfree']=$HWS['swapfree']/1024; }
        $HWS['swapfree']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['swapfree']), 0, -2));
        $HWS['swapfree']=$HWS['swapfree'].' '.$xmemstr;
    $HWS['swapused']=$xSt-$xSf;
        if ($HWS['swapused']/1024>=1) { $xmemstr='MiB'; $HWS['swapused']=$HWS['swapused']/1024; }
        if ($HWS['swapused']/1024>=1) { $xmemstr='GiB'; $HWS['swapused']=$HWS['swapused']/1024; }
        $HWS['swapused']=sprintf("%.2f",substr(sprintf("%.4f",$HWS['swapused']), 0, -2));
        $HWS['swapused']=$HWS['swapused'].' '.$xmemstr;
    return $HWS; // 返回内存相关信息
}

function Get_info_Net() {
    // 获取网络相关信息
    $HWS['NICs']=trim(`lspci | grep Ethernet | wc -l`); // 获取系统网卡数量
    return $HWS; // 返回网络相关信息
}

function Get_info_Time() {
    // 获取时间相关信息
    $HWS['sys-timetxt']=`date "+%Y-%m-%d %H:%M:%S %z"`; // Linux系统时间
        $HWS['sys-timetxt']=rtrim(str_replace(array("\r\n", "\r", "\n","\\n","\\l"), "", $HWS['sys-timetxt'] ));
    $HWS['sys-uptimetxt']=`uptime -p`; // Linux系统运行时间
        $xstr   = @file_get_contents('/proc/uptime');
        $xnum   = floatval($xstr);
        $xsecs  = fmod($xnum, 60); $xnum = intdiv($xnum, 60);
        $xmins  = $xnum % 60;      $xnum = intdiv($xnum, 60);
        $xhours = $xnum % 24;      $xnum = intdiv($xnum, 24);
        $xdays  = $xnum;
        $HWS['sys-uptime']=[$xdays,$xhours,$xmins];
            $HWS['sys-uptimelang']='';
            if ($xdays>0)  { if ($xdays>1) { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].' '.$xdays.' '.trans('main.txt-v-uptime-ds'); } else { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].' '.$xdays.' '.trans('main.txt-v-uptime-d'); } }
            if ($xdays!=0) { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].trans('main.txt-v-uptime-dss'); }
            if ($xhours>0)  { if ($xhours>1) { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].' '.$xhours.' '.trans('main.txt-v-uptime-hs'); } else { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].' '.$xhours.' '.trans('main.txt-v-uptime-h'); } }
            if ($xhours!=0) { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].trans('main.txt-v-uptime-hss'); }
            if ($xmins>0)  { if ($xmins>1) { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].' '.$xmins.' '.trans('main.txt-v-uptime-ms'); } else { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].' '.$xmins.' '.trans('main.txt-v-uptime-m'); } }
            if ($xmins!=0) { $HWS['sys-uptimelang']=$HWS['sys-uptimelang'].trans('main.txt-v-uptime-mss'); }
            if (trim($HWS['sys-uptimelang'])=='') { $HWS['sys-uptimelang']='...'; }
            $HWS['sys-uptimelang']=trim($HWS['sys-uptimelang']);
    return $HWS; // 返回时间相关信息
}


/* ======  自定义全局变量  ====== */
global $qLangs; //系统支持的全部语言
$qLangs=[
    'en' => 'en', // 英语
    'zh' => 'zh-CN', 'zhcn' => 'zh-CN', 'zh-cn' => 'zh-CN', 'zh_cn' => 'zh-CN', 'zh_cn' => 'zh-CN', 'chs' => 'zh-CN',
    'zhtw' => 'zh-TW', 'zh-tw' => 'zh-TW', 'zh_tw' => 'zh-TW', 'cht' => 'zh-TW', // 繁体中文
    'jp' => 'jp', // 日语
    'de' => 'de', //德语
];


/* ======  Web Routes  ======
|   Here is where you can register web routes for your application. These routes are loaded by the RouteServiceProvider within a group which contains the "web" middleware group. Now create something great! */

Route::match(['get','post'],'/', function () {   // 系统登录页面，处理登录相关功能
    $zU=Session::get('User',''); $zP=Session::get('Pass','');
    if (trim($zU)=='' or  trim($zP)=='') { return redirect('/login'); /* 无验证信息 , 重定向至登录页面; */ } else { return redirect('/index'); /* 有验证信息 , 重定向至主控制面板; */ }
});

Route::get('/lang', function (Request $request) {   // 设置语言页面，处理设置语言的功能
    $LangValue = $request->input('lang'); if (!isset($LangValue)) {$LangValue = $request->input('l', 'en'); } //get和post一起取，同名post覆盖get;
    $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); }//设置语言
    $LangValue = App::getLocale(); Session::put('Lang',$LangValue); //设置Session->Lang; //其他: Session::forget(['key1', 'key2']); //删除多个Session;
    Cookie::queue('Lang', $LangValue,60*24*365*1); /* 设置Cookie->Lang，参数格式：$name, $value, $minutes; */   
    Session::put('Lang',$LangValue);//设置Session->Lang;
    Session::put('LastRequest',date("Y-m-d H:i:s",time()));/*[End]*/
    return response()->json(['Lang' =>$LangValue]);//输出页面;
});

Route::get('/message', function (Request $request) {   // 显示提示信息
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
    Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
    /* ====== 处理路由 Begin ====== */
        $Message = $request->input('mid');//get和post一起取，同名post覆盖get;
        if (!isset($Message)) {$Message = $request->input('m', '000000');}
        $Data['xMessage']=trans('main.Message_'.$Message);
        $Data['xMessage_Center']='F'; // 文字左对齐
        $Data['xMessage_UrlTime']=-1;// 不倒计时
        $Data['xMessage_Url']='';
        if (strtolower(trim($Data['xMessage']))==strtolower(trim('main.Message_'.$Message))) { $Data['xMessage']=trans('main.Message_000000'); }
    /*[End]*/
    return view('message',$Data);//输出页面;
});

Route::get('/logout', function (Request $request) {   // 登出系统
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
        /* --- 判断是否登录成功[Begin] --- */
        $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
        $xV=Chk_Authenticate_Session($xU,$xP); if ($xV[0]==false) { Session::forget(['User','Pass']); return redirect('/login'); /* 用户名密码验证失败 , 重定向至登录页面; */ }
    /* ====== 处理路由 Begin ====== */
    $Data['xMessage']=trans('main.LogoutMsg');
    $Data['xMessage_Center']='T'; // 文字中间对齐
    $Data['xMessage_UrlTime']=3;
    $Data['xMessage_Url']='/';
    Session::forget(['User','Pass']);/*[End]*/
    return view('message',$Data);//输出页面;
});

Route::get('/reboot', function (Request $request) {   // 重启系统
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
        /* --- 判断是否登录成功[Begin] --- */
        $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
        $xV=Chk_Authenticate_Session($xU,$xP); if ($xV[0]==false) { Session::forget(['User','Pass']); return redirect('/login'); /* 用户名密码验证失败 , 重定向至登录页面; */ }
    /* ====== 处理路由 Begin ====== */
    $Data['xMessage']=trans('main.RebootMsg');
    $Data['xMessage_Center']='T'; // 文字中间对齐
    $Data['xMessage_UrlTime']=60; // 倒计时60秒
    $Data['xMessage_Url']='/';
    system("nohup sudo shutdown -r now > /dev/null &");
    return view('message',$Data);//输出页面;
});

Route::get('/shutdown', function (Request $request) {   // 关闭系统
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
        /* --- 判断是否登录成功[Begin] --- */
        $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
        $xV=Chk_Authenticate_Session($xU,$xP); if ($xV[0]==false) { Session::forget(['User','Pass']); return redirect('/login'); /* 用户名密码验证失败 , 重定向至登录页面; */ }
    /* ====== 处理路由 Begin ====== */
    $Data['xMessage']=trans('main.ShutdownMsg');
    $Data['xMessage_Center']='T'; // 文字中间对齐
    $Data['xMessage_UrlTime']=8;// 倒计时8秒
    $Data['xMessage_Url']='/';
    Session::forget(['User','Pass']);;
    system("nohup sudo shutdown -h now > /dev/null &");
    return view('message',$Data);//输出页面;
});

Route::match(['get','post'],'/getapi',function(Request $request){   // 系统登录页面，处理登录相关功能
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());/*[End]*/
    /* ====== 处理路由 Begin ====== */
    $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
    if ( $xU.$xP=='' ) { $Data['API']=''; $Data['[OK!]']=-1; }  //无登录信息，输出空数据
    else {
        $AValue = $request->input('API'); if (!isset($AValue)) {$AValue = $request->input('A', ''); } //get和post一起取，同名post覆盖get;
        if ( strtolower(trim($AValue))==strtolower(trim('HWSysinfo'))  ) { $Data['API']=Get_HWSysinfo();  $Data['[OK!]']=0; }
        else if  ( strtolower(trim($AValue))==strtolower(trim('info_System'))  ) { $Data['API']=Get_info_System();  $Data['[OK!]']=0; }
        else if  ( strtolower(trim($AValue))==strtolower(trim('info_CPU'))  ) { $Data['API']=Get_info_CPU();  $Data['[OK!]']=0; }
        else if  ( strtolower(trim($AValue))==strtolower(trim('info_Mem'))  ) { $Data['API']=Get_info_Mem();  $Data['[OK!]']=0; }
        else if  ( strtolower(trim($AValue))==strtolower(trim('info_Net'))  ) { $Data['API']=Get_info_Net();  $Data['[OK!]']=0; }
        else if  ( strtolower(trim($AValue))==strtolower(trim('info_Time'))  ) { $Data['API']=Get_info_Time();  $Data['[OK!]']=0; }
        else if  ( strtolower(trim($AValue))==strtolower(trim('info_Status'))  ) { $Data['API']=Get_info_Status();  $Data['[OK!]']=0; }
        else { $Data['API']=''; $Data['[OK!]']=-1; } //参数错误，输出空数据
    }
    return response()->json($Data);//输出页面;
})->name('getapi');

Route::match(['get','post'],'/login',function(Request $request){   // 系统登录页面，处理登录相关功能
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
    /* ====== 处理路由 Begin ====== */
    $Data['xUser_authenticate']=''; $Data['xErr']=''; $Data['xOldUser']='';
    if ($request->isMethod('get')){ /* Get路由 */
        // 系统登录页面 <<<=== /Login Get ===
        Session::forget(['User', 'Pass']); return view('login',$Data);//输出页面;
    }elseif($request->isMethod('post')){ /* Post路由 */
        // 检验登录信息 <<<=== /Login Post ===
        Session::forget(['User', 'Pass']); $xU=trim($request->request->get('vUser','')); $xP=trim($request->request->get('vPass', ''));
        if ( $xU=='' or $xP=='' ) { $Data['xErr']=trans('main.UPValidation1'); return view('login',$Data); } //用户名密码错误, 输出错误页面;
        $xV=Chk_Authenticate($xU,$xP);
        if ($xV[0]==false) {
            if (trim($xV[1])!='') {$Data['xOldUser']=trim($xV[1]);}//用户名存在就保留用户名，如果考虑避免被试探用户名是否在MohistNas组，可注释此行！
            $Data['xErr']=trans('main.UPValidation2'); return view('login',$Data);//用户名密码验证失败
        } else {
            // 写入登录完成的信息 >>>
            $Data['xUser_authenticate']='OK!';//密码正确;
            Session::put('User',$xV[1]); Session::put('Pass',$xV[2]);//设置 Session->zUser; Session->zPass;
            Log::info('登录成功！');
            return redirect('/index');//完成验证，重定向至主控制面板 >>>
            //用户名密码验证成功！
        }
        Session::forget(['User', 'Pass']); return response('Post /Login', 200); // 输出页面; 
    }
})->name('login');

Route::get('/index', function (Request $request) {   // 系统首页
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
        /* --- 判断是否登录成功[Begin] --- */
        $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
        $xV=Chk_Authenticate_Session($xU,$xP); if ($xV[0]==false) { Session::forget(['User','Pass']); return redirect('/login'); /* 用户名密码验证失败 , 重定向至登录页面; */ }
    // 密码验证正确，开始输出控制面板 ===>>>
    //$HW=Get_phpSysinfo($hosturl=URL::secureAsset(''),$decode=true);
    $Data['xSysInfo']=Get_HWSysinfo();
    Log::info('打开控制面板！');
    $Data['xUser']=trim($xV[1]); return view('index',$Data);//输出页面;
    /*[End]*/
});

Route::get('/log', function (Request $request) {   // 日志页面
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
        /* --- 判断是否登录成功[Begin] --- */
        $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
        $xV=Chk_Authenticate_Session($xU,$xP); if ($xV[0]==false) { Session::forget(['User','Pass']); return redirect('/login'); /* 用户名密码验证失败 , 重定向至登录页面; */ }
    // 密码验证正确，开始输出控制面板 ===>>>
    Log::info('打开控制面板！');
    $Data['xUser']=trim($xV[1]); return view('log',$Data);//输出页面;
    /*[End]*/
});

Route::get('/about', function (Request $request) {   // 关于页面
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
        /* --- 判断是否登录成功[Begin] --- */
        $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
        $xV=Chk_Authenticate_Session($xU,$xP); if ($xV[0]==false) { Session::forget(['User','Pass']); return redirect('/login'); /* 用户名密码验证失败 , 重定向至登录页面; */ }
    // 密码验证正确，开始输出控制面板 ===>>>
    Log::info('打开控制面板！');
    $Data['xUser']=trim($xV[1]); return view('about',$Data);//输出页面;
    /*[End]*/
});

Route::get('/preferences', function (Request $request) {   // 首选项页面
        /* --- 设置语言[Begin] --- */
        $LangValue = $request->cookie('Lang'); //读取Cookie中的Lang;
        if (!isset($LangValue)) {$LangValue = Session::get('Lang','en');}//如果Cookie未设置就读取Session中的Lang;
        $LangValue = strtolower(trim($LangValue)); global $qLangs;  if (isset($qLangs[$LangValue])) { App::setLocale($qLangs[$LangValue]); } else { App::setLocale('en'); } //设置语言
        $Data['xLang']=App::getLocale(); $Data['xUri']=trim(Route::getFacadeRoot()->current()->uri()); $Data['xUrl']=trim($request->fullUrl()); $Data['xReferer']=trim(request()->headers->get('referer')); $Data['xClientIP']=trim($request->ip());
        $Data['xLastRequest']=date("Y-m-d H:i:s",time()); Session::put('LastRequest',$Data['xLastRequest']);/*[End]*/
        /* --- 判断是否登录成功[Begin] --- */
        $xU=trim(Session::get('User','')); $xP=trim(Session::get('Pass',''));
        $xV=Chk_Authenticate_Session($xU,$xP); if ($xV[0]==false) { Session::forget(['User','Pass']); return redirect('/login'); /* 用户名密码验证失败 , 重定向至登录页面; */ }
    // 密码验证正确，开始输出控制面板 ===>>>
    Log::info('打开控制面板！');
    $Data['xUser']=trim($xV[1]); return view('preferences',$Data);//输出页面;
    /*[End]*/
});

/* ======  Debug 路由区域  ====== */
Route::get('getcookie', function (Request $request) {  //调试输出全部 Cookie
    Session::put('LastRequest',date("Y-m-d H:i:s",time()));
    abort(404);//默认方式不能输出调试信息;
    return response()->json($request->cookie());//输出页面;
});

Route::get('getsession', function (Request $request) {  //调试输出全部 Session
    Session::put('LastRequest',date("Y-m-d H:i:s",time()));
    abort(404);//默认方式不能输出调试信息;
    return response()->json(Session::all());//输出页面;
});

Route::match(['get','post'],'debug', function (Request $request) { //调试内部函数使用
    Session::put('LastRequest',date("Y-m-d H:i:s",time()));
    abort(404);//默认方式不能输出调试信息;
    //$Data['Debug']=Get_Hardware_phpsysinfo($hosturl=URL::secureAsset(''),$decode=true);
    $Data['Text']='Debug';
    return response()->json($Data);//输出页面;
});

/* ======  [ END ]  ====== */