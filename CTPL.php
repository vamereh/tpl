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
 * Класс рендеринга шаблонов.
 * @author Кузнецов Алексей
 */
class CTPL
{
  
  /**
   * Массив блоков.
   * @var array
   */
  private $blocks = array();
  
  /**
   * Адрес шаблона, который расширяет данный.
   * @var string
   */ 
  private $expand_tpl_name = null;
  
  /**
   * Рендеринг файла шаблона и вывод полученного результата пользователю.
   * @param string $filename Имя файла шаблона.
   * @param array $vars Массив переменных передаваемых в шаблон.
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new CTPL(); <br>
   *    $tpl->Render("/home/sites/test.ru/views/tpl.html.php", $vars); <br>
   */
  public function Render($filename, $vars=array()){
    $render_tpl_content = "";
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    require($filename);
    if($this->expand_tpl_name===null){
      ob_end_flush();
    }
    else{
      ob_end_clean();
      $filename = $this->expand_tpl_name;
      $this->expand_tpl_name = null;
      $this->Render($filename, $vars);
    }
  }
  
  /**
   * Рендеринг файла в строку.
   * @param string $filename Имя файла шаблона.
   * @param array $vars Массив переменных передаваемых в шаблон.
   * @return string
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new CTPL(); <br>
   *    $rendered_tpl = $tpl->RenderToString("/home/sites/test.ru/views/tpl.html.php", $vars); <br>
   */
  public function RenderToString($filename, $vars=array()){
    $render_tpl_content = "";
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    require($filename);
    if($this->expand_tpl_name===null){
      $render_tpl_content = ob_get_contents();
      ob_end_clean();
    }
    else{
      ob_end_clean();
      $filename = $this->expand_tpl_name;
      $this->expand_tpl_name = null;
      $render_tpl_content = $this->RenderToString($filename, $vars);
    }
    return $render_tpl_content;
  }
  
  /**
   * Рендеринг строки и вывод полученного результата пользователю.
   * @param string $string Содержание шаблона ввиде строки.
   * @param array $vars Массив переменных передаваемых в шаблон.
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new CTPL(); <br>
   *    $tpl->RenderFromString("<?= $hello ?> <?= $world ?>", $vars); <br>
   *    // => hello world <br><br>
   */
  public function RenderFromString($string, $vars=array()){
    $render_tpl_content = "";
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    eval(" ?>".$string."<?php "); 
    if($this->expand_tpl_name===null){
      ob_end_flush();
    }
    else{
      ob_end_clean();
      $filename = $this->expand_tpl_name;
      $this->expand_tpl_name = null;
      $this->Render($filename, $vars);
    }
  }
  
  /**
   * Рендеринг из строки в строку.
   * @param string $string Содержание шаблона ввиде строки.
   * @param array $vars Массив переменных передаваемых в шаблон.
   * @return string 
   * @example
   *    $vars = array("hello" => "hello", "world" => "world");  <br>
   *    $tpl = new CTPL(); <br>
   *    $rendered_tpl = $tpl->RenderFromString("<?= $hello ?> <?= $world ?>", $vars); <br>
   *    echo $rendered_tpl; <br>
   *    // => hello world <br><br>
   */
  public function RenderFromToString($string, $vars=array()){
    $render_tpl_content = "";
    $_vars = $vars;
    extract($_vars, EXTR_OVERWRITE);
    ob_start();
    eval(" ?>".$string."<?php ");    
    if($this->expand_tpl_name===null){
      $render_tpl_content = ob_get_contents();
      ob_end_clean();
    }
    else{
      ob_end_clean();
      $filename = $this->expand_tpl_name;
      $this->expand_tpl_name = null;
      $render_tpl_content = $this->RenderToString($filename, $vars);
    }
    return $render_tpl_content;
  }
  
  /**
   * Данный метод позволяет указать шаблон, который расширяется данным шаблоном. 
   * @param string $tplname Адрес шаблона, который расширяет данный шаблон. 
   */
  public function Expands($tplname){
    $this->expand_tpl_name = $tplname;
  }
  
  /**
   * Указатель на начало блока, который необходимо переопределить.
   * @param string $block_name Имя блока.
   */
  public function StartBlock($block_name){
    ob_start();
  }
  
  /**
   * Указатель на конец блока, который необходимо переопределить.
   * @param string $block_name Имя блока.
   * @example
   */
  public function EndBlock($block_name){
    if(array_key_exists($block_name, $this->blocks)===true){
      ob_end_clean();
    }
    else{
      $render_block_content = "";
      $render_block_content = ob_get_contents();
      ob_end_clean();
      $this->blocks[$block_name] = $render_block_content;
    }
    echo $this->blocks[$block_name];
  }
  
}//CTPL

?>