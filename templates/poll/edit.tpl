{START_FORM}
<div class="top-label">
    <div class="padded">
        {TITLE_LABEL}<br />{TITLE}
        <!-- BEGIN title-error --><div class="poll-error">{TITLE_ERROR}</div><!-- END title-error -->
    </div>
    <div class="padded">
        {QUESTION_LABEL}<br />{QUESTION}
        <!-- BEGIN question-error --><div class="poll-error">{QUESTION_ERROR}</div><!-- END question-error -->
    </div>
    <div class="padded">
        <label class="textfield-label">{OPTIONS_LABEL}</label>
        <!-- BEGIN option1 --><br />{OPTION1}<!-- END option1 -->
        <!-- BEGIN option2 --><br />{OPTION2}<!-- END option2 -->
        <!-- BEGIN option3 --><br />{OPTION3}<!-- END option3 -->
        <!-- BEGIN option4 --><br />{OPTION4}<!-- END option4 -->
        <!-- BEGIN option5 --><br />{OPTION5}<!-- END option5 -->
        <!-- BEGIN option6 --><br />{OPTION6}<!-- END option6 -->
        <!-- BEGIN option7 --><br />{OPTION7}<!-- END option7 -->
        <!-- BEGIN option8 --><br />{OPTION8}<!-- END option8 -->
        {ADD_OPTION}
        <!-- BEGIN options-error --><div class="poll-error">{OPTIONS_ERROR}</div><!-- END options-error -->
    </div>
    <div class="padded">
        {USERS_ONLY} {USERS_ONLY_LABEL}<br />
        {ALLOW_COMMENTS} {ALLOW_COMMENTS_LABEL}
    </div>
</div>
{SUBMIT} {CANCEL}
{END_FORM}
