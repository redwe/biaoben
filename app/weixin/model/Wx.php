<?php

namespace fast;
use think\Config;

/**
 * 微信类
 */
class Wx
{
	protected $appid = "wxe617cab422654b35";
	protected $secret = "8c94950ff8561ea07c1009b0727eaf66";
	//获取access_token
	public function getAccessToken(){

		$grant_type = 'client_credential';
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" .$this->appid. "&secret=" .$this->secret;

		$curl = new \Curl();
		$curl->get($url);
		$result = $curl->response;
		return $result;
	}

	//获取小程序二维码
	public function getQrCode($access_token,$user_id){
		$url =  "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=" . $access_token;
		$curl = new \Curl\Curl();
		$path = "pages/distribution/distribution?userid=" . $user_id;
		$curl->post($url,json_encode(['path' => $path,'width' => 430]));
		$result = $curl->response;

		//存储图片
		$filepath = './uploads/qrcode/' . date('Ymd',time()).time().rand(10,99) . '.jpg';

		file_put_contents($filepath, $result);


		return substr($filepath, 1);

		// return $result;

	}

	
	/**
	 *发送模板消息
	 * $access_token 
	 * $params 
	**/
	public function sendWxMsg($access_token,$params = []){

		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=". $access_token;
		$curl = new \Curl\Curl();
		$jsonData = json_encode([
			'touser' => $params['openid'],
			'template_id' => 'fNtdXy4r-9R1vP2wMu107XNZCWNPM7ZiXvetedtVZYA',
			'page' => 'index',
			'form_id' => $params['prepay_id'],
			'data' => [
				'keyword1' => [
					'value' => $params['order_sn'],
					'color' => '#173177'
				],
				'keyword2' => [
					'value' => $params['details'],
					'color' => '#173177'
				],
				'keyword1' => [
					'value' => $params['amount'],
					'color' => '#173177'
				],

				'keyword2' => [
					'value' => date('Y-m-d H:i:s',time()),
					'color' => '#173177'
				],
				
				'keyword1' => [
					'value' => '支付成功',
					'color' => '#173177'
				],
				
			]

		]);

		$curl->post($url,$jsonData);
		$result = $curl->response;
		echo json_encode($result);

	}

	/**
	 *发送优惠券到期提醒
	 * $access_token 
	 * $params 
	**/
	public function sendWxCouponMsg($access_token,$params = []){

		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=". $access_token;
		$curl = new \Curl\Curl();
		$jsonData = json_encode([
				'touser' => $params['openid'],
				'template_id' => 'N3k1W7d3SgLJyHZmhMfS0EQITuFIvuzRsGabRHaiO6c',
				'page' => 'index',
				'form_id' => $params['form_id'],
				'data' => [
					'keyword1' => [
						'value' => $params['name'],
						'color' => '#173177'
					],
					'keyword2' => [
						'value' => $params['endtime'],
						'color' => '#173177'
					],
					'keyword3' => [
						'value' => $params['notice'],
						'color' => '#173177'
					],
				]
			]

		);

		$curl->post($url,$jsonData);
		$result = $curl->response;
		echo json_encode($result);

	}



}