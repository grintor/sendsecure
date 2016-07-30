<html>
	<head>
		<link rel="stylesheet" type="text/css" href="read.css">
	</head>
	<body>
		<div class = 'mailHead'>
			<p><span class = 'vars'>Subject:</span><span><b>{$subject}</b></span></p>
			<p><span class = 'vars'>From:</span><span>{$from}</span></p>
			<p><span class = 'vars'>Date:</span><span>{$date}</span></p>
			<p><span class = 'vars'>To:</span><span>{$to}</span></p>
			{$attachments}
			<p class='options'><span class = 'vars'>Options:</span><span>
				<a href="#" onclick="window.print();">Print this page</a> | 
				<a href="#" onclick="window.print();">View Message Details</a> | 
				<a href="#" onclick="window.print();">Forward</a> | 
				<a href="#" onclick="window.print();">Reply</a> | 
				<a href="#" onclick="window.print();">Reply to all</a>
			</span></p>
		</div>
		<div class = 'mailBody'>
			<span class = "mailMessage">{$message}</span>
		<div>
		</div>
	</body>
</html>
