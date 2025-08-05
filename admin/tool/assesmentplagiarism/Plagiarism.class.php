<?php
class Plagiarism
{
	public $content1;
	public $content2;
	public function __construct()
	{
		$this->content1 = '';
		$this->content2 = '';
	}
	
	function process($content1,$content2)
	{
		$sim = similar_text($content1, $content2, $perc);
		//echo '<br/><hr>';
		return round($perc,1);
		//echo '<hr>';
		
	}
}