		<p align="center" style="color:#FF0000;">{msg}</p>
    <table width="700" align="center" cellspacing=0 cellpadding=3 style="{border:1px solid #000000;}">
      <tr bgcolor="{tr_off}">
        <td><b>{lang_submitted_by}</b></td>
        <td colspan="2">{username}</td>
      </tr>
      <tr bgcolor="{tr_on}">
        <td><b>{lang_views}</b></td>
        <td colspan="2">{views}</td>
      </tr>
      <tr bgcolor="{tr_off}">
        <td><b>{lang_rating}</b></td>
        <td colspan="2">{rating}</td>
      </tr>
      <tr>
        <td colspan="3"><hr></td>
      </tr>
      <tr bgcolor="{tr_off}">
        <td valign="TOP"><b>{lang_title}</b></td>
        <td colspan="2">{title}</td>
      </tr>
      <tr bgcolor="{tr_on}">
        <td valign="TOP"><b>{lang_text}</b></td>
        <td colspan="2">
          {text}<br>
        </td>
      </tr>
      <tr bgcolor="{tr_off}">
        <td valign="TOP"><b>{lang_related_url}</b></td>
        <td colspan="2">
          {rel_link}<br>
        </td>
      </tr>
      <tr class="navbg" bgcolor="{tr_on}">
        <td colspan=3><b>{lang_rating}</b></td>
      </tr>
      <!-- BEGIN b_rate -->
      <tr bgcolor="{tr_off}">
        <td colspan="3">{lang_rate_why_explain}</td>
      </tr>
      <tr bgcolor="{tr_off}">
        <td>&nbsp;</td>
        <td colspan="2">
           ({lang_poor})&nbsp;|
				<!-- BEGIN click_rating -->
			     <a href="{rate_link}">{rate_val}</a>&nbsp;|&nbsp;
				<!-- END click_rating -->
				({lang_excellent})
        </td>
      </tr>
      <!-- END b_rate -->
      <!-- BEGIN b_no_rate -->
      <tr bgcolor="{tr_off}">
        <td colspan="3">
          {lang_rate_msg}<br>
        </td>
      </tr>
      <!-- END b_no_rate -->
      <tr bgcolor="{tr_on}">
			<td colspan=2><b>{lang_comments}</b></td>
      </tr>
		<!-- BEGIN cmnt -->
      <tr >
        <td width='20%' valign="top"><b>{comment_user}</b></td>
        <td>{comment_text}<br>&nbsp</td>
      </tr>
		<!-- END cmnt -->
      <tr bgcolor="{tr_off}">
  		  <td colspan=2>
				{comment_form}
  		  </td>
      </tr>
      <tr>
    	<td colspan=3 align="center">&nbsp;
    
    	</td>
      </tr>
    </table>

