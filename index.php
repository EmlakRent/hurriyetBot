<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/html; charset=utf-8');
ini_set('allow_url_fopen','1');
require 'functions.php';


$url = file_get_contents("http://www.hurriyetemlak.com/emlak?p44=denizli+çamlaraltı&pageSize=50");
preg_match_all('@<a id="ctl00_cphContent_ctlRealtyListNew1_rptRealtyList_lnkOverlay(.*?) href="(.*?)">@si',$url,$detay_icin_link);



for ( $j = 0; $j < 50 ; $j++ )
{
    # Current url
    $url ="http://www.hurriyetemlak.com".$detay_icin_link[2][$j]."<br>";

    $lokasyon = file_get_contents("http://www.hurriyetemlak.com".$detay_icin_link[2][$j]);
    preg_match_all('@<meta itemprop="latitude" content="(.*?)"/>@si',$lokasyon,$yazi_lokasyon_lat);
    preg_match_all('@<meta itemprop="longitude" content="(.*?)" />@si',$lokasyon,$yazi_lokasyon_lon);
    preg_match_all('@<span id="ctl00_cphContent_ctlRealtyDetailNew1_lblRealtyPrice" class="fz22 ff-m700">(.*?)</span>@si',$lokasyon, $fiyati);

    echo "latitude = ".$yazi_lokasyon_lat[1][0]."<br>";
    echo "longitude = ".$yazi_lokasyon_lon[1][0]."<br>";
    echo "Fiyatı = ".$fiyati[1][0]."<br>";



    echo "<b>".$url."</b>";
}