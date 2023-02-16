<?php
echo "<pre>";
$mapurl = "https://maps.google.com/maps?q=51.521068%C2%B0N+0.079533%C2%B0W&hl=en&ie=UTF8&ll=51.52064,-0.080252&spn=0.005247,0.033023&t=h&layer=c&cbll=51.520638,-0.080244&panoid=gznjaF_ki1BR-lIfYbFXiA&cbp=11,16.62,,0,-34.09&z=16";
$headers = get_headers($mapurl);
echo "\n\n\n\n\n\n\n\n\n". $mapurl;
print_r($headers);

$mapurl = "https://www.google.com/maps/place/%C4%B0rem+Bebe+-+Emin%C3%B6n%C3%BC+Ma%C4%9Fazas%C4%B1/@41.0152665,28.9724279,124m/data=!3m1!1e3!4m5!1m2!2m1!1sdeutsche+orient+bank!3m1!1s0x0000000000000000:0x3768bc31c016a4db!6m1!1e1?hl=en";
$headers = get_headers($mapurl);
echo "\n\n\n\n\n\n\n\n\n". $mapurl;
print_r($headers);

echo "</pre>";
?>