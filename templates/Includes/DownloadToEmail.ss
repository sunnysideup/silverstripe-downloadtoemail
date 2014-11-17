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
</div>
