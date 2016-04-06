<?php
/**
 * Created by PhpStorm.
 * User: cagatay
 * Date: 05/04/16
 * Time: 18:56
 */

class Functions
{

    function file_get_contents_curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    function getUrlContent($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpcode>=200 && $httpcode<300) ? $data : false;
    }

    /**
     * @param $ilan_sayisi
     * @param $sonuc
     */
    function removeFarResult($ilan_sayisi, $sonuc)
    {
        for ($j = 0; $j < $ilan_sayisi; $j++) {

            if ($sonuc[$j]['location']['distance'] > 1 or $sonuc[$j]['location']['distance'] == 0) {
                unset($sonuc[$j]);
            }
        }

        return $sonuc;
    }
    /**
     * @param $sonuc
     * @return array
     */
    function sortNearestResult($sonuc)
    {
        $data = [];

        foreach (array_values($sonuc) as $r) {
            $data[] = $r;
        }

        usort($data, 'cmp');
        return $data;
    }

    /**
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @param $unit
     * @return float
     */
    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    /**
     * @param $latitude
     * @param $longitude
     */
    function getStreet($latitude, $longitude)
    {
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude . "&sensor=true";
        $data = @file_get_contents($url);
        $jsondata = json_decode($data, true);


        return $jsondata['results']['0']['address_components']['2']['long_name'];
    }


    /**
     * @param $site
     * @return array
     */
    function getLocation($site,$latitude,$longitude) # Current locations
    {
        preg_match_all('@<div id="gmap" data-lat="(.*?)" data-lon="(.*?)" data-lang="tr"></div>@si', $site, $lokasyon);

        return $konum;
    }

    /**
     * @param $site
     * @return mixed
     */
    function getPrice($site)
    {
        preg_match_all('@<h3>(.*?)</h3>@si', $site, $fiyat);

        $string = trim(preg_replace('/\s\s+/', ' ', strip_tags($fiyat[0][0])));
        return $string;
    }

    /**
     * @param $site
     * @return mixed
     */
    function getDescription($site)
    {
        preg_match_all('@<div id="classifiedDescription" class="uiBoxContainer">(.*?)</div>@si', $site, $aciklama);

        //$aciklama = validate($aciklama[0][0]);

        return strip_tags($aciklama);
    }

    /**
     * @param $site
     * @return mixed
     */
    function getPhoto($site)
    {
        preg_match_all('@<img width="480" height="360" src="(.*?)" alt="(.*?)">@si', $site, $resimler);
        return $resimler;
    }

    /**
     * @param $aciklama
     * @return mixed|string
     */
    function validate($aciklama)
    {
        $temizle = strip_tags($aciklama);
        $temizle = str_replace('<div', '', $temizle);
        $temizle = htmlentities($temizle);
        $temizle = str_replace('<font', '', $temizle);
        $temizle = str_replace('<span', '', $temizle);
        $temizle = str_replace('<p', '', $temizle);
        $temizle = html_entity_decode($temizle);
        return $temizle;
    }

    /**
     * @param $url
     * @return array
     */
    function getDetail($url,$latitude,$longitude)
    {
        $result = array();

//        $site = $this->getUrlContent($url);

   //     $site = file_get_contents($url);
        $site = $this->file_get_contents_curl($url);

        $result["url"]   = $url;
        $result["location"] = $this->getLocation($site,$latitude,$longitude);
        $result["price"]  = $this->getPrice($site);

        return $result;
    }

    /**
     * @param $street
     * @param $detay_icin_link
     * @param $functions
     * @param $latitude
     * @param $longitude
     * @param $sonuc
     * @return array
     */
    function collectResults($street, $detay_icin_link, $functions, $latitude, $longitude, $sonuc)
    {

        $ilan_sayisi = 0;

        for ( $i = 0 ; $i < 50 ; $i = $i + 50)
        {
            $url = file_get_contents("http://www.sahibinden.com/emlak-konut?pagingSize=50&pagingOffset=$i&query_text=$street");

            //echo "<a href='$url'>$url</a><br>";

            preg_match_all('@<a class="classifiedTitle" href="(.*?)">(.*?)</a>@si',$url,$detay_icin_link);


            $ilan_sayisi = count($detay_icin_link[0]);

            for ( $j = 0; $j < $ilan_sayisi ; $j++ )
            {
                # Current url
                $url ="http://www.sahibinden.com".$detay_icin_link[1][$j];

                $sonuc[$j] = $this->getDetail($url,$latitude,$longitude);
            }
        }


        return array($ilan_sayisi,$sonuc);
    }

    /**
     * @return array
     */
    function getParam()
    {
        $latitude = $_POST["latitude"];
        $longitude = $_POST["longitude"];
        $street = $_POST["street"];
        return array($latitude, $longitude, $street);
    }

    /**
     * @param $data
     */
    function response($data)
    {
        $return = json_encode($data,JSON_UNESCAPED_UNICODE);
        echo $return;
    }
}