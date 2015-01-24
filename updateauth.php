<?php
$db = new SQLite3('DATABASENAME');

//arxh prosdiorismou auth
$authurl = "http://invenio.lib.auth.gr/search?ln=el&p=%CE%A4%CE%BC%CE%AE%CE%BC%CE%B1+%CE%91%CE%B3%CF%81%CE%BF%CE%BD%CF%8C%CE%BC%CF%89%CE%BD+%CE%A4%CE%BF%CF%80%CE%BF%CE%B3%CF%81%CE%AC%CF%86%CF%89%CE%BD+%CE%9C%CE%B7%CF%87%CE%B1%CE%BD%CE%B9%CE%BA%CF%8E%CE%BD&f=&action_search=%CE%91%CE%BD%CE%B1%CE%B6%CE%AE%CF%84%CE%B7%CF%83%CE%B7&c=Psifiothiki&sf=&so=d&rm=&rg=1&sc=1&of=hb";
$authcontent = file_get_contents($authurl);
preg_match('#<strong>Αποτελέσμα έρευνας:</strong> Βρέθηκαν <strong>(.*)</strong>#', $authcontent, $records);
$AuthRecords = $records[1];
echo "Auth Records:$AuthRecords\n";
//telos AuthRecords
$count = $db->querySingle("SELECT COUNT(*) as count FROM diplomas WHERE idryma='AUTH'");
$count = (int) $count;

if ($AuthRecords > $count) {
	$diff = $AuthRecords - $count;
	$url = "http://invenio.lib.auth.gr/rss?f=faculty&cc=Theses&p=%CE%A4%CE%BC%CE%AE%CE%BC%CE%B1+%CE%91%CE%B3%CF%81%CE%BF%CE%BD%CF%8C%CE%BC%CF%89%CE%BD+%CE%A4%CE%BF%CF%80%CE%BF%CE%B3%CF%81%CE%AC%CF%86%CF%89%CE%BD+%CE%9C%CE%B7%CF%87%CE%B1%CE%BD%CE%B9%CE%BA%CF%8E%CE%BD&ln=el&rg=$diff&sc=1&of=xm";
	$xml = simplexml_load_file($url);
	for($i = 0; $i < $diff; $i++){
		$title = $xml->channel->item[$i]->title;
		$link = $xml->channel->item[$i]->link;
		$description = $xml->channel->item[$i]->description;
		$author = $xml->channel->item[$i]->author;
		$pubDate = $xml->channel->item[$i]->pubDate;
		$pubdate = date("Y-m-d", strtotime($pubDate));
    
		$db->exec("INSERT INTO diplomas (idryma,title,author,abstract,date,link) VALUES ('AUTH', '$title', '$author','$description','$pubdate','$link' )"); 
		echo "Inserted '$title' into database <br />";
	}
}
?>
