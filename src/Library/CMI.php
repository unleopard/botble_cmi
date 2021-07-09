<?php

namespace Botble\Cmi\Library;


class CMI
{

    public function create_form(array $requestData)
    {

       /* $requestData = [
            "clientid"=>"600001409",
            "amount" => "33.04",
            "okUrl" => "https://cosmos.isla-dev.com/checkout/cmi/success",
            "failUrl" => "https://cosmos.isla-dev.com/checkout/cmi/fail",
            "TranType" => "PreAuth",
            "callbackUrl" => "https://cosmos.isla-dev.com/checkout/cmi/success",
            "shopurl" => "https://cosmos.isla-dev.com",
            "currency" => 504,
            "rnd"=> "0.13410700 1624617765",
            "storetype" => "3D_PAY_HOSTING",
            "hashAlgorithm" => "ver3",
            "lang" => "fr",
            "BillToName" => "said mohammed",
            "BillToCompany" => "",
            "BillToStreet1" => "205, Rue Mostafa el Maani",
            "BillToCity" => "Casablanca",
            "BillToStateProv" => "ben m'sik",
            "BillToCountry" => "MA",
            "email" => "said.mohammed@gmail.com",
            "BillToTelVoice" => "0661616161",
            "encoding" => "UTF-8",
            "AutoRedirect" => "false",
            "CallbackResponse" => "1",
            "oid" => "10000061"
        ];
*/

        $file = str_replace('src/Library', 'resources/views/form.clt.php', __DIR__);
        $html = file_get_contents($file);

        $secret = get_payment_setting('secret', CMI_PAYMENT_METHOD_NAME); // store key
        // $public = get_payment_setting('public', CMI_PAYMENT_METHOD_NAME); // public key
        $hash = $this->hashValue($requestData, $secret); // calcule hash

        $html = str_replace('#hash#', $hash, $html);


        foreach ($requestData as $key => $value) {
            $html = str_replace('#' . $key . '#', $value, $html);
        }

        echo $html;

        exit;
    }




    function hashValue($data, $storeKey){
        $postParams = array();
        foreach ($data as $key => $value){
            array_push($postParams, $key);
        }
        natcasesort($postParams);

        $hashval = "";
        foreach ($postParams as $param) {
            $paramValue = trim(html_entity_decode(preg_replace("/\n$/","",$data[$param]), ENT_QUOTES, 'UTF-8'));
            $escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue));
            $escapedParamValue = preg_replace('/document(.)/i', 'document.', $escapedParamValue);

            $lowerParam = strtolower($param);
            if($lowerParam != "hash" && $lowerParam != "encoding" )	{
                $hashval = $hashval . $escapedParamValue . "|";
            }
        }

        $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
        $hashval = $hashval . $escapedStoreKey;

        $calculatedHashValue = hash('sha512', $hashval);
        $hash = base64_encode (pack('H*',$calculatedHashValue));

        return $hash;
    }







    private function do_post_request($url, $data, $optional_headers = null)
    {
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $url, $php_errormsg");
        }
        exit;
    }

}