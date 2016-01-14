<?php

class WikiAPI {

	public static function governors()
	{
		ini_set('user_agent', 'ContactMyReps/0.1 (https://contactmyreps.org/; developing@contactmyreps.org)');
		$ht = file_get_contents('https://en.wikipedia.org/wiki/List_of_current_United_States_governors');
		$doc = new DOMDocument();
		$doc->loadHTML($ht);
		$x = new DOMXpath($doc);
	    $table = $x->query('//table[contains(@class, "sortable")]')[0];
	    $rows = $x->query('.//tr[td]', $table);
	    foreach($rows as $row){
	    	$name = $x->query('.//td[3]//span[@class="sortkey"]', $row);
	    	if (count($name) > 0){
	    		$name = str_replace(", ", "-", $name[0]->textContent);
		    	$img = $x->query('.//img', $row)[1];
		    	if ($img->hasAttribute('srcset')){
		    		$srcs = explode(",", $img->getAttribute('srcset'));
		    		$src = explode(" ", trim(array_pop($srcs)))[0];
		    	}
		    	if (!isset($src)) $src = $img->getAttribute('src');

		    	File::put(public_path('images/reps/'.$name.'.jpg'), file_get_contents('http:'.$src));
	    	}
	    }
	}
}