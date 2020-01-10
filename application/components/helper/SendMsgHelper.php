<?php 

namespace app\components\helper;

use app\components\helper\HttpHelper;

/**
* 推送消息类
*/
class SendMsgHelper
{
	//又拍云短信模板id
	const UPYUN_TEMPID_EXCEPTION = 1713; //网站打不开异常
	const UPYUN_TEMPID_TRYAPPLY = 1776; //申请报名
	const UPYUN_TEMPID_LIUXUEAPPLY = 1832; //申请留学报名

	/**
	* 又拍云推送短信
	* @param string|array $phone 手机号，多个用英文逗号隔开
	* @param int $templateid 模板id
	* @param string $vars 短信参数，短信参数（以 | 分隔）
	*/
	public static function sendSmsByUpyun($phone, $templateid, $vars = '')
	{
		if(empty($phone)) {
			return false;
		}
		if(is_array($phone)) {
			$phone = implode(',', $phone);
		}

		$smsApi = 'https://sms-api.upyun.com/api/messages';
		$post = array(
			'mobile' => $phone,
			'template_id' => $templateid, //模板id
			'vars' => $vars,
		);
		$params['header'][] = "Content-type: application/x-www-form-urlencoded\r\nAuthorization: C5c2m7QwcAg8ST2M69AHxAUs38caTV";
		$params['ssl'] = true;

		$res = HttpHelper::curlPost($smsApi, http_build_query($post), $params);
		writeRecordLog($res, 'sms.log');

		return $res;
	}

}