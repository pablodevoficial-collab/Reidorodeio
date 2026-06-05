<?php

namespace App\Lib;

class ClientInfo{

    /**
    * Get requestor IP information with scalar values
    *
    * @return array
    */
	public static function ipInfo()
	{
	    $ip = getRealIP();

	    $xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip) ?: null;

	    $country = $xml ? (string) @$xml->geoplugin_countryName : null;
	    $city = $xml ? (string) @$xml->geoplugin_city : null;
	    $area = $xml ? (string) @$xml->geoplugin_areaCode : null;
	    $code = $xml ? (string) @$xml->geoplugin_countryCode : null;
	    $longitude = $xml ? (string) @$xml->geoplugin_longitude : null;
	    $latitude = $xml ? (string) @$xml->geoplugin_latitude : null;

	    return [
	        'country' => $country ?: null,
	        'city' => $city ?: null,
	        'area' => $area ?: null,
	        // Keep both keys for backward compatibility
	        'code' => $code ?: null,
	        'countryCode' => $code ?: null,
	        'long' => $longitude ?: null,
	        'lon' => $longitude ?: null,
	        'lat' => $latitude ?: null,
	        'ip' => $ip,
	        'time' => date('Y-m-d h:i:s A'),
	    ];
	}

    /**
    * Get requestor operating system information
    *
    * @return array
    */
	public static function osBrowser(){
	    $userAgent = $_SERVER['HTTP_USER_AGENT'];
	    $osPlatform = "Unknown OS Platform";
	    $osArray = array(
	        '/windows nt 10/i' => 'Windows 10',
	        '/windows nt 6.3/i' => 'Windows 8.1',
	        '/windows nt 6.2/i' => 'Windows 8',
	        '/windows nt 6.1/i' => 'Windows 7',
	        '/windows nt 6.0/i' => 'Windows Vista',
	        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
	        '/windows nt 5.1/i' => 'Windows XP',
	        '/windows xp/i' => 'Windows XP',
	        '/windows nt 5.0/i' => 'Windows 2000',
	        '/windows me/i' => 'Windows ME',
	        '/win98/i' => 'Windows 98',
	        '/win95/i' => 'Windows 95',
	        '/win16/i' => 'Windows 3.11',
	        '/macintosh|mac os x/i' => 'Mac OS X',
	        '/mac_powerpc/i' => 'Mac OS 9',
	        '/linux/i' => 'Linux',
	        '/ubuntu/i' => 'Ubuntu',
	        '/iphone/i' => 'iPhone',
	        '/ipod/i' => 'iPod',
	        '/ipad/i' => 'iPad',
	        '/android/i' => 'Android',
	        '/blackberry/i' => 'BlackBerry',
	        '/webos/i' => 'Mobile'
	    );
	    foreach ($osArray as $regex => $value) {
	        if (preg_match($regex, $userAgent)) {
	            $osPlatform = $value;
	        }
	    }
	    $browser = "Unknown Browser";
	    $browserArray = array(
	        '/msie/i' => 'Internet Explorer',
	        '/firefox/i' => 'Firefox',
	        '/safari/i' => 'Safari',
	        '/chrome/i' => 'Chrome',
	        '/edge/i' => 'Edge',
	        '/opera/i' => 'Opera',
	        '/netscape/i' => 'Netscape',
	        '/maxthon/i' => 'Maxthon',
	        '/konqueror/i' => 'Konqueror',
	        '/mobile/i' => 'Handheld Browser'
	    );
	    foreach ($browserArray as $regex => $value) {
	        if (preg_match($regex, $userAgent)) {
	            $browser = $value;
	        }
	    }

	    $data['os_platform'] = $osPlatform;
	    $data['browser'] = $browser;

	    return $data;
	}

}
