<style>
	td
	{
		text-align: left;
	}
</style>
<center>
<form name="adv_search_form" method="POST" action="{form_action}">
	<table style="border:1px solid black">
		<tr class="th">
			<th colspan="2" style="text-align:center">{lang_advanced_search}</th>
		</tr>
		<tr class="row_off">
			<td colspan=2>
				<table>
					<tr>
						<td valign="top" width=50>{lang_find}</td>
						<td>
							<table>
								<tr>
									<td>{lang_all_words}:</td>
									<td><input type="text" size=50 name="all_words"></td>
								</tr>
								<tr>
									<td>{lang_phrase}:</td>
									<td><input type="text" size=50 name="phrase"></td>
								</tr>
								<tr>
									<td>{lang_one_word}:</td>
									<td><input type="text" size=50 name="one_word"></td>
								</tr>
								<tr>
									<td>{lang_without_word}</td>
									<td><input type="text" size=50 name="without_words"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr class="row_on">
			<td width=150>{lang_show_cats}:</td>
			<td><select name="cat"><option value="0">{lang_all}</option>{select_categories}</select>&nbsp;&nbsp;{lang_include_subs}: <input type="checkbox" name="include_subs" value=True></td>
		</tr>
		<tr class="row_off">
			<td>{lang_pub_date}:</td>
			<td>
				<select name="pub_date">
					<option value="0" selected>{lang_anytime}</option>
					<option value="3">{lang_3_months}</option>
					<option value="6">{lang_6_months}</option>
					<option value="year">{lang_past_year}</option>
				</select>
			</td>
		</tr>
		<tr class="row_on">
			<td>{lang_ocurrences}:</td>
			<td>
				<select name="ocurrences">
					<option value="0" selected>{lang_anywhere}</option>
					<option value="title">{lang_in_title}</option>
					<option value="topic">{lang_in_topic}</option>
					<option value="text">{lang_in_text}</option>
				</select>
			</td>
		</tr>
		<tr class="row_off">
			<td>{lang_num_res}:</td>
			<td>
				<select name="num_res">
					<option value="0" selected>{lang_user_prefs}</option>
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="50">50</option>
					<option value="100">100</option>
				</select>
			</td>
		</tr>
		<tr class="row_on">
			<td>{lang_order}</td>
			<td>
				<select name="order">
					<option value="created" selected>{lang_created}</option>
					<option value="art_id">{lang_artid}</option>
					<option value="title">{lang_title}</option>
					<option value="user_id">{lang_user}</option>
					<option value="modified">{lang_modified}</option>
				</select>
				&nbsp;&nbsp;<select name="sort"><option value="DESC" selected>{lang_desc}</option><option value="ASC">{lang_asc}</option></select>
			</td>
		</tr>
		<tr>
			<td colspan=2 align=center style="padding: 10px 0 10px 0">
				<input type="submit" name="adv_search" value="{lang_search}">
			</td>
		</tr>
	</table>
</form>
</center>
