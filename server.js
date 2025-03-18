const express = require('express');
const path = require('path');
const mongoose = require('mongoose'); //Importa mongoose

const app = express();

// Conexión a MongoDB Atlas
const mongoURI = 'mongodb+srv://jcautomotors2:jcautomotors88!@clusterjcautomotors.gy742.mongodb.net/?retryWrites=true&w=majority&appName=ClusterJCAutomotors';
mongoose.connect(mongoURI, { 
    useNewUrlParser: true, 
    useUnifiedTopology: true 
})
.then(() => console.log('🔥 Conectado a MongoDB Atlas'))
.catch(err => console.error('❌ Error al conectar a MongoDB:', err));

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.static('public'));

app.get('/', (req, res) => {
    res.render('index');
});

app.get('/login', (req, res) => {
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
