const mongoose = require('mongoose');

const userSchema = new mongoose.Schema({
    usuario: String,     
    password: String,
    rol: String
}, { collection: 'User' });

const User = mongoose.model('User', userSchema);
module.exports = User;