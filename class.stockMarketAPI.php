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
		$file = "http://download.finance.yahoo.com/d/quotes.csv?s=".implode(",", $symbolList)."&f=s0n0l1p2j1d2k0s6=.csv";
		//echo $file . "\n";
		if (($handle = fopen($file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
			{
				//die(print_r($data));
				$potential = 0;
				if(floatval($data[2]) > 0)
				{
					$potential = floatval($data[6])/floatval($data[2]) * 100;
				}
				
				
				
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
			
				$earningRatio = 0;
				if($marketIntegerVal != 0)
				{ 
					$earningRatio = $revenueIntegerVal/$marketIntegerVal;
				}
				
				$stockDataArray[] = array(
											"symbol"	=> $data[0],
											"name"		=> $data[1],
											"price"		=> $data[2],
											"change"	=> $data[3],
											"marketCap"	=> $data[4],
											"date"		=> $data[5],
											"oneYearTop"=> $data[6],
											"revenue"	=> $data[7],
											"potential"	=> $potential, 
											"earningRatio" => $earningRatio,
											"marketIntegerVal" => $marketIntegerVal,
											"revenueIntegerVal" => $revenueIntegerVal
											
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
			if($stock["price"] != "N/A" && $stock["oneYearTop"] != "N/A")
			{
				mysql_select_db('stock');

				$sql = 'INSERT INTO quote '.'VALUES ("'.$stock["symbol"].'","'.$stock["name"].'",'.$stock["price"].',"'.$stock["change"].'","'.$stock["marketCap"].'","'.$stock["revenue"].'", CURDATE(),'.$stock["oneYearTop"].','.$stock["potential"].','.$stock["earningRatio"].','.$stock["marketIntegerVal"].','.$stock["revenueIntegerVal"].')';
				//echo $sql . "\n";
				$retval = mysql_query( $sql, $this->connection );
				if(! $retval )
				{
				  //echo 'Could not enter data: ' . mysql_error();
				}
				//echo "Entered data successfully\n";
			}
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
		
		if($viewType == "CAN")
		{
			$sql = "select * from quote where SUBSTRING(symbol, -3) = '.TO' AND marketCapInt > 100000000 AND earningRatio > 2 AND potential > 200 AND symbol NOT IN ('TWGP', 'DXM', 'SWSH') AND date = '" .Date("Y-m-d") . "' group by symbol order by potential DESC";
		}
		else if($viewType == "US")
		{
			$sql = "select * from quote where SUBSTRING(symbol, -3) != '.TO' AND marketCapInt > 100000000 AND earningRatio > 2 AND potential > 200 AND symbol NOT IN ('TWGP', 'DXM', 'SWSH') AND date = '" .Date("Y-m-d") . "' group by symbol order by potential DESC";
		}
		else
		{
			$sql = "select * from quote where marketCapInt > 100000000 AND earningRatio > 2 AND potential > 200 AND symbol NOT IN ('TWGP', 'DXM', 'SWSH') AND date = '" .Date("Y-m-d") . "' group by symbol order by potential DESC";
		}
		$result = mysql_query( $sql, $this->connection );
		$rank = 0;
		$tableString = "<table><tr><td>Rank</td><td>Symbol</td><td>Name</td><td>Price</td><td>Change</td><td>MarketCap</td><td>Revenue</td><td>Potential</td><td>EarningRatio</td></tr>";
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$rank+=1;
		    $tableString = $tableString ."<tr><td>".$rank."</td><td>".$row["symbol"]."</td><td>".$row["name"]."</td><td>$".$row["price"]."</td><td>".$row["change"]."</td><td>".$row["marketCap"].".</td><td>".$row["revenue"].".</td><td>".$row["potential"]."%</td><td>".$row["earningRatio"]."</td></tr>";
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
