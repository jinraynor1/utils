<?php

namespace Jinraynor1\Utils;

class SSH
{


    public function __construct($host, $port, $user, $password, $timeout = 5)
    {
        $originalConnectionTimeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', $timeout);
        $this->connection = ssh2_connect($this->config['host'], $this->config['port']);
        ini_set('default_socket_timeout', $originalConnectionTimeout);


        if (!ssh2_auth_password($this->connection, $this->config['username'], $this->config['password'])) {
            throw new \Exception("Error en la conexion SSH");
        }

        $this->sftp = ssh2_sftp($this->connection);

    }

    public function close()
    {
        ssh2_exec($this->connection, 'exit');
        unset($connection);
    }

    public function downloadFile($remotefile, $localfile, $permissions = 644)
    {
        $result = ssh2_scp_recv($this->connection, $remotefile, $localfile);
        if ($result) {
            chmod($localfile, $permissions);
        }

        return $result;
    }


    public function listDirectory($directory)
    {


        $results = scandir('ssh2.sftp://' . $this->sftp . $directory);
        $files = array();

        if ($results && !empty($results)) {
            foreach ($results as $result) {
                if (in_array($result, array('.', '..'))) {
                    continue;
                }

                $files [] = sprintf('%s/%s', $directory, $result);
            }
        }


        return $files;

    }

    public function listRecent($directory, $time)
    {

        $stream = ssh2_exec($this->connection, "find $directory -cmin -$time");
        stream_set_blocking($stream, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $files_stream = stream_get_contents($stream_out);


        $files = array();
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $files_stream) as $line) {
            if (pathinfo($line, PATHINFO_EXTENSION)) {
                $files[] = $line;
            }

        }

        return $files;


    }


}