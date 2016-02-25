<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DOMDocument;
use DOMXpath;
use File;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class WikiController extends Controller
{

    public function xpath($url)
    {
        ini_set('user_agent', 'ContactMyReps/0.1 (https://contactmyreps.org/; developing@contactmyreps.org)');
        $ht = file_get_contents($url);
        $doc = new DOMDocument();
        $doc->loadHTML($ht);
        return new DOMXpath($doc);
    }

    public function senators()
    {
        $x = $this->xpath('https://en.wikipedia.org/wiki/List_of_current_United_States_Senators');
        $table = $x->query('//table[contains(@class, "sortable")]')[0];
        $rows = $x->query('.//tr[td]', $table);
        foreach($rows as $row){
            $name = $x->query('.//td[5]//span[contains(@class, "sortkey")]', $row)[0]->textContent;
            $img = $x->query('.//img', $row)[0];
            $src = $this->getSrc($img);
            $this->save($name, $src);
        }
    }

    public function house()
    {
        $x = $this->xpath('https://en.wikipedia.org/wiki/Current_members_of_the_United_States_House_of_Representatives');
        $table = $x->query('//table[contains(@class, "sortable")]')[1];
        $rows = $x->query('.//tr[td]', $table);
        foreach($rows as $row){
            $name = $x->query('.//td[4]//span[contains(@class, "sortkey")]', $row)[0]->textContent;
            $img = $x->query('.//img', $row)[0];
            $src = $this->getSrc($img);
            $this->save($name, $src);
        }
    }

    public function governors()
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
        $name = str_replace(', ', '-', $name);
        $name = str_replace(' ', '-', $name);
        $ns = explode('-', $name);
        $ns = array_filter($ns, function($i){
            return strlen($i) > 2; //no initials
        });
        $name = implode("-", $ns);
        File::put(public_path('images/reps/'.$name.'.jpg'), file_get_contents('http:'.$src));
    }

    public function getSrc($img)
    {
        if ($img->hasAttribute('srcset')){
            $srcs = explode(',', $img->getAttribute('srcset'));
            return explode(" ", trim(array_pop($srcs)))[0];
        }
        if ($img->hasAttribute('src'))
            return $img->getAttribute('src');
    }

}
