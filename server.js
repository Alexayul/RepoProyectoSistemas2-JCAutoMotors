const express = require('express');
const path = require('path');
const mongoose = require('mongoose'); //Importa mongoose
const bodyParser = require('body-parser'); //Importa body-parser
const User = require('./models/User'); // Importamos el modelo User

const app = express();
// Middleware para parsear datos de formularios
app.use(express.urlencoded({ extended: true }));

//Servir archivos estáticos
app.use(express.static(path.join(__dirname, 'public')));

// Conexión a MongoDB Atlas
const mongoURI = 'mongodb+srv://jcautomotors2:jcautomotors88!@clusterjcautomotors.gy742.mongodb.net/JCAutomotors?retryWrites=true&w=majority&appName=ClusterJCAutomotors';
mongoose.connect(mongoURI, { 
    useNewUrlParser: true, 
    useUnifiedTopology: true 
})
.then(() => console.log('🔥 Conectado a MongoDB Atlas'))
.catch(err => console.error('❌ Error al conectar a MongoDB:', err));

// Definir el esquema y modelo para User
const userSchema = new mongoose.Schema({
    usuario: String,
    password: String,
    rol: String
}, { collection: 'User' }); // Asegura que use la colección "User"


// Ruta para manejar el login y redirigir según el rol
app.post('/login', async (req, res) => {
    const { usuario, password } = req.body;

    try {
        // Find user with matching username and password
        const user = await User.findOne({ usuario, password });

        if (!user) {
            return res.send('<script>alert("Usuario o contraseña incorrectos"); window.location.href="/login";</script>');
        }

        // Redirect based on role
        switch (user.rol.toLowerCase()) {
            case 'admin':
                return res.redirect('/admin');
            case 'empleado':
                return res.redirect('/empleados');
            case 'cliente':
                return res.redirect('/');
            default:
                return res.send('<script>alert("Rol desconocido"); window.location.href="/login";</script>');
        }
    } catch (error) {
        console.error("Error al buscar usuario:", error);
        res.status(500).send("Error interno del servidor");
    }
});



app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.static('public'));

// Rutas de vistas
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

// Insertar usuarios de prueba solo si la colección está vacía
/*
async function insertarUsuarios() {
    // Check if these specific users already exist
    const existingUsers = await User.find({
        usuario: { $in: ['mauriciomarces', 'alexayul', 'maciiuwu'] }
    });
    
    // Get array of existing usernames
    const existingUsernames = existingUsers.map(user => user.usuario);
    
    // Filter out the users that already exist
    const newUsers = [
        { usuario: 'mauriciomarces', password: '12345678abc', rol: 'admin' },
        { usuario: 'alexayul', password: '987654321top', rol: 'empleado' },
        { usuario: 'maciiuwu', password: '77777777tsf', rol: 'cliente' }
    ].filter(user => !existingUsernames.includes(user.usuario));
    
    // Insert only the new users
    if (newUsers.length > 0) {
        await User.insertMany(newUsers);
        console.log(`✅ ${newUsers.length} nuevos usuarios insertados en la base de datos`);
        console.log('Usuarios insertados:', newUsers.map(u => u.usuario).join(', '));
    } else {
        console.log('⚠️ Todos estos usuarios ya existen en la base de datos');
    }
}

insertarUsuarios().catch(err => console.error('❌ Error al insertar usuarios:', err));*/
/*
async function insertarUsuarios() {
    const count = await User.countDocuments(); // Verifica si ya hay documentos
    if (count === 0) {
        await User.insertMany([
            { usuario: 'cliente1', password: '1234', rol: 'cliente' },
            { usuario: 'cliente2', password: '1234', rol: 'cliente' },
            { usuario: 'empleado1', password: '1234', rol: 'empleado' },
            { usuario: 'empleado2', password: '1234', rol: 'empleado' },
            { usuario: 'admin1', password: '1234', rol: 'admin' },
            { usuario: 'admin2', password: '1234', rol: 'admin' }
        ]);
        console.log('✅ Usuarios insertados en la base de datos');
    } else {
        console.log('⚠️ Los usuarios ya existen en la base de datos');
    }
}

insertarUsuarios().catch(err => console.error('❌ Error al insertar usuarios:', err));
*/

//SE COMENTÓ LA LÍNEA ANTERIOR PARA INSERTAR DOCUMENTOS DE MANERA MANUAL, YA QUE NO SE PUEDE HACER DE MANERA AUTOMÁTICA POR EL MOMENTO
//COMO LOS DOCUMENTOS YA SE INSERTARON NO ES NECESARIO DESCOMENTARLO PERO SE DEJARÁ PARA EL FUTURO EN CASO DE QUE SE REQUIERA

app.listen(3000, () => {
    console.log('🚀 Servidor en http://localhost:3000');
});
