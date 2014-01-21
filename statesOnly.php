<html>
<body>
	<?php
		require_once('class.stockMarketAPI.php');
		$StockMarketAPI = new StockMarketAPI();
		echo $StockMarketAPI->getTableHTMLView("US");
	?>
	<a href="/">Combined</a>
	<a href="torontoOnly.php">Toronto Only</a>
	<a href="refresh.php">refresh</a>
</body>
</html>