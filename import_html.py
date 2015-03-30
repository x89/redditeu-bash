#!/usr/bin/env python3

# generates a postgres sql script to import quotes from html files.

from bs4 import BeautifulSoup
import datetime
import time
import re

day_regex = re.compile(r'(\d+)(st|nd|rd|th)')
imported = set()

def find_quote_info(quote):
	qid = quote.find('a').get_text()
	if qid in imported:
		return
	imported.add(qid)

	text = quote.find(class_='quote-text').get_text()

	score = quote.find(class_='score')
	score = score.get_text()[1:-1]

	date = quote.find(class_='rox').find_previous(text=True).strip()
	date = re.sub(day_regex, r'\1', date)
	date = datetime.datetime.strptime(date, "%b %d %Y")
	timestamp = str(int(time.mktime(date.timetuple())))

	text = text.replace("'", "\\'")
	text = text.replace("\n", "\\n")
	text = text.replace('â€™', "\\'")
	text = text.replace('â€œ', '"')
	text = text.replace('â€”', '*')
	text = text.replace('â€', '"')
	text = text.replace('Â«', '«')
	text = text.replace('Â»', '»')
	text = "E'"+text+"'"

	print("INSERT INTO bc_quotes (\"quote\", \"timestamp\", \"popularity\", \"active\")",
		"VALUES ("+text+", "+timestamp+", "+score+", true);")

def find_quotes(html):
	soup = BeautifulSoup(html)
	quotes = soup.find_all(class_="quote")
	for quote in quotes:
		find_quote_info(quote)

def main():
	for x in range(0,4):
		with open(str(x) + '.html') as f:
			find_quotes(f.read())

if __name__ == '__main__':
	main()
