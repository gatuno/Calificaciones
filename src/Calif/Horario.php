<?php

class Calif_Horario extends Gatuf_Model {
	public $id;
	public $nrc;
	public $hora_inicio;
	public $hora_fin;
	public $salon;
	
	public $lunes, $martes, $miercoles, $jueves, $viernes, $sabado;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Horarios';
		$this->default_query = array(
                       'select' => '*',
                       'from' => $this->getSqlTable(),
                       'join' => '',
                       'where' => '',
                       'group' => '',
                       'having' => '',
                       'order' => '',
                       'limit' => '',
                       );
	}
	
	public function restore () {
		$this->lunes = Gatuf_DB_BooleanFromDB ($this->lunes);
		$this->martes = Gatuf_DB_BooleanFromDB ($this->martes);
		$this->miercoles = Gatuf_DB_BooleanFromDB ($this->miercoles);
		$this->jueves = Gatuf_DB_BooleanFromDB ($this->jueves);
		$this->viernes = Gatuf_DB_BooleanFromDB ($this->viernes);
		$this->sabado = Gatuf_DB_BooleanFromDB ($this->sabado);
		$this->hora_inicio = Gatuf_DB_HoraSiiauFromDB ($this->hora_inicio);
		$this->hora_fin = Gatuf_DB_HoraSiiauFromDB ($this->hora_fin);
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (nrc, hora_inicio, hora_fin, salon, lunes, martes, miercoles, jueves, viernes, sabado) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_HoraSiiauToDb ($this->hora_inicio, $this->_con), Gatuf_DB_HoraSiiauToDb ($this->hora_fin, $this->_con), Gatuf_DB_IntegerToDb ($this->salon, $this->_con), Gatuf_DB_BooleanToDB ($this->lunes, $this->_con), Gatuf_DB_BooleanToDB ($this->martes, $this->_con), Gatuf_DB_BooleanToDB ($this->miercoles, $this->_con), Gatuf_DB_BooleanToDB ($this->jueves, $this->_con), Gatuf_DB_BooleanToDB ($this->viernes, $this->_con), Gatuf_DB_BooleanToDB ($this->sabado, $this->_con));
		
		$this->_con->execute ($req);
		
		$this->id = $this->_con->getLastId ();
		
		return true;
	}
	
	function update () {
		$req = sprintf ('UPDATE %s SET hora_inicio=%s, hora_fin=%s, salon=%s, lunes=%s, martes=%s, miercoles=%s, jueves=%s, viernes=%s, sabado=%s WHERE id = %s', $this->getSqlTable(), Gatuf_DB_HoraSiiauToDb ($this->hora_inicio, $this->_con), Gatuf_DB_HoraSiiauToDb ($this->hora_fin, $this->_con), Gatuf_DB_IntegerToDb ($this->salon, $this->_con), Gatuf_DB_BooleanToDB ($this->lunes, $this->_con), Gatuf_DB_BooleanToDB ($this->martes, $this->_con), Gatuf_DB_BooleanToDB ($this->miercoles, $this->_con), Gatuf_DB_BooleanToDB ($this->jueves, $this->_con), Gatuf_DB_BooleanToDB ($this->viernes, $this->_con), Gatuf_DB_BooleanToDB ($this->sabado, $this->_con), Gatuf_DB_IntegerToDb ($this->id, $this->_con));
		
		$this->_con->execute ($req);
		
		return true;
	}
	
	function delete () {
		$req = sprintf ('DELETE FROM %s WHERE id=%s', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->id, $this->_con));
		
		$this->_con->execute ($req);
		
		$this->id = 0;
		return true;
	}
	
	function getHorario ($id) {
		$req = sprintf ('SELECT * FROM %s WHERE id=%s', $this->getSqlTable (), Gatuf_DB_IntegerToDb ($id, $this->_con));
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if (count ($rs) == 0) {
			return false;
		}
		foreach ($rs[0] as $col => $val) {
			$this->$col = $val;
		}
		
		$this->restore ();
		return true;
	}
	
	function displayDias () {
		$cadena = '';
		$letra = array ('L', 'M', 'I', 'J', 'V', 'S');
		foreach (array ('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') as $index => $dia) {
			if ($this->$dia) {
				$cadena .= $letra[$index];
			} else {
				$cadena .= '.';
			}
		}
		return $cadena;
	}
}
