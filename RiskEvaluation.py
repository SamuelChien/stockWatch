if __name__ == "__main__":
	next = True
	currentPrice = input('Stock Price: ')
	while next:	
		optionPrice = input('Option Price Per Share: ')
		strikePrice = input('Strike Price: ')
		print str(currentPrice) + "\n"
		print str(optionPrice) + "\n"
		print str(strikePrice) + "\n"
		print "-------------------\n"
		print "Physical Fees Per Contract: $" + str(optionPrice * 100) + "\n"
		print "Future Fees Per Contract: $" + str((strikePrice - currentPrice) * 100) + "\n"
		print "Total Fees Per Contract: $" + str((strikePrice - currentPrice + optionPrice) * 100) + "\n"
		print "Money Borrowed Per Contract: $" + str(currentPrice * 100) + "\n"
		print "Lose Money if stock didn't increase: " + str(round((strikePrice/currentPrice - 1) * 100, 3)) + "% \n"
		print "If is below the strike price, I lose: " + str(round(optionPrice/currentPrice * 100, 3)) + "% \n"
		print "To make 100 percent return, stock need to increase: " + str(round(((strikePrice + 2 * optionPrice)/currentPrice - 1) * 100, 3)) + "% \n"
		print "Percentage To Increase One Fold after target price " + str(round(((optionPrice)/currentPrice) * 100, 3)) + "% \n"
		
		

	
	
	