<?php
// Standard GPL and phpdocs
namespace tool_assesmentresults\output;                                                                                                         
 
use renderable;                                                                                                                     
use renderer_base;                                                                                                                  
use templatable;                                                                                                                    
use stdClass;                                                                                                                       
 
class index_page implements renderable, templatable {                                                                               
    /** @var string $sometext Some text to show how to pass data to a template. */                                                  
    var $sometext = null;                                                                                                           
 
    public function __construct($arr,$coursestr,$studentsstr,$page,$total,$poststudentname,$postcoursename,$cid,$sid,$sortval) {                                                                                        
        $this->arr = $arr;                        
        $this->page = $page;  
        $this->coursestr = $coursestr;
        $this->listcount = $total;
        $this->studentsstr = $studentsstr;
        $this->postcoursename = $postcoursename;
        $this->poststudentname = $poststudentname;
        $this->cid = $cid;
        $this->sid = $sid;
        $this->sortval = $sortval;
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
        $data->studentsstr=$this->studentsstr;
        $data->postcoursename=$this->postcoursename;
        $data->poststudentname=$this->poststudentname;
        $data->cid = $this->cid;
        $data->sid = $this->sid;
        $data->sortval = $this->sortval;
        return $data;                                                                                                               
    }
}