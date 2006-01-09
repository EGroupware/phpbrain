{message}
{search_tpl}
<table width=100% style='border:1px solid black;'>
	{browse_cats}
	<tr>
		<td>
			<table>
				<tr>
					<td>{path}</td>
				</tr>
				<tr>
					<td>
						<table>
							{categories}
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="1" cellpadding="3" >
				<tr>
					<td width=65% valign=top style="padding-right:30px;">
						<table width=100% cellspacing=0 cellpadding=0>
							<tr class={tr_class}>
								<td align=center><b>{lang_articles}</b></td>
							</tr>
							<tr>
								<td style='padding: 0 10px 0 10px' align=center>
									<!-- BEGIN articles_navigation_block -->
									<table border=0 cellpadding=0 cellspacing=0 style="padding-top:5px">
										<tr>
											{left}<td align=center>{num_regs}</td>{right}
										</tr>
									</table>
									<!-- END articles_navigation_block -->
								</td>
							</tr>
							<tr>
								<td>
									<!-- BEGIN articles_block -->
									<i class=kbnum>({art_num}) </i><a href="{art_href}">{art_title}</a><br>
									<div>{lang_last_modified}: {art_date} - {img_stars} {attachment}</div>
									<div style='font-size:80%;color:green'>{art_category}</div>
									{art_topic}<br><br>
									<!-- END articles_block -->
								</td>
							</tr>
						</table>
					</td>
					<td width=35% valign=top>
						<table width=100% border=0 cellspacing=0 cellpadding=0>
							<tr class={tr_class}>
								<td colspan=2 align=center style="font-weight:bold">{lang_latest}</td>
							</tr>
							<!-- BEGIN articles_latest_block -->
							<tr style="background-color:{bg_lists}">
								<td valign=top width=1%>{line_num}.&nbsp;&nbsp;</td>
								<td>
									<a href="{art_href}">{art_title} </a><span style='font-size: 80%'>({art_date})</span><br>
									<span style='font-size:80%;color:green'>{art_category}</span>
								</td>
							</tr>
							<!-- END articles_latest_block -->
							<tr><td colspan=2>&nbsp;</td></tr>
							<tr class={tr_class}>
								<td colspan=2 align=center style="font-weight:bold">{lang_most_viewed}</td>
							</tr>
							<tr>
							<!-- BEGIN articles_mostviewed_block -->
							<tr style="background-color:{bg_lists}">
								<td valign=top width=1%>{line_num}.&nbsp;&nbsp;</td>
								<td>
									<a href="{art_href}">{art_title} </a><span style='font-size: 80%'>({art_views} {lang_views})</span><br>
									<span style='font-size:80%;color:green'>{art_category}</span>
								</td>
							</tr>
							<!-- END articles_mostviewed_block -->
							<tr><td colspan=2>&nbsp;</td></tr>
							</tr>
							<tr class={tr_class}>
								<td colspan=2 align=center style="font-weight:bold">{lang_unanswered}</td>
							</tr>
							<tr>
							<!-- BEGIN unanswered_questions_block -->
							<tr style="background-color:{bg_lists}">
								<td valign=top width=1%>&bull;&nbsp;&nbsp;</td>
								<td>
									<a href="{art_href}">{art_title}</a> ({who})<br>
									<span style='font-size:80%;color:green'>{unanswered_category}</span>
								</td>
							</tr>
							<!-- END unanswered_questions_block -->
							<tr><td colspan=2>{more_questions}</td></tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
