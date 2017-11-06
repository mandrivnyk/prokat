<?

	require_once("./core_functions/product_functions.php");

$brends_arr = GetProductBrends();
/*echo '<pre>';
print_r($product);
echo '</pre>';*/
/*<tr>
		  <td align="right">'.ADMIN_PALATKA_MODEL.'</td>
		  <td><input type=text name="model" value=""></td>
	  </tr>*/
echo '
	  <tr>
	  	  <td align="right">'.ADMIN_PALATKA_CLASS.'</td>
		  <td>
			<select name="class">
				<option value="0">'.ADMIN_CHOICE.'</option>
				<option value="1"'; if($product['class']=='1'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_CLASSIC.'</option>
				<option value="2"'; if($product['class']=='2'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_CAMPING.'</option>
				<option value="3"'; if($product['class']=='3'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_OUTDOOR.'</option>
				<option value="4"'; if($product['class']=='4'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_TREKING.'</option>
				<option value="5"'; if($product['class']=='5'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_EXTREME.'</option>
				<option value="6"'; if($product['class']=='6'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_CLIMBING.'</option>
				<option value="7"'; if($product['class']=='7'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_VELO.'</option>
				<option value="8"'; if($product['class']=='8'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_FISH.'</option>
				<option value="9"'; if($product['class']=='9'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_HUNTER.'</option>
				<option value="10"'; if($product['class']=='10'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_TENT.'</option>
			</select>
		  </td>
	  </tr>
	  <tr>
	  	  <td align="right">'.ADMIN_BREND.'</td>
		  <td>
			<select name="brend_id">';
				echo '<option value="0">'.ADMIN_CHOICE.'</option>';
				for($i=0; $i<count($brends_arr);$i++)
				{
					echo '<option value="'.$brends_arr[$i]['id'].'"'; if($product['brend_id']==$brends_arr[$i]['id']){ echo 'selected';} else echo ''; echo '>'.$brends_arr[$i]['name'].'</option>';
				}
		echo '		
			</select>
		  </td>
	  </tr>
	  <tr>
	  	<td align="right">'.ADMIN_UPLOAD_IMAGE.'</td>
	  	<td><input type="hidden" name="MAX_FILE_SIZE" value="1000000">
					<input type="file" name="photo">';
		if(isset($product['img_small']) && $product['img_small'] !== '')
		{	
				
			echo '<img src="images/products/'.$product['img_small'].'" width="100px;"><br>';
			//echo '<a href="images/products/'.$product['img_orig'].'" target="blank">'.ADMIN_IMG_ORIG.'</a><br>';
			echo ADMIN_IMG_CHANGE.'<br><br>';
			echo '<a href="images/products/'.$product['img_small'].'" target="blank">'.ADMIN_IMG_SMALL.'</a><br><br>';
			echo '<a href="images/products/'.$product['img_big'].'" target="blank">'.ADMIN_IMG_BIG.'</a><br><br>';
			echo '<input type="checkbox" name="del_img" value="1">'.ADMIN_IMG_DEL.'<br>';
		}
		echo'
		</td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_NUM_PLACES.'</td>
		  <td><input type=text name="num_place" value="'; if(isset($product['num_place']) && $product['num_place'] !== '') { echo $product['num_place'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_NUM_ENTRIES.'</td>
		  <td><input type=text name="num_entry" value="'; if(isset($product['num_entry']) && $product['num_entry'] !== '') { echo $product['num_entry'];} 
		echo '"></td>
	  </tr>
	  <tr>
	  	  <td align="right">'.ADMIN_PALATKA_SYSTEM.'</td>
		  <td>
			<select name="system">
				<option value="0">'.ADMIN_CHOICE.'</option>
				<option value="1"'; if($product['system']=='1'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_POLUSFERA.'</option>
				<option value="2"'; if($product['system']=='2'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_POLUBOCHKA.'</option>
				<option value="3"'; if($product['system']=='3'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_DVUHSKATNAJA.'</option>
				<option value="4"'; if($product['system']=='4'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_PODVESNAJA.'</option>
			</select>
		  </td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_TAMBUR_H.'</td>
		  <td><input type=text name="tambur_h" value="'; if(isset($product['tambur_h']) && $product['tambur_h'] !== '') { echo $product['tambur_h'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_TAMBUR_W.'</td>
		  <td><input type=text name="tambur_w" value="'; if(isset($product['tambur_w']) && $product['tambur_w'] !== '') { echo $product['tambur_w'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_NUM_DUG.'</td>
		  <td><input type=text name="num_dugi" value="'; if(isset($product['num_dugi']) && $product['num_dugi'] !== '') { echo $product['num_dugi'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_KARKAS.'</td>
		  <td><select name="karkas">
		  		<option value="0">'.ADMIN_CHOICE.'</option>
		  		<option value="1"'; if($product['karkas']=='1'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_PLASTIC.'</option>
				<option value="2"'; if($product['karkas']=='2'){ echo 'selected';} else echo ''; echo '>'.ADMIN_PALATKA_METALL.'</option>
 		      </select></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_FURNITURE.'</td>
		  <td><input type=text name="furniture" value="'; if(isset($product['furniture']) && $product['furniture'] !== '') { echo $product['furniture'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_TENT_OUT.'</td>
		  <td><input type=text name="tent" value="'; if(isset($product['tent']) && $product['tent'] !== '') { echo $product['tent'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_TENT_INNER.'</td>
		  <td><input type=text name="inner_tent" value="'; if(isset($product['inner_tent']) && $product['inner_tent'] !== '') { echo $product['inner_tent'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_FLOOR.'</td>
		  <td><input type=text name="floor" value="'; if(isset($product['floor']) && $product['floor'] !== '') { echo $product['floor'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_FLOOR_SIZE_H.'</td>
		  <td><input type=text name="floor_size_h" value="'; if(isset($product['floor_size_h']) && $product['floor_size_h'] !== '') { echo $product['floor_size_h'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_FLOOR_SIZE_W.'</td>
		  <td><input type=text name="floor_size_w" value="'; if(isset($product['floor_size_w']) && $product['floor_size_w'] !== '') { echo $product['floor_size_w'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_PLOSCH_SIZE.'</td>
		  <td><input type=text name="size_plosch" value="'; if(isset($product['size_plosch']) && $product['size_plosch'] !== '') { echo $product['size_plosch'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_HEIGHT.'</td>
		  <td><input type=text name="height" value="'; if(isset($product['height']) && $product['height'] !== '') { echo $product['height'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_WEIGHT.'</td>
		  <td><input type=text name="weight" value="'; if(isset($product['weight']) && $product['weight'] !== '') { echo $product['weight'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_SIZE_PACKAGE.'</td>
		  <td><input type=text name="size_package" value="'; if(isset($product['size_package']) && $product['size_package'] !== '') { echo $product['size_package'];} 
		echo '"></td>
	  </tr>
	  <tr>
		  <td align="right">'.ADMIN_PALATKA_COLOR.'</td>
		  <td><input type=text name="color" value="'; if(isset($product['color']) && $product['color'] !== '') { echo $product['color'];} 
		echo '"></td>
	  </tr>
	 ';

?>