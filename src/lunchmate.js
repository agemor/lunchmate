var express = require('express');
var app = express();

app.get('/', function (req, res) {
  res.send('Hello Worqegqgeld!');
});

app.listen(8080, function () {
  console.log('Lunchmate service started (port: 8080)');
});
