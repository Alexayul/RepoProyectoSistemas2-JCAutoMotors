const express = require('express');
const path = require('path');
const app = express();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.static('public'));

app.get('/', (req, res) => {
    res.render('index');
});

app.get('/', (req, res) => {
    res.render('login'); 
});
app.get('/registro', (req, res) => {
    res.render('registro');
});


app.get('/empleados', (req, res) => {
    res.render('empleados');
});

app.get('/admin', (req, res) => {
    res.render('admin');
});

app.get('/catalogo', (req, res) => {
    res.render('catalogo');
});

app.get('/direccion', (req, res) => {
    res.render('direccion');
});

app.listen(3000, () => {
    console.log('Servidor en http://localhost:3000');
});
