<?php
/* -*- tab-width: 4; indent-tabs-mode: nil; c-basic-offset: 4 -*- */
/*
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Plume Framework, a simple PHP Application Framework.
# Copyright (C) 2001-2007 Loic d'Anterroches and contributors.
#
# Plume Framework is free software; you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation; either version 2.1 of the License, or
# (at your option) any later version.
#
# Plume Framework is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
# ***** END LICENSE BLOCK ***** */

class Gatuf_Group extends Gatuf_Model {
	public $id;
	public $name;
	public $description;
	
	function __construct () {
		$this->_getConnection ();
		$this->tabla = 'groups';
		
		/* Relacion N-M con los permisos */
		$tabla = 'groups_permissions';
		
		$this->views['__groups_permissions__'] = array ();
		$this->views['__groups_permissions__']['tabla'] = $tabla;
		$this->views['__groups_permissions__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$this->getSqlViewTable ().'.id='.$this->_con->pfx.$tabla.'.group';
		
		/* Relacion N-M con los usuarios */
		$tabla = 'groups_users';
		
		$this->views['__groups_users__'] = array ();
		$this->views['__groups_users__']['tabla'] = $tabla;
		$this->views['__groups_users__']['join'] = ' LEFT JOIN '.$this->_con->pfx.$tabla.' ON '.$this->getSqlViewTable ().'.id='.$this->_con->pfx.$tabla.'.group';
	}
	
	public function getPermissionsList ($p = array ()) {
		$default = array('view' => null,
		                 'filter' => null,
		                 'order' => null,
		                 'start' => null,
		                 'nb' => null,
		                 'count' => false);
		$p = array_merge ($default, $p);
		
		$permi = new Gatuf_Permissions ();
		$sql = new Gatuf_SQL ($this->_con->pfx.$permi->views['__groups_permissions__']['tabla'].'.group=%s', $this->id);
		
		$permi->views['__groups_permissions__']['where'] = $sql->gen ();
		
		$p['view'] = '__groups_permissions__';
		
		return $permi->getList ($p);
	}
	
	function __toString() {
		return $this->name;
	}
}
