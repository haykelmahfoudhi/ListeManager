<?php

define('LM_ROOT', dirname(__FILE__).'/');
define('LM_SRC', 'src/');
define('LM_LIB', LM_ROOT.'lib/');
define('LM_PHPXL', LM_ROOT.'PHPExcel/Classes/');
define('LM_XLS', LM_SRC.'excel/');
define('LM_JS', LM_SRC.'js/');
define('LM_CSS', LM_SRC.'css/');
define('LM_IMG', LM_SRC.'img/');
define('LM_CACHE', LM_ROOT.'cache/');

require_once LM_PHPXL.'PHPExcel.php';

require_once 'pdooci/src/PDO.php';

require_once 'autoloader.php';

?>