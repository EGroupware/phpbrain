    <form action="{add_answer_link}" method="POST">
      <input type="HIDDEN" name="faq_id" value="{faq_id}">
		  <input type="HIDDEN" name="is_faq" value="{is_faq}">
      <table width="700" align="center" cellspacing=0 cellpadding=3 style="{border:1px solid #000000;}">
			<tr bgcolor="{tr_off}">
				<td colspan=3>
					<p>{lang_check_before_submit}</p>
				    <p>{lang_not_submit_qs_warn}</p>
					<p>{lang_inspire_by_suggestions}</p>
					<br>
				</td>
			</tr>
        <tr bgcolor="{tr_on}">
          <td valign="TOP">
            <b>{lang_title}</b>
          </td>
          <td colspan="2">
            <input type="TEXT" name="title" size="60" maxlength="120" value="{title}" class="edit">
          </td>
        </tr>
        <tr bgcolor="{tr_off}">
          <td valign="TOP">
            <b>{lang_keywords}</b>
          </td>
          <td colspan="2">
            <input type="TEXT" name="keywords" size="60" maxlength="120" value="{keywords}" class="edit">
          </td>
        </tr>
        <tr bgcolor="{tr_on}">
          <td valign="TOP">
            <b>{lang_category}</b>
          </td>
          <td colspan="2">
            <select name="cat_id">
		{cats_options}
            </select> 
          </td>
        </tr>
        <tr bgcolor="{tr_off}">
          <td valign="TOP">
            <b>{lang_related_url}</b>
          </td>
          <td colspan="2">
            <input type="TEXT" name="url" size="60" maxlength="120" value="{url}" class="edit">
          </td>
        </tr>
	<!-- BEGIN b_status -->
	<tr>
          <td valign="TOP">
            <b>{lang_status}</b>
          </td>
          <td colspan="2">
            <input type="checkbox" name="published" value=1 {check}>  {lang_active_when_checked}
          </td>
        </tr>
	<!-- END b_status -->
        <tr bgcolor="{tr_on}">
          <td valign="TOP">
            <b>{lang_text}</b>
          </td>
          <td colspan="2">
            <textarea name="text" cols="55" rows="20" class="edit">{text}</textarea>
          </td>
        </tr>
        <tr class="navbg">
          <td align="LEFT">
            <input type="SUBMIT" name="save" value="{lang_save}" class="search">
          	<input type="submit" name="cancel" value="{lang_submit_cancel}" class="search">
          </td>
        </tr>
      </table>
    </form>
<!-- BEGIN admin_button -->
	<!-- i will add functions here delete -->
<!-- END admin_button -->
