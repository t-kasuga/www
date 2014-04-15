<?php
/**
  memcachedの内容を確認する
  http://blog.cles.jp/item/2141
 */

$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

$key = null;
if(isset($_GET['cacheKey'])){
	$key = $_GET['cacheKey'];
}

if(isset($_GET['flush'])) {
	$items = $memcache->getStats('items');
	foreach($items['items'] as $key => $item){
		$number = $item['number'];
		$dump = $memcache->getStats('cachedump', $key, $number*2);
		foreach( $dump as $ckey => $carr ){
			$memcache->delete($ckey);
		}
	}
}

$items = $memcache->getStats('items');

$count = 0;
$ktotal = 0;
$vtotal = 0;
$outline = '';

foreach($items['items'] as $key => $item) {
	$number = $item['number'];
	$dump = $memcache->getStats('cachedump', $key, $number*2);
	foreach($dump as $ckey => $carr) {
		$outline .= "$key:$ckey: [{$carr[0]}b; {$carr[1]}s]\n";
		$count++;
		$vtotal += $carr[0];
		$ktotal += strlen($ckey);
	}
}
$total = $vtotal + $ktotal;

//==================================================================================================
?>

<html>
<head>
 <title>memcached - cached items</title>
</head>
<body>

<h2>Item Detail</h2>

<form action="" method="get">
  <input type="text" name="cacheKey" value="<?php echo $key ?>"/>
  <input type="submit" value="submit" />
</form>

<?php if($key){ ?>
<h3>Key: <?php echo $key ?></h3>
<pre>
  <?php echo var_dump($memcache->get($key)); ?>
</pre>
<?php } ?>

<h2>Cached Items</h2>

<form action="" method="get">
  <input type="hidden" name="flush" value="1"/>
  <input type="submit" value="flush!" />
</form>

<pre>
<?php echo $outline ?>
--
key total: <?php echo $ktotal ?> b
value total: <?php echo $vtotal ?> b
total: <?php echo $total ?> b
count: <?php echo $count ?>
--
<?php
$memcache->set("str_key", "文字列を格納");
$memcache->set("num_key", 123); // 数値を格納

$object = new StdClass;
$object->attribute = 'test';
$memcache->set("obj_key", $object); // オブジェクトを格納

$array = Array('assoc'=>123, 345, 567);
$memcache->set("arr_key", $array);  // 配列を格納

echo nl2br( print_r($memcache->get('str_key'), true ))."<br />";
echo nl2br( print_r($memcache->get('num_key'), true ))."<br />";
echo nl2br( print_r($memcache->get('obj_key'), true ))."<br />";
echo nl2br( print_r($memcache->get('arr_key'), true ))."<br />";
?>
</pre>

</body>
</html>
