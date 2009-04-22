<div class="poll">
  <div class="box">
    <div class="box-title"><h1>{TITLE}</h1></div>
    <div class="box-content">
      <h3>{MESSAGE}</h3>
      <h2>{QUESTION}</h2>

      <!-- BEGIN listoptions -->
      <div class="padded">
        <strong>{OPTION_LABEL}</strong>
        <table cellpadding="4" cellspacing="0">
          <tr>
            <td style="width:{WIDTH}px" class="bgcolor3">&nbsp;</td>
            <td>{VOTES} / {PERCENT}%</td>
          </tr>
        </table>
      </div>
      <!-- END listoptions -->

      <div class="padded"><h2>{TOTAL_VOTES}</h2></div>
      <!-- BEGIN comments -->{COMMENTS}<!-- END comments -->
    </div>
  </div>
</div>
