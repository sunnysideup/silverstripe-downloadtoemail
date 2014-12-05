<div class="downloadToEmail">
	<% if FeedbackMessage %><p class="message $FeedbackMessageStyle">$FeedbackMessage</p><% end_if %>
	<% if ShowDownloadForm %>
	<div class="downloadLink">
		<a href="{$Link}dodownload" class="formatAsButton">Download $TitleOfFile</a>
	</div>
	<div class="downloadForm">
		$DownloadForm
	</div>
	<% end_if %>
	<% if AlreadyRequestedSuccessfully %>
		<% if AllowReRequest %>
	<p class="message good"><a href="$ReRequestLink">$AllowReRequestLabel</a></p>
		<% else %>
	<p class="message warning">$DeclineReRequestLabel</p>
		<% end_if %>
	<% end_if %>
</div>
