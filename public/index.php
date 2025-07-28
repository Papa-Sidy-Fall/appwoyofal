<?php

use DevNoKage\Router;

require_once '../app/config/bootstrap.php';

// Charger les routes Woyofal
require_once '../routes/woyofal.php';

Router::resolve();