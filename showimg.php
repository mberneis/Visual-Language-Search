<?php
/**
 * Ajax Image Handler
 *
 * @author Michael Berneis
 *
 */

require_once "config.php";

$locales = array(
  'ar' => 'ar-XA',
  'bg' => 'bg-BG',
  'cs' => 'cs-CZ',
  'da' => 'da-DK',
  'nl' => 'nl-NL',
  'en' => 'en-US',
  'et' => 'et-EE',
  'fi' => 'fi-FI',
  'fr' => 'fr-FR',
  'de' => 'de-DE',
  'el' => 'el-GR',
  'he' => 'he-IL',
  'hu' => 'hu-HU',
  'it' => 'it-IT',
  'ja' => 'ja-JP',
  'ko' => 'ko-KR',
  'lv' => 'lv-LV',
  'lt' => 'lt-LT',
  'no' => 'nb-NO',
  'pl' => 'pl-PL',
  'pt' => 'pt-PT',
  'ro' => 'ro-RO',
  'ru' => 'ru-RU',
  'sk' => 'sk-SK',
  'sl' => 'sl-SL',
  'es' => 'es-ES',
  'sv' => 'sv-SE',
  'th' => 'th-TH',
  'tr' => 'tr-TR',
  'uk' => 'uk-UA'
);

$locale= $locales[$_GET['locale']];
$term = $_GET['term'];

$cred = sprintf( 'Authorization: Basic %s', base64_encode( "ignored:" . $AccountKey ) );

$context = stream_context_create( array(
    'http' => array(
      'header'  => $cred
    )
  ) );

$request ='https://api.datamarket.azure.com/Bing/Search/v1/Image?$format=json&Query=%27'.urlencode( $term ).'%27&Market=%27'.urlencode( $locale ).'%27';

$response = file_get_contents( $request, 0, $context );

$jsonobj = json_decode( $response );

foreach ( $jsonobj->d->results as $value ) {
  $url = $value->SourceUrl;
  $pic = $value->Thumbnail->MediaUrl;
  print "<div><a href='$url' target='_blank'><img  style='width:100%' src='".$pic."' /></a></div>";
}
