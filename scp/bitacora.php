<?php

require('staff.inc.php');
//require_once(INCLUDE_DIR.'class.faq.php');
//$category=null;
//if($_REQUEST['cid'] && !($category=Category::lookup($_REQUEST['cid'])))
//    $errors['err']='Unknown or invalid FAQ category';

//$inc='faq-categories.inc.php'; //KB landing page.
/*if($category && $_REQUEST['a']!='search') {
    $inc='faq-category.inc.php';
}
*/
$inc='bitacora.inc.php';
$nav->setTabActive('bitacora');
require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
?>
