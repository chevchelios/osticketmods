<?php
/*********************************************************************
    slas.php

    SLA - Service Level Agreements

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.ambiente.php');

$amb=null;
if($_REQUEST['id'] && !($amb=  Ambiente::lookup($_REQUEST['id'])))
    $errors['err']='Unknown or invalid API key ID.';

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$amb){
                $errors['err']='Unknown or invalid Ambiente Code.';
            }elseif($amb->update($_POST,$errors)){
                $msg='Ambiente updated successfully';
            }elseif(!$errors['err']){
                $errors['err']='Error updating Ambiente. Try again!';
            }
            break;
        case 'add':
            if(($id=  Ambiente::create($_POST,$errors))){
                $msg='Ambiente added successfully';
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']='Unable to add Ambiente. Correct error(s) below and try again.';
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = 'You must select at least one Ambiente.';
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.AMBIENTE_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                    
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected Ambiente enabled';
                            else
                                $warn = "$num of $count selected Ambientes enabled";
                        } else {
                            $errors['err'] = 'Unable to enable selected Ambientes.';
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.AMBIENTE_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Selected Ambientes disabled';
                            else
                                $warn = "$num of $count selected Ambientes disabled";
                        } else {
                            $errors['err'] = 'Unable to disable selected Ambientes';
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($p=  Ambiente::lookup($v)) && $p->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = 'Selected Ambientes successfully';
                        elseif($i>0)
                            $warn = "$i of $count selected Ambientes deleted";
                        elseif(!$errors['err'])
                            $errors['err'] = 'Unable to delete selected Ambiente';
                        break;
                    default:
                        $errors['err']='Unknown action - get technical help.';
                }
            }
            break;
        default:
            $errors['err']='Unknown action/command';
            break;
    }
}

$page='ambientes.inc.php';
if($amb || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add')))
    $page='ambiente.inc.php';

$nav->setTabActive('manage');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
