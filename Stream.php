<?php

class Log {
	
	/**
	 * stream句柄
	 *
	 * @var resource
	 */
	private $_handle = null;
	public static $file = null;
	
	public function __construct() {
		try {
			$this->_formatter = '';
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * 关闭
	 *
	 * @return void
	 */
	public function shutDown() {
		if (empty($this->_handle) || true === is_resource($this->_handle)) {
			@fclose($this->_handle);
		}
	}
	
	/**
	 * 写日志接口
	 *
	 * @return void
	 */
	public function write($events) {
        // todo can configure
        $this->maxBackupIndex = 9;
        $this->maxFileSize = 100 * 1024 * 1024;
        if (empty(self::$file)) {
			throw new Exception(1003);
		}
		
		$dirName = dirname(self::$file);
		if (!is_dir($dirName)) {
			mkdir($dirName, 0755, true);
		}
        if (!$this->_handle = @fopen(self::$file, 'a+', false)) {
            throw new Exception(1002);
        }
        $events = $this->_formatter->format($events);
        if (flock($this->_handle, LOCK_EX)) {	
            @fwrite($this->_handle, $events);
            clearstatcache(true, self::$file);
			if (filesize(self::$file) > $this->maxFileSize) {
				try {
                    $this->rollOver();
                } catch (Exception $e) {
                    // do nothing 
                }
            }
			flock($this->_handle, LOCK_UN);
            $this->shutDown();
        }
	}

    private function rollOver() {
		if($this->maxBackupIndex > 0) {
			$file = self::$file . '.' . $this->maxBackupIndex;
			
			if (file_exists($file) && !unlink($file)) {
                throw new Exception(1002);
			}
			$this->renameArchievedLogs(self::$file);
			$this->moveToBackup(self::$file);
		}
	}

	private function renameArchievedLogs($fileName) {
		for($i = $this->maxBackupIndex - 1; $i >= 1; $i--) {
			$source = $fileName . "." . $i;
			if(file_exists($source)) {
				$target = $fileName . '.' . ($i + 1);
				rename($source, $target);
			}
		}
	}

    private function moveToBackup($source) {
        $target = $source . '.1';
        rename($source, $target);
    }

}
