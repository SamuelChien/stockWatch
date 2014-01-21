from BeautifulSoup import BeautifulSoup
import urllib
import csv
alphabetPage = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"]
file = open("result.txt", "w")
for alphabet in alphabetPage:
	html = urllib.urlopen("http://eoddata.com/stocklist/TSX/"+alphabet+".htm").read()
	soup = BeautifulSoup(html)
	for symbolSection in soup.findAll('tr', 'ro'):
		file.write(symbolSection.find('a').text + ",")
file.close()


