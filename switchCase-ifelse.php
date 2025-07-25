<?php 
$ay ="Haziran";

if($ay=="Aralık"||$ay=="Ocak"||$ay=="Şubat"){
 echo"Kış Mevsimi";
}
else if($ay=="Mart"||$ay=="Nisan"||$ay=="Mayıs"){
 echo"İlkbahar Mevsimi";
}
else if($ay=="Haziran"||$ay=="Temmuz"||$ay=="Ağustos"){
 echo"Yaz mevsimi";
}
else if($ay=="Eylül"||$ay=="Ekim"||$ay=="Kasım"){
 echo"Sonbahar mevsimi";
}
else{
 echo"Geçersiz mevsim";
}
// if else ile yapılan mevsim uygulaması


<?php 
$ay="Ocak";

switch ($ay){
case "Aralık":
case"Ocak":
case"Şubat":
echo "Kış mevsimi";
break;

case "Mart":
case"Nisan":
case"Mayıs":
echo "İlkbahar mevsimi";
break;
case "Haziran":
case"Temmuz":
case"Ağustos":
echo "Yaz mevsimi";
break;
case "Eylül":
case"Ekim":
case"Kasım":
echo "Sonbahar mevsimi";
break;

default:
echo"Geçersiz Mevsim";

}
 // Switch -Case ile yapılan mevsim uygulaması

