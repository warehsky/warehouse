<?php
ini_set("display_errors", "1");
  error_reporting(E_ALL);
include_once '../img/ImageHandler.php';
 
// Подключаем выбранный шрифт текста
$fontPath = '../fonts/HelveticaBlack.ttf';
 
// Путь к оригинальному изображению
if($_REQUEST['t']=="1")
  $imagePath = '../img/markersp.png';
else
  $imagePath = '../img/markerfp.png';

// Указываем размер шрифта 
$fontSize = 220;  
// Задаем цвет
$colorArray = array(255, 0, 0);

if($_REQUEST['t']=="full"){
  $imagePath = '../img/markerfull.png';
  $fontSize = 200;
  $colorArray = array(0, 163, 62);
}
if($_REQUEST['t']=="empty"){
  $imagePath = '../img/markerempty.png';
  $fontSize = 200;
  $colorArray = array(0, 255, 0);
}

 

 
// Создаем экземпляр класса LImageHandler
$ih = new ImageHandler;
 
// Загружаем изображение
$imgObj = $ih->load($imagePath);
 
// Выполняем наложение текста на изображение
$imgObj->text($_REQUEST['n'], $fontPath, $fontSize, $colorArray, ImageHandler::CORNER_CENTER_TOP, 0, 0);
 
$imgObj->show(false, 100); 
 /*
  //устанавливаем тип содержимого
  header('content-type: image/png');
 
  //Определяем размер изображения – 300x300 пикселей
  $image = imagecreate(30, 30);
 
  //Устанавливаем фоновый темно-серый цвет
  $dark_grey = imagecolorallocate($image, 102, 102, 102);
  $white = imagecolorallocate($image, 255, 255, 255);
 
  //Указываем путь к шрифту
  $font_path = 'fonts/HelveticaBlack.ttf';
 
  //Пишем текст
  $string = 'Hello World!';
 
  //Соединяем текст и картинку
  imagettftext($image, 10, 0, 10, 160, $white, $font_path, $string);
 
  //Сохраняем изображение
 
  imagepng($image);
 
  //Чистим память
  imagedestroy($image);*/
?>
