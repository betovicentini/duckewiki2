<?php
$file = $_GET['file'];
function highlight_num($file) 
{ 
  $lines = implode(range(1, count(file($file))), '<br />'); 
  $content = highlight_file($file, true); 

  
  echo ' 
    <style type="text/css"> 
        .num { 
        float: left; 
        color: gray; 
        font-size: 13px;    
        font-family: monospace; 
        text-align: right; 
        margin-right: 6pt; 
        padding-right: 6pt; 
        border-right: 1px solid gray;} 

        body {margin: 0px; margin-left: 5px;} 
        td {vertical-align: top;} 
        code {white-space: nowrap;} 
    </style>'; 
    
    
    
    echo "
    <br ><strong>Mostrando código para ".$file."</strong><br ><br >
    <table><tr><td class=\"num\">\n$lines\n</td><td>\n$content\n</td></tr></table>"; 
} 

highlight_num($file);

?>
