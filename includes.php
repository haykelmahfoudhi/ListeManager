<?php

defined('LM_ROOT') or die('Veuillez définir la constante LM_ROOT avant d\'utiliser ListManager. Consultez le Wiki pour plus d\'informations');

define('LM_SRC', LM_ROOT.'src/');
define('LM_LIB', LM_ROOT.'lib/');
define('LM_PHPXL', LM_ROOT.'PHPExcel/Classes/');
define('LM_XLS', LM_ROOT.'xls/');
define('LM_XLS_PUBLIC', '/ListManager/xls/');
define('LM_JS', LM_SRC.'js/');
define('LM_IMG', LM_SRC.'img/');
define('LM_CSS', LM_SRC.'css/');

require_once LM_PHPXL.'PHPExcel.php';

require_once LM_ROOT.'pdooci/src/PDO.php';

require_once LM_ROOT.'autoloader.php';
 
?>
