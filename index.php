<?php

  $config = array(

    # ===============================================================
    #  Ändere hier deine Daten für deine MySQL Verbindung.
    # ===============================================================

    'host' => 'localhost',
    'username' => '...',
    'passwort' => '...',
    'datenbank' => '...',

    # ===============================================================
    #  Hier kannst du verschiedene Optionen festlegen.
    #
    #  Für genauere Infos: http://www.backup-mysql.de/docu/options
    # ===============================================================

    'ZIP-Komprimierung' => true,
    'FTP-Sicherung' => true,
    'Dropbox-Sicherung' => false,

    'Max. Backup-Dateien' => 5,
    'Max. Alter der Backup-Dateien' => 86400, // 1 Tag

    'Max. Groeße für FTP-Sicherung' => 104857600, // 100 MB
    'Max. Groeße für Dropbox-Sicherung' => 104857600, // 100 MB

    'Backup-Ordner' => 'mysql_backups',

    # ===============================================================
    #  FTP-Daten auf die deine Backups hochgeladen werden.
    #
    #  Für genauere Infos: http://www.backup-mysql.de/docu/data#ftp
    # ===============================================================

    'FTP-Daten' => array(

      'Server-1' => array(
        'server' => 'ftp.example.com',
        'username' => '...',
        'password' => '...',
        'path' => '...',
        'SSL' => false,
        'SSH' => false
      ),
    ),

    # ===============================================================
    #  Dein API-Schlüssel.
    #
    #  Dieser stellt die Verbindung zwischen deinem Server
    #  und dem von backup-mysql.de her. Bitte NICHT ändern!
    # ===============================================================

    'API-Schluessel' => 'udP4cx7Mhu8ivZqjoFT8pRU1HptegxKl',

  );

  /**
   * Ladet alle benötigten Dateien vom backupmysql Server und erstellt diese.
   */
  class Init {

    /*public function __construct()
    {
      if( ! is_dir('app')) mkdir('app', 0777);

      $files = json_decode(file_get_contents('http://80.240.132.120/backup/Files.json'), true);

      foreach($files['files'] as $file) {
        if( ! file_exists('app/' . $file . '.php')) {
          $fileContent = file_get_contents('http://80.240.132.120/backup/' . $file . '.conf');
          file_put_contents('app/' . $file . '.php', $fileContent);
        }

        require 'app/' . $file . '.php';
      }
    }*/

    public function __construct()
    {
      require 'app/Backupmysql.php';
      require 'app/BackupCleaner.php';
      require 'app/Backup.php';

      require 'app/upload/Upload.php';
      require 'app/upload/UploadFTP.php';
      require 'app/upload/UploadDropbox.php';

      require 'app/ZIP.php';
    }
  }

  new Init();
  new Backup($config);
