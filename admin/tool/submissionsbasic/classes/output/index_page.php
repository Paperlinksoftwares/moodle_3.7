<?php
// Standard GPL and phpdocs
namespace tool_submissionsbasic\output;                                                                                                         
 
use renderable;                                                                                                                     
use renderer_base;                                                                                                                  
use templatable;                                                                                                                    
use stdClass;                                                                                                                       
 
class index_page implements renderable, templatable {                                                                               
    /** @var string $sometext Some text to show how to pass data to a template. */                                                  
    var $sometext = null;                                                                                                           
 
    public function __construct($arr,$coursestr,$assessmentstr,$studentsstr,$studentsstrusername,$page,$total,$poststudentname,$postcoursename,$postassessmentname,$poststudentusername,$cid,$aid,$sid,$suid,$sortval,$sortvalold,$sorttype,$showallstudent,$set,$year,$term) {                                                                                        
        $this->arr = $arr;                        
        $this->page = $page;  
        $this->coursestr = $coursestr;
        $this->listcount = $total;
        $this->studentsstr = $studentsstr;
        $this->assessmentstr = $assessmentstr;
        $this->studentsstrusername = $studentsstrusername;
        $this->postcoursename = $postcoursename;
        $this->poststudentname = $poststudentname;
        $this->poststudentusername = $poststudentusername;
        $this->postassessmentname = $postassessmentname;
        $this->cid = $cid;
        $this->sid = $sid;
        $this->suid = $suid;
        $this->aid = $aid;
        $this->sortval = $sortval;
        $this->sortvalold = $sortvalold;
        $this->sorttype = $sorttype;
        $this->showallstudent = $showallstudent;
		$this->set = $set;
		$this->term = $term;
		$this->year = $year;
    }
 
    /**                                                                                                                             
     * Export this data so it can be used as the context for a mustache template.                                                   
     *                                                                                                                              
     * @return stdClass                                                                                                             
     */                                                                                                                             
    public function export_for_template(renderer_base $output) {                                                                    
        $data = new stdClass();    
        $data->user=$this->arr;
        $data->listcount=$this->listcount;
        $data->page=$this->page;
        $data->coursestr=$this->coursestr;
        $data->studentsstrusername=$this->studentsstrusername;
        $data->studentsstr=$this->studentsstr;
        $data->assessmentstr = $this->assessmentstr;
        $data->postcoursename=$this->postcoursename;
        $data->poststudentname=$this->poststudentname;
        $data->poststudentusername=$this->poststudentusername;
        $data->postassessmentname = $this->postassessmentname;
        $data->cid = $this->cid;
        $data->sid = $this->sid;
        $data->suid = $this->suid;
        $data->aid = $this->aid;
        $data->sortval = $this->sortval;
        $data->sortvalold = $this->sortvalold;
        $data->sorttype = $this->sorttype;
        $data->showallstudent = $this->showallstudent;
		$data->set = $this->set;
		$data->year = $this->year;
		$data->term = $this->term;
         
        return $data;                                                                                                               
    }
}