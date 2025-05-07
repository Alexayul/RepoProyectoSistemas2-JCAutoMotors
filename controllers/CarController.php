<?php
require_once __DIR__ . '/../models/Car.php';
require_once 'BaseController.php';

class CarController extends BaseController {
    public function index() {
        $cars = Car::getAll();
        $this->renderView('cars/index', ['cars' => $cars]);
    }

    public function show($id) {
        $car = Car::findById($id);
        $this->renderView('cars/show', ['car' => $car]);
    }
}
