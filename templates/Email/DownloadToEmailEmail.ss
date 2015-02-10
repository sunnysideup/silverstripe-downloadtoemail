<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>$EmailSubject</title>
</head>

<body>

Email Subject: $EmailSubject<br />

Title of File: $TitleOfFile<br />

<% if HasHasFile %>

	Valid Until: $ValidUntil<br />

	<% with File %>$Link<% end_with %><br />

	Download Link: $DownloadLink<br />

	File Location: $FileLocation
<% else %>
	<% if HasLink %>

	Place to download: $LinkToThirdPartyDownload

	<% else %>

	<p>ERROR: no download found...</p>

	<% end_if %>

<% end_if %>

</body>

</html>
