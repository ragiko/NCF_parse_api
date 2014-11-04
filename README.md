# NCFのAPI

* EXAMPLE

## すれ違いAPI
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
  
curl http://192.168.56.14/meet?user_id=3RWoUwexIC\&start_date=2014-11-03%2002:09:24\&end_date=201 | json_pp
```
{
   "status" : "exception 'Exception' with message 'DateTime::__construct(): Failed to parse time string (201) at position 0 (2): Unexpected character' in /home/treasure2014/NCF_parse_api/src/meet.php:35\nStack trace:\n#0 /home/treasure2014/NCF_parse_api/src/meet.php(35): DateTime->__construct('201')\n#1 [internal function]: {closure}()\n#2 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Route.php(462): call_user_func_array(Object(Closure), Array)\n#3 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Slim.php(1326): Slim\\Route->dispatch()\n#4 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Middleware/Flash.php(85): Slim\\Slim->call()\n#5 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Middleware/MethodOverride.php(92): Slim\\Middleware\\Flash->call()\n#6 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Middleware/PrettyExceptions.php(67): Slim\\Middleware\\MethodOverride->call()\n#7 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Slim.php(1271): Slim\\Middleware\\PrettyExceptions->call()\n#8 /home/treasure2014/NCF_parse_api/index.php(11): Slim\\Slim->run()\n#9 {main}",
   "data" : []
}
```
  
curl http://192.168.56.14/meet?user_id=3RWoUw\&start_date=2014-11-03%2002:09:24\&end_date=2014-11-03%2002:09:39 | json_pp
```
{
   "status" : "exception 'Parse\\ParseException' with message 'Object not found.' in /home/treasure2014/NCF_parse_api/vendor/parse/php-sdk/src/Parse/ParseQuery.php:72\nStack trace:\n#0 /home/treasure2014/NCF_parse_api/src/meet.php(46): Parse\\ParseQuery->get('3RWoUw')\n#1 [internal function]: {closure}()\n#2 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Route.php(462): call_user_func_array(Object(Closure), Array)\n#3 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Slim.php(1326): Slim\\Route->dispatch()\n#4 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Middleware/Flash.php(85): Slim\\Slim->call()\n#5 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Middleware/MethodOverride.php(92): Slim\\Middleware\\Flash->call()\n#6 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Middleware/PrettyExceptions.php(67): Slim\\Middleware\\MethodOverride->call()\n#7 /home/treasure2014/NCF_parse_api/vendor/slim/slim/Slim/Slim.php(1271): Slim\\Middleware\\PrettyExceptions->call()\n#8 /home/treasure2014/NCF_parse_api/index.php(11): Slim\\Slim->run()\n#9 {main}",
   "data" : []
}
```

curl http://192.168.56.14/meet?user_id=3RWoUw\&start_date=2014-11-03%2002:09:24\&end_ | json_pp
```
{
   "status" : "NotParameterError",
   "data" : []
}
```

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
