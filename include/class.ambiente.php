<?php
/*********************************************************************
    class.sla.php

    SLA

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
class Ambiente {

    var $id;

    var $info;

    function Ambiente($id) {
        $this->id=0;
        $this->load($id);
    }

    function load($id=0) {

        if(!$id && !($id=$this->getId()))
            return false;

        $sql='SELECT * FROM '.AMBIENTE_TABLE.' WHERE id='.db_input($id);
        if(!($res=db_query($sql)) || !db_num_rows($res))
            return false;

        $this->ht=db_fetch_array($res);
        $this->id=$this->ht['id'];
        return true;
    }

    function reload() {
        return $this->load();
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->ht['name'];
    }
        
    function getNotes() {
        return $this->ht['notes'];
    }

    function getHashtable() {
        return  $this->ht;
    }

    function getInfo() {
        return $this->getHashtable();
    }

    function isActive() {
        return ($this->ht['isactive']);
    }

    function update($vars,&$errors) {
        
        if(!Ambiente::save($this->getId(),$vars,$errors))
            return false;

        $this->reload();

        return true;
    }

    function delete() {
        global $cfg;

        if(!$cfg || $cfg->getDefaultAmbienteId()==$this->getId())
            return false;

        $id=$this->getId();
        $sql='DELETE FROM '.AMBIENTE_TABLE.' WHERE id='.db_input($id).' LIMIT 1';
        if(db_query($sql) && ($num=db_affected_rows())) {
            db_query('UPDATE '.TICKET_TABLE.' SET ambiente_id='.db_input($cfg->getDefaultAmbienteId()).' WHERE ambiente_id='.db_input($id));
        }

        return $num;
    }

    /** static functions **/
    function create($vars,&$errors) {
        return Ambiente::save(0,$vars,$errors);
    }
/*get SLAs*/
    function getAmbientes() {

        $slas=array();
        $sql='SELECT id, name, isactive, FROM '.AMBIENTE_TABLE.' ORDER BY name';
        if(($res=db_query($sql)) && db_num_rows($res)) {
            while($row=db_fetch_array($res))
                $ambs[$row['id']] = sprintf('%s (%d hrs - %s)',
                        $row['name'], 
                        $row['isactive']?'Active':'Disabled');
        }

        return $ambs;
    }


    function getIdByName($name) {

        $sql='SELECT id FROM '.AMBIENTE_TABLE.' WHERE name='.db_input($name);
        if(($res=db_query($sql)) && db_num_rows($res))
            list($id)=db_fetch_row($res);

        return $id;
    }

    function lookup($id) {
        return ($id && is_numeric($id) && ($amb= new Ambiente($id)) && $amb->getId()==$id)?$amb:null;
    }

    function save($id,$vars,&$errors) {

/*
        if(!$vars['grace_period'])
            $errors['grace_period']='Grace period required';
        elseif(!is_numeric($vars['grace_period']))
            $errors['grace_period']='Numeric value required (in hours)';
 */           
        if(!$vars['name'])
            $errors['name']='Name required';
        elseif(($sid=  Ambiente::getIdByName($vars['name'])) && $sid!=$id)
            $errors['name']='Name already exists';

        if($errors) return false;

        $sql=' updated=NOW() '.
             ',isactive='.db_input($vars['isactive']).
             ',name='.db_input($vars['name']).
             ',notes='.db_input($vars['notes']);

        if($id) {
            $sql='UPDATE '.AMBIENTE_TABLE.' SET '.$sql.' WHERE id='.db_input($id);
            if(db_query($sql))
                return true;

            $errors['err']='Unable to update Ambiente. Internal error occurred';
        }else{
            $sql='INSERT INTO '.AMBIENTE_TABLE.' SET '.$sql.',created=NOW() ';
            if(db_query($sql) && ($id=db_insert_id()))
                return $id;

            $errors['err']='Unable to add Ambiente. Internal error';
        }

        return false;
    }
}
?>
