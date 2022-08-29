<?php 
class CKudaGo
{
	const requestUrl = "https://kudago.com/public-api/";
	const apiVersion = "v1.4";
	
	/* 
	/ функция выполняет запрос на указанный метод api с заданными параметрами
	/ возвращает результат либо null 
	*/
	static function request($method, $arParams = [])
	{
		$requestUrl = self::requestUrl . self::apiVersion . "/$method/" . (!empty($arParams) ? "?" . http_build_query($arParams) : "");

		//self::toLog($requestUrl);

		$arResult = null;
		if($curl = curl_init()) 
		{
			curl_setopt($curl, CURLOPT_URL, $requestUrl);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			$arResult = json_decode(curl_exec($curl), true);
			curl_close($curl);
		}
		return $arResult;
	}
	
	/* 
	/ возвращает список доступных городов 
	*/
	static function getLocations()
	{
		return self::request('locations');
	}
	
	/* 
	/ возвращает псевдоним города по его названию, либо null 
	*/
	static function getCitySlugByName($sCityName)
	{
		$arLocations = self::getLocations();
		foreach($arLocations as $arLocationItem)
			if($arLocationItem['name'] == $sCityName)
				return $arLocationItem['slug'];

		return null;
	}
	
	/* 
	/ возвращает список событий для города по названию 
	*/
	static function getEvents($arLocation)
	{
		$sCitySlug = self::getCitySlugByName($arLocation['city']);
		
		$arEvents = [
			'count' => 0,
		];
		if(!is_null($sCitySlug))
		{
			$arParams = [
				'location' => $sCitySlug, 
				'fields' => 'id,title,short_title,description,dates,images,age_restriction,site_url',
				'actual_since' => time(),
				'text_format' => 'text',
			];
			
			$arEvents = self::request('events', $arParams);
		}
		
		if($arEvents['count'] == 0)
		{
			$arParams = [
				'fields' => 'id,title,short_title,description,dates,images,age_restriction,site_url',
				'actual_since' => time(),
				'text_format' => 'text',
				'lon' => $arLocation['lon'], 
				'lat' => $arLocation['lat'], 
				'radius' => 200000,
			];
			
			$arEvents = self::request('events', $arParams);
			
			$GLOBALS['events_in_radius'] = true;
		}

		self::prepareEventsData($arEvents);

		return $arEvents;
	}
	
	/* 
	/ выполняет обработку полученных данных по событиям 
	*/
	static function prepareEventsData(&$arEvents)
	{
		if($arEvents['count'] > 0)
			$arEvents = $arEvents['results'];
		else
			$arEvents = [];
	
		foreach($arEvents as &$arEventItem)
		{
			// оставим первую ближайшую дату
			$currentTime = time();
			foreach($arEventItem['dates'] as $arDateItem)
			{
				// если событие уже началось, берем сегодняшную дату
				if($currentTime > $arDateItem['start'] && $currentTime < $arDateItem['end'])
				{
					$arEventItem['date'] = strtotime(date("Y-m-d", time()));
					break;
				}
				// если еще не началось - берем дату начала
				if($currentTime < $arDateItem['start'])
				{
					$arEventItem['date'] = $arDateItem['start'];
					break;
				}
			}
			if(isset($arEventItem['date']))
				unset($arEventItem['dates']);

			// оставим одну картинку
			if(isset($arEventItem['images'][0]))
				$arEventItem['image'] = $arEventItem['images'][0]['image'];
			unset($arEventItem['images']);
			
			// сделаем в title первую букву заглавной
			$arEventItem['title'] = self::mb_ucfirst($arEventItem['title']);
			
			// обрежем описание события до первого пробела после 200 символов
			for($i = 200; $i < mb_strlen($arEventItem['description']); $i++)
			{
				if(mb_substr($arEventItem['description'], $i, 1) == ' ')
				{
					$arEventItem['description'] = mb_substr($arEventItem['description'], 0, $i) . "...";
					break;
				}
			}
		}
		unset($arEventItem);

		// сортируем события по дате
		usort($arEvents, function($a, $b){
			return ($a['date'] - $b['date']);
		});
	}
	
	
	/* 
	/ заменяет первую букву в строке на заглавную 
	*/
	static function mb_ucfirst($string)
	{
		$fc = mb_strtoupper(mb_substr($string, 0, 1));
		return $fc.mb_substr($string, 1);
	}

	/* 
	/ логирование 
	*/
	static function toLog($data)
	{
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/log.txt", date("Y-m-d H:i:s") . " | " . print_r($data, true) . "\r\n\r\n", FILE_APPEND);
	}
}