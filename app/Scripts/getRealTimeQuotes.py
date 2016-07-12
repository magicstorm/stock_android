#!/usr/bin/python
import tushare as ts
import sys



def constructCodes():
    codes = sys.argv[1:]
    return codes

def getQuotes(codes):
    df = ts.get_realtime_quotes(codes)
    print df.to_json(orient='records')

if __name__ == "__main__":
    getQuotes(constructCodes())
