curl -X PUT http://rene:postm4n@localhost:5984/_users/org.couchdb.user:guest -H "Accept: application/json" -H "Content-Type: application/json"      -d '{"_id":"org.couchdb.user:guest", "name": "guest", "password": "guest", "roles": ["guest"], "type": "user"}' 



