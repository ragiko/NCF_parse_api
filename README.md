# NCFのAPI

## ＊注意＊
ドメインは153.121.51.112です。192.168.56.14を書き直して利用してください。

## すれ違いAPI
#### Request
* user_id : ユーザのid
* start_date : 検索したい始めの時間 (2014-11-03%2002:09:24)
* end_date : 検索したい終わりの時間 (2014-11-03%2002:09:39)

#### EXAMPLE
curl http://192.168.56.14/meet?user_id=3RWoUwexIC\&start_date=2014-11-03%2002:09:24\&end_date=2014-11-03%2002:09:39 | json_pp
```
{
   "status" : "success",
   "data" : [
      {
         "youtube_id" : "adK2Fol2SkM",
         "user_id" : "i3uyGwEsEh"
      }
   ]
}
```
  
#### ERROR
* error NotParameterError




## ムードから曲をレコメンドするAPI 
## (V1)
#### Request
* moodid : ムードのid
* userid : ユーザのid

#### EXAMPLE
curl http://192.168.56.14/music?moodid=65326\&userid=iFtJtDtEW1 | json_pp
```
{
   "status" : "success",
   "data" : {
      "artist" : "Lorde",
      "youtube_id" : "MysTo_ssZU4",
      "title" : "Pure Heroine"
   }
}
```




## ムードから曲をレコメンドするAPI 
## 朝は元気な曲, 夜はゆったりした曲をレコメンド
## (V2)

#### Request
* timeid : 午前 or 午後のid (午前: 0, 午後: 1)
* userid : ユーザのid

#### EXAMPLE
curl http://192.168.56.14/v2/music?timeid=0\&userid=iFtJtDtEW1 | json_pp
```
{
   "status" : "success",
   "data" : {
      "artist" : "Whitney Houston",
      "youtube_id" : "OlAaK9W8Ee0",
      "title" : "Whitney"
   }
}
```

#### ERROR
* error [Mood id is wrong]
* error [Mood id is nothing]
