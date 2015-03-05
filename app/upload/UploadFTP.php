<?php

  /**
   * FTP Upload Klasse.
   *
   * Überträgt das Backup über FTP auf einen oder mehrere externe Server.
   */
  class UploadFTP extends Upload {

    public function __construct($folder, $filePath, $data, $maxBackupFiles, $maxAgeOfBackupFile)
    {
      if( ! $this->isFTPActivated()) {
        return false;
      }

      parent::__construct($folder, $filePath, $data, $maxBackupFiles, $maxAgeOfBackupFile);

      $this->boot($data);
    }

    /**
     * Bootstrap den Upload.
     */
    private function boot($data)
    {
      foreach($data as $key => $value) {
        if($this->isConnectionDataClean($value)) {
          $this->connect($value);
          $this->upload();
          $this->deleteOldBackups();
          ftp_close($this->connection);
        } else {
          // Error 'Es wurden für $key nicht alle FTP-Daten angegeben'
        }
      }
    }

    /**
     * Stellt die Verbindung zum externen Server her.
     *
     * http://php.net/manual/de/function.ftp-ssl-connect.php
     */
    private function connect($dataFTP)
    {
      // Es wird überprüft ob eine sichere Verbindung angegeben wurde, und ob der Server diese funktion zur verfügung hat.
      // Wenn nicht, wird eine normale Verbindung hergestellt.
      if($dataFTP['SSL'] && function_exists('ftp_ssl_connect')) {
        $this->connection = ftp_ssl_connect($dataFTP['server']);
      } else {
        if($dataFTP['SSL']) {
          // Error 'Es konnte keine sichere FTP-Verbindung hergestellt werden. Eventuell unterstützt dein Server diese nicht. Es wird versucht eine normale FTP-Verbindung herzustellen'
        }

        $this->connection = ftp_connect($dataFTP['server']);
      }

      // Es wird geprüft ob ein login möglich ist. Wenn nicht, und eine sichere Verbindung angegeben wurde,
      // kann der Server scheinbar mit verschlüsselten Details nicht umgehen, und es wird versucht eine normale FTP-Verbindung aufzubauen.
      if( ! $login = ftp_login($this->connection, $dataFTP['username'], $dataFTP['password'])) {
        $this->connection = ftp_connect($dataFTP['server']);
        $login = ftp_login($this->connection, $dataFTP['username'], $dataFTP['password']);
      }

      if( ! $this->connection || ! $login) {
        // Error 'Es konnte keine FTP-Verbindung aufgebaut werden. Stimmt der Server Name und der Benutzername bzw. das Passwort?'
      }

      ftp_pasv($this->connection, true);

      $this->changeAndCreateDir($this->folder, $dataFTP['path']);
    }

    /**
     * Ladet die Datei hoch.
     */
    private function upload()
    {
      if(file_exists($this->filePath . '.sql')) {
        $filePath = $this->filePath . '.sql';
        $ftpMode = FTP_ASCII;
      } elseif(file_exists($this->filePath . '.zip')) {
        $filePath = $this->filePath . '.zip';
        $ftpMode = FTP_BINARY;
      } else {
        // Error 'Der FTP-Upload ist fehlgeschlagen. Es wurde keine Backup Datei gefunden'
      }

      $filename = explode('/', $filePath);

      if( ! ftp_put($this->connection, end($filename), $filePath, $ftpMode)) {
        // Error 'Der FTP-Upload ist fehlgeschlagen'
      }
    }

    /**
     * Löscht alte Backups raus.
     */
    private function deleteOldBackups()
    {
      BackupCleaner::deleteOldBackupsFromFTP($this->connection, $this->maxAgeOfBackupFile, $this->maxBackupFiles);
    }

    /**
     * Falls angegeben, wird der Pfad auf dem externen Server gewechselt.
     *
     * Außerdem wird die Ordnerstruktur für die Backups erstellt.
     */
    private function changeAndCreateDir($folder, $path)
    {
      if( ! ftp_chdir($this->connection, $path)) {
        // Error 'Es konnt nicht auf den angegebenen Start-Pfad gewechselt werden. Existiert der Ordner $path? Es wurde der Standard-Pfad genommen'
      }
      $dirs = explode('/', $folder);
      foreach($dirs as $dir) {
        if( ! ftp_chdir($this->connection, $dir)) {
          ftp_mkdir($this->connection, $dir);
          ftp_chdir($this->connection, $dir);
          ftp_chmod($this->connection, 0777, $dir);
        }
      }
    }

    /**
     * Kontrolliert ob der Server die FTP Erweiterung installiert hat.
     */
    private function isFTPActivated()
    {
      if ( ! extension_loaded('ftp')) {
        // Error 'Dein Server hat die FTP Erweiterung nicht Aktiviert'
        return false;
      }

      return true;
    }
  }
