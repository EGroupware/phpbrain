<form method="POST" action="{form_action}" style="height:100%;">
{hidden_fields}
<table  width="100%" border="0" cellspacing="1" cellpadding="3">
	{message}
	<!-- BEGIN answer_question_block -->
	<tr class="th">
		<td colspan=2><b>{lang_head_question}:</b></td>
	</tr>
	<tr>
		<td align=right>{lang_summary}: </td><td>{question_summary}</td>
	</tr>
	<tr>
		<td align=right>{lang_details}:</td><td>{question_details}</td>
	</tr>
	<!-- END answer_question_block -->
	<!-- BEGIN article_id_block -->
	<tr class="th">
		<td align=right>
			{lang_articleID}:
		</td>
		<td>
			{show_articleID}
		</td>
	</tr>
	<!-- END article_id_block -->
	<tr class="row_on">
		<td width="10%" align="right">
			<span style='font:normal 12px sans-serif'>{lang_category}:</span>
		</td>
		<td width="90%">
			<select name="cat_id">
				<option value="0">{lang_none}</option>
				{select_category}
			</select>
		</td>
	</tr>
	<tr class="row_off">
		<td align=right>
			<span style='font:normal 12px sans-serif'>{lang_title}:</span>
		</td>
		<td>
			<input type=text size=40 name="title" value="{value_title}">
		</td>
	</tr>
	<tr class="row_on">
		<td align=right>
			<span style='font:normal 12px sans-serif'>{lang_topic}:</span>
		</td>
		<td>
			<input type=text size=40 name="topic" value="{value_topic}">
		</td>
	</tr>
	<tr class="row_off">
		<td align=right>
			<span style='font:normal 12px sans-serif'>{lang_keywords}:</span>
		</td>
		<td>
			<input type=text size=40 name="keywords" value="{value_keywords}">
		</td>
	</tr>
</table>
<div style="display: block;height: calc(100% - 180px);margin-top:10px;margin-bottom:10px;">
    {value_text}
</div>
<div>
    {btn_save}{btn_cancel}
</div>
</form>

