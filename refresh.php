<html>
<body>
	<?php
		require_once('class.stockMarketAPI.php');
		$StockMarketAPI = new StockMarketAPI();
		echo $StockMarketAPI->getTableHTMLView("REFRESH");
	?>
	<a href="/">Combined</a>
	<a href="statesOnly.php">States Only</a>
	<a href="torontoOnly.php">Toronto Only</a>
</body>
</html>