
<tr>
	<td colspan=2 align=center>

		<center>
			<a href="JavaScript:PhotoHideTable();">
				<?php echo ADMIN_PHOTOS;?>
			</a>
			<input type=hidden name='PhotoHideTable_hidden'
					value='<?php echo $showPhotoTable;?>'>
		</center>

		<script language='javascript'>

		function PhotoHideTable()
		{
			if ( PhotoTable.style.display == 'none' )
			{
				PhotoTable.style.display = 'block';
				document.MainForm.PhotoHideTable_hidden.value='1';
			}
			else
			{
				PhotoTable.style.display = 'none';
				document.MainForm.PhotoHideTable_hidden.value='0';
			}
		}

		</script>

		<table id='PhotoTable'><tr><td>
		
		<table border=0 cellpadding=5 cellspacing=1 bgcolor=#C3BD7C>

			<tr>
				<td colspan=5 align=center>
				<b><?php echo ADMIN_PHOTOS;?></b>
				</td>
			</tr>

			<tr bgcolor=#F5F5C5>
				<td><?php echo ADMIN_DEFAULT_PHOTO;?></td>
				<td><?php echo ADMIN_PRODUCT_PICTURE;?></td>
				<td><?php echo ADMIN_PRODUCT_THUMBNAIL;?></td>
				<td><?php echo ADMIN_PRODUCT_BIGPICTURE;?></td>
				<td width=1%>&nbsp;</td>
			</tr>

			<?php
				foreach( $picturies as $picture )
				{
					echo("<tr bgcolor=#FFFFE2>");
					// default picture radio button
					if ( $picture["default_picture"] == 1 )
					{
						$default_picture_exists = true;
						echo("<td><input type=radio name=default_picture value='".$picture["photoID"].
								"' checked></input></td>");
					}
					else
						echo("<td><input type=radio name=default_picture value='".$picture["photoID"].
								"'></input></td>");

					// conventional picture ( filename field )
					echo("<td>");
					echo("		<input type=text name=filename_".$picture["photoID"].
							" value='".$picture["filename"]."'><br>" );
					if ( file_exists("./products_pictures/".$picture["filename"])
						 && trim($picture["filename"]) != "" )
						echo("		<a class=small href='javascript:open_window(\"products_pictures/".$picture["filename"]."\",".GetPictureSize($picture["filename"]).")'>".ADMIN_PHOTO_PREVIEW."</a>");
					else
						echo(ADMIN_PICTURE_NOT_UPLOADED);
					echo("</td>");

					// small picture ( thumbnail field )
					echo("<td>");
					echo("		<input type=text name=thumbnail_".$picture["photoID"].
							" value='".$picture["thumbnail"]."'><br>" );
					if ( file_exists("./products_pictures/".$picture["thumbnail"]) 
						 && trim($picture["thumbnail"]) != "" )
					{
						echo("		<a class=small href='javascript:open_window(\"products_pictures/".$picture["thumbnail"]."\",".GetPictureSize($picture["thumbnail"]).")'>".ADMIN_PHOTO_PREVIEW."</a>");
						echo("		<a class=small href=\"javascript:confirmDelete('".QUESTION_DELETE_PICTURE."', 'products.php?delete_one_picture=1&thumbnail=".$picture["photoID"]."&productID=".$_GET["productID"]."')\">".DELETE_BUTTON."</a>" );
					}
					else
						echo(ADMIN_PICTURE_NOT_UPLOADED);
					echo("</td>");

					// large picture ( enlarged field )
					echo("<td>");
					echo("		<input type=text name=enlarged_".$picture["photoID"].
							" value='".$picture["enlarged"]."'><br>" );
					if ( file_exists("./products_pictures/".$picture["enlarged"]) 
						 && trim($picture["enlarged"]) != "" )
					{
						echo("		<a class=small href='javascript:open_window(\"products_pictures/".$picture["enlarged"]."\",".GetPictureSize($picture["enlarged"]).")'>".ADMIN_PHOTO_PREVIEW."</a>");
						echo("		<a class=small href=\"javascript:confirmDelete('".QUESTION_DELETE_PICTURE."', 'products.php?delete_one_picture=1&enlarged=".$picture["photoID"]."&productID=".$_GET["productID"]."')\">".DELETE_BUTTON."</a>" );
					}
					else
						echo( ADMIN_PICTURE_NOT_UPLOADED );
					echo("</td>");

					// delete button
					echo("<td>");
					?>		
						<a href=
							"javascript:confirmDelete('<?php echo QUESTION_DELETE_PICTURE?>','products.php?productID=<?php echo $_GET["productID"]?>&photoID=<?php echo $picture["photoID"]?>&delete_pictures=1');">
							<img src="images/remove.jpg" border=0 alt="<?php echo DELETE_BUTTON?>">
						</a>
					<?php
					echo("</td>");
					echo("</tr>");
				}
			?>

			<tr>
				<td colspan=5 align=center>
					<?php echo ADD_BUTTON?>:
				</td>
			</tr>

			<tr bgcolor=#FFFFE2>
				<td width=1%>
					<input type=radio name=default_picture
					<?php
						if ( !isset($default_picture_exists) )
						{	
					?>
						checked
					<?php
						}
					?>
						value=-1
						>
					</input>
				</td>
				<td>
				<input type="file" name="new_filename" width=10></td>
				<td><input type="file" name="new_thumbnail"></td>
				<td><input type="file" name="new_enlarged"></td>
				<td width=1%>&nbsp;</td>
			</tr>

		</table>

		<br>
		<center>
			<input type=submit name="save_pictures" value="<?php echo ADMIN_SAVE_PHOTOS?>">
		</center>

		</td></td></table>

		<script language='JavaScript'>
			<?php
			if ( $showPhotoTable == 0 )
			{
			?>
				PhotoTable.style.display = 'none';
			<?php
			}
			?>
		</script>

	</td>
</tr>

<tr>
	<td colspan=2 align=center>
	</td>
</tr>

<?php
// }
?>
