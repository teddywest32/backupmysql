<?php

  /**
   * Backupmysql.
   *
   * @author Viktor Geringer <devfakeplus@googlemail.com>
   * @link https://github.com/devfake/backupmysql
   */
  class Backupmysql {

    protected $dbData = [];

    protected $databaseAlias;
    protected $zipCompression;
    protected $uploadFTP;
    protected $uploadDropbox;

    protected $maxBackupFiles;
    protected $maxAgeOfBackupFile;

    protected $maxBackupSizeForFTP;
    protected $maxBackupSizeForDropbox;

    protected $backupFolder;
    protected $backupDB;

    protected $dataFTP = array();

    protected $apiKey;

    protected $db;

    protected $folder;

    public function __construct(array $config)
    {
      $config = array_values($config);

      $this->dbData['host'] = $config[0];
      $this->dbData['username'] = $config[1];
      $this->dbData['password'] = $config[2];
      $this->dbData['database'] = $config[3];

      $this->databaseAlias = $config[4] ?: $this->dbData['database'];
      $this->zipCompression = $config[5];
      $this->uploadFTP = $config[6];
      $this->uploadDropbox = $config[7];

      $this->maxBackupFiles = $config[8];
      $this->maxAgeOfBackupFile = $config[9];

      // Umrechnung in Byte.
      $this->maxBackupSizeForFTP = (int) $config[10] * 1024 * 1024;
      $this->maxBackupSizeForDropbox = (int) $config[11] * 1024 * 1024;

      $this->backupFolder = $config[12];
      $this->backupDB = $config[13] ?: $this->dbData['database'];

      $this->dataFTP = $config[14];
      $this->dataDropbox = $config[15];

      $this->apiKey = $config[16];

      // todo: deaktivieren.
      error_reporting(-1);
      ini_set('display_errors', 'On');
      set_time_limit(0);

      // Gibt für größere Datenbanken genügend Speicher frei.
      ini_set('memory_limit', '1024M');

      $this->createBackupFolder();
      if($this->isConnectionDataClean()) {
        $this->connectDB();
      }
    }

    protected function getDBName()
    {
      return $this->dbData['database'];
    }

    protected function getDBAliasName()
    {
      return $this->databaseAlias;
    }

    /**
     * Erstelle einen Backup Ordner für die lokale Sicherung.
     */
    private function createBackupFolder()
    {
      $this->folder = $this->backupFolder . '/' . $this->databaseAlias;

      if( ! file_exists($this->folder) && ! mkdir($this->folder, 0777, true)) {
        // Error 'Keine Berechtigung zum erstellen für den Ordner'
      }
    }

    /**
     * Prüft ob leere MySql-Verbindungsdaten hinterlegt sind.
     */
    private function isConnectionDataClean()
    {
      if($this->dbData['host'] != '' && $this->dbData['username'] != '' && $this->dbData['password'] != '' && $this->dbData['database'] != '') {
        return true;
      }

      // Error 'Prüfen Sie ob alle erforderlichen MySql-Verbindungsdaten hinterlegt sind'
      return false;
    }

    /**
     * Stellt die Datenbankverbindung her.
     */
    private function connectDB()
    {
      $this->db = new mysqli($this->dbData['host'], $this->dbData['username'], $this->dbData['password'], $this->dbData['database']);

      if($this->db->connect_errno) {
        // Error 'Es konnte keine Datenbank Verbindung aufgebaut werden'
      }

      $this->db->set_charset("utf8");
    }
  }