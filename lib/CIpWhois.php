<?php 

class CIpWhois
{
	const requestUrl = "http://ipwho.is/";
	
	static function getLocationData($ip)
	{
		$arResult = null;
		if($curl = curl_init()) 
		{
			curl_setopt($curl, CURLOPT_URL, self::requestUrl . $ip . "?lang=ru");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			$arResult = json_decode(curl_exec($curl), true);
			curl_close($curl);
		}
		
		if($arResult['success'])
		{
			$arResult = [
				'city' => $arResult['city'], 
				'lat' => $arResult['latitude'],
				'lon' => $arResult['longitude'],
			];
			return $arResult;
		}
		
		return null;
	}
/*	
	static function getLocationCity($ip)
	{
		$arLocationData = self::getLocationData($ip);
		if($arLocationData['success'])
			return $arLocationData['city'];
		
		return null;
	}
*/
}