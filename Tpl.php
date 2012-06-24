<?php

/*
The MIT License

Copyright (c) 2011 Kuznetsov Alex
vamereh@gmail.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 */


/**
 * Рендеринг шаблонов.
 * @author Кузнецов Алексей
 */
class Tpl
{
  
  /**
   * Содержаний блоков.
   * @var Array
   */
  protected $blocks = array();
  
  /**
   * Содержание добавок к блокам.
   * @var Array
   */
  protected $contentForBlocks = array();
  
  /**
   * Адрес шаблона, который расширяет данный.
   * @var String
   */ 
  protected $expandFilename = null;
  
  /**
   * Рендеринг файла шаблона и вывод полученного результата пользователю.
   * @param String $filename Имя файла шаблона.
   * @param Array $vars Массив переменных передаваемых в шаблон.
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new Tpl(); <br>
   *    $tpl->render("/home/sites/test.ru/views/tpl.html.php", $vars); <br>
   */
  public function render($filename, $vars=array()){
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    require($filename);
    if($this->expandFilename===null){
      ob_end_flush();
    }
    else{
      ob_end_clean();
      $expandFilename = $this->expandFilename;
      $this->expandFilename = null; 
      $this->render($expandFilename, $vars);
    }
  }
  
  /**
   * Рендеринг файла в строку.
   * @param String $filename Имя файла шаблона.
   * @param Array $vars Массив переменных передаваемых в шаблон.
   * @return String
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new Tpl(); <br>
   *    $renderedTpl = $tpl->renderToString("/home/sites/test.ru/views/tpl.html.php", $vars); <br>
   */
  public function renderToString($filename, $vars=array()){
    $renderContent = "";
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    require($filename);
    if($this->expandFilename===null){
      $renderContent = ob_get_contents();
      ob_end_clean();
    }
    else{
      ob_end_clean();
      $expandFilename = $this->expandFilename;
      $this->expandFilename = null; 
      $renderContent = $this->renderToString($expandFilename, $vars);
    }
    return $renderContent;
  }
  
  /**
   * Рендеринг строки и вывод полученного результата пользователю.
   * @param String $string Содержание шаблона ввиде строки.
   * @param Array $vars Массив переменных передаваемых в шаблон.
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new Tpl(); <br>
   *    $tpl->renderFromString("<?= $hello ?> <?= $world ?>", $vars); <br>
   *    // => hello world <br><br>
   */
  public function renderFromString($string, $vars=array()){
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    eval(" ?>".$string."<?php "); 
    if($this->expandFilename===null){
      ob_end_flush();
    }
    else{
      ob_end_clean();
      $expandFilename = $this->expandFilename;
      $this->expandFilename = null;
      $this->render(expandFilename, $vars);
    }
  }
  
  /**
   * Рендеринг из строки в строку.
   * @param String $string Содержание шаблона ввиде строки.
   * @param Array $vars Массив переменных передаваемых в шаблон.
   * @return String 
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new Tpl(); <br>
   *    $renderedTpl = $tpl->renderFromString("<?= $hello ?> <?= $world ?>", $vars); <br>
   *    echo $renderedTpl; <br>
   *    // => hello world <br><br>
   */
  public function renderFromToString($string, $vars=array()){
    $renderContent = "";
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    eval(" ?>".$string."<?php ");    
    if($this->expandFilename===null){
      $renderContent = ob_get_contents();
      ob_end_clean();
    }
    else{
      ob_end_clean();
      $expandFilename = $this->expandFilename;
      $this->expandFilename = null;
      $renderContent = $this->renderToString($expandFilename, $vars);
    }
    return $renderContent;
  }
  
  /**
   * Данный метод позволяет указать шаблон, который расширяется данным шаблоном. 
   * @param String $filename Адрес шаблона, который расширяет данный шаблон. 
   */
  public function expands($filename){
    $this->expandFilename = $filename;
  }
  
  /**
   * Указатель на начало блока, который необходимо переопределить.
   * @param String $blockName Имя блока.
   */
  public function beginBlock($blockName){
    ob_start();
  }
  
  /**
   * Указатель на конец блока, который необходимо переопределить.
   * @param String $blockName Имя блока.
   */
  public function endBlock($blockName){
    if(array_key_exists($blockName, $this->blocks)===true){
      ob_end_clean();
    }
    else{
      $renderBlockContent = ob_get_contents();
      ob_end_clean();
      $this->blocks[$blockName] = $renderBlockContent;
    }
    if(array_key_exists($blockName, $this->contentForBlocks)===true){
      echo $this->blocks[$blockName] . $this->contentForBlocks[$blockName];
    }
    else{
      echo $this->blocks[$blockName];
    }
  }
  
  /**
   * Начало добавление содержимого в конец блока.
   * @param String $blockName Имя блока.
   */
  public function beginContentFor($blockName){
    ob_start();
  }
  
  /**
   * Окончание добавление содержимого в конец блока.
   * @param String $blockName Имя блока.
   */
  public function endContentFor($blockName){
    $renderBlockContent = "";
    $renderBlockContent = ob_get_contents();
    ob_end_clean();
    $this->contentForBlocks[$blockName] = $renderBlockContent . $this->contentForBlocks[$blockName];
  }
  
}//Tpl

?>