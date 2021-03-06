<?php
/** 
 * 截取处理UTF-8编码字符串 
 * 规则：中文2个字符，数字、英文1个字符；截取末尾不足一个汉字的则舍弃。 
 * @param string $str 
 * @param int    $len 截取出的字符长度 
 * @author flyer0126 
 * @since 2012/05/03 
 */  
function substr_utf8_cn($str, $len)  
{  
    $length  = strlen($str);  
    if ($length <=  $len)  
    {  
        return $str;  
    }  
  
    $result_str = '';  
    for($i=0;$i<$len;$i++)  
    {  
        $temp_str=substr($str,0,1);  
        if(ord($temp_str) > 127)  
        {  
            if($i+1<$len)  
            {  
                $result_str .= substr($str,0,3);  
                $str = substr($str,3);  
            }
            $i++;
        }
        else  
        {  
            $result_str .= substr($str,0,1);  
            $str=substr($str,1);
        }  
    }  
  
    return $result_str;  
}  

/**
* 多图上传方法
* @param Array $_FILES 上传的图片信息
* @return Array
*/
function upLoads(){
    $upload = new \Think\Upload();// 实例化上传类
    // 开启子目录保存 并以日期（格式为Ymd）为子目录
    $upload->autoSub = true;
    $upload->subName = date("Ymd");
    $upload->maxSize = 3145728 ;// 设置附件上传大小
    $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->rootPath = $_SERVER['DOCUMENT_ROOT'].'/Public/attached/image/'; // 设置附件上传根目录
    $upload->saveName = date("YmdHis").'_'.rand(111111,999999);
    // 上传文件 
    $info = $upload->upload();
    if(!$info) {// 上传错误提示错误信息
        $da = false;
    }else{// 上传成功 获取上传文件信息
        foreach($info as $file){
            $da[] = '/Public/attached/image/'.$file['savepath'].$file['savename'];
        }
    }
    return $da;
}

//单图上传方法
function upLoadOne($files){
	$upload = new \Think\Upload();// 实例化上传类
    // 开启子目录保存 并以日期（格式为Ymd）为子目录
	$upload->autoSub = true;
	$upload->subName = date("Ymd");
	$upload->maxSize = 3145728 ;// 设置附件上传大小
	$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	$upload->rootPath = './Public/attached/image/'; // 设置附件上传根目录
	$upload->saveName = date("YmdHis").'_'.rand(111111,999999);
    // 上传单个文件 
    $info   =   $upload->uploadOne($_FILES[$files]);
    if(!$info) {// 上传错误提示错误信息
        $da['msg'] = $upload->getError();
        $da['status'] = 'error';
    }else{// 上传成功 获取上传文件信息
        $da['img_url'] = '/Public/attached/image/'.$info['savepath'].$info['savename'];
        $da['status'] = 'ok';
    }
    return $da;
}

/**
 * 图片压缩处理
 * @Author   slz@yujia.com
 * @DateTime 2017-06-29T13:39:32+0800
 * @param    string                   $img_url [图片地址]
 * @param    array                   $size  [压缩配置]
 * @return   string                            [返回压缩后的图片地址]
 */
function thumb_image($img_url,$size){
    $image = new \Think\Image(); 
    $sub = substr($img_url,0,-4).$size[0].'*'.$size[1].'.jpg';
    $image->open('.'.$img_url)->thumb($size[0],$size[1])->save('.'.$sub);          
    return $sub;
}

/**
 * 删除原图
 * @Author   slz@yujia.com
 * @DateTime 2017-06-29T17:45:28+0800
 * @param    [type]                   $img_url [description]
 * @return   [type]                            [description]
 */
function del_image($img_url){
    $image = new \Think\Image();
    $sub = substr($img_url,0,-4).'w.jpg';
    $image->open('.'.$img_url)->save('.'.$sub); 
    if($sub){
        unlink('.'.$img_url);
    }
    return $sub;
}

//数组元素删除函数
function remove_array($ys,$arr){
	$k = array_search($ys,$arr);
	if ($k !== false){
		array_splice($arr,$k,1);
	}
	return $arr;
}

