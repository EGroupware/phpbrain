<!-- BEGIN header -->
<p style="text-align: center; color: {th_err};">{error}</p>
<form method="POST" action="{action_url}">
{hidden_vars}
<table border="0" align="center">
   <tr class="th">
    <th colspan="2">{title}</th>
   </tr>
<!-- END header -->

<!-- BEGIN body -->
<tr class="row_on">
	<td colspan="2">&nbsp;</td>
</tr>
<tr class="row_off">
	<td colspan="2"><b>{lang_Knowledge_Base_configuration}</b></td>
</tr>
<tr class="row_on">
	<td>{lang_Publish_articles_automatically?}</td>
	<td>
		<select name="newsettings[publish_articles]">
			<option value="True"{selected_publish_articles_True}>{lang_Yes}</option>
			<option value="False"{selected_publish_articles_False}>{lang_Have_to_be_approved_first}</option>
		</select>
	</td>
</tr>
<tr class="row_off">
	<td>{lang_Publish_comments_automatically?}</td>
	<td>
		<select name="newsettings[publish_comments]">
			<option value="True"{selected_publish_comments_True}>{lang_Yes}</option>
			<option value="False"{selected_publish_comments_False}>{lang_Have_to_be_approved_first}</option>
		</select>
	</td>
</tr>
<tr class="row_on">
	<td>{lang_Publish_questions_automatically?}</td>
	<td>
		<select name="newsettings[publish_questions]">
			<option value="True"{selected_publish_questions_True}>{lang_Yes}</option>
			<option value="False"{selected_publish_questions_False}>{lang_Have_to_be_approved_first}</option>
		</select>
	</td>
</tr>
<tr class="row_off">
	<td>  
	  	{lang_Image_directory_relative_to_document_root_(use_/_!),_example:} /images<br />
  		{lang_An_existing_AND_by_the_webserver_readable_directory_enables_the_image_browser_and_upload.}<br />
  		{lang_Upload_requires_the_directory_to_be_writable_by_the_webserver!}
	<td>
		<input name="newsettings[upload_dir]" size="40" value="{value_upload_dir}"/>
	</td>
</tr>
<tr class="row_on">
	<td>{lang_Should_the_article_view_make_an_automatic_table_of_contents?}</td>
	<td>
		<select name="newsettings[show_toc]">
			<option value="False"{selected_show_toc_False}>{lang_No}</option>		
			<option value="True"{selected_show_toc_True}>{lang_Yes}</option>			
		</select>
	</td>
</tr>
<tr class="row_off">
	<td>{lang_Name_of_the_backlink_to_the_index:}</td>
	<td>
		<input name="newsettings[backlinkText]" size="40" value="{value_backlinkText}"/>
	</td>
</tr>
<!-- END body -->

<!-- BEGIN footer -->
  <tr class="th" >
    <td colspan="2">
&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
