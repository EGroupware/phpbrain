    <table border="0" width="600" cellspacing="0">
        <tr class="navbg">
          <td colspan="2" align="center">
			  	<a href="{index_url}" class="contrlink">{lang_return_to_index}</a>
			  </td>
        </tr>
    </table>
		<p>{msg}</p>
		<!-- BEGIN open_block -->
    <h1>{lang_cur_open_qs}</h1>
    <p>{lang_know_contrib}</p>
    <table border="0" cellspacing=0 width="600">
		<!-- BEGIN open_list -->
      <tr bgcolor="{row_bg}">
        <td nowrap="nowrap" width="10%">
          <a href="{link_option}">{lang_option}</a> 
        </td>
        <td width="5%">{question_id}</td>
        <td>{question_text}</td>
      </tr>
		<!-- END open_list -->
    </table>
		<!-- END open_block -->
		{question_form}