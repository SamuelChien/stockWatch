<html>
<body>
	<?php
		require_once('class.stockMarketAPI.php');
		$StockMarketAPI = new StockMarketAPI();
		echo $StockMarketAPI->getTableHTMLView("CAN");
	?>
	<a href="/">Combined</a>
	<a href="statesOnly.php">States Only</a>
	<a href="refresh.php">refresh</a>
</body>
</html>