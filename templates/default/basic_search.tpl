<table width=100% style='border:1px solid black; margin-bottom:5px'>
	<tr class={class_tr} style="text-align:left">
		<td><b>{lang_search_kb}</b></td>
	</tr>
	<tr>
		<td style='padding:10px 0 10px 0'>
			{lang_enter_words}:<br>
			<form name="search" method="POST" action="{form_search_action}">
				<input type="text" name="query" size=90% value="{query_value}"/>&nbsp;<input type="submit" name="search" value="{lang_search}" />
				&nbsp;&nbsp;<a href="{link_adv_search}">{lang_advanced_search}</a>
			</form>
		</td>
	</tr>
</table>
