#!/usr/bin/python3
import requests, argparse, re

#test data
#isbn = '0596158068'
#isbn = '9780596101398'

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("isbn")
    args = parser.parse_args()
    isbn = args.isbn

    # adding in agent headers as amazon returns 503 without them
    #headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',}
    headers = {'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36'}
    url = 'https://www.amazon.com/s?k=%s&ref=nb_sb_noss' % isbn
    res = requests.get(url, headers=headers)
    try:
        res.raise_for_status()
        #amazonResult = bs4.BeautifulSoup(res.text, features="lxml")
        
        #print(type(amazonResult))
        #print(amazonResult)
        
        #itemPrice = amazonResult.find_all("span", {"class":"a-color-base"})
        #itemPrice = amazonResult.find_all(text=re.compile('<span class\=\"a-color-base\">\$[0-9]*\.[0-9][0-9]</span>'))
        itemPrice = re.findall("<span class\=\"a-color-base\">\$[0-9]*\.[0-9][0-9]</span>", res.text)
        
        
        #print(type(itemPrice))
        #print(itemPrice)
        
        #itemPrice = amazonResult.select("span.s-item__price")
        itemPrice = str(itemPrice[0])
        itemPrice = itemPrice[28:-7] # trim html from number
        print(itemPrice)
        
    except Exception as exc:
        print('There was a problem: %s' % (exc))


if __name__== "__main__":
  main()
