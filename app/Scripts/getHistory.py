#!/usr/bin/python
import tushare as ts
import pandas
import sys
import os

def getHistory(code, start, end):
    # print code + start + end
    console = sys.stdout
    sys.stderr = open(os.devnull, 'w')
    sys.stdout = open(os.devnull, 'w')
    df = ts.get_h_data(code, start, end)
    sys.stdout = console
    print df.to_json(orient="records")

if __name__ == "__main__":
    getHistory(sys.argv[1],sys.argv[2], sys.argv[3])