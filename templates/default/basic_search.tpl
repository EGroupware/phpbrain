<div class="PhPBrain_Search">
        <form name="search" method="POST" action="{form_search_action}">
                <table width=100% class="PhPBrain_SearchTable">
                        <tr class={class_tr} style="text-align:left">
                                <td colspan=3><b>{lang_search_kb}</b></td>
                        </tr>
                        <tr>
                                <td colspan=3 style='padding:10px 0 10px 0'>{lang_enter_words}:</td>
                        </tr>
                        <tr>
                                <td style='width:75%; text-align:center'>
                                        <input type="text" name="query" style='width:99%' value="{query_value}"/>
                                </td>
                                <td style='width:5%; text-align:center'>
                                        <input type="submit" name="search" value="{lang_search}" />
                                </td>
                                <td valign=bottom style='width:20%; text-align:left; padding-left:10px'>
                                        <a href="{link_adv_search}">{lang_advanced_search}</a>
                                </td>
                        </tr>
                </table>
        </form>
</div>
