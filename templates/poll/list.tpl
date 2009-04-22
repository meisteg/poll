<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{TITLE_SORT}</th>
    <th>{QUESTION_SORT}</th>
    <th>{CREATED_SORT}</th>
    <th>{USERS_ONLY_SORT}</th>
    <th>{ALLOW_COMMENTS_SORT}</th>
    <th>{ACTIVE_SORT}</th>
    <th>{ACTION}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr{TOGGLE}>
    <td>{TITLE}</td>
    <td>{QUESTION}</td>
    <td>{CREATED}</td>
    <td>{USERS_ONLY}</td>
    <td>{ALLOW_COMMENTS}</td>
    <td>{ACTIVE}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
<!-- BEGIN empty_message -->
  <tr{TOGGLE}>
    <td colspan="7">{EMPTY_MESSAGE}</td>
  </tr>
<!-- END empty_message -->
</table>

<!-- BEGIN navigation -->
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<!-- END navigation -->
<!-- BEGIN search -->
<div class="align-right">
{SEARCH}
</div>
<!-- END search -->
