<?php
/**
 * Token Handler
 *
 * @author: Michael Berneis
 *
 * expires every 10min
 * make sure to refresh in time
 */
require_once "config.php";

$ClientSecret = urlencode( $ClientSecret );
$ClientID = urlencode( $ClientID );

// Get a 10-minute access token for Microsoft Translator API.
$url = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13";
$postParams = "grant_type=client_credentials&client_id=$ClientID&client_secret=$ClientSecret&scope=http://api.microsofttranslator.com";
error_log( "curl -d '{$postParams}'' '{$url}'\n" );

$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $postParams );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
$rsp = curl_exec( $ch );

$obj = json_decode( $rsp );
$obj->token = urldecode( $obj->access_token );
error_log( print_r( $obj, true ) );

error_log( "curl -H 'Authorization:bearer {$obj->access_token}'  'http://api.microsofttranslator.com/V2/Http.svc/Translate?text=fish&from=en&to=fr'" );

print $rsp;
