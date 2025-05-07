<?php

class BaseController {
    protected function renderView($view, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $view . '.php';
    }
}
