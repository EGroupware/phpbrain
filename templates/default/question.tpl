<table width=100% style='border:1px solid black;'>
	<tr>
		<td align=center style='color: red'>{message}</td>
	</tr>
	<tr class=th>
		<td colspan=2><b>{lang_search_kb}:</b></td>
	</tr>
	<tr>
		<td colspan=2 style='padding:10px 0 10px 0'>
			{lang_enter_words}:<br>
			<form name="search" method=POST action={search_target}>
				<input type="text" name="query" size=90%>&nbsp;<input type="submit" name="Search" value="Search">
				&nbsp;&nbsp;<a href="{link_adv_search}">{lang_advanced_search}</a>
			</form>
		</td>
	</tr>
</table>
<br>
<div align="center" style='border:1px solid black;'>
<form method=POST action="{form_question_action}">
	<table width=100%>
		<tr class=th>
			<td align=left colspan=2><b>{lang_post_question}:</b></td>
		</tr>
		<tr>
			<td width=1%>{lang_summary}:</td><td><input type=text name="summary" style="width:90%"></td>
		</tr>
		<tr>
			<td valign=top>{lang_details}:</td><td><textarea name="details" style="width:90%; height:100px"></textarea></td>
		</tr>
		<tr>
			<td>{lang_select_cat}:</td>
			<td>
				<select name="cat_id">
					<option value="0" selected>{lang_none}</option>
					{select_category}
				</select>
			</td>
		</tr>
		<tr>
			<td colspan=2><br><b>{posting_process}</b><br><br></td>
		</tr>
		<tr>
			<td colspan=2><input type=submit name=submit value="{lang_submit}">&nbsp;&nbsp;&nbsp;<input type=submit name=cancel value="{lang_cancel}"></td>
		</tr>
	</table>
</form>
</div>
