<table width="100%" cellpadding=2 cellspacing=0 border=0>
<tr bgcolor="{header_bgcolor}">
 <td nowrap class="navbg">
	<b>{lang_kb_contains}</b> {lang_num_faqs} | {lang_num_tutes} | {lang_num_open} 
 </td>

 <td valign="top" rowspan=2 colspan=2 class="navbg">
		<b>{lang_current_questions}</b><br>

		<!-- BEGIN current_questions -->
		<a href="{cq_url}" class="contrlink">{cq_descr}</a><br>
		<!-- END current_questions -->
 </td>
</tr>
<tr>
	<td bgcolor="{header_bgcolor}" valign=top>
		<FORM ACTION="{search_url}" NAME="search_form" METHOD="POST">
		<TABLE BORDER="0">
		<TR>
			<TD class="navbg">{lang_question}</TD>
			<TD valign="middle" class="navbg">
				<INPUT TYPE=TEXT NAME="search" SIZE=40 VALUE="{current_search}" class="search">
				<input type="submit" name="go" value="{lang_search}" class="search"><br>
				<b>{lang_example}:</b> <a href="{example_url}" class="contrlink">'{example_txt}'</a>
			</TD>
		</TR>
		<TR class="navbg">
			<TD>{lang_show}</TD>
			<TD>
				<INPUT TYPE=RADIO NAME="show" VALUE="" {both_check}>{lang_faqs_and_tutes}
				<INPUT TYPE=RADIO NAME="show" VALUE="1" {faq_check}>{lang_faqs}
				<INPUT TYPE=RADIO NAME="show" VALUE="0" {tut_check}>{lang_tutorials}
			</TD>
		</TR>
	</TABLE>
	</FORM>
	</td>
</tr>

<tr>
 <td class="nav" valign="BOTTOM">
 <ul>
	<li><a href="{link_browse}" class="contrlink">{lang_browse}</a></li>
	<li><a href="{link_add_answer}" class="contrlink">{lang_add_answer}</a></li>
	<li><a href="{link_open_qs}" class="contrlink">{lang_add_q}</font></a></li>
	<li><a href="{link_help}" target="_blank" class="contrlink">{lang_help}</a></li>
 </ul>
<!-- BEGIN admin -->
<!-- admin reports will be added here soon 
	<a href="index.php?mode=highrates&key=*" class="contrlink">Highest ratings</a>
	| <a href="index.php?mode=lowrates&key=*" class="contrlink">Lowest ratings</a>
	| <a href="statistics.php" class="contrlink">Usage Statistics</a>
	| <a href="index.php?mode=unpublished&key=*" class="contrlink">Unpublished (use %1 in lang)</a>
-->
<!-- END admin -->
	</td>
	<td bgcolor="#FFCC66" VALIGN="BOTTOM" colspan=2>&nbsp;</td>
</tr>
</table>
<p>{message}</p>



