<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center">
   <tr bgcolor="{th_bg}">
    <td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
<!-- END header -->

<!-- BEGIN body -->
   <tr bgcolor="{row_on}">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr bgcolor="{row_off}">
    <td colspan="2"><b>{lang_phpbrain_config}</b></td>
   </tr>
   <tr bgcolor="{row_on}">
    <td>{lang_allow_html}</td>
    <td>
     <select name="newsettings[allow_html]">
      <option value=""{selected_allow_html_False}>{lang_No}</option>
      <option value="True"{selected_allow_html_True}>{lang_Yes}</option>
     </select>
    </td>
   </tr>
   <tr bgcolor="{row_off}">
    <td>{lang_anon_user}:</td>
    <td>
		{hook_anon_user}
    </td>
   </tr>
<!-- END body -->

<!-- BEGIN footer -->
  <tr bgcolor="{th_bg}">
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
