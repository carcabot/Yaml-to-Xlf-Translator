<?php

class Translator {

    protected $to, $from, $client, $secret, $languages;

    public function __construct() {
        
    }

    public function execute() {
        $languages = $this->getLanguages();

        if (empty($this->from)) {
            throw new Exception("\033[31m 'from' language is empty\033[0m\n");
        } elseif (empty($this->to)) {
            throw new Exception("\033[31m 'to' language is empty\033[0m\n");
        } elseif (!array_key_exists($this->from, $languages)) {
            throw new Exception("\033[31m 'from' language is not valid\033[0m\n");
        } elseif (!array_key_exists($this->to, $languages)) {
            throw new Exception("\033[31m 'to' language is not valid\033[0m\n");
        } elseif (empty($this->client)) {
            throw new Exception("\033[31m 'client' is not set\033[0m\n");
        } elseif (empty($this->secret)) {
            throw new Exception("\033[31m 'secret' is not set\033[0m\n");
        }


        echo "\033[32m Converting from {$languages[$this->from]} to {$languages[$this->to]}\033[0m\n"; // print converting language

        $parsed = yaml_parse(file_get_contents('langs/' . $this->from . ".yml"));

        $f = fopen('langs/' . $this->to . '.xlf', 'a');
        $header = <<<XML
<?xml version="1.0"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="messages.{$this->to}.xlf">
        <body>
XML;
        $footer = <<<XML
        </body>
    </file>
</xliff>    
XML;
        fwrite($f, $header);
        $i = 1;

        foreach ($parsed as $inputKey => $inputStr) {
            if (is_array($inputStr)) {
                foreach ($inputStr as $_k => $_v) {
                    $this->translate(sprintf('%s.%s', $inputKey, $_k), $_v, $i);
                    $i++;
                }
            } else {
                $this->translate($inputKey, $inputStr, $i);
            }
            $i++;
        }
        fwrite($f, $footer);
        fclose($f);
    }

    function setTo($to) {
        $this->to = $to;
    }

    function setFrom($from) {
        $this->from = $from;
    }

    function setClient($client) {
        $this->client = $client;
    }

    function setSecret($secret) {
        $this->secret = $secret;
    }

    private function translate($inputKey, $inputStr, $i) {
        $f = fopen('langs/' . $this->to . '.xlf', 'a');
        try {
            //Client ID of the application.
            $clientID = $this->client;
            //Client Secret key of the application.
            $clientSecret = $this->secret;
            //OAuth Url.
            $authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
            //Application Scope Url
            $scopeUrl = "http://api.microsofttranslator.com";
            //Application grant type
            $grantType = "client_credentials";
            //Get the Access token.
            $accessToken = $this->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
            //Create the authorization Header string.
            $authHeader = "Authorization: Bearer " . $accessToken;
            $params = "text=" . urlencode($inputStr) . "&to=" . $this->to . "&from=" . $this->from;
            $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";

            //Get the curlResponse.
            $curlResponse = $this->curlRequest($translateUrl, $authHeader);
            //Interprets a string of XML into an object.
            $xmlObj = simplexml_load_string($curlResponse);
            foreach ((array) $xmlObj[0] as $val) {
                $translatedStr = $val;
            }
            //$string_converted = iconv(mb_detect_encoding($string_converted, mb_detect_order(), true), "UTF-8", $string_converted);
            $string_converted = html_entity_decode(htmlentities($translatedStr, ENT_QUOTES | ENT_IGNORE, "UTF-8"));

//                $continut = sprintf("<translation id=\"%s\"><![CDATA[%s]]></translation>\n", (string)$v['id'], $string_converted);
            $continut = <<<XML
            <trans-unit id="{$i}">
                <source>{$inputKey}</source>
                <target><![CDATA[{$string_converted}]]></target>
            </trans-unit>
XML;
            fwrite($f, $continut);
            fclose($f);
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Available languages
     * @return array
     */
    public function getLanguages() {
        return [
            'en' => 'English',
            'ar' => 'Arabic',
            'bg' => 'Bulgarian',
            'ca' => 'Catalan',
            'zh-CHS' => 'Chinese Simplified',
            'zh-CHT' => 'Chinese Traditional',
            'bs-Latn' => 'Bosnian (Latin)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'et' => 'Estonian',
            'fi' => 'Finnish',
            'fr' => 'French',
            'de' => 'German',
            'el' => 'Greek',
            'ht' => 'Haitian Creole',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'mww' => 'Hmong Daw',
            'hu' => 'Hungarian',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'tlh' => 'Klingon',
            'tlh-Qaak' => 'Klingon (pIqaD)',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'mt' => 'Maltese',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr-Cyrl' => 'Serbian (Cyrillic)',
            'sr-Latn' => 'Serbian (Latin)',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'sv' => 'Swedish',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'vi' => 'Vietnamese',
            'cy' => 'Welsh'
        ];
    }

    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */

    private function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl) {
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array(
                'grant_type' => $grantType,
                'scope' => $scopeUrl,
                'client_id' => $clientID,
                'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if (isset($objResponse->error)) {
                throw new Exception($objResponse->error_description);
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            echo "Exception-" . $e->getMessage();
        }
    }

    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
     *
     */

    private function curlRequest($url, $authHeader) {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($authHeader, "Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False);
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }

}
