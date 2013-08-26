<?php
/**
 * Visual Language Search
 *
 * @author: Michael Berneis
 */
$first = @$_POST['first'];
$second = @$_POST['second'];

if ( $first && $second ) {
  $res1 = get_results( $first );
  $res2 = get_results( $second );

  $results = array();
  $left = array();
  $right=array();
  foreach ( $res1 as $r ) {
    $results[$r->imageId] = $r;
    $left[] = $r->imageId;
  }
  foreach ( $res2 as $r ) {
    $results[$r->imageId] = $r;
    $right[] = $r->imageId;
  }

  $middle = array_intersect( $left, $right );
  $left = array_diff( $left, $middle );
  $right = array_diff( $right, $middle );

  $content1 = "<pre>".print_r( $left, true )."</pre>";
  $content2 = "<pre>".print_r( $right, true )."</pre>";
  $content3 = "<pre>".print_r( $middle, true )."</pre>";
}

function get_results( $term ) {
  $url = "https://ajax.googleapis.com/ajax/services/search/images?v=1.0&rsz=8&q=".urlencode( $term );
  $json = get_content( $url );
  $result = json_decode( $json );
  return $result->responseData->results;
}

function get_content( $url ) {
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  $body = curl_exec( $ch );
  curl_close( $ch );
  return $body;
}

$languages = array(
  'ar' => 'Arabic',
  'bg' => 'Bulgarian',
  'cs' => 'Czech',
  'da' => 'Danish',
  'nl' => 'Dutch',
  'en' => 'English',
  'et' => 'Estonian',
  'fi' => 'Finnish',
  'fr' => 'French',
  'de' => 'German',
  'el' => 'Greek',
  'he' => 'Hebrew',
  'hu' => 'Hungarian',
  'it' => 'Italian',
  'ja' => 'Japanese',
  'ko' => 'Korean',
  'lv' => 'Latvian',
  'lt' => 'Lithuanian',
  'no' => 'Norwegian',
  'pl' => 'Polish',
  'pt' => 'Portuguese',
  'ro' => 'Romanian',
  'ru' => 'Russian',
  'sk' => 'Slovak',
  'sl' => 'Slovenian',
  'es' => 'Spanish',
  'sv' => 'Swedish',
  'th' => 'Thai',
  'tr' => 'Turkish',
  'uk' => 'Ukrainian'
);

function column( $c ) {
  global $languages;
  $html="";
  $deflang = array( 'en', 'de', 'fr', 'es', 'it', 'pt' );
  $id = 'col'.$c;
  $html .= "<div class='span2 id='$id'>\n";
  $html .= "<div class='span2 trans'><input class='span2 myinp' type='text' id='val{$c}' /></div>";
  $lang = ( isset( $_COOKIE[$id] ) ) ? $_COOKIE[$id] : $deflang[$c -1];
  $_COOKIE['$id'] = $lang;
  $html .= "<select class='span2' id='{$id}'>";
  foreach ( $languages as $locale => $name ) {
    $html .= "<option value='{$locale}'";
    if ( $locale==$lang ) $html .= " selected";
    $html .= ">".$name."</option>";
  }
  $html .= "</select>";
  $html .= "<div class='span2 left'  id='img{$c}'></div>";
  $html .= "</div>\n";
  return $html;
}

