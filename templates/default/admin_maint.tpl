		<p>{msg}</p>
		<!-- BEGIN pending_block -->
    <h1>{lang_admin_section}</h1>
    <p>{lang_explain_function}</p>
		<form action="{form_action}" method="POST">
    <table border="0" cellspacing=0 width="600">
		<!-- BEGIN pending_list -->
      <tr bgcolor="{row_bg}">
        <td nowrap="nowrap" width="10%" align="center">
          <input type="checkbox" name="{id}"> 
        </td>
        <td>{text} {extra}</td>
      </tr>
		<!-- END pending_list -->
      <tr class="navbg">
        <td width="10%">
          <input type="submit" name="activate" value="{lang_enable}" class="search"> 
        </td>
        <td align="right">
          <input type="submit" name="delete" value="{lang_delete}" class="search"> 
        </td>
      </tr>
    </table>
		</form>
		<!-- END pending_block -->
