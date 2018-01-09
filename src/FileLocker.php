<?php

namespace Jinraynor1\Utils;


/**
 * Lock using file and id of process
 */
class FileLocker
{
    /**
     * @var string ID of the lock
     */
    private $id;

    /**
     * @var string RegExp for Lock ID
     */
    private $regId = '~^[a-zA-Z0-9\-_]+$~';

    /**
     * @var string RegExp for Process ID
     */
    private $regPid = '~^\d+$~';


    public function __construct($id, $lockDir)
    {
        // Test ID
        if (!preg_match($this->regId, $id)) {
            throw new \Exception('Invalid ID');
        }

        $this->id = $id;
        $this->lockDir = $lockDir;
    }


    /**
     * {@inheritdoc}
     */
    public function lock()
    {
        // Check if already locked
        if ($this->isLocked()) {
            throw new \Exception('Already locked');
        }

        // Try to lock
        if (false === @file_put_contents($this->getFilePath(), getmypid())) {
            throw new \Exception(sprintf('Failed to write lock file "%s"', $this->getFilePath()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unlock()
    {
        if ($this->isLocked()) {
            if (false === @unlink($this->getFilePath())) {
                throw new \Exception(sprintf('Failed to delete the lock file %s', $this->getFilePath()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked()
    {
        // Check the lock file
        if (!file_exists($this->getFilePath())) {
            return false;
        }

        // Get pid of last process
        $pid = @file_get_contents($this->getFilePath());
        if (false === $pid) {
            throw new \Exception(sprintf('Failed to read the lock file %s', $this->getFilePath()));
        }

        // if pid file is blank
        if (empty($pid)) {
            return false;
        }

        // Check if pid is valid
        if (!preg_match($this->regPid, $pid)) {
            throw new \Exception(sprintf('Unexpected content in lock file %s', $this->getFilePath()));
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('TASKLIST /NH /FO "CSV" /FI "PID eq ' . $pid . '"', $outputA);
            $outputB = explode('","', $outputA[0]);
            return isset($outputB[1]) ? true : false;
        } else {
            // Check if pid exist
            if (!@file_exists('/proc/' . $pid)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper function for the full path of the lock file
     *
     * @return string Absolute path to the lock file
     */
    private function getFilePath()
    {
        $lockFile = $this->lockDir . DIRECTORY_SEPARATOR . $this->id . '.lock';
        return $lockFile;
    }
}