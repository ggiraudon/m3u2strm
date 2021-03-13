<?php
require __DIR__ . '/vendor/autoload.php';

use M3uParser\M3uParser;

if(!isset($argv[1]))
{
	die("Usage : php m3u2strm <m3u url or m3u filename>\n");
}
error_reporting(E_ALL & ~E_NOTICE);

$m3uParser = new M3uParser();
$m3uParser->addDefaultTags();
try
{
	$raw_m3u = file_get_contents($argv[1]);

	//$data = $m3uParser->parseFile('data.m3u');
	if($data = $m3uParser->parse($raw_m3u))
	{
		/** @var \M3uParser\M3uEntry $entry */
		$index=[];
		foreach ($data as $entry) {

			$meta=[];
			foreach ($entry->getExtTags() as $extTag) {
				switch ($extTag) {
				case $extTag instanceof \M3uParser\Tag\ExtInf: // If EXTINF tag
					$meta['group']=str_replace(["/",".","'","`"],"_",trim($extTag->getAttribute("group-title")));
					$meta['title']=str_replace(["/",".","'","`"],"_",trim($extTag->getAttribute("tvg-name")));
					$meta['title']=str_replace("#EXTINF:0,","",$meta['title']);

				}
			}
			$meta['path']=$entry->getPath();
			$index[$meta['group']][]=$meta;

		}
		foreach($index as $group=>$entries)
		{
			mkdir("output/".$group,0777,true);
			foreach($entries as $entry)
			{
				echo "Group : $group | Title : {$entry['title']}\n";
				file_put_contents("output/".$group."/".$entry['title'].".strm",$entry['path']);
			}

		}
	}else{
		die("Failed to parse m3u file\n");
	}
}catch(Exception $e){
	echo $e;
	die("Error: ".$e->getMessage()."\n");

}
