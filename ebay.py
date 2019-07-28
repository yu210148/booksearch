#!/usr/bin/python3
import requests, bs4, argparse

#test data
#isbn = '0596158068'
#isbn = '9780596101398'

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("isbn")
    args = parser.parse_args()
    isbn = args.isbn
    url = 'https://www.ebay.com/sch/i.html?_from=R40&_nkw=%s&_sacat=0&_sop=15' % isbn
    res = requests.get(url)
    try:
        res.raise_for_status()
        ebayResult = bs4.BeautifulSoup(res.text, features="lxml")
        itemPrice = ebayResult.select("span.s-item__price")
        itemPrice = str(itemPrice[0])
        itemPrice = itemPrice[29:-7] # trim html from number
        print(itemPrice)
        
    except Exception as exc:
        print('There was a problem: %s' % (exc))


if __name__== "__main__":
  main()
