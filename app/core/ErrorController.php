<?php

namespace DevNoKage;

use DevNoKage\Abstract\AbstractController;

class ErrorController extends AbstractController
{
    public function _404(): void {
        echo "404";
    }
}