//添加sku数组详情
function add_sku_item($datas,$goods_id){
    $goods_sku = M('goods_sku');
    foreach (json_decode($datas,true)  as $key => $value) {
        $reqs_skuall[$key]['goods_id'] = $goods_id;
        $reqs_skuall[$key]['skus_id'] = $value['path'];
        $reqs_skuall[$key]['price'] = $value['price'];
        $reqs_skuall[$key]['repertory'] = $value['num'];
    }
    $ret = $goods_sku->addAll($reqs_skuall);
    return $ret ? true : false;
}

//添加系统消息
function send_msg($receiver_id,$type,$content,$tips){
    $user_msg = M('user_msg');
    if ($receiver_id && $type && $content) {
        $data['receiver_id'] = $receiver_id;
        $data['type'] = $type;
        $data['content'] = $content;
        $data['tips'] = $tips;
        $data['creat_time'] = date('Y-m-d H:i:s');
        if ($user_msg->add($data)) {
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/**
 * 功能：生成二维码
 * @param string $qr_data   手机扫描后要跳转的网址
 * @param string $qr_level  默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
 * @param string $qr_size   二维码图大小，1－10可选，数字越大图片尺寸越大
 * @param string $save_path 图片存储路径
 * @param string $save_prefix 图片名称前缀
 */
function createQRcode($filename,$save_path,$qr_data='PHP QR Code :)',$logo = "",$qr_level='L',$qr_size=4,$save_prefix='qrcode'){
    if(!isset($save_path)) return '';
    //设置生成png图片的路径
    $PNG_TEMP_DIR = & $save_path;
    //导入二维码核心程序
    vendor('PHPQRcode.class#phpqrcode');  //注意这里的大小写哦，不然会出现找不到类，PHPQRcode是文件夹名字，class#phpqrcode就代表class.phpqrcode.php文件名
    //检测并创建生成文件夹
    if (!file_exists($PNG_TEMP_DIR)){
        //mkdir($PNG_TEMP_DIR);
        mkdir($PNG_TEMP_DIR,0755,true);
    }
    //$filename = $PNG_TEMP_DIR.'test.png';
    $errorCorrectionLevel = 'L';
    if (isset($qr_level) && in_array($qr_level, array('L','M','Q','H'))){
        $errorCorrectionLevel = & $qr_level;
    }
    $matrixPointSize = 4;
    if (isset($qr_size)){
        $matrixPointSize = & min(max((int)$qr_size, 1), 10);
    }

    if (isset($qr_data)) {
        if (trim($qr_data) == ''){
            die('data cannot be empty!');
        }
        //生成文件名 文件路径+图片名字前缀+md5(名称)+.png
        $filename = $PNG_TEMP_DIR.$save_prefix.md5($filename).'.jpg';
        $filenameLogo = $PNG_TEMP_DIR.$save_prefix.md5($filename).'_l.jpg';
        //开始生成
        QRcode::png($qr_data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    } else {
        //默认生成
        QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }
    if(file_exists($PNG_TEMP_DIR.basename($filename))){
        //合并logo
        if($logo){
            mergeImage($filename,$logo,$filenameLogo,array('align'=>'center'));
            return basename($filenameLogo);
        }

        return basename($filename);
    }else{
        return FALSE;
    }
}
if(!function_exists('convert2Word')){
    function convert2Word($data,$srcFile,$distFile,$qrcode=null){
       vendor("PhpWord.bootstrap");
        ///require_once $vendorDirPath.'/bootstrap.php';
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($srcFile);
        if($data && $srcFile && $distFile){
            foreach ($data as $key => $value) {
                $templateProcessor->setValue($key, $value);
            }
            $templateProcessor->saveAs($distFile);
        }
    }
}
if(!function_exists('setQrcode')){
    function setQrcode($word,$qrcode,$newword){
        vendor("PhpWord.bootstrap");
        $phpword = \PhpOffice\PhpWord\IOFactory::load($word);
        $section = $phpword->addSection();
        $imageStyle = array('width'=>110, 'height'=>110, 'position' => 'absolute', 'top' => -108, 'left' => 480, 'zIndex' => 4);

        $section->addImage($qrcode,$imageStyle);  
        //生成文件  
        $wordWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpword, "Word2007");  
        $wordWriter->save($newword);
    }
}

if(!function_exists('http_request')){
    function http_request( $url , $params = array(), $method = 'GET' , $multi = false, $extheaders = array()){
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ci, CURLOPT_TIMEOUT, 3);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_FAILONERROR, false);
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
            curl_setopt($ci, CURLOPT_HEADER, true);
        }else{
            curl_setopt($ci, CURLOPT_HEADER, false);
        }
        if (FALSE === strpos("$".$url, "https://"))
        {
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        }
        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }
}
//windows
if(!function_exists('word2pdf')){
	function word2pdf($root_path,$docUrl,$centreno){  
		$ext = '.pdf';
		
		$baseinfo = pathinfo($docUrl);
		$file = $baseinfo['filename'];
		$path = $baseinfo['dirname'];
		$srcUrl = $root_path.$docUrl;
		$outDir = $root_path . $path;
		$distFile = $path . '/' . $centreno . $ext;
		if(file_exists($root_path . $distFile)){
			@unlink($root_path . $distFile);
		}
			
		$word = new COM("Word.Application") or die ("Could not initialise Object.");  
		$word->Visible = 0;  
		$word->DisplayAlerts = 0;  
		$word->Documents->Open($srcUrl);  
		// save it as word 2003  
		//  $word->ActiveDocument->SaveAs('4.doc');  
		// convert word 2007-2013 to PDF  
		// $pdfname='D:/www/fa/3.pdf';  
		$word->ActiveDocument->ExportAsFixedFormat($root_path . $distFile, 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);  
		// quit the Word process  
		$word->Quit(false);  
		// clean up  
		unset($word);  
		return $distFile;
	}
}
/** user the thirdpart api */
if(!function_exists('convert2Pdf')){
    function convert2Pdf($root_path,$docUrl,$centreno){
		$is_os_win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
		if($is_os_win){
			return word2pdf($root_path,$docUrl,$centreno);
		}else{
			$ext = '.pdf';
			$baseinfo = pathinfo($docUrl);
			$file = $baseinfo['filename'];
			$path = $baseinfo['dirname'];
			$srcUrl = $root_path.$docUrl;
			$outDir = $root_path . $path;
			$distFile = $path . '/' . $centreno . $ext;
			if(file_exists($root_path . $distFile)){
				@unlink($root_path . $distFile);
			}
			$cmd = "soffice --headless --invisible --convert-to pdf:writer_pdf_Export {$srcUrl} --outdir {$outDir} > /dev/null";
			exec($cmd,$output,$return);
			return $distFile;
		}
    }
}
if(!function_exists('convertPdf2Image')){
    function convertPdf2Image($root_path,$pdfFile,$prefix=""){
        vendor("Pdflib.vendor.autoload");

        $baseinfo = pathinfo($pdfFile);
        $file = $baseinfo['filename'];
        $path = $baseinfo['dirname'];
        $srcFile = $root_path.$pdfFile;
        $outDir = $root_path . $path;

        $pdflib = new \ImalH\PDFLib\PDFLib();
        $pdflib->setPdfPath($srcFile);
        $pdflib->setOutputPath($outDir);
        $pdflib->setImageFormat(\ImalH\PDFLib\PDFLib::$IMAGE_FORMAT_JPEG);
        $pdflib->setDPI(300);
        $pdflib->setPageRange(1,$pdflib->getNumberOfPages());
		$pdflib->setPrefix($prefix);

        $filesArray = $pdflib->convert();
        if($filesArray){
            $tmp = array();
            foreach ($filesArray as $value) {
                $tmp[] = $root_path . $path . '/' .$value;
            }
            $filesArray = $tmp;
        }
        return $filesArray;
    }
}
if(!function_exists('convertImageToPdf')){
    function convertImageToPdf($root_path,$pdfFileName,$imagePaths,$scaleWidth=false,$watermark=null){
        vendor("Pdflib.vendor.autoload");
        $pdfLib = new \ImalH\PDFLib\PDFLib();

		$pdfFileName = $root_path.$pdfFileName;
        if(@file_exists($pdfFileName)){
            @unlink($pdfFileName);
        }
		if($scaleWidth){
			//zoom
			$tmp = array();
			$image = new \Think\Image();
			foreach($imagePaths as $val){
				$image->open($val);
				$baseinfo = pathinfo($val);
                $tmpSavefile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-thumb.'.$baseinfo['extension'];
				$watermark && $tmpWmfile = $baseinfo['dirname'] . '/'.$baseinfo['filename'].'-wmthumb.'.$baseinfo['extension'];//加水印平铺

				$width = $image->width(); // 返回图片的宽度
				$height = $image->height();
				$width = $width/$scaleWidth; //取得图片的长宽比  190是要输出的图片的宽度
				$height = ceil($height/$width);
				$image->thumb($scaleWidth,$height)->save($tmpSavefile);
                if($watermark){
                    $image->open($tmpSavefile)->water($watermark,\Think\Image::IMAGE_WATER_FULL,30)->save($tmpWmfile); 
                    $tmp[]=$tmpWmfile;
                }else{
                    $tmp[] = $tmpSavefile;
                }
			}
			$imagePaths = $tmp;
		
		}
        $res = $pdfLib->makePDF($pdfFileName,$imagePaths);
        return $res;
    }
}