?><!doctype html>
<head>
    <meta charset="utf-8">
  <title>Multilangual Image Search</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">  <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
  <script src="jquery.cookie.js"></script>
  <script>

  var g_token = '';
  var lastresponse = '';


  function getToken() {
    var requestStr = "./token.php";
    var lastresponse='';

    $.ajax({
      url: requestStr,
      type: "GET",
      cache: true,
      dataType: 'json',
      success: function (data) {
        g_token = data.access_token;
      }
    });
  }

    $(function() {
        $.cookie.raw = true;
        $('select').bind('change',function() {
            var id = $(this).attr('id');
            //console.log ($(this).val()+'|'+id);
            $.cookie (id,$(this).val());
            var col = id.substr(3,1);
            //console.log (col);
            if (col >0)  trans(col);

        });
        $(".myinp").bind('blur',function() {
            var id = $(this).attr('id');
            var col = id.substr(3,1);
            //console.log (col);
            var lastresponse = $(this).val();
            var locale = $("#col"+col).val();
            if (col >0)   $("#img"+col).load("./showimg.php?term="+escape(lastresponse)+'&locale='+locale);
        })
        getToken();
        // Get a new one every 9 minutes.
        setInterval(getToken, 9 * 60 * 1000);
    })
    function trans(dest) {
       var inp = $('#inpsearch').val();
       var from = $('#col0').val();
       var to = $('#col'+dest).val();
       translate (inp,from,to,dest);
    }

    function translate(text, from, to, dest) {
        var p = new Object;
        p.text = text;
        p.from = from;
        p.to = to;

        // A major puzzle solved.  Who would have guessed you specify the jsonp callback in oncomplete?
        p.oncomplete = 'ajaxTranslateCallback';

       // Another major puzzle.  The authentication is supplied in the deprecated appId param.
        p.appId = "Bearer " + g_token;
        $("#img"+dest).html("Loading...");
        var requestStr = "http://api.microsofttranslator.com/V2/Ajax.svc/Translate";

        // console.log(p);
        $("#img"+dest).html('');
        $.ajax({
          url: requestStr,
          type: "GET",
          data: p,
          dataType: 'jsonp',
          complete: function (data) {
            $('#val'+dest).val(lastresponse);
            var locale = $("#col"+dest).val();
            $("#img"+dest).load("./showimg.php?term="+escape(lastresponse)+'&locale='+locale)
          },
          cache: true
        });
      }

    function ajaxTranslateCallback(response) {
      lastresponse = response;
    }
    function doit() {
      for (var i=1; i<=6; i++) trans(i);
        $("#intro").hide();
        $("#main").show();
      return false;
    }
  </script>
  <style>
    .trans {
      font-weight:bold;
      background: #f7f7f7;
      border:1px solid #ddd;
      margin-left:0;
    }
    .left {margin-left:0;}
    img {padding: 2px 0;}
    #intro {font-size:150%;line-height:180%;}
    #intro p {color:#777;margin-bottom:20px;}
  </style>
</head>
 <body>

  <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="index.php">Multilangual Image Search</a>
          <div class="nav-collapse collapse">
               <form class="navbar-form pull-right" onsubmit="return doit()">
                <select class="span2" id='col0'>
                  <?php
$lang = ( isset( $_COOKIE['col0'] ) ) ? $_COOKIE['col0'] : 'en';
foreach ( $languages as $locale => $name ) {
  $html .= "<option value='{$locale}'";
  if ( $locale==$lang ) $html .= " selected";
  $html .= ">".$name."</option>";
}
echo $html;
?>
                </select>
                <input class="span2" type="search"  id='inpsearch' placeholder="Search Term">
                <input type="submit" class="btn btn-success btn-search">Search</button>
              </form>
          </div>
        </div>
      </div>
    </div>



    <div class="container" style='margin-top:50px;'>

      <div class='row' id='intro'>
        <h2>What is this about?</h2>
        <p>The idea for this application arose from a conversation betwen <a href='http://www.minigorille.com'>Lysandre Follet</a> and <a href='http://michael.berneis.com'>Michael Berneis</a> during a cup of coffee at the Nike cateferia in March 2013.</p>
        <p>Lysandre discovered that visual searches performed in different languages against their respective language environments show interesting correlations which pure US speakers are not really aware of.</p>
        <p>To simplify the process of creating a multi-language matrix of image searches, Michael coded this app.</p>
        <p>Just enter a generic word into the search box above (you can start of in a variety of languages) and watch the results. The site also allows you to switch the 6 output languages to others of your choice and your choices will be stored in local cookies to be remembered across sessions </p>
        <p>Or set the all to the same language and change the translated terms - It's all up to you...</p>
        <hr>
        <h6>Translations and Image search was implemented via the Bing API's as Google does not allow free translation calls and restricts the number of image results in their basic free API.</h6>
        <h6>Bing limits its free API to 5000 queries / month - so be gentle</h6>


      </div>

      <div class='row' id='main' style='display:none'>
      <?php
for ( $i=1; $i <=6; $i++ ) echo column( $i );
?>
      </div>
    </div> <!-- /container -->


  </body></html>
