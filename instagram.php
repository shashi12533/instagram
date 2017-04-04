<?php
$phinstagram_json_object = null;
$username = readline("Enter username to get the link: ");
define("TMP_DIR", "/tmp");
define("CACHE_FILE_NAME",$username.".json");
define("LOCAL_CACHE_IN_SECONDS", 300);

	if (file_exists(TMP_DIR."/".CACHE_FILE_NAME) && (filemtime(TMP_DIR."/".CACHE_FILE_NAME) > (time() - LOCAL_CACHE_IN_SECONDS )))
	{

			$phinstagram_json_object = json_decode(file_get_contents(TMP_DIR."/".CACHE_FILE_NAME));
	}
	else
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/$username/");
		// return data
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// set a custom useragent
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36');
		$html = curl_exec($ch);
		curl_close($ch);

		// Create a new DOMDocument to parse the result in
		$doc = new DOMDocument();

		//supress the HTML5 tags warning //
		libxml_use_internal_errors(true);

		// Load content as DOMDocument //
		 $doc->loadHTML($html);

		// Check every line in the textContent node //
		foreach(explode("\n", $doc->textContent) as $line) {
			// When string found, fix it and make a json object from it
			if (strpos($line, 'window._sharedData = ') !== false) {
				// do some cleanup before we can use it as json
				$json_string = str_replace("window._sharedData = ",'',$line);
				$json_string = substr_replace($json_string ,"",-1,1);
				// decode the string to an object //
				$phinstagram_json_object = json_decode($json_string);
			}
		}

		if($phinstagram_json_object == NULL)
		{
			// return last working json string if it exists //
			if (file_exists(TMP_DIR."/".CACHE_FILE_NAME)) {
				$phinstagram_json_object = json_decode(file_get_contents(TMP_DIR."/".CACHE_FILE_NAME));
			} else {
				//oh nooo .. we didnt have a stored old json string on disk.. nor parsing instagram.com site succeeded!!! FAIL, DIE!
				$phinstagram_json_object = array("error" => json_last_error());
				}
		}
		else
		{
			// save to local cache if the new one works //
			file_put_contents(TMP_DIR."/".CACHE_FILE_NAME, $json_string);
		}
	} #end if statment time cache #
header('Content-type: application/json');
$data= ($phinstagram_json_object);
//$data1 = array($data);
//echo $data1;
echo($data->entry_data->ProfilePage[0]->user->media->nodes[0]->display_src);
echo "\n";
//echo gettype($data);








