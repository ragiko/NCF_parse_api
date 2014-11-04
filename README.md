# NCFのAPI

## すれ違いAPI

comming soon ...

## ムードから曲をレコメンドするAPI

* EXAMPLE
curl http://153.121.51.112/NCF_parse_api/gracenote?moodid=1 | json_pp
```
{
       "status" : "error [Mood id is wrong]",
          "data" : []
}
```
  
curl http://153.121.51.112/NCF_parse_api/gracenote | json_pp
```
{
       "status" : "error [Mood id is nothing]",
          "data" : []
}
```
  
curl http://192.168.56.14/gracenote?moodid=42961 | json_pp
```
{
   "status" : "success",
   "data" : {
      "artist" : "Ke$ha",
      "youtube_id" : "RDPNGX6UMl8",
      "title" : "Animal"
   }
}
```
