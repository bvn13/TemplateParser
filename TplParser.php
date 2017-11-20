<?
class TplParser {

  private $content;       // для хранения контента
  private $templateName;  // имя файла шаблона
  private $errorMessage;  // сообщение об ошибке
  private $title;         // заголовок <title></tile>
  private $result;

  // инициализация
  function TplParser() {
    $content = array();
    //$this->setParam("", ""); //на случай, если не будет задано ни одного параметра
    $templateName = "";
    $errorMessage = "";
    $title = "";
  }

  public function setParam($name,$value) {
      $this->content[$name] = $value;
  }

  public function setTpl($filename) {
      $this->templateName = $filename;
  }

  public function getErrorMessage() {
      return $this->errorMessage;
  }

  public function getParams() {
      return $this->content;
  }

  public function setTitle($value) {
      $this->title = $value;
  }

  // подготовка данных
  public function parse() {
    // загрузка шаблона
    //$res = file_get_contents($this->templateName);

    if (!$this->content) {
        $this->content = array();
        //$this->setParam("not-a-param", NULL);
    }
    extract ( $this->content ); // Extract the vars to local namespace
    ob_start (); // Start output buffering
    include( $this->templateName ); // Include the file
    $res = ob_get_contents (); // Get the contents of the buffer
    ob_end_clean (); // End buffering and discard


    if (!$res) {
      $errorMessage = "<span style=\"color: Red\"> Ошибка: Файл шаблона <strong>(".$this->templateName.")</strong> не найден.</span><br />";
      $this->result = $errorMessage;
      return $errorMessage;
    } else {
      // ищем <title></title>
      $titleTemplate = "@(<title>(.*)</title>)|(<TITLE>(.*)</TITLE>)|(<Title>(.*)</Title>)@"; //TODO: универсализиоровать - вне зависимости от регистра вообще
      //if (ereg($titleTemplate, $res, $ss)) {
      if (preg_match($titleTemplate, $res, $ss)) {
        if ($this->title != NULL) {
          $newTitle = $this->title;
          $res = preg_replace($titleTemplate, "<title>$newTitle</title>", $res);
        }
      }

      // поиск и замена блоков контента самим контентом
      //var_dump($this->content);
      
      if (count($this->content) != 0) {
          foreach ($this->content as $key => $value) {
            $res = str_replace("<php:".$key.">", $value, $res);
            $res = str_replace("<php:".$key."/>", $value, $res);
          }
      }
      //echo $result;
      $this->result = $res;
      return true;
    }
    return false;
  }

  public function tprint() {
      echo $this->result;
  }

  //на случай, когда обработанный шаблон будет использоваться в параметре другого шаблона
  public function getResult() {
      return $this->result;
  }
  
  static function s_tprint($filename, $params) { // префикс s_ означает статичность
      $tpl = new TplParser();
      $tpl->setTpl($filename);
      foreach ($params as $key => $value) {
         $tpl->setParam($key,$value);
      }
      $tpl->parse();
      $tpl->tprint();
  }
  
  static function s_getResult($filename, $params) {
      $tpl = new TplParser();
      $tpl->setTpl($filename);
      foreach ($params as $key => $value) {
         $tpl->setParam($key,$value);
      }
      $tpl->parse();
      return $tpl->getResult();
  }

  
  static function s_getProcessed ($file,$vars,$title=NULL) {

    extract ( $vars ); // Extract the vars to local namespace
    ob_start (); // Start output buffering
    include( $file ); // Include the file
    $contents = ob_get_contents (); // Get the contents of the buffer
    ob_end_clean (); // End buffering and discard
    
    $res = $contents;

    if (!$res) {
      $errorMessage = "<span style=\"color: Red\"> Ошибка: Файл шаблона <strong>(".$file.")</strong> не найден.</span><br />";
      return $errorMessage;
    } else {

      $titleTemplate = "(<title>(.*)</title>)|(<TITLE>(.*)</TITLE>)|(<Title>(.*)</Title>)"; //TODO: универсализиоровать - вне зависимости от регистра вообще
      if (ereg($titleTemplate, $res, $ss)) {
        if ($title != NULL) {
          $newTitle = $title;
          $res = ereg_replace($titleTemplate, "<title>$newTitle</title>", $res);
        }
      }
      if (count($vars) != 0) {
          foreach ($vars as $key => $value) {
            $res = str_replace("<php:".$key.">", $value, $res);
          }
      }

    }

    return $res ; // Return the contents
  } 

    static function s_printProcessed($file, $vars,$title=NULL) {

        $content = TplParser::s_getProcessed($file, $vars, $title);
        echo $content;

    }

}
