<?php
/**
 * TOP API: alibaba.geoip.get request
 * 
 * @author auto create
 * @since 1.0, 2015.08.20
 */
class AlibabaGeoipGetRequest
{
	/** 
	 * 要查询的IP地址,与language一起使用，与iplist二选一使用，提供单个IP查询
	 **/
	private $ip;
	
	/** 
	 * 返回结果的文字语言，cn中文；en英文
	 **/
	private $language;
	
	private $apiParas = array();
	
	public function setIp($ip)
	{
		$this->ip = $ip;
		$this->apiParas["ip"] = $ip;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function setLanguage($language)
	{
		$this->language = $language;
		$this->apiParas["language"] = $language;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function getApiMethodName()
	{
		return "alibaba.geoip.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->ip,"ip");
		RequestCheckUtil::checkNotNull($this->language,"language");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
