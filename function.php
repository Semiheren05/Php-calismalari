<?php 
function merhaba($ad)
{
      
      echo "Benim adım ". $ad;
}
merhaba("Semih");


////////
function toplama ($a,$b)
{
      return $a + $b;
}
$sonuc = toplama(5,10);
echo $sonuc;


/////////////////////
function Selamver($isim="Misafir"){
      echo "Merhaba, $isim!";
}
$isim= isset($_GET["İSİM"])? $_GET["isim"]:null;
Selamver($isim);
