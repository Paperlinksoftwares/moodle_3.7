<?php
// Standard GPL and phpdocs
namespace tool_assesmentplagiarism\output;                                                                                                         
 
use renderable;                                                                                                                     
use renderer_base;                                                                                                                  
use templatable;                                                                                                                    
use stdClass;                                                                                                                       
 
class index_page implements renderable, templatable {                                                                               
    /** @var string $sometext Some text to show how to pass data to a template. */                                                  
    var $sometext = null;                                                                                                           
 
    public function __construct($arr,$coursestr,$assessmentstr,$studentsstr,$studentsstrusername,$page,$total,$poststudentname,$postcoursename,$postassessmentname,$poststudentusername,$cid,$aid,$sid,$suid,$sortval,$sortvalold,$sorttype,$showallstudent,$set,$score_array_mean,$plag_score_array) {                                                                                        
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
		$this->score_array_mean = $score_array_mean;
		$this->plag_score_array = $plag_score_array;
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
		$data->score_array_mean = $this->score_array_mean;
		$data->plag_score_array = $this->plag_score_array;
         
        return $data;                                                                                                               
    }
}