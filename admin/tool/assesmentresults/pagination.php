<?php
function displayPaginationHere($total,$per_page,$page){
       $params = $_GET;
       unset($params['page']);
       $page_url = '?';
       if (!empty($params)) {
           $page_url .= http_build_query($params) . '&';
       }
      // global $DB;
        //$query = "SELECT COUNT(*) as totalCount FROM pagination_data";
        //$rec = mysql_fetch_array(mysql_query($query));
        //$total = $rec['totalCount'];
       
        //$total = 100;
        $adjacents = "2"; 

        $page = ($page == 0 ? 1 : $page);  
        $start = ($page - 1) * $per_page;                                
        
        $prev = $page - 1;                            
        $next = $page + 1;
        $setLastpage = ceil($total/$per_page);
        $lpm1 = $setLastpage - 1;
        
        $paging = "<div align='center'>";
        if ($setLastpage > 1)
        {    
            $paging .= "<ul class='pagination'>";
                    //$paging .= "<li class='setPage'>Page $page of $setLastpage</li>";
            if ($setLastpage < 7 + ($adjacents * 2))
             {   
                for ($counter = 1; $counter <= $setLastpage; $counter++)
                {
                    if ($counter == $page)
                        $paging.= "<li><a class='current_page'>$counter</a></li>";
                    else
                        $paging.= "<li><a href='{$page_url}page=$counter'>$counter</a></li>";                    
                }
            }
            elseif($setLastpage > 5 + ($adjacents * 2))
              {
                if($page < 1 + ($adjacents * 2))        
                 {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                     {
                        if ($counter == $page)
                            $paging.= "<li><a class='current_page'>$counter</a></li>";
                        else
                            $paging.= "<li><a href='{$page_url}page=$counter'>$counter</a></li>";                    
                    }
                    $paging.= "<li class='dot'>...</li>";
                    $paging.= "<li><a href='{$page_url}page=$lpm1'>$lpm1</a></li>";
                    $paging.= "<li><a href='{$page_url}page=$setLastpage'>$setLastpage</a></li>";        
                }
                elseif($setLastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
                 {
                    $paging.= "<li><a href='{$page_url}page=1'>1</a></li>";
                    $paging.= "<li><a href='{$page_url}page=2'>2</a></li>";
                    $paging.= "<li class='dot'>...</li>";
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                     {
                        if ($counter == $page)
                            $paging.= "<li><a class='current_page'>$counter</a></li>";
                        else
                            $paging.= "<li><a href='{$page_url}page=$counter'>$counter</a></li>";                    
                    }
                    $paging.= "<li class='dot'>...</li>";
                    $paging.= "<li><a href='{$page_url}page=$lpm1'>$lpm1</a></li>";
                    $paging.= "<li><a href='{$page_url}page=$setLastpage'>$setLastpage</a></li>";        
                }
                else
                 {
                    $paging.= "<li><a href='{$page_url}page=1'>1</a></li>";
                    $paging.= "<li><a href='{$page_url}page=2'>2</a></li>";
                    $paging.= "<li class='dot'>...</li>";
                    for ($counter = $setLastpage - (2 + ($adjacents * 2)); $counter <= $setLastpage; $counter++)
                     {
                        if ($counter == $page)
                            $paging.= "<li><a class='current_page'>$counter</a></li>";
                        else
                            $paging.= "<li><a href='{$page_url}page=$counter'>$counter</a></li>";                    
                    }
                }
            }
            
            if ($page < $counter - 1){ 
                $paging.= "<li><a href='{$page_url}page=$next'>Next</a></li>";
                $paging.= "<li><a href='{$page_url}page=$setLastpage'>Last</a></li>";
            } else  {
                $paging.= "<li><a class='current_page'>Next</a></li>";
                $paging.= "<li><a class='current_page'>Last</a></li>";
            }

            $paging.= "</ul></div>\n";        
        }
    
        return $paging;
    } 
?>