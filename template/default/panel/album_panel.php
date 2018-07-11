<?php

if (!isset($albums))
    die("Invalid Request!");

    echo '<aside id="main_aside" class="col-sm-2"><table>';
    foreach ($albums as $alb){
	
		echo '<tr>
				<td>
					<a class="albumLink" id="albumID-'.$alb['albumID'].'">'.$alb['name'].'</a>
				</td>
				<td>'.$alb['photos'].' photos </td>	
				<td>'.$alb['created_date'].'</td>
			</tr>';
	
	}
    echo '</table></aside>';

?>