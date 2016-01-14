<?php

namespace App\Providers\Wiki;

class WikiAPI extends Controller {

	public function xpath($url)
	{
		ini_set('user_agent', 'ContactMyReps/0.1 (https://contactmyreps.org/; developing@contactmyreps.org)');
		$ht = file_get_contents($url);
		$doc = new DOMDocument();
		$doc->loadHTML($ht);
		return new DOMXpath($doc);
	}

	public function representatives()
	{
		$x = $this->xpath('https://en.wikipedia.org/wiki/Current_members_of_the_United_States_House_of_Representatives');
		$table = $x->query('//table[contains(@class, "sortable")]')[1];
		$rows = $x->query('.//tr[td], $table');
		foreach($rows as $row){
			$name = $x->query('.//td[4]//span(@class, "sortkey")]', $row);
			$img = $x->query('.//img', $row)[0];
	    	if ($img->hasAttribute('srcset'))
	    		$src = $this->srcset($img->getAttribute('srcset'));
	    	if (!isset($src))
	    		$src = $img->getAttribute('src');

	    	$this->save($name, $src);
		}
	}

	public static function governors()
	{
		$x = $this->xpath('https://en.wikipedia.org/wiki/List_of_current_United_States_governors');
	    $table = $x->query('//table[contains(@class, "sortable")]')[0];
	    $rows = $x->query('.//tr[td]', $table);
	    foreach($rows as $row){
	    	$name = $x->query('.//td[3]//span[@class="sortkey"]', $row);
	    	if (count($name) > 0){
	    		$name = str_replace(", ", "-", $name[0]->textContent);
		    	$img = $x->query('.//img', $row)[1];
		    	if ($img->hasAttribute('srcset'))
		    		$src = $this->srcset($img->getAttribute('srcset'));
		    	if (!isset($src))
		    		$src = $img->getAttribute('src');
		    	File::put(public_path('images/reps/'.$name.'.jpg'), file_get_contents('http:'.$src));
	    	}
	    }
	}

	public function save($name, $src)
	{
		File::put(public_path('images/reps/'.$name.'.jpg'), file_get_contents('http:'.$src));
	}

	public function srcset($set)
	{
		$srcs = explode(",", $img->getAttribute('srcset'));
		return explode(" ", trim(array_pop($srcs)))[0];
	}

}
