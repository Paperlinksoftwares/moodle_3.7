<?php
// Standard GPL and phpdocs
namespace tool_assesmentresults\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class index_page implements renderable, templatable {

    public function __construct($arr, $coursestr, $assessmentstr, $studentsstr, $studentsstrusername,
            $page, $total, $poststudentname, $postcoursename, $postassessmentname, $poststudentusername,
            $cid, $aid, $sid, $suid, $sortval, $sortvalold, $sorttype, $showallstudent, $selectterm,
            $selectyear, $years) {
        $this->arr = $arr; 
        $this->coursestr = $coursestr;
        $this->assessmentstr = $assessmentstr;
        $this->studentsstr = $studentsstr;
        $this->studentsstrusername = $studentsstrusername;
        $this->page = $page;
        $this->listcount = $total;
        $this->poststudentname = $poststudentname;
        $this->postcoursename = $postcoursename;
        $this->postassessmentname = $postassessmentname;
        $this->poststudentusername = $poststudentusername;
        $this->cid = $cid;
        $this->aid = $aid;
        $this->sid = $sid;
        $this->suid = $suid;
        $this->sortval = $sortval;
		$this->sortvalold = $sortvalold;
        $this->sorttype = $sorttype;
        $this->showallstudent = $showallstudent;
        $this->selectterm = $selectterm;
        $this->selectyear = $selectyear;
        $this->years = $years;
    }
 
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->user = $this->arr;
        $data->listcount = $this->listcount;
        $data->page = $this->page;
        $data->coursestr = $this->coursestr;
        $data->assessmentstr = $this->assessmentstr;
        $data->studentsstr = $this->studentsstr;
        $data->studentsstrusername = $this->studentsstrusername;
        $data->poststudentname = $this->poststudentname;
        $data->postcoursename = $this->postcoursename;
        $data->postassessmentname = $this->postassessmentname;
        $data->poststudentusername = $this->poststudentusername;
        $data->cid = $this->cid;
        $data->aid = $this->aid;
        $data->sid = $this->sid;
        $data->suid = $this->suid;
        $data->sortval = $this->sortval;
		$data->sortvalold = $this->sortvalold;
        $data->sorttype = $this->sorttype;
        $data->showallstudent = $this->showallstudent;
        $data->selectterm = $this->selectterm;
        $data->selectyear = $this->selectyear;
        $data->years = $this->years;
        return $data;                                                                                                               
    }
}