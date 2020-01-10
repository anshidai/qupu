<?php 

namespace app\components\helper;

/**
* 字符串处理类
*/
class StringHelper
{
	
	/**
     * 检查字符串是否是UTF8编码
     * @param string $string 字符串
     * @return boolean
     */
	public static function isUtf8($str)
	{
		$c=0; $b=0; $bits = 0;
        $len = strlen($str);
        for($i=0; $i<$len; $i++) {
            $c = ord($str[$i]);
            if($c > 128) {
                if(($c >= 254)) return false;
                elseif($c >= 252) $bits=6;
                elseif($c >= 248) $bits=5;
                elseif($c >= 240) $bits=4;
                elseif($c >= 224) $bits=3;
                elseif($c >= 192) $bits=2;
                else return false;
                if(($i+$bits) > $len) return false;
                while($bits > 1) {
                    $i++;
                    $b=ord($str[$i]);
                    if($b < 128 || $b > 191) return false;
                    $bits--;
                }
            }
        }
        return true;
	}
	
	/**
     * 字符串截取，支持中文和其他编码
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    public static function msubstr($str, $start = 0, $length, $charset = 'utf-8', $suffix = true) 
	{
        if(function_exists('mb_substr'))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice =  implode('',array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }
	
	/**
     * 产生随机字串，可用来自动生成密码
     * 默认长度6位 字母和数字混合 支持中文
     * @param string $len 长度
     * @param string $type 字串类型 0 字母 1 数字 其它 混合
     * @param string $addChars 额外字符
     * @return string
     */
    public static function randString($len = 6, $type = '', $addChars = '') 
	{
        $str ='';
        switch($type) {
            case 0:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
                break;
            case 1:
                $chars = str_repeat('0123456789', 3);
                break;
            case 2:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
                break;
            case 3:
                $chars = 'abcdefghijklmnopqrstuvwxyz'.$addChars;
                break;
            case 4:
                $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
                break;
            default :
                // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
                $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
                break;
        }
        if($len>10) {//位数过长重复字符串一定次数
            $chars = $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
        }
        if($type!=4) {
            $chars = str_shuffle($chars);
            $str = substr($chars,0,$len);
        }else{
            // 中文随机字
            for($i=0; $i<$len; $i++) {
              $str .= self::msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1,'utf-8',false);
            }
        }
        return $str;
    }
	
	/**
     * 生成一定数量的随机数，并且不重复
     * @param integer $number 数量
     * @param string $len 长度
     * @param string $type 字串类型 0 字母 1 数字 其它 混合
     * @return string
     */
    public static function buildCountRand($number, $length = 4, $mode = 1) 
	{
		//不足以生成一定数量的不重复数字
		if($mode==1 && $length<strlen($number)) {
			return false;
		}
		$rand = array();
		for($i=0; $i<$number; $i++) {
			$rand[] = self::randString($length,$mode);
		}
		$unqiue = array_unique($rand);
		if(count($unqiue)==count($rand)) {
			return $rand;
		}
		$count = count($rand) - count($unqiue);
		for($i=0; $i<$count*3; $i++) {
			$rand[] = self::randString($length,$mode);
		}
		$rand = array_slice(array_unique($rand),0,$number);
		return $rand;
    }
    
    /**
    * 生成不重复字符串(类似短网址) 
    */
    public static function uniqueShortRand($length = 6)
    {
         $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
         $string = self::buildCountRand(1, $length, 0);
         $string = is_array($string)? current($string): $string;
         for(; $length>=1; $length--) {
            $position = rand() % strlen($chars);
            $position2 = rand() % strlen($string);    
            $string = substr_replace($string, substr($chars, $position, 1), $position2, 0);
         }
         return $string;  
    }
	
	/**
     * 获取一定范围内的随机数字 位数不足补零
     * @param integer $min 最小值
     * @param integer $max 最大值
     * @return string
     */
    public static function randNumber($min, $max) 
	{
		return sprintf("%0".strlen($max)."d", mt_rand($min,$max));
    }
	
	/**
     * 对数据进行编码转换
     * @param string|array $data 需要转换数据
     * @param string $output 转换后编码
     */
	public static function StrToiconv($data, $output = 'utf-8')
	{
		$encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');  
		$encoded = mb_detect_encoding($data, $encode_arr);
		if(!is_array($data)) {  
			return mb_convert_encoding($data, $output, $encoded);  
		} else {
			foreach($data as $key=>$val) {  
				$key = self::StrToiconv($key, $output);  
				if(is_array($val)) {  
					$data[$key] = self::StrToiconv($val, $output);  
				} else {  
					$data[$key] = mb_convert_encoding($data, $output, $encoded);  
				}  
			}
			return $data;
		}
	}
	
	/**
	* 过滤字符串中特殊空格或空白， 通常用trim, str_replace(' ', '', $str)也无法替换的
	* 原理： 先把字符串json_encode, 能看到特殊字符 \u3000 是特殊空格或空白， 然后替换成空， 最后json_decode还原
	*/
	public static function replaceSpecialNull($str)
	{
		$str = str_replace('\u3000', '', json_encode($str));
		
		return json_decode($str);
	}
	
	/** 
	* 中文截取2，单字节截取模式
	* @param string  $str  需要截取的字符串
	* @param int  $slen  截取的长度
	* @param int  $startdd  开始标记处
	* @param string $output 转换后编码
	* @return string
	*/ 
	public static function strToSubstr($str, $slen, $startdd = 0, $output = 'utf-8')
	{
		if($output == 'utf-8') {
            return self::strSubstrUtf8($str, $slen, $startdd);
        }
        $restr = '';
        $c = '';
        $str_len = strlen($str);
        if($str_len < $startdd+1) {
            return '';
        }
        if($str_len < $startdd + $slen || $slen==0) {
            $slen = $str_len - $startdd;
        }
        $enddd = $startdd + $slen - 1;
        for($i=0;$i<$str_len;$i++) {
            if($startdd==0) {
                $restr .= $c;
            }
            else if($i > $startdd) {
                $restr .= $c;
            }

            if(ord($str[$i])>0x80) {
                if($str_len>$i+1) {
                    $c = $str[$i].$str[$i+1];
                }
                $i++;
            } else{
                $c = $str[$i];
            }

            if($i >= $enddd) {
                if(strlen($restr)+strlen($c)>$slen) {
                    break;
                } else {
                    $restr .= $c;
                    break;
                }
            }
        }
		
        return $restr; 
	} 
	
	/**
	 * utf-8中文截取，单字节截取模式
	 * @param string  $str  需要截取的字符串
	 * @param int  $slen  截取的长度
	 * @param int  $startdd  开始标记处
	 * @return string
	 */
	public static function strSubstrUtf8($str, $length, $start = 0)
    {
        if(strlen($str) < $start+1) {
            return '';
        }
        preg_match_all("/./su", $str, $ar);
        $str = '';
        $tstr = '';

        //为了兼容mysql4.1以下版本,与数据库varchar一致,这里使用按字节截取
        for($i=0; isset($ar[0][$i]); $i++) {
            if(strlen($tstr) < $start) {
                $tstr .= $ar[0][$i];
            } else {
                if(strlen($str) < $length + strlen($ar[0][$i])) {
                    $str .= $ar[0][$i];
                } else {
                    break;
                }
            }
        }
		
        return $str;
    }
	
	/**
     * 获取中文或字母的首字母
     * @param string $str 
     */
	public static function getFirstCharter($str) 
	{ 
		if(empty($str)) {
			return '';
		}
        $fchar = ord($str{0});
		if($fchar >= ord('A') && $fchar <= ord('z'))
			return strtoupper($str{0});
		$s1 = iconv('UTF-8', 'gb2312', $str);
		$s2 = iconv('gb2312', 'UTF-8', $s1);
		$s = $s2 == $str? $s1: $str;
		
		$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
		if ($asc >= -20319 && $asc <= -20284)
			return 'A';
		if ($asc >= -20283 && $asc <= -19776)
			return 'B';
		if ($asc >= -19775 && $asc <= -19219)
			return 'C';
		if ($asc >= -19218 && $asc <= -18711)
			return 'D';
		if ($asc >= -18710 && $asc <= -18527)
			return 'E';
		if ($asc >= -18526 && $asc <= -18240)
			return 'F';
		if ($asc >= -18239 && $asc <= -17923)
			return 'G';		
		if ($asc >= -17922 && $asc <= -17418)
			return 'H';	
		if ($asc >= -17417 && $asc <= -16475)
			return 'J';
		if ($asc >= -16474 && $asc <= -16213)
			return 'K';
		if ($asc >= -16212 && $asc <= -15641)
			return 'L';
		if ($asc >= -15640 && $asc <= -15166)
			return 'M';
		if ($asc >= -15165 && $asc <= -14923)
			return 'N';
		if ($asc >= -14922 && $asc <= -14915)
			return 'O';
		if ($asc >= -14914 && $asc <= -14631)
			return 'P';
		if ($asc >= -14630 && $asc <= -14150)
			return 'Q';
		if ($asc >= -14149 && $asc <= -14091)
			return 'R';
		if ($asc >= -14090 && $asc <= -13319)
			return 'S';
		if ($asc >= -13318 && $asc <= -12839)
			return 'T';
		if ($asc >= -12838 && $asc <= -12557)
			return 'W';
		if ($asc >= -12556 && $asc <= -11848)
			return 'X';
		if ($asc >= -11847 && $asc <= -11056)
			return 'Y';
		if ($asc >= -11055 && $asc <= -10247)
			return 'Z';
		
		return '';
	} 



    /**
     * 获取汉字拼音首字母
     * @param string $str 
     */
    public static function getFirstPinyin($str)
    {  //获取整条字符串汉字拼音首字母
        $ret = "";
        $s1 = iconv("UTF-8", "gb2312", $str);
        $s2 = iconv("gb2312", "UTF-8", $s1);
        if ($s2 == $str) {
            $str = $s1;
        }
        for ($i = 0; $i < strlen($str); $i++) {
            $s1 = substr($str, $i, 1);
            $p = ord($s1);
            if ($p > 160) {
                $s2 = substr($str,$i++,2);
                $ret .= self::getFirstCharter($s2);
            } else {
                $ret .= $s1;
            }
        }
        return $ret;
    }
	
	//生成guid字符串
	public static function guid($separated = true) 
	{
		if(function_exists('com_create_guid')) {
			$charid = substr(strtolower(com_create_guid()), 1, - 1);
			if(!$separated) {
				return str_replace('-', '', $charid);
			} else{
				return $charid;
			}
		} else {
			//生成一个随机的md5串, 然后通过分割来 获得guid
			mt_srand((double) microtime () * 10000);
			$charid = md5(uniqid(rand(), true));
		}
		
		if(!$separated) {
			return $charid;
		}
		$hyphen = chr(45);
		$uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
		
		return $uuid;
	}
	
	
	/**
	* 过滤html所有标签
	*/
	public static function deleteHtmlTag($str = '', $element = array())
	{
		static $strTxt = '';
        $strTxt = trim($str);
        $strTxt = preg_replace('/<\?|\?'.'>/', '', $strTxt); //完全过滤动态代码

        if(empty($element)) {
            $element = array('script', 'html', 'head', 'meta', 'link', 'base', 'body', 'title','style', 'form', 'iframe', 'frame', 'frameset', 'a', 'p', 'span', 'strong', 'img', 'div', 'ul', 'ol', 'li');
        }

        foreach($element as $ele) {
            if(in_array($ele, array('a', 'p', 'span', 'strong', 'img', 'div', 'ul', 'ol', 'li'))) {
                $upperEle = strtoupper($ele);
                $strTxt = preg_replace("/<\/?(".$ele."|".$upperEle.")[^><]*>/i", '', $strTxt);
            }elseif(in_array($ele, array('classstyle'))) {
                $strTxt = preg_replace("/style=.+?[*|\"]/i", '', $strTxt);
            }else {
                $strTxt = preg_replace("/<\/?".$ele."[^><]*>/i", '', $strTxt);
            }
        }

        //过滤多余html
        //$strTxt = preg_replace('/<\/?(html|head|meta|link|base|body|title|style|script|form|iframe|frame|frameset|p|img)[^><]*>/i','',$strTxt);
        
        // $str = preg_replace('/<\/?html[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?head[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?meta[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?link[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?base[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?body[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?title[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?style[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?script[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?form[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?iframe[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?frame[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?frameset[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?(p|P)[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?(span|SPAN)[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?(strong|STRONG)[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?(img|IMG)[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?(div|DIV)[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?(ul|UL)[^><]*>/i', '', $str);
        // $str = preg_replace('/<\/?(li|LI)[^><]*>/i', '', $str);
        
        //过滤on事件lang js
        /*
        while(preg_match('/(<[^><]+)(lang|onfinish|onmouse|onexit|onerror|onclick|onkey|onload|onchange|onfocus|onblur)[^><]+/i', $strTxt, $mat)){
            $strTxt = str_replace($mat[0], $mat[1], $strTxt);
        }
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $strTxt, $mat)) {
            $strTxt = str_replace($mat[0], $mat[1].$mat[3], $strTxt);
        }
        */
		
		return $strTxt;
	}
	
	/**
    * 字符串截取方式获取指定区域内容 
    * @param string $startTag 开始区域
    * @param string $endTag 结束区域
	* @param string $content 在该字符串中进行查找
    */
    public static function contentSubstr($startTag, $endTag, $content)
    {
        $startlen = strpos($content, $startTag);
        if($startlen === false) {
            return '';
        }
        $startlen = $startlen + strlen($startTag);
        $endLen = strpos($content, $endTag, $startlen);
		if($endLen === false) {
			return '';
		}
        return trim(substr($content, $startlen, $endLen - $startlen));  
    }
	
	/**
    * 正则截取内容 
    * @param string $pattern 正则表达式
    * @param string $content 在该字符串中进行查找
    * @param int $pos 对应正则获取位置
    */
    public static function contentPreg($pattern, $content, $pos = 1)
    {
        $data = '';
        if(preg_match($pattern, $content, $match)) {
            $data = trim($match[$pos]);
        }
        return $data;
    }

    /**
    * 过滤非utf8字符
    */
    public static function filterUtf8($str)
    {
        if(!empty($str)) {
            //先把正常的utf8替换成英文逗号
            $result = preg_replace('%(
                [\x09\x0A\x0D\x20-\x7E]
                | [\xC2-\xDF][\x80-\xBF]
                | \xE0[\xA0-\xBF][\x80-\xBF]
                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
                | \xED[\x80-\x9F][\x80-\xBF]
                | \xF0[\x90-\xBF][\x80-\xBF]{2}
                | [\xF1-\xF3][\x80-\xBF]{3}
                | \xF4[\x80-\x8F][\x80-\xBF]{2}
                )%xs',',',$str);
            
            //转成字符数字
            $charArr = explode(',', $result);
            
            //过滤空值、重复值以及重新索引排序
            $findArr = array_values(array_flip(array_flip(array_filter($charArr))));

            $str = $findArr ? str_replace($findArr, "", $str) : $str;
        }

        $str = preg_replace('/[\x00-\x08\x0b-\x0c\x0e-\x1f\x7f]/', '', $str);
        
        return $str;
    }

    /**
    * 替换特殊符号
    */
    public static function replaceSymbol($content)
    {
        $findStr = ['&nbsp;', '&quot;', '&amp;','&lt;','&gt','&rdquo;','&ldquo;','&hellip;','&mdash;'];
        $replaceStr = [' ', '"', '&', '<', '>', '”', '“', '…', '—'];
        $content = str_replace($findStr, $replaceStr, $content);
        $content = trim($content);

        return $content;
    }
	
    /**
    * 删除空格和换行
    */
    public static function clearTrim($content)
    {
        $content = str_replace(['   ','　',' ',"\r\n","\n"], '', $content);
        $content = trim($content);

        return $content;
    }

    /**
    * 向一个字符串随机插入一个字符串
    * @param string $oldstr 老字符串
    * @param string $instr 插入的字符串
    * @param string $encoding 字符串编码
    */
    public static function randInStr($oldstr, $instr, $encoding = 'utf-8')
    {
        $len = mb_strlen($oldstr, $encoding);
        $insert_point = mt_rand(1, $len - 1);
        $pre_str = mb_substr($oldstr, 0, $insert_point, $encoding);
        $after_str = mb_substr($oldstr, $insert_point, $len - $insert_point, $encoding);
        $newstr = $pre_str.$instr.$after_str;

        return $newstr;
    }

    /**
    * [字符串转换为(2,8,16进制)ASCII码]
    * @param string $str   [待处理字符串]
    * @param boolean $encode [字符串转换为ASCII|ASCII转换为字符串]
    * @param string $intType [2,8,16进制标示]
    * @return string byte_str [处理结果]
    */
    public static function strtoascii($str, $encode = true, $intType = '2')
    {
        if ($encode == true) {
            $byte_array = str_split($str);
            foreach ($byte_array as &$value) {
                $value = ord($value);
                switch ($intType) {
                    case 16:
                        $value = sprintf("%02x", $value);
                        break;
                    case 8:
                        $value = sprintf("%03o", $value);
                        break;
                    default:
                        $value = sprintf("%08b", $value);
                        break;
                }
            }
            unset($value);
            $byte_str = implode('', $byte_array);

        } else {
            $chunk_size = $intType == 16 ? 2 : ($intType == 8 ? 3 : 8);
            $byte_array = chunk_split($str, $chunk_size);
            $byte_array = array_filter(explode("\r\n", $byte_array));

            foreach ($byte_array as &$value) {
                $fun_name = $intType == 16 ? 'hexdec' : ($intType == 8 ? 'octdec' : 'bindec');
                $value = $fun_name($value);
                $value = chr($value);
            }

            unset($value);
            $byte_str = implode('', $byte_array);
        }

        return $byte_str;
    }  

    /**
    * 判断字符串中是否含有中文
    */
    public static function checkHasChinese($str)
    {
        $halt = false;

        if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $str, $match)) {
            $halt = true;
        }

        return $halt;
    }

    /**
    * 将字符串分隔成数组
    */
    public static function mbStrSplit($str)
    {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }

}
