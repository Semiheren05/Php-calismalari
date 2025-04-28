<?php
/* Camel Case ( Deve sırtı Notasyonu)
Her kelimenin ilk harfi büyük yazılır ( ilk kelime hariç).
Tanım : Birden fazla kelimeden oluşan isimlerde ilk kelime küçük harfle başlar sonraki kelimeler büyük harf
kullanım alanları:
- Değişkenler
- metod isimleri
- nesne özellikleri (properties)
lavarel ve symfony gibi frameworklerde metod değişken isimlerinde Camel Case Tercih edilir.
*/
$ogrenciAdi="Ali";
$urunfiyati=50;
/*
Snake case ( Alt çizgi kullanımı)
kelime araları alt çizgiyle ayrılır
Tanım : Birden fazla kelime küçük harflerle yazılır ve kelime arasına _ ( alt çizgi) eklenir.
kullanım alanları:
- Veritabanı alan isimleri
-Sabitler ( bazı durumlarda)
Lavarel, Veritabanı tablolarında ve sütun isimlerinde Snake Case kullanılır
Symfony ve Doctrine gibi  ORM ( Object Relational Mapper)Araçlarında veritabanı alanları genelde snake case kullanır.
*/
$ogrenci_adi="Ali";
$urunfiyati=50;
/* 
Pascal case

Her kelimenin ilk harfi büyük yazılır ( genellikle sınıf adları için kullanılır).
Kullanım alanları :
-Sınıf isimleri
-PHP 8.0 ile gelen adlandırılmış parametreler gibi durumlar

PSR standarlarına uygun olarak sınıf isimleri Pascal Case ile adlandırılır.
Lavarel ve Symfony sınıf isimlendirmelerinde Pascal Case kullanır.
*/
$OgrenciAdi ="Ali";
$UrunFiyati = 50;

