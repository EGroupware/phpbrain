    <form action="{add_answer_link}" method="POST">
      <input type="HIDDEN" name="faq_id" value="{faq_id}">
		  <input type="HIDDEN" name="is_faq" value="{is_faq}">
      <table border="0">
			<tr class="navbg">
				<td colspan=3 align="center"><h1>{lang_add_answer}</h1></td>
			</tr>
			<tr>
				<td colspan=3>
					<p>{lang_check_before_submit}</p>
				    <p>{lang_not_submit_qs_warn}</p>
					<p>{lang_inspire_by_suggestions}</p>
					<br>
				</td>
			</tr>
        <tr>
          <td valign="TOP">
            <b>{lang_title}</b>
          </td>
          <td colspan="2">
            <input type="TEXT" name="title" size="60" maxlength="120" value="{title}" class="edit">
          </td>
        </tr>
        <tr>
          <td valign="TOP">
            <b>{lang_keywords}</b>
          </td>
          <td colspan="2">
            <input type="TEXT" name="keywords" size="60" maxlength="120" value="{keywords}" class="edit">
          </td>
        </tr>
        <tr>
          <td valign="TOP">
            <b>{lang_category}</b>
          </td>
          <td colspan="2">
            <select name="cat_id">
					{cats_options}
            </select> 
          </td>
        </tr>
        <tr>
          <td valign="TOP">
            <b>{lang_text}</b>
          </td>
          <td colspan="2">
            <textarea name="text" cols="60" rows="20" class="edit">{text}</textarea>
          </td>
        </tr>
        <tr class="navbg">
          <td align="RIGHT" colspan="2">
            <input type="SUBMIT" value="{lang_save}" class="search">
          </td>
          <td align="RIGHT">
            <input type="RESET" value="{lang_reset}" class="search">
          </td>
        </tr>
      </table>
    </form>
<!-- BEGIN admin_button -->
	<!-- i will add functions here delete -->
<!-- END admin_button -->
