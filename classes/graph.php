<?php

include_once ('./config.php');
include_once($CFG->pchartloc  . '/pData.php');
include_once($CFG->pchartloc  . '/pChart.php');

global $CFG;

class Graph {

	private $imagelocation = 'tmp/';
	public $origseriesnames = array ('Commencement', 'Mid-Point', 'Completion');
	public $size = array (700, 230);
	public $title = '';
	public $YAxisName = 'Grade';
	public $XAxisName = '';
	private $filename;
	private $graphtype;
	public $longstrings = array ("l"=>"Learner", "a"=>"Learner/Advisor");

	public function __construct($graphtype='a') {
		global $CFG;
		$this->graphtype = $graphtype;
		$this->generateFileName ();
	}

	public function displayGraph ($dataseries=array()) {
		global $CFG;
		$filename = '';
		switch ($this->graphtype) {
			case 'a':
				$filename = $this->renderLineA($dataseries);
				break;
			case 'b':
				$filename = $this->renderLineB($dataseries);
				break;
		}
		echo '<img src="';
		echo $filename;
		echo '"/>';
	}
	
	private function generateFileName () {
		global $CFG;
		$this->filename = md5(uniqid()) . '.png';
	}
	private function renderLineA ($dataseries=array(), $dest='screen') {
		global $CFG;
		// Dataset definition
		$DataSet = new pData;
		
		// add dataserieses - giving each a unique name (Serie1, Serie2, etc) using $counter;
		$counter = 1;
		foreach ($dataseries as $series) {
			$DataSet->AddPoint($series,"Serie" . $counter);
			$counter ++;
		}

		$DataSet->AddAllSeries();
		$DataSet->SetAbsciseLabelSerie();
		
		// add series names
		$counter = 1;
		foreach ($this->seriesnames as $name) {
			$DataSet->SetSerieName($name, "Serie" . $counter++);
		}
		
		$DataSet->SetYAxisName($this->YAxisName);
		$DataSet->SetXAxisName($this->XAxisName);
		$DataSet->SetXAxisUnit("");
		//$DataSet->SetYAxisUnit("µs");

		// Initialise the graph
		$Test = new pChart($this->size[0], $this->size[1]);
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",8);
		$Test->setGraphArea(70,30,680,200);
		$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
		$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
		$Test->drawGraphArea(255,255,255,TRUE);
		//$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
		$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,0,0,0);

		$Test->drawGrid(4,TRUE,230,230,230,50);

		// Draw the 0 line
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",6);
		$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

		// Draw the line graph
		$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
		$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

		// Finish the graph
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",8);
		$Test->drawLegend(75,35,$DataSet->GetDataDescription(),255,255,255);
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",10);
		$Test->drawTitle(60,22,$this->title,50,50,50,585);
		// $Test->Stroke("example1.png");

		
		
		$Test->Render ( $CFG->pcharttmp . '/' . $this->filename);
		
		return $CFG->pcharttmphttp . '/' . $this->filename;

	}
	
private function renderLineB ($dataseries=array(), $dest='screen') {
	global $CFG;
	$this->YAxisName = '';
	$this->size = array (800, 250);
	$this->seriesnames = array ('Q1', 'Q2', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8');
		// Dataset definition
		$DataSet = new pData;
		
		// add dataserieses - giving each a unique name (Serie1, Serie2, etc) using $counter;
		$counter = 1;
	//	print_r ($dataseries);
		
		foreach ($dataseries as $series) {
			
			$DataSet->AddPoint($series,"Serie" . $counter);
			$counter ++;
		}

			$DataSet->AddSerie ("Serie1");
			$DataSet->AddSerie ("Serie2");
			$DataSet->AddSerie ("Serie3");
			$DataSet->AddSerie ("Serie4");
			$DataSet->AddSerie ("Serie5");
			$DataSet->AddSerie ("Serie6");
		
	//	$DataSet->AddAllSeries();
		
		
		// add series names
		$counter = 1;
		foreach ($this->seriesnames as $name) {
			$DataSet->SetSerieName($name, "Serie" . $counter++);
		}
		
		$DataSet->SetAbsciseLabelSerie("Serie7");
		
		
		//$DataSet->SetXAxisFormat('date');
		$DataSet->SetYAxisName($this->YAxisName);
		$DataSet->SetXAxisName($this->XAxisName);
		$DataSet->SetXAxisUnit("");
		//$DataSet->SetYAxisUnit("µs");

		// Initialise the graph
		$Test = new pChart($this->size[0], $this->size[1]);
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",8);
		$Test->setGraphArea(100, 30, 680, 200);
	//	$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
	//	$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
		$Test->drawGraphArea(255,255,255,TRUE);
		//$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
		$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,0,0,0);

		$Test->drawGrid(4,TRUE,230,230,230,50);

		// Draw the 0 line
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",6);
		$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

		// Draw the line graph
		$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
		$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

		// Finish the graph
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",8);
		$Test->drawLegend(110, 35, $DataSet->GetDataDescription(), 255, 255,255);
		$Test->setFontProperties($CFG->pchartloc  . "/Fonts/tahoma.ttf",10);
		$Test->drawTitle(60,22,$this->title,50,50,50,685);
		// $Test->Stroke("example1.png");

		
		
		//TODO - create new guid for each image?
		$Test->Render ( $CFG->pcharttmp . '/' . $this->filename);
		return $CFG->pcharttmphttp . '/' . $this->filename;

	}
}