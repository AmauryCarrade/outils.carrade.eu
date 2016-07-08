<?php

class Downloader
{
    const POST = 'POST';
    const GET = 'GET';

    /**
     * Initialize CURL
     * @param resource  $curl    The CURL resource.
     * @param array     $data    The request's parameters.
     * @param string    $url     The URL of the file to load.
     * @param string    $method  The request's method (self::GET or self::POST). Default value: self::GET.
     */
    protected function curlSetOptions($curl, $url, $data = array(), $method = self::GET)
    {
        // CURL must return the response.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        if($method == self::POST)
        {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data
            ));
        }
        else
        {
            $parameters = NULL;
            if($data != array())
            {
                $parameters = '?' . http_build_query($data);
            }

            curl_setopt($curl, CURLOPT_URL, $url . $parameters);
        }

        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    }

    /**
     * Download a file using CURL
     * @param string    $url     The URL of the file to load.
     * @param array     $data    The request's parameters.
     * @param string    $method  The request's method (self::GET or self::POST). Default value: self::GET.
     * @return array             An array with the following keys:
     *   - data: the file's content;
     *   - contentType: the file's content-type;
     *   - HTTPCode: the HTTP Code returned by the server;
     *   - infos: some infos about the request. @see http://fr.php.net/manual/fr/function.curl-getinfo.php
     *
     * @throws RuntimeException if initialization of CURL fails.
     * @throws RuntimeException if request fails.
     */
    protected function curl($url, $data = array(), $method = self::GET)
    {
        $result = $HTTPCode = $contentType = $infos = NULL;

        if($curl = curl_init())
        {
            $this->curlSetOptions($curl, $url, $data, $method);
            $result = curl_exec($curl);

            if($result === false)
                throw new RuntimeException('Error while sending request to server.' . "\n" . 'Error:' . curl_error($curl));

            $infos       = curl_getinfo($curl);
            $HTTPCode    = $infos['http_code'];
            $contentType = $infos['content_type'];

            curl_close($curl);
        }
        else throw new RuntimeException('Error while initializing CURL.');

        return array(
            'body'        => $result,
            'contentType' => $contentType,
            'HTTPCode'    => $HTTPCode,
            'infos'       => $infos
        );
    }

    /**
     * Prepare the HTTP request
     * @param string   $host    The server's host
     * @param string   $path    The absolute path to the file.
     * @param array    $data    The request's parameters.
     * @param string   $method  The request's method (self::GET or self::POST). Default value: self::GET.
     * @return string           The request to send to server.
     */
    protected function createHTTPRequest($host, $path, array $data = array(), $method = self::GET)
    {
        $original_header = $body = $parameters = NULL;

        if ($data != array())
            $parameters = '?' . http_build_query($data);

        // POST
        if ($method == self::POST)
        {
            if (strpos($path, '/') !== 0)
                $path = '/' . $path; // The path must be absolute and have to start with a "/".


            $original_header  = "POST $path HTTP/1.1\n";
            $original_header .= "Host: $host\n";
            $original_header .= "Content-type: application/x-www-form-urlencoded\n";
            $original_header .= 'Content-length: ' . strlen($parameters) . "\n";

            if($parameters != NULL) {
                $body = $parameters . "\n";
            }
        }

        // GET
        else
        {
            $original_header  = "GET $path" . ($parameters != NULL ? '?' . $parameters : NULL) . " HTTP/1.1\n";
            $original_header .= "Host: $host\n";
        }

        $original_header .= "Connection: close\n\n";

        return $original_header . $body;
    }

    /**
     * Extract header and body of a HTTP response.
     * @param string $result The HTTP Response
     * @return array         An array with the following keys:
     *      - original_header: the received header;
     *      - HTTPCode: the HTTP Status Code;
     *      - contentType: the Content-Type header returned, if any (without "Content-Type" header, NULL);
     *      - headers: the received headers;
     *      - body: the received body.
     *
     * @throws RuntimeException if the HTTP Response contains errors.
    */
    protected function analyseHTTPRequest($HTTPResponse)
    {
        $HTTPCode = $contentType = $headers = $original_header = $body = NULL;

        // Extract header and body
        $array_response = preg_split('#\n\n|\r\n\r\n|\r\r#', $HTTPResponse);

        // $array_response must contains at least two items: one for header and one for body.
        if(is_array($array_response) && count($array_response) >= 2)
        {
            $original_header = array_shift($array_response);
            $body = implode("\n", $array_response);

            // We extract the HTTP Status Code from the header
            preg_match('#^HTTP/1\.\d\s+(\d{3})#', $original_header, $matches);
            $HTTPCode = $matches[1];

            // We extract headers from... the header
            $headers = array();

            $array_headers = explode("\n", $original_header);
            array_shift($array_headers); // We remove the first line ("HTTP/1.1 ... ...").

            foreach($array_headers AS $header_item)
            {
                $header_parts = array_map('trim', explode(':', $header_item));
                $header_name = array_shift($header_parts);
                $headers[$header_name] = implode(':', $header_parts);
            }

            $contentType = $headers['Content-Type'];
        }
        else throw new RuntimeException('The HTTP Response contains some errors. Headers and body must be divided by a blank line.');

        return array(
            'original_header' => $original_header,
            'HTTPCode' => $HTTPCode,
            'contentType' => $contentType,
            'headers' => $headers,
            'body' => $body
        );
    }

    /**
     * Download a file using sockets.
     * @param string    $url     The URL of the file to load.
     * @param array     $data    The request's parameters.
     * @param string    $method  The request's method (self::GET or self::POST). Default value: self::GET.
     * @param array    &$steps    An array used internally who store all sub-requests.
     * @return array             An array with the following keys:
     *   - data: the file's content;
     *   - contentType: the file's content-type;
     *   - HTTPCode: the HTTP Code returned by the server;
     *   - infos: some infos about the request. @see http://fr.php.net/manual/fr/function.curl-getinfo.php
     *     NOTE: with sockets, this array do NOT contains the following keys:
     *      - namelookup_time
     *      - connect_time
     *      - pretransfer_time
     *      - starttransfer_time
     *      - ssl_verify_result
     *      - certinfo
     *     And the following keys will be added later:
     *      - speed_upload
     *      - speed_download
     *      - total_time
     * @throws RuntimeException if request fails.
     */
    protected function sockets($url, $data = array(), $method = self::GET, &$steps = array())
    {
        $HTTPResponse = $response = $sentRequestSize = NULL;
        $infos = array();

        $infos['url'] = $url;

        // We extract host and path
        preg_match('#https?://([^/]+)(/.+)#i', $url, $matches);
        if(!isset($matches[1])) {
            throw new InvalidArgumentException('The given URL is not valid');
        }
        $host = $matches[1];
        $path = $matches[2];

        // We open connection
        if($socket = fsockopen($host, 80))
        {
            // Preparing request...
            $request = $infos['request_header'] = $this->createHTTPRequest($host, $path, $data, $method);
            $infos['request_size'] = $infos['size_upload'] = strlen($request);
            $headerAndBody = preg_split('#\n\n|\r\n\r\n|\r\r#', $request);
            $infos['header_size']  = strlen($headerAndBody[0]);

            // Sending data...
            if (!fwrite($socket, $request))
                throw new RuntimeException('Error while sending request');


            // Checking sent...
            if ($infos['request_size'] == strlen($request))
            {
                // We get the HTTP Response
                $HTTPResponse = stream_get_contents($socket);

                if ($HTTPResponse === NULL)
                    throw new RuntimeException('Error while reading data: no data received');
                else
                {
                    $infos['size_download'] = strlen($HTTPResponse);
                    $response = $this->analyseHTTPRequest($HTTPResponse);

                    if (in_array($response['HTTPCode'], array(301, 302)) && isset($response['headers']['Location']))
                    {
                        // We need to redirect the request.
                        $timeStep = microtime(true);
                        $response = $this->sockets($response['headers']['Location'], $data, $method, $steps);
                        $steps[] = microtime(true) - $timeStep;

                        $infos['url'] = $response['infos']['url'];
                        $infos['size_upload'] += $response['infos']['size_upload'];
                        $infos['size_download'] += $response['infos']['size_download'];
                    }
                }
            }
            else throw new RuntimeException('Error while sending request: some data was lost');

            fclose($socket);
        }
        else throw new RuntimeException('Error while connecting to server.');


        $infos['filetime'] = isset($response['headers']['Last-Modified']) ? $response['headers']['Last-Modified'] : NULL;
        $infos['redirect_count'] = count($steps);
        $infos['redirect_time'] = array_sum($steps);
        $infos['upload_content_length'] = $infos['size_upload'];
        $infos['download_content_length'] = isset($infos['headers']['Content-Length']) ? $infos['headers']['Content-Length'] : NULL;
        $infos['steps'] = $steps;

        return array(
            'body'        => $response['body'],
            'contentType' => $response['contentType'],
            'HTTPCode'    => $response['HTTPCode'],
            'infos'       => $infos
        );
    }


    /**
     * Download a file from a server.
     *
     * @param string $url    L'URL à appeler.
     * @param array  $data   arrayleau de données à passer en paramètres au serveur distant.
     * @param string $method Méthode d'envoi. Peut être self::GET (par défaut) ou self::POST.
     * @param string $use    La méthode à utiliser. 'auto' sélectionne la meilleure. 'curl', CURL. Autre chose, des
     *                       sockets.
     *
     * @return string Le contenu du fichier.
     */
    public function request($url, array $data = array(), $method = self::GET, $use = 'auto') {
        $response = NULL;
        // If we can use CURL, we use it.
        if($use == 'curl' || $use == 'auto' && function_exists('curl_init')) {
            $response = $this->curl($url, $data, $method);
        }
        // Else, we use sockets.
        else {
            $time = microtime(true);
            $response = $this->sockets($url, $data, $method);
            $response['infos']['total_time']     = microtime(true) - $time;
            $response['infos']['speed_upload']   = $response['infos']['size_upload'] == NULL ? 0 : $response['infos']['size_upload'] / $response['infos']['size_upload'];
            $response['infos']['speed_download'] = $response['infos']['size_download'] == NULL ? 0 : $response['infos']['size_download'] / $response['infos']['size_download'];
        }

        return $response;
    }

    public function get($url, array $data = array(), $use = 'auto') {
        return $this->request($url, $data, self::GET, $use);
    }

    public function post($url, array $data = array(), $use = 'auto') {
        return $this->request($url, $data, self::POST, $use);
    }
}