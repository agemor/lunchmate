var express = require('express');

var app = express();
app.set('view engine', 'ejs');

app.get('/', function (req, res) {
  res.render('pages/index', {title: "Lunchmate"});
});

app.listen(8080, function () {
  console.log('Lunchmate service started (port: 8080)');
});
