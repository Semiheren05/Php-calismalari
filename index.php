<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>PHP VE HTML'in birlikte çalışması</h1>
    <p><?php echo " Bu içerik Php tarafından oluşturuldu"; ?> </p>
    <?php
    echo "bu bir php kodudur";
    ?>

<?php
$isim ="ahmet";
echo $isim;
$isim = " Mehmet";
echo $isim;

$sayi = 5;
echo $sayi;
echo gettype($sayi);
echo var_dump($sayi);

include 'config.php';
echo"API anahtarım:".API_KEY;

?>

</body>
</html>