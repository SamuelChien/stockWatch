<?php 
class StockMarketAPI
{
	function __construct() {
	    $this->dbhost = '127.0.0.1';
		$this->dbuser = 'root';
		$this->dbpass = '';
		$this->connection = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
	}
	
	private function _getSymbols()
	{
		$symbolList = array();
		
		$filename = "./result.txt";
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		$torontoList = explode(",", $contents);
		
		foreach($torontoList as $torontoSymbol)
		{
			if($torontoSymbol != "")
			{
				$tmpSymbol = $torontoSymbol . ".TO";
				$symbolList[$tmpSymbol] = $tmpSymbol;
			}
		}
		
		$NASDAQFile = "companylist.csv";
		$NYSEFile = "companylist2.csv";
		$fileArray = array($NASDAQFile, $NYSEFile);
		foreach($fileArray as $file)
		{
			if (($handle = fopen($file, "r")) !== FALSE) {
			    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
				{
					$symbol = trim($data[0]);
					if($symbol != "Symbol")
					{
						$symbolList[$symbol] = $symbol;
					}
			    }
			    fclose($handle);
			}
		}
		return $symbolList;
	}
	
	
	
	private function _makeData($symbolList)
	{
		$stockDataArray = array();
		$file = "http://download.finance.yahoo.com/d/quotes.csv?s=".implode(",", $symbolList)."&f=snl1p2j1jks6j4ophgee7e8e9rr5yp6r6r7p5s7j2&e=.csv";
		//echo $file . "\n";
		if (($handle = fopen($file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
			{
				//die(print_r($data));				
				
				$marketNumber = substr($data[4], 0, -1);
				$marketDollarUnit = substr($data[4], -1, 1);
				$marketIntegerVal = floatval($marketNumber);
				if($marketDollarUnit == "T")
				{
					$marketIntegerVal= floatval($marketNumber) * 1000000000000;
				}
				else if($marketDollarUnit == "B")
				{
					$marketIntegerVal= floatval($marketNumber) * 1000000000;
				}
				else if($marketDollarUnit == "M")
				{
					$marketIntegerVal= floatval($marketNumber) * 1000000;
				}
				else if($marketDollarUnit == "K")
				{
					$marketIntegerVal= floatval($marketNumber) * 1000;
				}
				
				
				$revenueNumber = substr($data[7], 0, -1);
				$revenueDollarUnit = substr($data[7], -1, 1);
				$revenueIntegerVal = floatval($revenueNumber);
				if($revenueDollarUnit == "T")
				{
					$revenueIntegerVal= floatval($revenueNumber) * 1000000000000;
				}
				else if($revenueDollarUnit == "B")
				{
					$revenueIntegerVal= floatval($revenueNumber) * 1000000000;
				}
				else if($revenueDollarUnit == "M")
				{
					$revenueIntegerVal= floatval($revenueNumber) * 1000000;
				}
				else if($revenueDollarUnit == "K")
				{
					$revenueIntegerVal= floatval($revenueNumber) * 1000;
				}
			
			
				$EBITDANumber = substr($data[8], 0, -1);
				$EBITDADollarUnit = substr($data[8], -1, 1);
				$EBITDAIntegerVal = floatval($EBITDANumber);
				if($EBITDADollarUnit == "T")
				{
					$revenueIntegerVal= floatval($EBITDANumber) * 1000000000000;
				}
				else if($EBITDADollarUnit == "B")
				{
					$EBITDAIntegerVal= floatval($EBITDANumber) * 1000000000;
				}
				else if($EBITDADollarUnit == "M")
				{
					$EBITDAIntegerVal= floatval($EBITDANumber) * 1000000;
				}
				else if($EBITDADollarUnit == "K")
				{
					$EBITDAIntegerVal= floatval($EBITDANumber) * 1000;
				}
				
				$cleanData = array();
				foreach($data as $item)
				{
					if($item == "N/A")
					{
						$cleanData[] = "0";
					}
					else
					{
						$cleanData[] = $item;
					}
				}
				
				$stockDataArray[] = array(
							"symbol"							=> $cleanData[0],
							"name"								=> $cleanData[1],
							"price"								=> $cleanData[2],
							"change"							=> $cleanData[3],
							"marketCap"							=> $cleanData[4],
							"oneYearLow"						=> $cleanData[5],
							"oneYearHigh"						=> $cleanData[6],
							"revenue"							=> $cleanData[7],
							"EBITDA"							=> $cleanData[8],
							"open"								=> $cleanData[9],
							"previousClose"						=> $cleanData[10],
							"dayHigh"							=> $cleanData[11],
							"dayLow"							=> $cleanData[12],
							"dilutedEPS" 						=> $cleanData[13],
							"EPSEstimateCurrentYear"			=> $cleanData[14],
							"EPSEstimateNextQuarter"			=> $cleanData[15],
							"EPSEstimateNextYear"				=> $cleanData[16],
							"PERatio"							=> $cleanData[17],
							"PEGRatio"							=> $cleanData[18],
							"PastAnnualDividendYieldInPercent"	=> $cleanData[19],
							"PriceBook"							=> $cleanData[20],
							"PriceEPSEstimateCurrentYear"		=> $cleanData[21],
							"PriceEPSEstimateNextYear"			=> $cleanData[22],
							"PriceSales" 						=> $cleanData[23],
							"ShortRatio"						=> $cleanData[24],
							"SharesOutstanding"					=> trim($cleanData[25]),
							"marketCapInt" 						=> $marketIntegerVal,
							"revenueInt" 						=> $revenueIntegerVal,
							"EBITDAInt" 						=> $EBITDAIntegerVal
										);	
				
		    }
		    fclose($handle);
		}
		//die(print_r($stockDataArray));
		return $stockDataArray;
	}
	
	private function _databaseInsert($symbolData)
	{
		foreach($symbolData as $stock)
		{
			if(!$this->connection)
			{
			  $this->connection = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
			}

			mysql_select_db('stock');
			
			$sql = 'INSERT INTO quote '.'VALUES (CURDATE(), "'.$stock["symbol"].'","'.$stock["name"].'",'.$stock["price"].',"'.$stock["change"].'","'.$stock["marketCap"].'",'.$stock["marketCapInt"].','.$stock["oneYearLow"].','.$stock["oneYearHigh"].',"'.$stock["revenue"].'",'.$stock["revenueInt"].',"'.$stock["EBITDA"].'",'.$stock["EBITDAInt"].','.$stock["open"].','.$stock["previousClose"].','.$stock["dayHigh"].','.$stock["dayLow"].','.$stock["dilutedEPS"].','.$stock["EPSEstimateCurrentYear"].','.$stock["EPSEstimateNextQuarter"].','.$stock["EPSEstimateNextYear"].','.$stock["PERatio"].','.$stock["PEGRatio"].','.$stock["PastAnnualDividendYieldInPercent"].','.$stock["PriceBook"].','.$stock["PriceEPSEstimateCurrentYear"].','.$stock["PriceEPSEstimateNextYear"].','.$stock["PriceSales"].','.$stock["ShortRatio"].','.$stock["SharesOutstanding"].')';
			
			//echo $sql . "\n";
			$retval = mysql_query( $sql, $this->connection );
			if(! $retval )
			{
			  //echo 'Could not enter data: ' . mysql_error();
			}
			//echo "Entered data successfully\n";
		}
	}
	private function _getPreviousDate()
	{
		if(!$this->connection)
		{
		  $this->connection = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
		}
		mysql_select_db('stock');
		
		$sql = 'select date from quote order by date DESC';
		$result = mysql_query( $sql, $this->connection );

		return mysql_result($result, 0);
		
	}
	
	private function _getStockTableData($viewType)
	{
		if(!$this->connection)
		{
		  $this->connection = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
		}
		mysql_select_db('stock');
		$sql = "";
		$locationFilter = "";
		if($viewType == "CAN")
		{
			$locationFilter = "SUBSTRING(symbol, -3) = '.TO'";
		}
		else if($viewType == "US")
		{
			$locationFilter = "SUBSTRING(symbol, -3) != '.TO' ";
		}
		
		$whereStatement = "(oneYearHigh/price) > 2 AND";
		$sql = "select symbol, quote.name, price, quote.change, marketCap, TRUNCATE((oneYearHigh/price)*100, 2) as potential, TRUNCATE((EBITDAInt/marketCapInt)*100, 2) as earningRate, TRUNCATE(dilutedEPS/price * 100, 2) as EPSP, TRUNCATE((EPSEstimateNextYear/EPSEstimateCurrentYear) *100, 2) as EPSGrowthRate, PEGRatio, PERatio, PastAnnualDividendYieldInPercent as DividendPercentage, PriceBook from quote where " .$whereStatement. " marketCapInt > 500000000 AND symbol NOT IN ('TWGP', 'DXM', 'SWSH') AND date = '" .Date("Y-m-d") . "' ";
		
		if($locationFilter != "")
		{
			$sql = $sql . "AND " . $locationFilter; 
		}
		$sql = $sql . "group by symbol order by potential DESC";
		
		//echo $sql . "\n";
		
		$result = mysql_query( $sql, $this->connection );
		if (!$result) {
		    trigger_error('Invalid query: ' . mysql_error());
		}
		
		$rank = 0;
		//$tableString = "<table><tr><td>Rank</td><td>Symbol</td><td>Name</td><td>Price</td><td>Change</td><td>MarketCap</td><td>Potential(HP/P)</td><td>EarningRate(E/M)</td><td>EPSP(EPS/P)</td><td>EPSGrowthRate</td><td>PEGRatio</td><td>PERatio</td><td>DividendPercentage</td><td>PriceBook</td></tr>";
		$tableString = "<table><tr><td>Rank</td><td>Symbol</td><td>Name</td><td>Price</td><td>Change</td><td>MarketCap</td><td>Potential</td><td>EarningRate</td></tr>";
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$rank+=1;
		    //$tableString = $tableString ."<tr><td>".$rank."</td><td>".$row["symbol"]."</td><td>".$row["name"]."</td><td>$".$row["price"]."</td><td>".$row["change"]."</td><td>".$row["marketCap"].".</td><td>".$row["potential"]."%</td><td>".$row["earningRate"]."%</td><td>".$row["EPSP"]."%</td><td>".$row["EPSGrowthRate"]."</td><td>".$row["PEGRatio"]."</td><td>".$row["PERatio"]."</td><td>".$row["DividendPercentage"]."%</td><td>".$row["PriceBook"]."</td></tr>";
			$tableString = $tableString ."<tr><td>".$rank."</td><td>".$row["symbol"]."</td><td>".$row["name"]."</td><td>$".$row["price"]."</td><td>".$row["change"]."</td><td>".$row["marketCap"].".</td><td>".$row["potential"]."%</td><td>".$row["earningRate"]."%</td></tr>";
		}
		return $tableString . "</table>";
		
	}
	
	
	public function getTableHTMLView($viewType)
	{
		date_default_timezone_set('America/Los_Angeles');
		if($viewType == "REFRESH")
		{
			if(!$this->connection)
			{
			  $this->connection = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
			}
			mysql_select_db('stock');

			$sql = "DELETE FROM quote WHERE date = '".$this->_getPreviousDate()."'";
			
			$result = mysql_query( $sql, $this->connection );
		}
		
		if($this->_getPreviousDate() != Date("Y-m-d"))
		{
			$this->InsertDatabase();
		}
		return $this->_getStockTableData($viewType);
		
	}
	
	public function InsertDatabase()
	{
		$symbolList = $this->_getSymbols();
		$symbolSublist = array();
		$count = 0;
		foreach($symbolList as $symbol)
		{
			$symbolSublist[] = $symbol;
			$count+=1;
			if($count == 200)
			{
				$symbolData = $this->_makeData($symbolSublist);
				//die(print_r($symbolData));
				$this->_databaseInsert($symbolData);
				$symbolSublist = array();
				$count = 0;
			}
		}
		$symbolData = $this->_makeData($symbolSublist);
		//die(print_r($symbolData));
		$this->_databaseInsert($symbolData);
	}
}