if(!function_exists('convert2Png')){
    function convert2Png($root_path,$pdfFile,$ext = "png"){
        $srcFile = $root_path . $pdfFile;
        if(file_exists($srcFile)){
            $baseinfo = pathinfo($pdfFile);
            $outFile = $root_path . $baseinfo['dirname'] . '/' . $baseinfo['filename'] .'.' . $ext;
            $cmd = "convert -density 600 {$srcFile} -alpha off -resize 2480x {$outFile} > /dev/null";
            exec($cmd,$output,$return);
        }
    }
}
if(!function_exists('mergeImage')){
    function mergeImage($bg,$qrcode,$save_file,$position=null){
        if(@file_exists($save_file)){
           @unlink($save_file); 
        }
        $bg = file_get_contents($bg);
        $bg = imagecreatefromstring($bg);
        $bg_width = imagesx($bg);
        $bg_height = imagesy($bg);
        if($position['align']=='center'){
            //居中
            $qrcode = file_get_contents($qrcode);
            $qrcode = imagecreatefromstring($qrcode);
            if(imageistruecolor($qrcode)) imagetruecolortopalette($qrcode, false, 65535);
            $qrcode_width = imagesx($qrcode);
            $qrcode_height = imagesy($qrcode);
            //计算圆角图片的宽高及相对于二维码的摆放位置,将圆角图片拷贝到二维码中央
            $qrcode_qr_width = $qrcode_width/4;
			$qrcode_qr_height = $qrcode_height/4;
            $from_width = ($bg_width-$qrcode_qr_width)/2;
            imagecopyresampled($bg, $qrcode, $from_width, $from_width, 0, 0, $qrcode_qr_width, $qrcode_qr_height, $qrcode_width, $qrcode_height);
        }else{
            // 报告的图片
            $qrcode = file_get_contents($qrcode);
            $qrcode = imagecreatefromstring($qrcode);
            if(imageistruecolor($qrcode)) imagetruecolortopalette($qrcode, false, 65535);
            $qrcode_width = imagesx($qrcode);
            $qrcode_height = imagesy($qrcode);
            
            if($position['x'] && $position['y']){
                $from_x = $position['x'];
                $from_y = $position['y'];
            }else{
                # code...
                $from_x = 8 * ($bg_width - $qrcode_width)/9;
                $from_y = 9 * ($bg_height - $qrcode_height)/10;
            }
            if($position['w'] && $position['h']){
                $bg_qr_width = $position['w'];
                $bg_qr_height = $position['h'];
            }else{
                $bg_qr_height = $bg_qr_width = $bg_width/8;
            }
            
            imagecopyresampled($bg, $qrcode, $from_x, $from_y, 0, 0, $bg_qr_height, $bg_qr_height, $qrcode_width, $qrcode_height);
        }
        imagepng($bg,$save_file);
        imagedestroy($bg);
        //imagedestroy($corner);
        imagedestroy($qrcode);
        return true;
    }
}
if(!function_exists('waterMark')){
    function waterMark($src,$water,$dst,$locate=\Think\Image::IMAGE_WATER_SOUTHEAST,$opacity = 100){
        $image = new \Think\Image();
        $image->open($src)->water($water,$locate,$opacity)->save($dst);
    }
}
//convert -density 600 SJ-4-77_2017.pdf -alpha off  SJ-4-77_2017.png