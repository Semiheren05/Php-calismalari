<?php
$isim ="ahmet";
echo $isim;
$isim = " Mehmet";
echo $isim; // tanımladığım string ifadesini veriyor

$sayi = 5;
echo $sayi;
echo gettype($isim); // Türünü gösteriyor örneğin string
echo gettype($sayi); //  integer olduğunu gösteriyor
echo var_dump($sayi); // hem türünü hem tanımladığım değişkeni veriyor