const mongoose = require('mongoose');

const MotoSchema = new mongoose.Schema({
    modelo: { type: String, required: true },
    precio: { type: Number, required: true },
    marca: { type: String, required: true },
    especificaciones: {
        velocidad_maxima: { type: Number, required: true }, // en km/h
        tipo: { type: String, required: true }, // Naked, Sport, etc.
        cilindrada: { type: Number, required: true }, // en cc
        peso: { type: Number, required: true }, // en kg
        combustible: { type: String, required: true }, // Gasolina, Eléctrica, etc.
    },
    imagen: { type: String, required: true } // URL de la imagen
}, { collection: 'Moto' });

const Moto = mongoose.model('Moto', MotoSchema);

module.exports = Moto;
