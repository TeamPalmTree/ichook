<?php

class IcecastHook {

    private $host;
    private $port;
    private $username;
    private $password;

    private static $stats_path = '/admin/stats';

    function __construct($host, $port, $username, $password) {

        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;

    }

    public function statistics() {

        // create a new curl resource
        $curl = curl_init();
        // set header url & port
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_URL, 'http://' . $this->host . ':' . $this->port . self::$stats_path);
        // set authentication parameters
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        // return as a string
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // output stats
        $xml = curl_exec($curl);
        // close curl resource to free up system resources
        curl_close($curl);
        // generate clean statistics from the output
        $statistics = Format::forge($xml, 'xml')->to_array();
        // success
        return $statistics;

    }

    public function mount_statistics($mount) {

        // get total server stats
        $statistics = $this->statistics();
        // get number of sources
        $statistics_sources_count = $statistics['sources'];
        // if we have no sources, we are done
        if ($statistics_sources_count == '0')
            return null;
        // if we have one source, return it
        if ($statistics_sources_count == '1')
            return $statistics['source'];
        // get all sources
        $statistics_sources = $statistics['source'];
        // we have many sources, find the right one
        foreach ($statistics_sources as $statistics_source) {
            if ($statistics_source['@attributes']['mount'] == $mount)
                return $statistics_source;
        }
        // fail
        return null;

    }

}