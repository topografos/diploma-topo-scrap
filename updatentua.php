<?php
$db = new SQLite3('DATABASENAME');
//arxh prosdiorismou synolikwn records ntua - prosoxh to url na einai atom alliws den doulevei
$ntuaurl = "https://dspace.lib.ntua.gr/open-search/search?rpp=1&query=%CE%91%CE%B3%CF%81%CE%BF%CE%BD%CF%8C%CE%BC%CF%89%CE%BD&format=atom&sort_by=3&order=descending";
$ntuaxml = simplexml_load_file($ntuaurl); 
$NtuaRecords = $ntuaxml->children('http://a9.com/-/spec/opensearch/1.1/')->totalResults;
$NtuaRecords = (int) $NtuaRecords;
echo "Ntua Records:$NtuaRecords\n";
//telos NtuaRecords

$count = $db->querySingle("SELECT COUNT(*) as count FROM diplomas WHERE idryma='NTUA'");
$count = (int) $count;
if ($NtuaRecords > $count) {
	$diff = $NtuaRecords - $count;
	echo "found $diff new entries";
		$group = 1;// omadopoihsh tvn listwn - an o ari8mos einai megalos mporei na episrepsei error
		$div = $diff/$group;
		$div = (int) $div;
		$ypoloipo = $diff%$group;
		//echo "div: $div";
		//echo "ypoloipo: $ypoloipo";
		$rpp = $group;
		if ($diff<$group){
			$rpp = $ypoloipo;
		}
		$i = 0;//oi eggrafes sto dspace metrane apo to mhden
			while ($i <= $diff) {
				$url = "https://dspace.lib.ntua.gr/open-search/search?rpp=$rpp&query=%CE%91%CE%B3%CF%81%CE%BF%CE%BD%CF%8C%CE%BC%CF%89%CE%BD&format=rss&sort_by=3&order=descending&start=$i";
				$xml = simplexml_load_file($url);
				if (!$xml){//an yparxei kapoio la8os me thn antlhsh twn stoixeiwn.. synh8ws shmainei pws den mporei na anazhth8ei h eggrafh
					echo "error rpp:$rpp start:$i";
					$i=$i+$group;
					if ($i == $diff - $ypoloipo){  //
					$rpp = $ypoloipo;
					}		
				}
					for($k = 0; $k < $rpp; $k++){
						//$title = $xml->channel->item[$k]->title;
						$link = $xml->channel->item[$k]->link;
						$ntuaid = substr($link, strrpos($link, '/') + 1);//εξάγει το uid της εργσαίας στο ntua dspace
						$ntuafulllink= "http://dspace.lib.ntua.gr/handle/123456789/$ntuaid?show=full";
						$ntuafullxmllink= "http://dspace.lib.ntua.gr/metadata/handle/123456789/$ntuaid/mets.xml";
						$tags = get_meta_tags($link);
						$fullcontent = file_get_contents($ntuafulllink);
						preg_match('#<meta name="DCTERMS.abstract" content="(.*)" xml:lang="el" />#', $fullcontent, $records);
						$abstract = htmlspecialchars_decode($records[1]);
						if ($abstract == ""){//giati symvainei se para polles h perilhpsh na mp[ainei katw apo to tag heal.abstract
							preg_match('#<td class="label-cell">heal.abstract<\/td>(.*)<td>el<\/td>#misU', $fullcontent, $records);
							$abstract = htmlspecialchars_decode($records[1]);
						}
						if ($abstract == ""){//giati polles fores h perilhpsh den dhlwnetai or8a, opote as traviksei oti mporei
							$abstract = $tags['dcterms_abstract'];
						}
						$abstract = sqlite_escape_string($abstract);
						$title = $tags['dc_title'];
						$author = $tags['citation_authors'];
						$pubDate =$tags['citation_date'];
						$pubdate = date("Y-m-d", strtotime($pubDate));
    
						$db->exec("INSERT INTO diplomas (source_id,idryma,title,author,abstract,date,link) VALUES ('$ntuaid','NTUA', '$title', '$author','$abstract','$pubdate','$link' )"); 
						echo "Inserted '$title' into database <br />";
					}
		
				//echo $url;
				//echo "<br>";	
				$i=$i+$group;
				if ($i == $diff - $ypoloipo){  //
					$rpp = $ypoloipo;
				}		
			}

}
else {
	echo "no new records";
}
?>
