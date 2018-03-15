<?php
	set_time_limit(0); //0
	$data = array();
	/***全图***/
	$data["5"] = array(
		"minx"=>-11,
		"maxx"=>9,
		"miny"=>-16,
		"maxy"=>15
	);
	$data["6"] = array(
		"minx"=>-21,
		"maxx"=>19,
		"miny"=>-16,
		"maxy"=>18
	);
	$data["7"] = array(
		"minx"=>-39,
		"maxx"=>38,
		"miny"=>-30,
		"maxy"=>37
	);
	$data["8"] = array(
		"minx"=> -77,
		"maxx"=>76,
		"miny"=>-62,
		"maxy"=>74
	);

	/****中国部分***/

	$data["9"] = array(
		"minx"=> 80,
		"maxx"=>114,
		"miny"=>12,
		"maxy"=>45
	);

	$data["10"] = array(
		"minx"=> 166,
		"maxx"=>217,
		"miny"=>29,
		"maxy"=>83
	);

	$data["11"] = array(
		"minx"=> 356,
		"maxx"=>446,
		"miny"=>73,
		"maxy"=>184
	);

	$data["12"] = array(
		"minx"=> 718,
		"maxx"=>893,
		"miny"=>148,
		"maxy"=>357
	);

	$data["13"] = array(
		"minx"=> 1362,
		"maxx"=>1775,
		"miny"=>314,
		"maxy"=>677
	);
	
	$data["14"] = array(
		"minx"=> 2676,
		"maxx"=>3685,
		"miny"=>693,
		"maxy"=>1550
	);

	$data["15"] = array(
		"minx"=> 5352,
		"maxx"=>7348,
		"miny"=>1389,
		"maxy"=>3011
	);
	
	$data["16"] = array(
		"minx"=> 12741,
		"maxx"=>13251,
		"miny"=>3516,
		"maxy"=>4030
	);
	$data["17"] = array(
		"minx"=> 25483,
		"maxx"=>26511,
		"miny"=>7032,
		"maxy"=>8063
	);
	$data["18"] = array(
		"minx"=> 50965,
		"maxx"=>53020,
		"miny"=>14063,
		"maxy"=>16125
	);
	
	$maxz = 16;
	
	$x = 0;
	$y = 0;
	$z = 15;
	$pathroot = dirname(__file__);
	for($z;$z<$maxz;$z++){
		for($x=5581;$x<=$data[$z]["maxx"];$x++){
			$t1 = microtime(true);
			$imgarr = array();
			$path     = $pathroot."\\tiles\\$z\\$x\\";
			if(!file_exists($path)){
				mkdir($path, 0777,true);
			}
			for($y=$data[$z]["miny"];$y<=$data[$z]["maxy"];$y++){
				$pathfile = $path.$y.'.jpg';
				if(!file_exists($pathfile)){
					$imgarr[$y] = getImgUrl($x,$y,$z);
				}
				unset($pathfile);
				if(count($imgarr) > 800){
					$dataimg = Curl_http($imgarr);//列表数据
				  	foreach ((array)$dataimg as $kk=>$vv){  
				   		if($vv !=''){
							$pathfile = $path.$kk.'.jpg';
				   			file_put_contents($pathfile, $vv);
				   		}
				   	}
					unset($imgarr);
					unset($dataimg);
				}
			}
			if(count($imgarr) > 0){
				//header("Content-type: text/html; charset=utf-8");
				$dataimg = Curl_http($imgarr);//列表数据
			  	foreach ((array)$dataimg as $kk=>$vv){  
			   		if($vv !=''){
						$pathfile = $path.$kk.'.jpg';
			   			file_put_contents($pathfile, $vv);
			   		}
			   	}
				unset($dataimg);
				//$t2 = microtime(true);
				//echo '耗时'.round($t2-$t1,3).'秒,'.count($imgarr).'个文件';
				//exit;
			}
			unset($pathfile);
			unset($imgarr);
		}
	}
/***返回图片url***/
function getImgUrl($x,$y,$z){
	return "http://online0.map.bdimg.com/tile/?qt=tile&x=$x&y=$y&z=$z&styles=pl&scaler=1&udt=20160202";
}
/***远程抓取图片资源**/
function getImg($url){
	$ip = get_rand_ip(); //每次请求都切换ip防止被封
	$header = array( 
		'CLIENT-IP:'.$ip,
		'X-FORWARDED-FOR:'.$ip
	); 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true); 
	$page_content = curl_exec($ch); 
	curl_close($ch);
	return $page_content;
}
//返回随机国内ip
function get_rand_ip(){
  $arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
  $randarr= mt_rand(0,count($arr_1)-1);
  $ip1id = $arr_1[$randarr];
  $ip2id=  round(rand(600000,  2550000)  /  10000);
  $ip3id=  round(rand(600000,  2550000)  /  10000);
  $ip4id=  round(rand(600000,  2550000)  /  10000);
  return  $ip1id . "." . $ip2id . "." . $ip3id . "." . $ip4id;
}

/**  
* curl 多线程  
* @author http://www.lai18.com 
* @param array $array 并行网址  
* @param int $timeout 超时时间 
* @return mix  
*/  
function Curl_http($array,$timeout='15'){
	$res = array();  

	$mh = curl_multi_init();//创建多个curl语柄  
	foreach($array as $k=>$url){    
		$ip = get_rand_ip(); //每次请求都切换ip防止被封
		$header = array( 
			'CLIENT-IP:'.$ip,
			'X-FORWARDED-FOR:'.$ip
		); 
		$conn[$k]=curl_init($url);//初始化  

		//curl_setopt($conn[$k], CURLOPT_TIMEOUT, $timeout);//设置超时时间  
		curl_setopt($conn[$k], CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');  
		curl_setopt($conn[$k], CURLOPT_MAXREDIRS, 7);//HTTp定向级别 ，7最高  
		curl_setopt($conn[$k], CURLOPT_HTTPHEADER, $header);//这里不要header，加块效率
		curl_setopt($conn[$k], CURLOPT_FOLLOWLOCATION, 1); // 302 redirect  
		curl_setopt($conn[$k], CURLOPT_RETURNTRANSFER,TRUE);//要求结果为字符串且输出到屏幕上            
		curl_setopt($conn[$k], CURLOPT_HTTPGET, true);  
        curl_setopt($conn[$k], CURLOPT_URL, $url);
		curl_multi_add_handle ($mh,$conn[$k]);  
	}
	//防止死循环耗死cpu 这段是根据网上的写法  
	/*do{  
		$mrc = curl_multi_exec($mh,$active);//当无数据，active=true  
	}while ($mrc == CURLM_CALL_MULTI_PERFORM);//当正在接受数据时  
	while ($active and $mrc == CURLM_OK) {//当无数据时或请求暂停时，active=true  
	  	if(curl_multi_select($mh) != -1) {  
			do {  
			  $mrc = curl_multi_exec($mh, $active);  
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);  
	    }  
	}   */

	do{
        curl_multi_exec($mh, $active);
    } while ($active);

    $active = null;
  
  	foreach ($array as $k => $url) {  
		if(!curl_errno($conn[$k])){  
			$data[$k]=curl_multi_getcontent($conn[$k]);//数据转换为array  
			$header[$k] = curl_getinfo($conn[$k]);//返回http头信息  
			curl_close($conn[$k]);//关闭语柄  
			curl_multi_remove_handle($mh, $conn[$k]);   //释放资源   
		}else{  
			unset($k,$url);  
		}  
	}
    curl_multi_close($mh);  
	return $data;
}  