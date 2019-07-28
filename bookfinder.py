#!/usr/bin/python3
import requests, bs4, argparse
from decimal import Decimal

#TODO FIGURE OUT A WAY TO EXCLUDE RENTALS The example 0596158068 I've been working with now shows up as not available on BF so <shurg>
#test data
#isbn = '0596158068'

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("isbn")
    args = parser.parse_args()
    isbn = args.isbn
    url = 'https://www.bookfinder.com/search/?isbn=%s&st=xl&ac=qr' % isbn
    
    '''
    the site returns different values depending on what the site preferences are set to 
    specifically the setting to include shipping in the resulting price or not
    Patricia at the CADL Lansing Book Burrorw wants the price evaluated without shipping
    the setting at bookfinder.com for this is currently stored in a cookie called search_prefs
    and is set by either bp (book price), or tp (total price)
    
    here I'm going to construct the search_prefs cookie to feed to requests.get with the bp
    value set
    
    '''
    jar = requests.cookies.RequestsCookieJar()
    jar.set('search_prefs', 'pr_il&en-us&pr_disable_my_recent&off&pr_mode&basic&search_used&&search_new&&pr_no_pod&&pr_no_isbn&&pr_lang&en&search_ebooks&&pr_destination&us&pr_ps&bp&pr_currency&USD&pr_classic&off', domain='bookfinder.com', path='/')
    
    res = requests.get(url, cookies=jar)
    #res = requests.get(url)
    
    
    #print(res.cookies['search_prefs'])
    '''
    <Cookie search_prefs=pr_search_used&&pr_search_ebooks&&pr_search_new&&pr_mode&basic&pr_no_pod&&pr_lang&&pr_destination&&pr_currency&&pr_classic&off for .bookfinder.com/>
    pr_il&en-us&pr_disable_my_recent&off&pr_mode&basic&search_used&&search_new&&pr_no_pod&&pr_no_isbn&&pr_lang&en&search_ebooks&&pr_destination&us&pr_ps&bp&pr_currency&USD&pr_classic&off
    
    jar = requests.cookies.RequestsCookieJar()
    jar.set('tasty_cookie', 'yum', domain='httpbin.org', path='/cookies')
    jar.set('gross_cookie', 'blech', domain='httpbin.org', path='/elsewhere')
    url = 'https://httpbin.org/cookies'
    r = requests.get(url, cookies=jar)
    r.text
    '{"cookies": {"tasty_cookie": "yum"}}'
    '''
    
    #print("\n")
    #print(url)
    
    try:
        res.raise_for_status()
        bookFinderResult = bs4.BeautifulSoup(res.text, features="lxml")
        #metaElems = bookFinderResult.find('meta', attrs={"itemprop": "lowPrice"})
        metaElems = bookFinderResult.find_all('span', {'class': 'results-price'})
        
        #print(type(metaElems))
        priceListString = list()
        priceList = list()
        
        for element in metaElems:
            priceListString.append(element.find('a').contents[0][1:])
        
        # list elements are strings at this point, in order to sort the way needed 
        # they must be converted to decimal values
        for price in priceListString:
            priceList.append(Decimal(price))
        
        priceList.sort()
        
        #debug
        #print (type(priceList[0]))
        
        lowPrice = str(priceList[0])
        
        print(lowPrice)
        
    except Exception as exc:
        print('There was a problem: %s' % (exc))


if __name__== "__main__":
  main()
