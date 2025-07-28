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
<?php
//////////////////////////////////

$kategoriler = [
      "Elektronik"=>[
            "Bilgisayar"=>[
            "Dizüstü",
            "Masaüstü",
      ],
      "Telefon"=>[
            "Akıllı Telefon",
            "Sabit Telefon",
      ],
],
"Ev ve Yaşam" =>[
      "Mobilya" =>[
            "Koltuk",
            "Masa",
      ],
      "Mutfak" =>[
            "Tencere",
            "Tabak",
      ],
],
];
Function kategorileriGoster($kategoriler,$seviye= 0)
{
      foreach($kategoriler as $kategori => $altKategori){
            if(is_array($altKategori)){
                  echo str_repeat("-",$seviye * 2). "$kategori<br> ";
                  kategorileriGoster($altKategori,$seviye + 1);
            } else{
                  echo str_repeat("-",$seviye * 2). "$altKategori<br>";
            }
      }
}
kategorileriGoster($kategoriler);
